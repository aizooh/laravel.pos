@extends('layouts.app')

@section('title', 'Reports — Mamicar POS')

@section('content')

<div class="card">
    <h2 style="margin:0;">Reports</h2>
    <p class="muted">Business insights — {{ $today->format('d M Y') }} — Today's sales: <strong>KSh {{ number_format($todaySales, 2) }}</strong> ({{ $todayCount }} tx)</p>
</div>

<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1rem;">
    <a href="{{ route('reports.daily') }}" class="card report-tile">
        <div class="rt-icon">📅</div>
        <div class="rt-title">Daily Report</div>
        <div class="rt-sub">One day — payments, profit, top items, attendants</div>
    </a>

    <a href="{{ route('reports.monthly') }}" class="card report-tile">
        <div class="rt-icon">📊</div>
        <div class="rt-title">Monthly Report</div>
        <div class="rt-sub">Full month — trends, best sellers, expenses by category</div>
    </a>

    <a href="{{ route('reports.inventory') }}" class="card report-tile">
        <div class="rt-icon">📦</div>
        <div class="rt-title">Inventory Report</div>
        <div class="rt-sub">Stock value, low stock alerts, retail potential</div>
    </a>

    <a href="{{ route('reports.voids') }}" class="card report-tile">
        <div class="rt-icon">🗑️</div>
        <div class="rt-title">Void Log</div>
        <div class="rt-sub">Every void / unvoid action with who, when, why</div>
    </a>
</div>

<style>
    .report-tile { text-decoration:none; color:inherit; display:block; transition: all 0.15s; }
    .report-tile:hover { border-color:#38bdf8; transform: translateY(-2px); }
    .rt-icon { font-size:1.8rem; margin-bottom:0.5rem; }
    .rt-title { font-size:1.1rem; font-weight:600; color:#fff; margin-bottom:0.25rem; }
    .rt-sub { font-size:0.85rem; color:#94a3b8; }

    /* Responsive: 4 → 2 → 1 columns */
    @media (max-width: 1000px) {
        div[style*="repeat(4, 1fr)"] { grid-template-columns: repeat(2, 1fr) !important; }
    }
    @media (max-width: 600px) {
        div[style*="repeat(4, 1fr)"] { grid-template-columns: 1fr !important; }
    }
</style>

@endsection