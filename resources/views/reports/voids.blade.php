@extends('layouts.app')

@section('title', 'Void Log — Mamicar POS')

@section('content')

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:end; flex-wrap:wrap; gap:1rem;">
        <div>
            <h2 style="margin:0;">Void Log</h2>
            <div class="muted">Every void / unvoid action across the shop.</div>
        </div>
        <form method="GET" style="display:flex; gap:0.5rem; align-items:end;">
            <div class="field" style="margin:0;">
                <label>From</label>
                <input type="date" name="from" value="{{ $from }}">
            </div>
            <div class="field" style="margin:0;">
                <label>To</label>
                <input type="date" name="to" value="{{ $to }}">
            </div>
            <button type="submit">Filter</button>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Action</th>
                <th>Invoice</th>
                <th>Product</th>
                <th>Qty</th>
                <th>By</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($logs as $log)
            @php
                $isUnvoid = str_starts_with($log->reason ?? '', 'Unvoid ');
                preg_match('/^(Void|Unvoid)\s+(INV-\d+)(?:\s+—\s+(.*))?$/', $log->reason ?? '', $m);
                $invoice = $m[2] ?? '—';
                $extra   = $m[3] ?? null;
            @endphp
            <tr>
                <td class="muted">{{ $log->created_at->format('d M Y H:i') }}</td>
                <td>
                    @if ($isUnvoid)
                        <span class="badge" style="background:#064e3b; color:#a7f3d0;">UNVOID</span>
                    @else
                        <span class="badge badge-danger">VOID</span>
                    @endif
                </td>
                <td><strong>{{ $invoice }}</strong></td>
                <td>{{ $log->product->name ?? '—' }}</td>
                <td>{{ $log->quantity > 0 ? '+' : '' }}{{ $log->quantity }}</td>
                <td>{{ $log->user->name ?? '—' }}</td>
            </tr>
            @if ($extra)
                <tr>
                    <td colspan="6" class="muted" style="font-size:0.8rem; padding-top:0; padding-bottom:0.75rem;">
                        Reason: <em>{{ $extra }}</em>
                    </td>
                </tr>
            @endif
        @empty
            <tr><td colspan="6" class="muted" style="text-align:center; padding:2rem;">No void actions in this range.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">{{ $logs->links() }}</div>
</div>

@endsection