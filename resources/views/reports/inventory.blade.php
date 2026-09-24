@extends('layouts.app')

@section('title', 'Inventory Report — Mamicar POS')

@section('content')

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:end; gap:1rem; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;">Inventory Report</h2>
            <div class="muted">{{ now()->format('d M Y H:i') }}</div>
        </div>
        <form method="GET" style="display:flex; gap:0.5rem; align-items:end;">
            <label style="display:flex; align-items:center; gap:0.4rem; margin:0; padding:0.6rem 0.75rem; background:#0f172a; border:1px solid #334155; border-radius:6px;">
                <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}>
                Low stock only
            </label>
            <label style="display:flex; align-items:center; gap:0.4rem; margin:0; padding:0.6rem 0.75rem; background:#0f172a; border:1px solid #334155; border-radius:6px;">
                <input type="checkbox" name="active_only" value="1" {{ request('active_only') ? 'checked' : '' }}>
                Active only
            </label>
            <button type="submit">Filter</button>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1rem; margin-bottom:1.25rem;">
    <div class="card kpi">
        <div class="kpi-label">Total Units</div>
        <div class="kpi-value">{{ number_format($totalUnits) }}</div>
        <div class="kpi-sub">items in stock</div>
    </div>
    <div class="card kpi">
        <div class="kpi-label">Inventory Value (Cost)</div>
        <div class="kpi-value">KSh {{ number_format($totalCost, 2) }}</div>
        <div class="kpi-sub">what you paid</div>
    </div>
    <div class="card kpi">
        <div class="kpi-label">Inventory Value (Retail)</div>
        <div class="kpi-value">KSh {{ number_format($totalRetail, 2) }}</div>
        <div class="kpi-sub">if all sold</div>
    </div>
    <div class="card kpi">
        <div class="kpi-label">Potential Profit</div>
        <div class="kpi-value" style="color:#34d399;">KSh {{ number_format($potentialProfit, 2) }}</div>
        <div class="kpi-sub">retail − cost</div>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0;">Products ({{ $products->count() }})</h3>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Buy</th>
                <th>Sell</th>
                <th>Margin</th>
                <th>Value (Cost)</th>
                <th>Value (Retail)</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($products as $p)
            @php
                $margin  = (float) $p->selling_price - (float) $p->buying_price;
                $vCost   = $p->stock * (float) $p->buying_price;
                $vRetail = $p->stock * (float) $p->selling_price;
            @endphp
            <tr>
                <td>
                    {{ $p->name }}
                    @if (! $p->active)<span class="badge badge-muted">inactive</span>@endif
                </td>
                <td class="muted">{{ $p->sku }}</td>
                <td class="muted">{{ $p->category ?? '—' }}</td>
                <td>
                    <strong>{{ $p->stock }}</strong>
                    @if ($p->isLowStock())
                        <span class="badge badge-danger">LOW</span>
                    @endif
                </td>
                <td>{{ number_format((float) $p->buying_price, 2) }}</td>
                <td>{{ number_format((float) $p->selling_price, 2) }}</td>
                <td style="color:{{ $margin >= 0 ? '#34d399' : '#fb7185' }};">
                    {{ number_format($margin, 2) }}
                </td>
                <td>{{ number_format($vCost, 2) }}</td>
                <td>{{ number_format($vRetail, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="muted" style="text-align:center; padding:2rem;">No products match.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
            <tr style="border-top:2px solid #475569;">
                <td colspan="7" style="text-align:right; font-weight:700;">TOTAL</td>
                <td style="font-weight:700;">KSh {{ number_format($totalCost, 2) }}</td>
                <td style="font-weight:700;">KSh {{ number_format($totalRetail, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<style>
    .kpi { display:flex; flex-direction:column; gap:0.25rem; }
    .kpi-label { font-size:0.8rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; }
    .kpi-value { font-size:1.35rem; font-weight:700; color:#fff; }
    .kpi-sub { font-size:0.8rem; color:#94a3b8; }
</style>

@endsection