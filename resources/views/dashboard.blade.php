@extends('layouts.app')

@section('title', 'Dashboard — Mamicar POS')

@section('content')

@if ($mode === 'admin')

    {{-- ============================================================
         ADMIN DASHBOARD
    ============================================================ --}}

    {{-- ---------- Header ---------- --}}
    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="m-0">Today — {{ $today->format('d M Y') }}</h2>
                <div class="muted">Welcome back, {{ auth()->user()->name }}.</div>
            </div>
            <a href="{{ route('pos.index') }}" class="btn">Open POS</a>
        </div>
    </div>

    {{-- ---------- KPI grid ---------- --}}
    @php
        $kpis = [
            [
                'label' => "Today's Sales",
                'value' => $todaySalesTotal,
                'sub'   => $todayTxCount . ' transaction' . ($todayTxCount === 1 ? '' : 's'),
                'color' => null,
            ],
            [
                'label' => 'Gross Profit',
                'value' => $todayGrossProfit,
                'sub'   => 'COGS: KSh ' . number_format($todayCogs, 2),
                'color' => '#34d399',
            ],
            [
                'label' => 'Expenses',
                'value' => $todayExpenses,
                'sub'   => 'Today only',
                'color' => '#fb7185',
            ],
            [
                'label' => 'Net',
                'value' => $todayNet,
                'sub'   => 'Profit − Expenses',
                'color' => $todayNet >= 0 ? '#34d399' : '#fb7185',
            ],
        ];
    @endphp

    <div class="grid grid-4">
        @foreach ($kpis as $kpi)
            <div class="card kpi">
                <div class="kpi-label">{{ $kpi['label'] }}</div>
                <div class="kpi-value" @if ($kpi['color']) style="color:{{ $kpi['color'] }};" @endif>
                    KSh {{ number_format((float) $kpi['value'], 2) }}
                </div>
                <div class="kpi-sub">{{ $kpi['sub'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ---------- Payment breakdown ---------- --}}
    <div class="card">
        <h3 class="m-0">Payment Methods — Today</h3>

        <div class="grid grid-3 grid-tight">
            @foreach (['cash' => 'Cash', 'mpesa' => 'M-PESA', 'card' => 'Card'] as $key => $label)
                <div class="stat-box">
                    <div class="stat-label">{{ $label }}</div>
                    <div class="stat-value">
                        KSh {{ number_format((float) ($paymentBreakdown[$key] ?? 0), 2) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ---------- Low stock ---------- --}}
    <div class="card">
        <div class="card-head">
            <h3 class="m-0">
                Low Stock
                @if ($lowStockCount > 0)
                    <span class="badge badge-danger">{{ $lowStockCount }}</span>
                @else
                    <span class="badge badge-ok">All good</span>
                @endif
            </h3>

            @if ($lowStockCount > 0)
                <a href="{{ route('products.index', ['low_stock' => 1]) }}"
                   class="btn btn-secondary btn-sm">View all</a>
            @endif
        </div>

        @if ($lowStockProducts->count())
            <table class="mt-1">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Stock</th>
                        <th>Alert at</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lowStockProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td class="muted">{{ $p->sku }}</td>
                            <td><strong class="text-danger">{{ $p->stock }}</strong></td>
                            <td class="muted">{{ $p->low_stock }}</td>
                            <td>
                                <a href="{{ route('products.edit', $p) }}"
                                   class="btn btn-secondary btn-sm">Restock</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted mt-1">No low stock items. 👍</p>
        @endif
    </div>

    {{-- ---------- Recent sales ---------- --}}
    <div class="card">
        <div class="card-head mb-1">
            <h3 class="m-0">Recent Sales</h3>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">View all</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Time</th>
                    <th>By</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentSales as $s)
                    <tr>
                        <td><strong>{{ $s->invoice_no }}</strong></td>
                        <td class="muted">{{ $s->created_at->format('H:i') }}</td>
                        <td>{{ $s->user->name ?? '—' }}</td>
                        <td><span class="badge badge-muted">{{ strtoupper($s->payment_method) }}</span></td>
                        <td>KSh {{ number_format((float) $s->total, 2) }}</td>
                        <td>
                            <a href="{{ route('sales.receipt', $s) }}"
                               class="btn btn-secondary btn-sm">Receipt</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted table-empty">No sales today.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ---------- Quick nav ---------- --}}
    <div class="card">
        <h3 class="m-0">Quick Nav</h3>

        <div class="actions mt-1">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Inventory</a>
            <a href="{{ route('services.index') }}" class="btn btn-secondary">Services</a>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Expenses</a>
            <a href="{{ route('sales.index') }}"    class="btn btn-secondary">Sales</a>
            <a href="{{ route('reports.index') }}"  class="btn btn-secondary">Reports</a>
            <a href="{{ route('positions.index') }}" class="btn btn-secondary">Daily Position</a>
            <a href="{{ route('users.index') }}"    class="btn btn-secondary">Users</a>
        </div>
    </div>

