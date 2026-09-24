@extends('layouts.app')

@section('title', 'Inventory — Mamicar POS')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin:0;">Inventory</h2>
        <a href="{{ route('products.create') }}" class="btn">+ New Product</a>
    </div>

    <form method="GET" style="display:flex; gap:0.75rem; align-items:end;">
        <div class="field" style="flex:1; margin:0;">
            <label>Search</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, SKU, category...">
        </div>
        <div class="field" style="margin:0;">
            <label>&nbsp;</label>
            <label style="display:flex; align-items:center; gap:0.4rem; margin:0; padding:0.6rem 0.75rem; background:#0f172a; border:1px solid #334155; border-radius:6px;">
                <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}>
                Low stock only
            </label>
        </div>
        <button type="submit">Filter</button>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Buy</th>
                <th>Sell</th>
                <th>Stock</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($products as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td class="muted">{{ $p->sku }}</td>
                <td class="muted">{{ $p->category ?? '—' }}</td>
                <td>{{ number_format($p->buying_price, 2) }}</td>
                <td>{{ number_format($p->selling_price, 2) }}</td>
                <td>
                    <strong>{{ $p->stock }}</strong>
                    @if ($p->isLowStock())
                        <span class="badge badge-danger">LOW</span>
                    @endif
                </td>
                <td>
                    @if ($p->active)
                        <span class="badge badge-ok">Active</span>
                    @else
                        <span class="badge badge-muted">Inactive</span>
                    @endif
                </td>
                <td>
                    <div class="row-actions">
                        <a href="{{ route('products.edit', $p) }}" class="btn btn-secondary btn-sm">Edit</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted" style="text-align:center; padding:2rem;">No products yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">
        {{ $products->links() }}
    </div>
</div>
@endsection