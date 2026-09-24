@extends('layouts.app')

@section('title', 'Edit ' . $product->name . ' — Mamicar POS')

@section('content')

<div class="card">
    <h2>Edit Product</h2>

    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')

        <div class="grid">
            <div class="field">
                <label>Product Name</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="field">
                <label>SKU</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required>
            </div>
        </div>

        <div class="field">
            <label>Category</label>
            <input type="text" name="category" value="{{ old('category', $product->category) }}">
        </div>

        <div class="grid-3">
            <div class="field">
                <label>Buying Price</label>
                <input type="number" step="0.01" name="buying_price"
                       value="{{ old('buying_price', $product->buying_price) }}" required>
            </div>
            <div class="field">
                <label>Selling Price</label>
                <input type="number" step="0.01" name="selling_price"
                       value="{{ old('selling_price', $product->selling_price) }}" required>
            </div>
            <div class="field">
                <label>Low Stock Alert</label>
                <input type="number" name="low_stock"
                       value="{{ old('low_stock', $product->low_stock) }}" required>
            </div>
        </div>

        <div class="field">
            <label style="display:flex; align-items:center; gap:0.5rem;">
                <input type="checkbox" name="active" value="1" {{ $product->active ? 'checked' : '' }}>
                Active (available for sale)
            </label>
        </div>

        <div style="display:flex; gap:0.5rem;">
            <button type="submit">Save Changes</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to Inventory</a>
        </div>
    </form>
</div>

<div class="card">
    <h2>Stock: {{ $product->stock }}
        @if ($product->isLowStock())
            <span class="badge badge-danger">LOW STOCK</span>
        @endif
    </h2>

    <form method="POST" action="{{ route('products.adjust', $product) }}">
        @csrf

        <div class="grid-3">
            <div class="field">
                <label>Type</label>
                <select name="type" required>
                    <option value="purchase">Purchase (add stock)</option>
                    <option value="return">Customer Return (add stock)</option>
                    <option value="damage">Damage / Loss (subtract)</option>
                    <option value="adjustment">Manual Adjustment</option>
                </select>
            </div>
            <div class="field">
                <label>Quantity</label>
                <input type="number" name="quantity" value="1" required>
            </div>
            <div class="field">
                <label>Reason (optional)</label>
                <input type="text" name="reason" placeholder="e.g. New stock received">
            </div>
        </div>

        <button type="submit">Apply Adjustment</button>
    </form>

    <h3>Recent Adjustments</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Reason</th>
                <th>By</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($adjustments as $a)
            <tr>
                <td class="muted">{{ $a->created_at->format('d M Y H:i') }}</td>
                <td><span class="badge badge-muted">{{ $a->type }}</span></td>
                <td>{{ $a->quantity > 0 ? '+' : '' }}{{ $a->quantity }}</td>
                <td class="muted">{{ $a->reason ?? '—' }}</td>
                <td class="muted">{{ $a->user->name ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted" style="text-align:center;">No adjustments yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection