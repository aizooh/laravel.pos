@extends('layouts.app')

@section('title', 'Daily Position — Mamicar POS')

@section('content')

{{-- ============ TODAY ============ --}}
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h2 style="margin:0;">Daily Financial Position</h2>
            <div class="muted">{{ $today->format('l, d M Y') }}</div>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <span class="badge {{ $opening ? 'badge-ok' : 'badge-warn' }}">
                Opening: {{ $opening ? 'Recorded' : 'Pending' }}
            </span>
            <span class="badge {{ $closing ? 'badge-ok' : 'badge-warn' }}">
                Closing: {{ $closing ? 'Recorded' : 'Pending' }}
            </span>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1.25rem;">

    {{-- OPENING --}}
    <div class="card">
        <h3 style="margin-top:0;">🌅 Opening Position</h3>

        @if ($opening)
            @include('positions._summary', ['p' => $opening, 'label' => 'Opening'])

            <div style="margin-top:1rem; display:flex; gap:0.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-toggle-edit="edit-opening">Edit</button>
            </div>

            <div id="edit-opening" style="display:none; margin-top:1rem;">
                @include('positions._form', ['position' => $opening, 'type' => 'opening', 'action' => route('positions.update', $opening), 'method' => 'PUT'])
            </div>
        @else
            @include('positions._form', ['position' => null, 'type' => 'opening', 'action' => route('positions.store'), 'method' => 'POST'])
        @endif
    </div>

    {{-- CLOSING --}}
    <div class="card">
        <h3 style="margin-top:0;">🌙 Closing Position</h3>

        @if ($closing)
            @include('positions._summary', ['p' => $closing, 'label' => 'Closing'])

            <div style="margin-top:1rem; display:flex; gap:0.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-toggle-edit="edit-closing">Edit</button>
            </div>

            <div id="edit-closing" style="display:none; margin-top:1rem;">
                @include('positions._form', ['position' => $closing, 'type' => 'closing', 'action' => route('positions.update', $closing), 'method' => 'PUT'])
            </div>
        @else
            @include('positions._form', ['position' => null, 'type' => 'closing', 'action' => route('positions.store'), 'method' => 'POST'])
        @endif
    </div>
</div>

