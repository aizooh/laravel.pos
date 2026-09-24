<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $showVoided = $request->boolean('show_voided');

        $q = $showVoided
            ? Sale::withVoided()->with('user', 'voidedBy')->withCount('items')
            : Sale::with('user', 'voidedBy')->withCount('items');

        // Attendants only see their own sales
        if (! $request->user()->isAdmin()) {
            $q->where('user_id', $request->user()->id);
        }

        if ($from = $request->input('from')) {
            $q->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        if ($method = $request->input('method')) {
            $q->where('payment_method', $method);
        }

        $sales = $q->latest()->paginate(20)->withQueryString();

        // Summary reflects only non-voided sales.
        $summaryQuery = Sale::query();

        if (! $request->user()->isAdmin()) {
            $summaryQuery->where('user_id', $request->user()->id);
        }

        if ($from) {
            $summaryQuery->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $summaryQuery->whereDate('created_at', '<=', $to);
        }

        if ($method) {
            $summaryQuery->where('payment_method', $method);
        }

        $summary = [
            'count' => $summaryQuery->count(),
            'total' => (float) $summaryQuery->sum('total'),
        ];

        return view('sales.index', compact(
            'sales',
            'summary',
            'showVoided'
        ));
    }

    public function receipt(Request $request, Sale $sale)
    {
        if (! $request->user()->isAdmin() && $sale->user_id !== $request->user()->id) {
            abort(403);
        }

        $sale->load('items', 'user', 'voidedBy');

        return view('sales.receipt', compact('sale'));
    }

    public function void(Request $request, Sale $sale)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Admins only.');
        }

        if ($sale->isVoided()) {
            return back()->withErrors([
                'void' => 'This sale is already voided.',
            ]);
        }

        $data = $request->validate([
            'void_reason' => ['required', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($sale, $data, $request) {
            // Restore stock for product items and log each adjustment.
            foreach ($sale->items as $item) {
                if ($item->item_type === 'product') {
                    $product = Product::find($item->item_id);

                    if ($product) {
                        $product->increment('stock', $item->qty);

                        StockAdjustment::create([
                            'product_id' => $product->id,
                            'user_id'    => $request->user()->id,
                            'type'       => 'adjustment',
                            'quantity'   => $item->qty,
                            'reason'     => 'Void ' . $sale->invoice_no .
                                            ' — ' . $data['void_reason'],
                        ]);
                    }
                }
            }

            // Mark the sale as voided.
            $sale->update([
                'voided_at'   => now(),
                'voided_by'   => $request->user()->id,
                'void_reason' => $data['void_reason'],
            ]);
        });

        return redirect()
            ->route('sales.receipt', $sale)
            ->with(
                'ok',
                "Sale {$sale->invoice_no} voided. Stock restored."
            );
    }

    public function unvoid(Request $request, Sale $sale)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Admins only.');
        }

        if (! $sale->isVoided()) {
            return back()->withErrors(['void' => 'This sale is not voided.']);
        }

        // Pre-flight: enough stock to subtract again?
        foreach ($sale->items as $item) {
            if ($item->item_type === 'product') {
                $product = Product::find($item->item_id);
                if (! $product) {
                    continue; // product was deleted, skip
                }
                if ($product->stock < $item->qty) {
                    return back()->withErrors([
                        'void' => "Cannot unvoid — {$product->name} only has {$product->stock} in stock (needs {$item->qty}).",
                    ]);
                }
            }
        }

        DB::transaction(function () use ($sale, $request) {
            foreach ($sale->items as $item) {
                if ($item->item_type === 'product') {
                    $product = Product::find($item->item_id);
                    if ($product) {
                        $product->decrement('stock', $item->qty);

                        StockAdjustment::create([
                            'product_id' => $product->id,
                            'user_id'    => $request->user()->id,
                            'type'       => 'adjustment',
                            'quantity'   => -$item->qty, // negative: stock goes back down
                            'reason'     => 'Unvoid ' . $sale->invoice_no,
                        ]);
                    }
                }
            }

            // restore the sale: clear void fields
            $sale->update([
                'voided_at'   => null,
                'voided_by'   => null,
                'void_reason' => null,
            ]);
        });

        return redirect()->route('sales.receipt', $sale)
            ->with('ok', "Sale {$sale->invoice_no} restored. Stock deducted again.");
    }
}