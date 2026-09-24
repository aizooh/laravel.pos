@extends('layouts.app')

@section('title', 'Sales — Mamicar POS')

@section('content')

<div class="card">
    <h2>{{ auth()->user()->isAdmin() ? 'All Sales' : 'My Sales' }}</h2>

    <form method="GET" class="grid-3" style="margin-bottom:1rem;">
        <div class="field" style="margin:0;">
            <label>From</label>
            <input type="text" name="from" value="{{ request('from') }}" placeholder="YYYY-MM-DD">
        </div>
        <div class="field" style="margin:0;">
            <label>To</label>
            <input type="text" name="to" value="{{ request('to') }}" placeholder="YYYY-MM-DD">
        </div>
        <div class="field" style="margin:0;">
            <label>Payment</label>
            <select name="method">
                <option value="">Any</option>
                <option value="cash"  {{ request('method') === 'cash'  ? 'selected' : '' }}>Cash</option>
                <option value="mpesa" {{ request('method') === 'mpesa' ? 'selected' : '' }}>M-PESA</option>
                <option value="card"  {{ request('method') === 'card'  ? 'selected' : '' }}>Card</option>
            </select>
        </div>

        {{-- Show voided toggle --}}
        <div class="field" style="margin:0;">
            <label>&nbsp;</label>
            <label style="display:flex; align-items:center; gap:0.4rem; margin:0; padding:0.6rem 0.75rem; background:#0f172a; border:1px solid #334155; border-radius:6px;">
                <input type="checkbox" name="show_voided" value="1" {{ $showVoided ? 'checked' : '' }}>
                Show voided
            </label>
        </div>

        <div style="grid-column: span 3; display:flex; gap:0.5rem;">
            <button type="submit">Filter</button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="muted" style="margin-bottom:0.5rem;">
        Showing {{ $sales->count() }} of {{ $sales->total() }} sales —
        filtered total: <strong>KSh {{ number_format((float) $summary['total'], 2) }}</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>By</th>
                <th>Items</th>
                <th>Payment</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($sales as $s)
            <tr style="{{ $s->isVoided() ? 'opacity:0.65;' : '' }}">
                <td>
                    <strong style="{{ $s->isVoided() ? 'text-decoration: line-through;' : '' }}">{{ $s->invoice_no }}</strong>

                    @if ($s->isVoided())
                        <div style="margin-top:0.25rem;">
                            <span class="badge badge-danger">VOIDED</span>
                        </div>
                        <div class="muted" style="font-size:0.75rem; margin-top:0.25rem;">
                            by <strong>{{ $s->voidedBy->name ?? '—' }}</strong><br>
                            {{ $s->voided_at?->format('d M Y H:i') }}
                            @if ($s->void_reason)
                                <div style="margin-top:0.15rem;">Reason: <em>{{ $s->void_reason }}</em></div>
                            @endif
                        </div>
                    @endif
                </td>
                <td class="muted">{{ $s->created_at->format('d M Y H:i') }}</td>
                <td>{{ $s->user->name ?? '—' }}</td>
                <td class="muted">{{ $s->items_count }}</td>
                <td>
                    <span class="badge badge-muted">{{ strtoupper($s->payment_method) }}</span>
                    @if ($s->mpesa_reference)
                        <div class="muted" style="font-size:0.75rem;">{{ $s->mpesa_reference }}</div>
                    @endif
                </td>
                <td>
                    @if ($s->isVoided())
                        <span style="text-decoration: line-through;">KSh {{ number_format((float) $s->total, 2) }}</span>
                    @else
                        <strong>KSh {{ number_format((float) $s->total, 2) }}</strong>
                    @endif
                </td>
                <td style="white-space:nowrap;">
                    <a href="{{ route('sales.receipt', $s) }}" class="btn btn-secondary btn-sm">Receipt</a>

                    @if (auth()->user()->isAdmin())
                        @if ($s->isVoided())
                            <button type="button" class="btn btn-sm unvoid-sale-btn"
                                    style="background:#10b981; color:#fff;"
                                    data-action="{{ route('sales.unvoid', $s) }}"
                                    data-invoice="{{ $s->invoice_no }}">Unvoid</button>
                        @else
                            <button type="button" class="btn btn-danger btn-sm void-sale-btn"
                                    data-action="{{ route('sales.void', $s) }}"
                                    data-invoice="{{ $s->invoice_no }}">Void</button>
                        @endif
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted" style="text-align:center; padding:2rem;">No sales yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">{{ $sales->links() }}</div>
</div>

{{-- Void modal --}}
<div id="void-modal" class="modal-backdrop">
    <div class="modal-box">
        <h2 style="margin-top:0;">Void Sale</h2>
        <p class="muted" id="void-invoice-label">Invoice: —</p>
        <p class="muted">Stock will be restored. This sale will be excluded from all reports.</p>

        <form method="POST" id="void-form">
            @csrf
            <div class="field">
                <label>Reason (required)</label>
                <input type="text" name="void_reason" required maxlength="200"
                       placeholder="e.g. Wrong item, customer cancelled, test sale">
            </div>

            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn btn-danger" style="flex:1;">Void Sale</button>
                <button type="button" class="btn btn-secondary" id="close-void">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Unvoid confirmation modal --}}
<div id="unvoid-modal" class="modal-backdrop">
    <div class="modal-box">
        <h2 style="margin-top:0;">Unvoid Sale</h2>
        <p class="muted" id="unvoid-invoice-label">Invoice: —</p>
        <p class="muted">
            Stock will be <strong>deducted again</strong> for each product on this sale.
            The sale will reappear in all reports and totals.
        </p>

        <form method="POST" id="unvoid-form">
            @csrf
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn" style="flex:1; background:#10b981; color:#fff;">
                    Confirm Unvoid
                </button>
                <button type="button" class="btn btn-secondary" id="close-unvoid">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal-backdrop {
        display:none; position:fixed; inset:0;
        background:rgba(0,0,0,0.6); z-index:100;
        align-items:center; justify-content:center;
    }
    .modal-backdrop.open { display:flex; }
    .modal-box {
        background:#1e293b; padding:1.5rem; border-radius:10px;
        width:100%; max-width:420px; border:1px solid #334155;
    }
</style>

<script>
(function () {
    const voidModal   = document.getElementById('void-modal');
    const voidForm    = document.getElementById('void-form');
    const voidLabel   = document.getElementById('void-invoice-label');

    const unvoidModal = document.getElementById('unvoid-modal');
    const unvoidForm  = document.getElementById('unvoid-form');
    const unvoidLabel = document.getElementById('unvoid-invoice-label');

    document.addEventListener('click', (e) => {
        const vBtn = e.target.closest('.void-sale-btn');
        if (vBtn) {
            voidForm.action = vBtn.dataset.action;
            voidLabel.textContent = 'Invoice: ' + vBtn.dataset.invoice;
            voidModal.classList.add('open');
            return;
        }

        const uBtn = e.target.closest('.unvoid-sale-btn');
        if (uBtn) {
            unvoidForm.action = uBtn.dataset.action;
            unvoidLabel.textContent = 'Invoice: ' + uBtn.dataset.invoice;
            unvoidModal.classList.add('open');
            return;
        }

        if (e.target.id === 'close-void' || e.target === voidModal) {
            voidModal.classList.remove('open');
        }
        if (e.target.id === 'close-unvoid' || e.target === unvoidModal) {
            unvoidModal.classList.remove('open');
        }
    });
})();
</script>

@endsection