@extends('layouts.app')

@section('title', 'POS — Mamicar POS')

@section('content')

@if (session('ok'))
    <div class="alert alert-ok">{{ session('ok') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-err">{{ $errors->first() }}</div>
@endif

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:1rem; align-items:start;">

    {{-- LEFT: search + item grid --}}
    <div>
        <div class="card">
            <input type="text" id="pos-search" placeholder="Search products or services by name or SKU..." autofocus autocomplete="off">
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Products</h3>
            <div id="product-grid" class="pos-grid">
                @forelse ($products as $p)
                    <button type="button" class="pos-item"
                            data-type="product" data-id="{{ $p->id }}">
                        <div class="pi-name">{{ $p->name }}</div>
                        <div class="pi-sku">{{ $p->sku }}</div>
                        <div class="pi-price">KSh {{ number_format($p->selling_price) }}</div>
                        <div class="pi-stock">{{ $p->stock }} in stock</div>
                    </button>
                @empty
                    <div class="muted">No products yet.</div>
                @endforelse
            </div>

            <h3>Services</h3>
            <div id="service-grid" class="pos-grid">
                @forelse ($services as $s)
                    <button type="button" class="pos-item"
                            data-type="service" data-id="{{ $s->id }}">
                        <div class="pi-name">{{ $s->name }}</div>
                        <div class="pi-price">KSh {{ number_format($s->price) }}</div>
                    </button>
                @empty
                    <div class="muted">No services yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RIGHT: cart --}}
    <div class="card" style="position:sticky; top:1rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
            <h2 style="margin:0;">Cart</h2>
            <button type="button" id="clear-cart" class="btn btn-secondary btn-sm">Clear</button>
        </div>

        <div id="cart-panel">
            @include('pos._cart', ['cart' => $cart])
        </div>
    </div>
</div>

