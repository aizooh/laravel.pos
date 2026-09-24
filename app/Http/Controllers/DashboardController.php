<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $today = today();

        if ($user->isAdmin()) {
            return $this->adminDashboard($today);
        }

        return $this->attendantDashboard($user, $today);
    }

    // ------------------------------------------------------------

    private function adminDashboard($today)
    {
        // Today's sales
        $todaySales      = Sale::whereDate('created_at', $today);
        $todaySalesTotal = (float) (clone $todaySales)->sum('total');
        $todayTxCount    = (clone $todaySales)->count();

        // Today's cost of goods sold (product items only, using snapshot buying_price)
        $todayCogs = (float) SaleItem::whereHas('sale', function ($q) use ($today) {
                $q->whereDate('created_at', $today);
            })
            ->where('item_type', 'product')
            ->selectRaw('COALESCE(SUM(buying_price * qty), 0) as cogs')
            ->value('cogs');

        $todayGrossProfit = $todaySalesTotal - $todayCogs;

        // Today's expenses
        $todayExpenses = (float) Expense::whereDate('date', $today)->sum('amount');

        $todayNet = $todayGrossProfit - $todayExpenses;

        // Payment breakdown
        $paymentBreakdown = Sale::whereDate('created_at', $today)
            ->selectRaw('payment_method, SUM(total) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        // Low stock
        $lowStockProducts = Product::active()->lowStock()->orderBy('stock')->limit(10)->get();
        $lowStockCount    = Product::active()->lowStock()->count();

        // Recent sales (last 10)
        $recentSales = Sale::with('user')->latest()->limit(10)->get();

        return view('dashboard', [
            'mode'             => 'admin',
            'today'            => $today,
            'todaySalesTotal'  => $todaySalesTotal,
            'todayTxCount'     => $todayTxCount,
            'todayCogs'        => $todayCogs,
            'todayGrossProfit' => $todayGrossProfit,
            'todayExpenses'    => $todayExpenses,
            'todayNet'         => $todayNet,
            'paymentBreakdown' => $paymentBreakdown,
            'lowStockProducts' => $lowStockProducts,
            'lowStockCount'    => $lowStockCount,
            'recentSales'      => $recentSales,
        ]);
    }

    // ------------------------------------------------------------

    private function attendantDashboard($user, $today)
    {
        $mySalesQuery = Sale::where('user_id', $user->id)->whereDate('created_at', $today);
        $myTotal      = (float) (clone $mySalesQuery)->sum('total');
        $myTxCount    = (clone $mySalesQuery)->count();

        $myRecent = Sale::where('user_id', $user->id)
            ->latest()->limit(5)->get();

        return view('dashboard', [
            'mode'      => 'attendant',
            'today'     => $today,
            'myTotal'   => $myTotal,
            'myTxCount' => $myTxCount,
            'myRecent'  => $myRecent,
        ]);
    }
}