@else

    {{-- ============================================================
         ATTENDANT DASHBOARD
    ============================================================ --}}

    {{-- ---------- Header ---------- --}}
    <div class="card">
        <h2 class="m-0">Hi {{ auth()->user()->name }} 👋</h2>
        <p class="muted">{{ $today->format('d M Y') }} — your shift snapshot</p>
        <a href="{{ route('pos.index') }}" class="btn mt-1">Open POS</a>
    </div>

    {{-- ---------- KPIs ---------- --}}
    <div class="grid grid-2">
        <div class="card kpi">
            <div class="kpi-label">My Sales Today</div>
            <div class="kpi-value">KSh {{ number_format((float) $myTotal, 2) }}</div>
            <div class="kpi-sub">{{ $myTxCount }} transaction{{ $myTxCount === 1 ? '' : 's' }}</div>
        </div>

        <div class="card kpi">
            <div class="kpi-label">Quick Action</div>
            <div class="mt-2">
                <a href="{{ route('pos.index') }}" class="btn">Start Selling</a>
            </div>
        </div>
    </div>

    {{-- ---------- My recent sales ---------- --}}
    <div class="card">
        <h3 class="m-0">My Recent Sales</h3>

        <table class="mt-1">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Time</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($myRecent as $s)
                    <tr>
                        <td><strong>{{ $s->invoice_no }}</strong></td>
                        <td class="muted">{{ $s->created_at->format('d M H:i') }}</td>
                        <td><span class="badge badge-muted">{{ strtoupper($s->payment_method) }}</span></td>
                        <td>KSh {{ number_format((float) $s->total, 2) }}</td>
                        <td>
                            <a href="{{ route('sales.receipt', $s) }}"
                               class="btn btn-secondary btn-sm">Receipt</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted table-empty">No sales yet today.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endif

{{-- ============================================================
     DASHBOARD STYLES
============================================================ --}}
<style>
    /* Layout helpers */
    .grid          { display:grid; gap:1rem; margin-bottom:1.25rem; }
    .grid-2        { grid-template-columns: repeat(2, 1fr); }
    .grid-3        { grid-template-columns: repeat(3, 1fr); }
    .grid-4        { grid-template-columns: repeat(4, 1fr); }
    .grid-tight    { margin-top:1rem; margin-bottom:0; }

    .card-head     { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; }
    .actions       { display:flex; gap:0.5rem; flex-wrap:wrap; }

    /* Spacing utilities */
    .m-0           { margin:0; }
    .mt-1          { margin-top:0.75rem; }
    .mt-2          { margin-top:0.5rem; }
    .mb-1          { margin-bottom:0.75rem; }

    /* Colour utilities */
    .text-danger   { color:#fb7185; }

    /* KPI blocks */
    .kpi           { display:flex; flex-direction:column; gap:0.25rem; }
    .kpi-label     { font-size:0.8rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; }
    .kpi-value     { font-size:1.5rem; font-weight:700; color:#fff; }
    .kpi-sub       { font-size:0.8rem; color:#94a3b8; }

    /* Inner stat boxes (payment breakdown) */
    .stat-box      { background:#0f172a; padding:1rem; border-radius:8px; border:1px solid #334155; }
    .stat-label    { font-size:0.8rem; color:#94a3b8; }
    .stat-value    { font-size:1.25rem; font-weight:700; }

    /* Table empty state */
    .table-empty   { text-align:center; padding:1.5rem; }

    /* Responsive */
    @media (max-width: 900px) {
        .grid-4 { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .grid-2,
        .grid-3,
        .grid-4 { grid-template-columns: 1fr; }
    }
</style>

@endsection