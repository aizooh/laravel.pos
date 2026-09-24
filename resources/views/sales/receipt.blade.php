<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $sale->invoice_no }} — Mamicar POS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #0f172a; color: #111;
            padding: 1.5rem; margin: 0;
            display: flex; flex-direction: column; align-items: center;
        }
        .receipt {
            background: #fff; width: 320px; padding: 1.25rem 1rem;
            border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            position: relative;
        }
        .r-head { text-align: center; margin-bottom: 0.75rem; }
        .r-head h1 { margin: 0; font-size: 1.15rem; letter-spacing: 0.05em; }
        .r-head .sub { font-size: 0.75rem; color: #555; margin-top: 0.2rem; }
        .r-line { display: flex; justify-content: space-between; font-size: 0.8rem; padding: 0.15rem 0; }
        .r-line.muted { color: #666; }
        hr { border: none; border-top: 1px dashed #999; margin: 0.6rem 0; }
        .items-head { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; }
        .item-name { font-size: 0.8rem; }
        .r-total {
            display: flex; justify-content: space-between;
            font-weight: 700; font-size: 1rem; margin-top: 0.5rem;
        }
        .r-foot { text-align: center; font-size: 0.75rem; color: #555; margin-top: 0.75rem; }
        .actions {
            margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;
            justify-content: center;
        }
        .actions a, .actions button {
            padding: 0.55rem 1rem; border-radius: 6px; border: none;
            background: #38bdf8; color: #0f172a; font-weight: 600;
            font-size: 0.85rem; cursor: pointer; text-decoration: none;
        }
        .actions a.secondary { background: #334155; color: #e2e8f0; }

        /* VOIDED banner + metadata */
        .void-banner {
            text-align: center; background:#7f1d1d; color:#fff;
            padding:0.4rem; margin:0.5rem 0; font-weight:700;
            letter-spacing:0.1em; border-radius:4px;
        }
        .void-meta {
            border: 1px dashed #b91c1c; border-radius:4px;
            padding: 0.4rem 0.5rem; margin-bottom: 0.5rem;
            background: #fef2f2;
        }
        .void-meta .r-line { color: #7f1d1d; }

        /* Thermal-printer friendly print */
        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; width: 100%; padding: 0; }
            .actions { display: none; }
            .modal-backdrop { display: none !important; }
        }

        /* Modals */
        .modal-backdrop {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,0.6); z-index:100;
            align-items:center; justify-content:center;
        }
        .modal-backdrop.open { display:flex; }
        .modal-box {
            background:#1e293b; color:#e2e8f0; padding:1.5rem; border-radius:10px;
            width:100%; max-width:420px; border:1px solid #334155;
            font-family: system-ui, sans-serif;
        }
        .modal-box .btn {
            padding:0.6rem 1rem; border-radius:6px; border:none;
            font-weight:600; font-size:0.9rem; cursor:pointer;
        }
        .modal-box .btn-secondary { background:#334155; color:#e2e8f0; }
    </style>
</head>
<body>

<div class="receipt">
    @if ($sale->isVoided())
        <div class="void-banner">*** VOIDED ***</div>

        <div class="void-meta">
            <div class="r-line">
                <span>Voided at</span>
                <span>{{ $sale->voided_at?->format('d M Y H:i') }}</span>
            </div>
            <div class="r-line">
                <span>Voided by</span>
                <span>{{ $sale->voidedBy->name ?? '—' }}</span>
            </div>
            @if ($sale->void_reason)
                <div class="r-line">
                    <span>Reason</span>
                    <span style="max-width:160px; text-align:right;">{{ $sale->void_reason }}</span>
                </div>
            @endif
        </div>
    @endif

    <div class="r-head">
        <h1>MAMICAR</h1>
        <div class="sub">Cyber &amp; Accessories</div>
    </div>

    <hr>

    <div class="r-line"><span>Invoice</span><span><strong>{{ $sale->invoice_no }}</strong></span></div>
    <div class="r-line"><span>Date</span><span>{{ $sale->created_at->format('d M Y H:i') }}</span></div>
    <div class="r-line"><span>Served by</span><span>{{ $sale->user->name ?? '—' }}</span></div>

    <hr>

    <div class="r-line items-head">
        <span>ITEM</span>
        <span>QTY × PRICE</span>
        <span>AMOUNT</span>
    </div>

    @foreach ($sale->items as $it)
        <div style="margin-top:0.4rem;">
            <div class="item-name">{{ $it->name }}</div>
            <div class="r-line muted">
                <span></span>
                <span>{{ $it->qty }} × {{ number_format((float) $it->price, 2) }}</span>
                <span>{{ number_format((float) $it->subtotal, 2) }}</span>
            </div>
        </div>
    @endforeach

    <hr>

    <div class="r-total">
        <span>TOTAL</span>
        <span>KSh {{ number_format((float) $sale->total, 2) }}</span>
    </div>

    <div class="r-line" style="margin-top:0.4rem;">
        <span>Payment</span>
        <span>{{ strtoupper($sale->payment_method) }}</span>
    </div>

    @if ($sale->mpesa_reference)
        <div class="r-line muted">
            <span>M-PESA Ref</span>
            <span>{{ $sale->mpesa_reference }}</span>
        </div>
    @endif

    <div class="r-foot">
        <hr>
        Thank you for your business!<br>
    </div>
</div>

<div class="actions">
    <button onclick="window.print()">Print</button>
    <a href="{{ route('pos.index') }}" class="secondary">New Sale</a>
    <a href="{{ route('sales.index') }}" class="secondary">All Sales</a>

    @if (auth()->user()->isAdmin())
        @if ($sale->isVoided())
            <button type="button" id="open-unvoid" style="background:#10b981; color:#fff;">Unvoid</button>
        @else
            <button type="button" id="open-void" style="background:#ef4444; color:#fff;">Void</button>
        @endif
    @endif
</div>

{{-- Void modal --}}
@if (auth()->user()->isAdmin() && ! $sale->isVoided())
    <div id="void-modal" class="modal-backdrop">
        <div class="modal-box">
            <h2 style="margin-top:0;">Void {{ $sale->invoice_no }}</h2>
            <p class="muted">
                Stock will be restored for each product. The sale will be excluded
                from all reports and totals.
            </p>

            <form method="POST" action="{{ route('sales.void', $sale) }}">
                @csrf
                <div style="margin-bottom:0.75rem;">
                    <label style="display:block; font-size:0.85rem; margin-bottom:0.25rem;">Reason (required)</label>
                    <input type="text" name="void_reason" required maxlength="200"
                           placeholder="e.g. Wrong item, customer cancelled, test sale"
                           style="width:100%; padding:0.55rem 0.7rem; border-radius:6px; border:1px solid #334155; background:#0f172a; color:#e2e8f0; font-size:0.9rem;">
                </div>

                <div style="display:flex; gap:0.5rem;">
                    <button type="submit" class="btn" style="flex:1; background:#ef4444; color:#fff;">
                        Confirm Void
                    </button>
                    <button type="button" class="btn btn-secondary" id="close-void">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const modal = document.getElementById('void-modal');
        document.getElementById('open-void').addEventListener('click', () => modal.classList.add('open'));
        document.getElementById('close-void').addEventListener('click', () => modal.classList.remove('open'));
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
    })();
    </script>
@endif

{{-- Unvoid modal --}}
@if (auth()->user()->isAdmin() && $sale->isVoided())
    <div id="unvoid-modal" class="modal-backdrop">
        <div class="modal-box">
            <h2 style="margin-top:0;">Unvoid {{ $sale->invoice_no }}</h2>
            <p class="muted">
                Stock will be deducted again for each product. The sale will reappear
                in all reports.
            </p>

            <form method="POST" action="{{ route('sales.unvoid', $sale) }}">
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

    <script>
    (function () {
        const modal = document.getElementById('unvoid-modal');
        document.getElementById('open-unvoid').addEventListener('click', () => modal.classList.add('open'));
        document.getElementById('close-unvoid').addEventListener('click', () => modal.classList.remove('open'));
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
    })();
    </script>
@endif

</body>
</html>