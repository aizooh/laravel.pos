<form method="POST" action="{{ $action }}" class="pos-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($method === 'POST')
        <input type="hidden" name="type" value="{{ $type }}">
    @endif

    <div class="grid-2">
        <div class="field">
            <label>KCB Bank</label>
            <input type="number" step="0.01" min="0" name="kcb" data-amount
                   value="{{ old('kcb', $position->kcb ?? 0) }}">
        </div>
        <div class="field">
            <label>Equity Bank</label>
            <input type="number" step="0.01" min="0" name="equity" data-amount
                   value="{{ old('equity', $position->equity ?? 0) }}">
        </div>
        <div class="field">
            <label>Absa Bank</label>
            <input type="number" step="0.01" min="0" name="absa" data-amount
                   value="{{ old('absa', $position->absa ?? 0) }}">
        </div>
        <div class="field">
            <label>M-PESA</label>
            <input type="number" step="0.01" min="0" name="mpesa" data-amount
                   value="{{ old('mpesa', $position->mpesa ?? 0) }}">
        </div>
        <div class="field">
            <label>Cash</label>
            <input type="number" step="0.01" min="0" name="cash" data-amount
                   value="{{ old('cash', $position->cash ?? 0) }}">
        </div>
        <div class="field">
            <label>Other Amount</label>
            <input type="number" step="0.01" min="0" name="other" data-amount
                   value="{{ old('other', $position->other ?? 0) }}">
        </div>
    </div>

    <div class="field">
        <label>Other Label (optional)</label>
        <input type="text" name="other_label" maxlength="60"
               value="{{ old('other_label', $position->other_label ?? '') }}"
               placeholder="e.g. Cooperative Bank">
    </div>

    <div class="field">
        <label>Notes (optional)</label>
        <input type="text" name="notes" maxlength="200"
               value="{{ old('notes', $position->notes ?? '') }}">
    </div>

    <div class="total-preview">
        <span class="label">Total {{ ucfirst($type) }}</span>
        <span class="amount">KSh 0.00</span>
    </div>

    <button type="submit" class="btn" style="width:100%; margin-top:0.75rem;">
        Save {{ ucfirst($type) }}
    </button>
</form>