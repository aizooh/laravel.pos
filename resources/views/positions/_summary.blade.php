<div style="display:grid; grid-template-columns: 1fr 1fr; gap:0.5rem; font-size:0.9rem;">
    <div class="r-line-sum"><span class="muted">KCB</span> <strong>KSh {{ number_format((float) $p->kcb, 2) }}</strong></div>
    <div class="r-line-sum"><span class="muted">Equity</span> <strong>KSh {{ number_format((float) $p->equity, 2) }}</strong></div>
    <div class="r-line-sum"><span class="muted">Absa</span> <strong>KSh {{ number_format((float) $p->absa, 2) }}</strong></div>
    <div class="r-line-sum"><span class="muted">M-PESA</span> <strong>KSh {{ number_format((float) $p->mpesa, 2) }}</strong></div>
    <div class="r-line-sum"><span class="muted">Cash</span> <strong>KSh {{ number_format((float) $p->cash, 2) }}</strong></div>
    <div class="r-line-sum">
        <span class="muted">Other{{ $p->other_label ? ' (' . $p->other_label . ')' : '' }}</span>
        <strong>KSh {{ number_format((float) $p->other, 2) }}</strong>
    </div>
</div>

<div style="margin-top:0.75rem; padding:0.75rem; background:#0f172a; border-radius:6px; border:1px solid #334155; display:flex; justify-content:space-between; align-items:center;">
    <span class="muted" style="text-transform:uppercase; letter-spacing:0.05em; font-size:0.85rem;">{{ $label }} Total</span>
    <strong style="font-size:1.15rem; color:#38bdf8;">KSh {{ number_format((float) $p->total, 2) }}</strong>
</div>

<div class="muted" style="margin-top:0.5rem; font-size:0.8rem;">
    Recorded by <strong>{{ $p->user->name ?? '—' }}</strong> at {{ $p->created_at->format('d M Y H:i') }}
    @if ($p->notes)
        <br>Notes: <em>{{ $p->notes }}</em>
    @endif
</div>

<style>
    .r-line-sum { display:flex; justify-content:space-between; padding:0.15rem 0; }
</style>