{{-- ============ TODAY'S CHANGE ============ --}}
@if ($opening && $closing)
    @php
        $change   = (float) $closing->total - (float) $opening->total;
        $changeUp = $change >= 0;
    @endphp
    <div class="card">
        <h3 style="margin-top:0;">📈 Today's Change</h3>
        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1rem;">
            <div class="pay-box">
                <div class="muted">Opening</div>
                <div class="pay-amount">KSh {{ number_format((float) $opening->total, 2) }}</div>
            </div>
            <div class="pay-box">
                <div class="muted">Closing</div>
                <div class="pay-amount">KSh {{ number_format((float) $closing->total, 2) }}</div>
            </div>
            <div class="pay-box">
                <div class="muted">Change</div>
                <div class="pay-amount" style="color:{{ $changeUp ? '#34d399' : '#fb7185' }};">
                    {{ $changeUp ? '+' : '' }}KSh {{ number_format($change, 2) }}
                </div>
            </div>
            <div class="pay-box">
                <div class="muted">Direction</div>
                <div class="pay-amount" style="color:{{ $changeUp ? '#34d399' : '#fb7185' }};">
                    {{ $changeUp ? '▲ Increase' : '▼ Decrease' }}
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ============ HISTORY ============ --}}
<div class="card">
    <h2>History</h2>

    @forelse ($history as $date => $records)
        @php
            $op = $records->firstWhere('type', 'opening');
            $cl = $records->firstWhere('type', 'closing');
            $change = ($op && $cl) ? (float) $cl->total - (float) $op->total : null;
        @endphp

        <div style="margin-bottom:1.5rem;">
            <h3 style="margin:0 0 0.5rem; font-size:0.95rem;">
                {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}
                @if ($change !== null)
                    <span class="badge {{ $change >= 0 ? 'badge-ok' : 'badge-danger' }}" style="margin-left:0.5rem;">
                        {{ $change >= 0 ? '+' : '' }}KSh {{ number_format($change, 2) }} during the day
                    </span>
                @endif
            </h3>

            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>KCB</th>
                        <th>Equity</th>
                        <th>Absa</th>
                        <th>M-PESA</th>
                        <th>Cash</th>
                        <th>Other</th>
                        <th>Total</th>
                        <th>Recorded By</th>
                        <th>Time</th>
                        @if (auth()->user()->isAdmin())
                            <th></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @foreach ($records as $p)
                    <tr>
                        <td>
                            @if ($p->type === 'opening')
                                <span class="badge badge-ok">OPENING</span>
                            @else
                                <span class="badge badge-muted">CLOSING</span>
                            @endif
                        </td>
                        <td>{{ number_format((float) $p->kcb, 2) }}</td>
                        <td>{{ number_format((float) $p->equity, 2) }}</td>
                        <td>{{ number_format((float) $p->absa, 2) }}</td>
                        <td>{{ number_format((float) $p->mpesa, 2) }}</td>
                        <td>{{ number_format((float) $p->cash, 2) }}</td>
                        <td>
                            {{ number_format((float) $p->other, 2) }}
                            @if ($p->other_label)
                                <div class="muted" style="font-size:0.7rem;">{{ $p->other_label }}</div>
                            @endif
                        </td>
                        <td><strong>KSh {{ number_format((float) $p->total, 2) }}</strong></td>
                        <td>{{ $p->user->name ?? '—' }}</td>
                        <td class="muted">{{ $p->created_at->format('H:i') }}</td>
                        @if (auth()->user()->isAdmin())
                            <td>
                                <form method="POST" action="{{ route('positions.destroy', $p) }}"
                                      onsubmit="return confirm('Delete this record?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">×</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p class="muted" style="text-align:center; padding:2rem;">No records yet. Start by filling in today's opening position.</p>
    @endforelse
</div>

{{-- ============ SHARED STYLES ============ --}}
<style>
    .pay-box { background:#0f172a; padding:1rem; border-radius:8px; border:1px solid #334155; }
    .pay-amount { font-size:1.15rem; font-weight:700; color:#fff; margin-top:0.25rem; }
    .pos-form .grid-2 { display:grid; grid-template-columns: 1fr 1fr; gap:0.6rem; }
    .pos-form .field { margin-bottom:0.6rem; }
    .pos-form label { font-size:0.8rem; }
    .pos-form input[type=number], .pos-form input[type=text] { padding:0.5rem 0.65rem; font-size:0.9rem; }
    .total-preview {
        margin-top:0.75rem; padding:0.75rem; background:#0f172a;
        border-radius:6px; border:1px solid #334155;
        display:flex; justify-content:space-between; align-items:center;
    }
    .total-preview .label { font-size:0.85rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; }
    .total-preview .amount { font-size:1.15rem; font-weight:700; color:#38bdf8; }
</style>

<script>
(function () {
    // Toggle edit forms
    document.querySelectorAll('[data-toggle-edit]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.toggleEdit);
            if (!target) return;
            const open = target.style.display !== 'none';
            target.style.display = open ? 'none' : 'block';
            btn.textContent = open ? 'Edit' : 'Cancel';
        });
    });

    // Live total preview per form
    document.querySelectorAll('.pos-form').forEach(form => {
        const fields = form.querySelectorAll('input[data-amount]');
        const out    = form.querySelector('.total-preview .amount');

        function recalc() {
            let total = 0;
            fields.forEach(f => { total += parseFloat(f.value) || 0; });
            out.textContent = 'KSh ' + total.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        fields.forEach(f => f.addEventListener('input', recalc));
        recalc();
    });
})();
</script>

@endsection