{{-- Checkout modal --}}
<div id="checkout-modal" class="modal-backdrop">
    <div class="modal-box">
        <h2 style="margin-top:0;">Complete Sale</h2>

        <form method="POST" action="{{ route('pos.checkout') }}">
            @csrf

            <div class="field">
                <label>Payment Method</label>
                <div class="pay-options">
                    <label class="pay-opt"><input type="radio" name="payment_method" value="cash" checked> Cash</label>
                    <label class="pay-opt"><input type="radio" name="payment_method" value="mpesa"> M-PESA</label>
                    <label class="pay-opt"><input type="radio" name="payment_method" value="card"> Card</label>
                </div>
            </div>

            <div class="field" id="mpesa-field" style="display:none;">
                <label>M-PESA Reference (optional)</label>
                <input type="text" name="mpesa_reference" placeholder="e.g. SLX123ABC" maxlength="50">
            </div>

            <div style="display:flex; gap:0.5rem; margin-top:1.25rem;">
                <button type="submit" class="btn" style="flex:1;">Complete Sale</button>
                <button type="button" class="btn btn-secondary" id="close-checkout">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
    .pos-grid {
        display: grid; gap: 0.6rem;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    .pos-item {
        text-align: left; padding: 0.75rem; border-radius: 8px;
        background: #0f172a; border: 1px solid #334155; color: #e2e8f0;
        cursor: pointer; font: inherit; transition: all 0.12s;
    }
    .pos-item:hover { background: #38bdf8; color: #0f172a; border-color: #38bdf8; }
    .pos-item:hover .pi-sku, .pos-item:hover .pi-stock, .pos-item:hover .pi-price { color: #0f172a; }
    .pi-name { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.2rem; }
    .pi-sku, .pi-stock { font-size: 0.75rem; color: #94a3b8; }
    .pi-price { color: #38bdf8; font-weight: 600; font-size: 0.9rem; margin: 0.25rem 0; }

    .cart-row {
        display: grid; grid-template-columns: 1fr auto auto auto;
        gap: 0.5rem; align-items: center;
        padding: 0.5rem 0; border-bottom: 1px solid #334155;
    }
    .cart-row .cr-name { font-size: 0.9rem; font-weight: 500; }
    .cart-row .cr-price { font-size: 0.75rem; color: #94a3b8; }
    .cart-qty {
        width: 55px; text-align: center; padding: 0.3rem;
        border-radius: 4px; border: 1px solid #334155;
        background: #0f172a; color: #fff;
    }
    .cart-total {
        display: flex; justify-content: space-between;
        padding: 1rem 0; font-size: 1.15rem; font-weight: 700;
        border-top: 2px solid #334155; margin-top: 0.5rem;
    }

    .modal-backdrop {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.6); z-index: 100;
        align-items: center; justify-content: center;
    }
    .modal-backdrop.open { display: flex; }
    .modal-box {
        background: #1e293b; padding: 1.5rem; border-radius: 10px;
        width: 100%; max-width: 420px; border: 1px solid #334155;
    }
    .pay-options { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; }
    .pay-opt {
        display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        padding: 0.65rem; background: #0f172a; border: 1px solid #334155;
        border-radius: 6px; cursor: pointer; font-size: 0.9rem;
    }
    .pay-opt:has(input:checked) { background: #38bdf8; color: #0f172a; border-color: #38bdf8; font-weight: 600; }
</style>

<script>
(function () {
    const csrf        = document.querySelector('meta[name="csrf-token"]').content;
    const searchEl    = document.getElementById('pos-search');
    const productGrid = document.getElementById('product-grid');
    const serviceGrid = document.getElementById('service-grid');
    const cartPanel   = document.getElementById('cart-panel');
    const modal       = document.getElementById('checkout-modal');
    const mpesaField  = document.getElementById('mpesa-field');

    // ---------- search ----------
    let searchTimer;
    searchEl.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 200);
    });

    function runSearch() {
        const q = searchEl.value.trim();
        fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            productGrid.innerHTML = data.products.length
                ? data.products.map(p => itemButton('product', p.id, p.name, p.sku, p.price, p.stock)).join('')
                : '<div class="muted">No matching products</div>';
            serviceGrid.innerHTML = data.services.length
                ? data.services.map(s => itemButton('service', s.id, s.name, null, s.price, null)).join('')
                : '<div class="muted">No matching services</div>';
        });
    }

    function itemButton(type, id, name, sku, price, stock) {
        return `<button type="button" class="pos-item" data-type="${type}" data-id="${id}">
            <div class="pi-name">${escapeHtml(name)}</div>
            ${sku ? `<div class="pi-sku">${escapeHtml(sku)}</div>` : ''}
            <div class="pi-price">KSh ${formatNum(price)}</div>
            ${stock !== null && stock !== undefined ? `<div class="pi-stock">${stock} in stock</div>` : ''}
        </button>`;
    }

    // ---------- add to cart ----------
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.pos-item');
        if (!btn) return;

        postJSON('{{ route('pos.add') }}', {
            type: btn.dataset.type,
            id:   parseInt(btn.dataset.id, 10),
            qty:  1,
        }).then(res => {
            if (!res.ok) { alert(res.data.error || 'Could not add item'); return; }
            renderCart(res.data);
        });
    });

    // ---------- qty change ----------
    cartPanel.addEventListener('change', (e) => {
        const input = e.target.closest('.cart-qty');
        if (!input) return;
        const qty = parseInt(input.value, 10);
        if (isNaN(qty) || qty < 1) { input.value = 1; return; }

        postJSON('{{ route('pos.qty') }}', {
            key: input.dataset.key,
            qty: qty,
        }).then(res => {
            if (!res.ok) { alert(res.data.error || 'Could not update'); return; }
            renderCart(res.data);
        });
    });

    // ---------- remove ----------
    cartPanel.addEventListener('click', (e) => {
        const btn = e.target.closest('.cart-remove');
        if (!btn) return;
        postJSON('{{ route('pos.remove') }}', { key: btn.dataset.key })
            .then(res => res.ok && renderCart(res.data));
    });

    // ---------- clear ----------
    document.getElementById('clear-cart').addEventListener('click', () => {
        if (!confirm('Clear the cart?')) return;
        fetch('{{ route('pos.clear') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        }).then(r => r.json()).then(renderCart);
    });

    // ---------- checkout modal (delegated so it works after re-render) ----------
    document.addEventListener('click', (e) => {
        if (e.target.id === 'open-checkout') {
            modal.classList.add('open');
        }
    });

    document.getElementById('close-checkout').addEventListener('click', () => {
        modal.classList.remove('open');
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('open');
    });

    modal.querySelectorAll('input[name="payment_method"]').forEach(r => {
        r.addEventListener('change', () => {
            mpesaField.style.display = (r.value === 'mpesa' && r.checked) ? 'block' : 'none';
        });
    });

    // ---------- helpers ----------
    function postJSON(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body),
        }).then(r => r.json().then(data => ({ ok: r.ok, data })));
    }

    function renderCart(data) {
        const cart = data.cart || {};
        const entries = Object.entries(cart);

        if (!entries.length) {
            cartPanel.innerHTML = '<p class="muted" style="text-align:center; padding:2rem 0;">Cart is empty</p>';
            return;
        }

        let html = '';
        entries.forEach(([key, it]) => {
            html += `<div class="cart-row">
                <div>
                    <div class="cr-name">${escapeHtml(it.name)}</div>
                    <div class="cr-price">KSh ${formatNum(it.price)} × ${it.qty}</div>
                </div>
                <input type="number" class="cart-qty" data-key="${key}" value="${it.qty}" min="1">
                <div style="font-weight:600;">${formatNum(it.price * it.qty)}</div>
                <button type="button" class="btn btn-danger btn-sm cart-remove" data-key="${key}">×</button>
            </div>`;
        });

        html += `<div class="cart-total">
            <span>TOTAL</span>
            <span>KSh ${formatNum(data.total)}</span>
        </div>
        <button type="button" id="open-checkout" class="btn" style="width:100%; padding:0.9rem; margin-top:0.5rem;">Checkout</button>`;

        cartPanel.innerHTML = html;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    }
    function formatNum(n) {
        return Number(n).toLocaleString('en-KE', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }
})();
</script>

@endsection