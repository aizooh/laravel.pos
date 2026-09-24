@extends('layouts.app')

@section('title', 'Monthly Report — Mamicar POS')

@section('content')

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:end; gap:1rem; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;">Monthly Report — {{ $month->format('F Y') }}</h2>
            <div class="muted">{{ $month->startOfMonth()->format('d M') }} → {{ $month->copy()->endOfMonth()->format('d M Y') }}</div>
        </div>
        <form method="GET" style="display:flex; gap:0.5rem; align-items:end;">
            <div class="field" style="margin:0;">
                <label>Month</label>
                <input type="month" name="month" value="{{ $month->format('Y-m') }}">
            </div>
            <button type="submit">View</button>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1rem; margin-bottom:1.25rem;">
    <div class="card kpi">
        <div class="kpi-label">Total Sales</div>
        <div class="kpi-value">KSh {{ number_format($totalSales, 2) }}</div>
        <div class="kpi-sub">{{ $txCount }} txns</div>
    </div>
    <div class="card kpi">
        <div class="kpi-label">Gross Profit</div>
        <div class="kpi-value" style="color:#34d399;">KSh {{ number_format($grossProfit, 2) }}</div>
        <div class="kpi-sub">COGS: KSh {{ number_format($cogs, 2) }}</div>
    </div>
    <div class="card kpi">
        <div class="kpi-label">Expenses</div>
        <div class="kpi-value" style="color:#fb7185;">KSh {{ number_format($expenses, 2) }}</div>
        <div class="kpi-sub">Whole month</div>
    </div>
    <div class="card kpi">
        <div class="kpi-label">Net Profit</div>
        <div class="kpi-value" style="color:{{ $netProfit >= 0 ? '#34d399' : '#fb7185' }};">
            KSh {{ number_format($netProfit, 2) }}
        </div>
        <div class="kpi-sub">Gross − Expenses</div>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0;">Payment Methods</h3>
    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:1rem;">
        <div class="pay-box"><div class="muted">Cash</div><div class="pay-amount">KSh {{ number_format($payments['cash'], 2) }}</div></div>
        <div class="pay-box"><div class="muted">M-PESA</div><div class="pay-amount">KSh {{ number_format($payments['mpesa'], 2) }}</div></div>
        <div class="pay-box"><div class="muted">Card</div><div class="pay-amount">KSh {{ number_format($payments['card'], 2) }}</div></div>
    </div>
</div>

{{-- Daily sales series --}}
@if (! empty($dailySeries))
    <div class="card">
        <h3 style="margin-top:0;">Daily Sales</h3>
        @php $max = max($dailySeries); @endphp
        <div class="bar-chart">
            @for ($d = 1; $d <= $month->copy()->endOfMonth()->day; $d++)
                @php
                    $key = str_pad((string) $d, 2, '0', STR_PAD_LEFT);
                    $val = (float) ($dailySeries[$key] ?? 0);
                    $h   = $max > 0 ? ($val / $max) * 100 : 0;
                @endphp
                <div class="bar-wrap" title="{{ $key }}: KSh {{ number_format($val) }}">
                    <div class="bar" style="height: {{ $h }}%;"></div>
                    <div class="bar-day">{{ (int) $key }}</div>
                </div>
            @endfor
        </div>
    </div>
@endif

{{-- Top products + services --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
    <div class="card">
        <h3 style="margin-top:0;">Best-Selling Products</h3>
        <table>
            <thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead>
            <tbody>
            @forelse ($topProducts as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->qty }}</td>
                    <td>KSh {{ number_format((float) $p->revenue, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted" style="text-align:center; padding:1.5rem;">No products sold.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Best-Selling Services</h3>
        <table>
            <thead><tr><th>Service</th><th>Qty</th><th>Revenue</th></tr></thead>
            <tbody>
            @forelse ($topServices as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->qty }}</td>
                    <td>KSh {{ number_format((float) $s->revenue, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted" style="text-align:center; padding:1.5rem;">No services sold.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- By attendant + expenses by category --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
    <div class="card">
        <h3 style="margin-top:0;">Sales by Attendant</h3>
        <table>
            <thead><tr><th>Name</th><th>Txns</th><th>Total</th></tr></thead>
            <tbody>
            @forelse ($byAttendant as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['count'] }}</td>
                    <td>KSh {{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted" style="text-align:center; padding:1.5rem;">No sales.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Expenses by Category</h3>
        <table>
            <thead><tr><th>Category</th><th>Total</th></tr></thead>
            <tbody>
            @forelse ($expensesByCategory as $e)
                <tr>
                    <td>{{ $e->category }}</td>
                    <td>KSh {{ number_format((float) $e->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="muted" style="text-align:center; padding:1.5rem;">No expenses.</td></tr>
            @endforelse
            @if ($expenses > 0)
                <tr>
                    <td style="text-align:right; font-weight:700;">Total</td>
                    <td style="font-weight:700;">KSh {{ number_format($expenses, 2) }}</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>

<style>
    .kpi { display:flex; flex-direction:column; gap:0.25rem; }
    .kpi-label { font-size:0.8rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; }
    .kpi-value { font-size:1.5rem; font-weight:700; color:#fff; }
    .kpi-sub { font-size:0.8rem; color:#94a3b8; }
    .pay-box { background:#0f172a; padding:1rem; border-radius:8px; border:1px solid #334155; }
    .pay-amount { font-size:1.25rem; font-weight:700; color:#fff; margin-top:0.25rem; }

    .bar-chart {
        display:flex; gap:3px; align-items:flex-end; height:140px;
        padding: 0.5rem 0; overflow-x:auto;
    }
    .bar-wrap {
        flex: 1; min-width: 14px; display:flex; flex-direction:column;
        align-items:center; justify-content:flex-end; height:100%;
    }
    .bar { width:100%; background:#38bdf8; border-radius:2px 2px 0 0; min-height:1px; }
    .bar-wrap:hover .bar { background:#0ea5e9; }
    .bar-day { font-size:0.65rem; color:#94a3b8; margin-top:2px; }
</style>

@endsection