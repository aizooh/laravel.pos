<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ---------- hub ----------
    public function index()
    {
        $today = today();

        $todaySales = (float) Sale::whereDate('created_at', $today)->sum('total');
        $todayCount = Sale::whereDate('created_at', $today)->count();

        return view('reports.index', compact('today', 'todaySales', 'todayCount'));
    }

    // ---------- daily ----------
    public function daily(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : today();

        $sales = Sale::with('user', 'items')
            ->whereDate('created_at', $date)
            ->orderBy('created_at')
            ->get();

        $saleIds = $sales->pluck('id');

        // totals
        $totalSales = (float) $sales->sum('total');
        $txCount    = $sales->count();

        // payment breakdown
        $payments = [
            'cash'  => (float) $sales->where('payment_method', 'cash')->sum('total'),
            'mpesa' => (float) $sales->where('payment_method', 'mpesa')->sum('total'),
            'card'  => (float) $sales->where('payment_method', 'card')->sum('total'),
        ];

        // COGS (products only, from snapshot)
        $cogs = (float) SaleItem::whereIn('sale_id', $saleIds)
            ->where('item_type', 'product')
            ->selectRaw('COALESCE(SUM(buying_price * qty), 0) as c')
            ->value('c');

        $grossProfit = $totalSales - $cogs;

        // Expenses for the day
        $expenses     = (float) Expense::whereDate('date', $date)->sum('amount');
        $netProfit    = $grossProfit - $expenses;
        $expenseLines = Expense::whereDate('date', $date)->orderBy('category')->get();

        // Top-selling items
        $topItems = SaleItem::whereIn('sale_id', $saleIds)
            ->select('name', 'item_type',
                DB::raw('SUM(qty) as qty'),
                DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('name', 'item_type')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // Sales by attendant
        $byAttendant = $sales->groupBy('user_id')->map(function ($group) {
            return [
                'name'  => $group->first()->user->name ?? '—',
                'count' => $group->count(),
                'total' => (float) $group->sum('total'),
            ];
        })->sortByDesc('total')->values();

        return view('reports.daily', compact(
            'date', 'sales', 'totalSales', 'txCount', 'payments',
            'cogs', 'grossProfit', 'expenses', 'netProfit', 'expenseLines',
            'topItems', 'byAttendant'
        ));
    }

    // ---------- monthly ----------
    public function monthly(Request $request)
    {
        $monthInput = $request->input('month'); // format: YYYY-MM
        $month = $monthInput
            ? Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth()
            : now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $sales = Sale::with('user')
            ->whereBetween('created_at', [$start, $end->copy()->endOfDay()])
            ->get();

        $saleIds = $sales->pluck('id');

        $totalSales = (float) $sales->sum('total');
        $txCount    = $sales->count();

        $payments = [
            'cash'  => (float) $sales->where('payment_method', 'cash')->sum('total'),
            'mpesa' => (float) $sales->where('payment_method', 'mpesa')->sum('total'),
            'card'  => (float) $sales->where('payment_method', 'card')->sum('total'),
        ];

        $cogs = (float) SaleItem::whereIn('sale_id', $saleIds)
            ->where('item_type', 'product')
            ->selectRaw('COALESCE(SUM(buying_price * qty), 0) as c')
            ->value('c');

        $grossProfit = $totalSales - $cogs;

        $expenses  = (float) Expense::whereBetween('date', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $netProfit = $grossProfit - $expenses;

        // top products
        $topProducts = SaleItem::whereIn('sale_id', $saleIds)
            ->where('item_type', 'product')
            ->select('name',
                DB::raw('SUM(qty) as qty'),
                DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // top services
        $topServices = SaleItem::whereIn('sale_id', $saleIds)
            ->where('item_type', 'service')
            ->select('name',
                DB::raw('SUM(qty) as qty'),
                DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // sales by attendant
        $byAttendant = $sales->groupBy('user_id')->map(function ($group) {
            return [
                'name'  => $group->first()->user->name ?? '—',
                'count' => $group->count(),
                'total' => (float) $group->sum('total'),
            ];
        })->sortByDesc('total')->values();

        // daily series for the month
        $dailySeries = Sale::whereBetween('created_at', [$start, $end->copy()->endOfDay()])
            ->selectRaw("strftime('%d', created_at) as day, SUM(total) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // expenses by category
        $expensesByCategory = Expense::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return view('reports.monthly', compact(
            'month', 'totalSales', 'txCount', 'payments',
            'cogs', 'grossProfit', 'expenses', 'netProfit',
            'topProducts', 'topServices', 'byAttendant',
            'dailySeries', 'expensesByCategory'
        ));
    }

    // ---------- inventory ----------
    public function inventory(Request $request)
    {
        $q = Product::query();

        if ($request->boolean('low_stock')) {
            $q->lowStock();
        }
        if ($request->boolean('active_only')) {
            $q->active();
        }

        $products = $q->orderBy('name')->get();

        $totalCost    = $products->sum(fn ($p) => $p->stock * (float) $p->buying_price);
        $totalRetail  = $products->sum(fn ($p) => $p->stock * (float) $p->selling_price);
        $totalUnits   = $products->sum('stock');
        $potentialProfit = $totalRetail - $totalCost;

        return view('reports.inventory', compact(
            'products', 'totalCost', 'totalRetail', 'totalUnits', 'potentialProfit'
        ));
    }

    // ---------- void / unvoid log ----------
    public function voids(Request $request)
    {
        $from = $request->input('from') ?: now()->subDays(30)->toDateString();
        $to   = $request->input('to')   ?: today()->toDateString();

        $logs = StockAdjustment::with('product', 'user')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where(function ($q) {
                $q->where('reason', 'like', 'Void %')
                  ->orWhere('reason', 'like', 'Unvoid %');
            })
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('reports.voids', compact('logs', 'from', 'to'));
    }
}