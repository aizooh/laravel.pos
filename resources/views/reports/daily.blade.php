@extends('layouts.app')

@section('title', 'Daily Report — Mamicar POS')

@section('content')

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:end; gap:1rem; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;">Daily Report — {{ $date->format('d M Y') }}</h2>
            <div class="muted">{{ $date->format('l') }}</div>
        </div>
        <form method="GET" style="display:flex; gap:0.5rem; align-items:end;">
            <div class="field" style="margin:0;">
                <label>Date</label>
                <input type="date" name="date" value="{{ $date->toDateString() }}">
            </div>
            <button type="submit">View</button>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

{{-- Summary KPIs --}}
<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1rem; margin-bottom:1.25rem;">
    <div class="card kpi">
        <div class="kpi-label">Total Sales</div>
        <div class="kpi-value">KSh {{ number_format($totalSales, 2) }}</div>
        <div class="kpi-sub">{{ $txCount }} transaction{{ $txCount === 1 ? '' : 's' }}</div>
    </div>
    <div class="card kpi">
        <div class="kpi-label">Gross Profit</div>
        <div class="kpi-value" style="color:#34d399;">KSh {{ number_format($grossProfit, 2) }}</div>
        <div class="kpi-sub">COGS: KSh {{ number_format($cogs, 2) }}</div>
    </div>
    <div class="card kpi">
        <div class="kpi-label">Expenses</div>
        <div class="kpi-value" style="color:#fb7185;">KSh {{ number_format($expenses, 2) }}</div>
        <div class="kpi-sub">Today only</div>
    </div>
    <div class="card kpi">
        <div class="kpi-label">Net Profit</div>
        <div class="kpi-value" style="color:{{ $netProfit >= 0 ? '#34d399' : '#fb7185' }};">
            KSh {{ number_format($netProfit, 2) }}
        </div>
        <div class="kpi-sub">Gross − Expenses</div>
    </div>
</div>

{{-- Payment breakdown --}}
<div class="card">
    <h3 style="margin-top:0;">Payment Methods</h3>
    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:1rem;">
        <div class="pay-box">
            <div class="muted">Cash</div>
            <div class="pay-amount">KSh {{ number_format($payments['cash'], 2) }}</div>
        </div>
        <div class="pay-box">
            <div class="muted">M-PESA</div>
            <div class="pay-amount">KSh {{ number_format($payments['mpesa'], 2) }}</div>
        </div>
        <div class="pay-box">
            <div class="muted">Card</div>
            <div class="pay-amount">KSh {{ number_format($payments['card'], 2) }}</div>
        </div>
    </div>
</div>

{{-- Two-column: Top items + By attendant --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">

    <div class="card">
        <h3 style="margin-top:0;">Top Selling Items</h3>
        <table>
            <thead>
                <tr><th>Item</th><th>Type</th><th>Qty</th><th>Revenue</th></tr>
            </thead>
            <tbody>
            @forelse ($topItems as $it)
                <tr>
                    <td>{{ $it->name }}</td>
                    <td><span class="badge badge-muted">{{ $it->item_type }}</span></td>
                    <td>{{ $it->qty }}</td>
                    <td>KSh {{ number_format((float) $it->revenue, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted" style="text-align:center; padding:1.5rem;">Nothing sold.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Sales by Attendant</h3>
        <table>
            <thead>
                <tr><th>Name</th><th>Txns</th><th>Total</th></tr>
            </thead>
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
</div>

{{-- All sales that day --}}
<div class="card">
    <h3 style="margin-top:0;">All Transactions ({{ $txCount }})</h3>
    <table>
        <thead>
            <tr><th>Time</th><th>Invoice</th><th>By</th><th>Payment</th><th>Total</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($sales as $s)
            <tr>
                <td class="muted">{{ $s->created_at->format('H:i') }}</td>
                <td><strong>{{ $s->invoice_no }}</strong></td>
                <td>{{ $s->user->name ?? '—' }}</td>
                <td><span class="badge badge-muted">{{ strtoupper($s->payment_method) }}</span></td>
                <td>KSh {{ number_format((float) $s->total, 2) }}</td>
                <td><a href="{{ route('sales.receipt', $s) }}" class="btn btn-secondary btn-sm">Receipt</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted" style="text-align:center; padding:1.5rem;">No sales on this date.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Expenses --}}
@if ($expenseLines->count())
    <div class="card">
        <h3 style="margin-top:0;">Expenses ({{ $expenseLines->count() }})</h3>
        <table>
            <thead>
                <tr><th>Description</th><th>Category</th><th>Amount</th></tr>
            </thead>
            <tbody>
            @foreach ($expenseLines as $e)
                <tr>
                    <td>{{ $e->description }}</td>
                    <td><span class="badge badge-muted">{{ $e->category }}</span></td>
                    <td>KSh {{ number_format((float) $e->amount, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" style="text-align:right; font-weight:700;">Total</td>
                <td style="font-weight:700;">KSh {{ number_format($expenses, 2) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
@endif

<style>
    .kpi { display:flex; flex-direction:column; gap:0.25rem; }
    .kpi-label { font-size:0.8rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; }
    .kpi-value { font-size:1.5rem; font-weight:700; color:#fff; }
    .kpi-sub { font-size:0.8rem; color:#94a3b8; }
    .pay-box { background:#0f172a; padding:1rem; border-radius:8px; border:1px solid #334155; }
    .pay-amount { font-size:1.25rem; font-weight:700; color:#fff; margin-top:0.25rem; }
</style>

@endsection