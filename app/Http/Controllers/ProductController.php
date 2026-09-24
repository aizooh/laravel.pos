<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::query();

        if ($search = $request->input('q')) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('low_stock')) {
            $q->lowStock();
        }

        $products = $q->orderBy('name')->paginate(15)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120'],
            'sku'           => ['required', 'string', 'max:50', 'unique:products,sku'],
            'category'      => ['nullable', 'string', 'max:60'],
            'buying_price'  => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock'         => ['required', 'integer', 'min:0'],
            'low_stock'     => ['required', 'integer', 'min:0'],
        ]);

        $data['active'] = true;

        $product = Product::create($data);

        // record opening stock as an adjustment
        if ($product->stock > 0) {
            StockAdjustment::create([
                'product_id' => $product->id,
                'user_id'    => $request->user()->id,
                'type'       => 'purchase',
                'quantity'   => $product->stock,
                'reason'     => 'Opening stock',
            ]);
        }

        return redirect()->route('products.index')
            ->with('ok', "Product '{$product->name}' created.");
    }

    public function edit(Product $product)
    {
        $adjustments = $product->adjustments()
            ->with('user')
            ->latest()
            ->limit(30)
            ->get();

        return view('products.edit', compact('product', 'adjustments'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120'],
            'sku'           => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'category'      => ['nullable', 'string', 'max:60'],
            'buying_price'  => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'low_stock'     => ['required', 'integer', 'min:0'],
            'active'        => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active');

        $product->update($data);

        return redirect()->route('products.edit', $product)
            ->with('ok', 'Product updated.');
    }

    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'type'     => ['required', Rule::in(['purchase', 'adjustment', 'damage', 'return'])],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reason'   => ['nullable', 'string', 'max:120'],
        ]);

        // for purchase/return: positive. For damage: we subtract.
        $qty = (int) $data['quantity'];

        if (in_array($data['type'], ['damage'])) {
            $qty = -abs($qty);
        } elseif (in_array($data['type'], ['purchase', 'return'])) {
            $qty = abs($qty);
        }

        $newStock = $product->stock + $qty;
        if ($newStock < 0) {
            return back()->withErrors(['quantity' => 'Stock cannot go below zero.']);
        }

        $product->update(['stock' => $newStock]);

        StockAdjustment::create([
            'product_id' => $product->id,
            'user_id'    => $request->user()->id,
            'type'       => $data['type'],
            'quantity'   => $qty,
            'reason'     => $data['reason'] ?? null,
        ]);

        return redirect()->route('products.edit', $product)
            ->with('ok', "Stock updated. New stock: {$newStock}.");
    }
}