@extends('layouts.app')

@section('title', 'POS — Mamicar POS')
@section('main-class', 'wide')

@section('content')

@if (session('ok'))
    <div class="alert alert-ok">{{ session('ok') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-err">{{ $errors->first() }}</div>
@endif

<div class="pos-wrap">

    {{-- ============ LEFT: SEARCH + ITEM GRIDS ============ --}}
    <div class="pos-left">

        <div class="card pos-search-card">
            <input type="text" id="pos-search"
                   placeholder="🔍 Search products or services..."
                   autofocus autocomplete="off">
        </div>

        <div class="card">
            <h3 class="section-title section-products" style="margin-top:0;">Products</h3>
            <div id="product-grid" class="pos-grid">
                @forelse ($products as $p)
                    <button type="button" class="pos-item pos-item-product"
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

            <h3 class="section-title section-services">Services</h3>
            <div id="service-grid" class="pos-grid">
                @forelse ($services as $s)
                    <button type="button" class="pos-item pos-item-service"
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

    {{-- ============ RIGHT: CART (desktop) / DRAWER (mobile) ============ --}}
    <div class="pos-cart-backdrop" id="cart-backdrop"></div>

    <div class="pos-cart" id="cart-panel-wrap">
        <div class="pos-cart-header">
            <h2 style="margin:0;">Cart <span class="cart-count-badge" id="cart-count">0</span></h2>
            <div style="display:flex; gap:0.4rem;">
                <button type="button" id="clear-cart" class="btn btn-secondary btn-sm">Clear</button>
                <button type="button" id="close-cart" class="btn btn-secondary btn-sm cart-close-btn">✕</button>
            </div>
        </div>

        <div id="cart-panel" class="pos-cart-body">
            @include('pos._cart', ['cart' => $cart])
        </div>
    </div>

    {{-- Mobile floating cart button --}}
    <button type="button" id="open-cart" class="cart-float">
        🛒 <span id="cart-count-float">0</span>
    </button>
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
    /* ---------- Layout ---------- */
    .pos-wrap {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1rem;
        align-items: start;
        position: relative;
    }
    .pos-left { min-width: 0; }
    .pos-search-card { padding: 0.75rem; }

    /* ---------- Grids ---------- */
    .pos-grid {
        display: grid;
        gap: 0.6rem;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }

    /* ---------- Item cards ---------- */
    .pos-item {
        text-align: left;
        padding: 0.85rem 0.75rem;
        border-radius: 10px;
        background: #1a1f3d;
        border: 1px solid #334155;
        border-left: 4px solid #38bdf8;
        color: #e2e8f0;
        cursor: pointer;
        font: inherit;
        transition: all 0.12s;
        min-height: 90px;
    }
    .pos-item:hover,
    .pos-item:active {
        background: #38bdf8;
        color: #0f172a;
        border-color: #38bdf8;
    }
    .pos-item:hover .pi-sku,
    .pos-item:hover .pi-stock,
    .pos-item:hover .pi-price { color: #0f172a; }

    .pos-item-service {
        background: #1a1f3d;
        border-left-color: #a78bfa;
    }
    .pos-item-service .pi-price { color: #c4b5fd; }
    .pos-item-service:hover,
    .pos-item-service:active {
        background: #7c3aed;
        border-color: #7c3aed;
        border-left-color: #7c3aed;
        color: #fff;
    }
    .pos-item-service:hover .pi-price { color: #fff; }

    .pi-name { font-weight: 600; font-size: 0.92rem; margin-bottom: 0.2rem; line-height: 1.2; }
    .pi-sku, .pi-stock { font-size: 0.75rem; color: #94a3b8; }
    .pi-price { color: #38bdf8; font-weight: 700; font-size: 0.95rem; margin: 0.3rem 0; }

    /* ---------- Section titles ---------- */
    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding-bottom: 0.4rem;
        margin: 1.25rem 0 0.75rem;
    }
    .section-products { color: #38bdf8; border-bottom: 2px solid #38bdf8; }
    .section-services { color: #a78bfa; border-bottom: 2px solid #a78bfa; }

    /* ---------- Cart (desktop) ---------- */
    .pos-cart {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 10px;
        padding: 1rem;
        position: sticky;
        top: 1rem;
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
    }
    .pos-cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #334155;
    }
    .pos-cart-body {
        overflow-y: auto;
        flex: 1;
        min-height: 100px;
    }
    .cart-count-badge {
        background: #38bdf8;
        color: #0f172a;
        font-size: 0.75rem;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        font-weight: 700;
    }
    .cart-close-btn { display: none; }
    .cart-float { display: none; }
    .pos-cart-backdrop { display: none; }

    /* ---------- Cart rows ---------- */
    .cart-row {
        display: grid;
        grid-template-columns: 1fr auto auto auto;
        gap: 0.5rem;
        align-items: center;
        padding: 0.6rem 0;
        border-bottom: 1px solid #334155;
    }
    .cart-row .cr-name { font-size: 0.9rem; font-weight: 500; }
    .cart-row .cr-price { font-size: 0.75rem; color: #94a3b8; }
    .cart-qty {
        width: 55px;
        text-align: center;
        padding: 0.4rem;
        border-radius: 6px;
        border: 1px solid #334155;
        background: #0f172a;
        color: #fff;
        font-size: 0.9rem;
    }
    .cart-total {
        display: flex;
        justify-content: space-between;
        padding: 1rem 0;
        font-size: 1.15rem;
        font-weight: 700;
        border-top: 2px solid #334155;
        margin-top: 0.5rem;
    }

    /* ---------- Modal ---------- */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.7);
        z-index: 200;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-backdrop.open { display: flex; }
    .modal-box {
        background: #1e293b;
        padding: 1.5rem;
        border-radius: 10px;
        width: 100%;
        max-width: 420px;
        border: 1px solid #334155;
    }
    .pay-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
    .pay-opt {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.75rem 0.5rem;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
    }
    .pay-opt:has(input:checked) {
        background: #38bdf8;
        color: #0f172a;
        border-color: #38bdf8;
        font-weight: 600;
    }

    /* ================================================
       MOBILE  (< 900px)
       ================================================ */
    @media (max-width: 900px) {
        .pos-wrap {
            grid-template-columns: 1fr;
            padding-bottom: 80px; /* space for floating cart btn */
        }

        .pos-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.5rem;
        }

        .pos-item {
            padding: 0.7rem 0.6rem;
            min-height: 82px;
        }
        .pi-name { font-size: 0.85rem; }

        /* Cart becomes a bottom drawer */
        .pos-cart {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            top: auto;
            max-height: 85vh;
            border-radius: 16px 16px 0 0;
            padding: 1.25rem 1rem 1rem;
            transform: translateY(100%);
            transition: transform 0.25s ease;
            z-index: 100;
            margin: 0;
        }
        .pos-cart.open {
            transform: translateY(0);
        }
        .cart-close-btn { display: inline-block; }

        .pos-cart-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 99;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }
        .pos-cart-backdrop.open {
            opacity: 1;
            pointer-events: auto;
        }

        /* Floating cart button */
        .cart-float {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            background: #38bdf8;
            color: #0f172a;
            border: none;
            border-radius: 999px;
            padding: 0.85rem 1.25rem;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 8px 24px rgba(56,189,248,0.4);
            cursor: pointer;
            z-index: 90;
        }
        .cart-float:active {
            transform: scale(0.97);
        }

        .pos-search-card { padding: 0.5rem; position: sticky; top: 60px; z-index: 40; }

        .modal-box { padding: 1.25rem; }

        .cart-qty {
            width: 60px;
            padding: 0.5rem;
            font-size: 1rem;
        }
    }

    /* Small phones */
    @media (max-width: 400px) {
        .pos-grid {
            grid-template-columns: 1fr 1fr;
        }
        .pi-price { font-size: 0.9rem; }
    }
</style>

<script>
(function () {
    const csrf        = document.querySelector('meta[name="csrf-token"]').content;
    const searchEl    = document.getElementById('pos-search');
    const productGrid = document.getElementById('product-grid');
    const serviceGrid = document.getElementById('service-grid');
    const cartPanel   = document.getElementById('cart-panel');
    const cartWrap    = document.getElementById('cart-panel-wrap');
    const backdrop    = document.getElementById('cart-backdrop');
    const modal       = document.getElementById('checkout-modal');
    const mpesaField  = document.getElementById('mpesa-field');
    const cartCount   = document.getElementById('cart-count');
    const cartCountF  = document.getElementById('cart-count-float');

    // ---------- Cart drawer (mobile) ----------
    function openCart()  { cartWrap.classList.add('open');    backdrop.classList.add('open'); }
    function closeCart() { cartWrap.classList.remove('open'); backdrop.classList.remove('open'); }

    document.addEventListener('click', (e) => {
        if (e.target.closest('#open-cart'))  { openCart();  return; }
        if (e.target.closest('#close-cart')) { closeCart(); return; }
        if (e.target.id === 'cart-backdrop') { closeCart(); return; }
    });

    // ---------- Search ----------
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
        const cls = type === 'service' ? 'pos-item pos-item-service' : 'pos-item pos-item-product';
        return `<button type="button" class="${cls}" data-type="${type}" data-id="${id}">
            <div class="pi-name">${escapeHtml(name)}</div>
            ${sku ? `<div class="pi-sku">${escapeHtml(sku)}</div>` : ''}
            <div class="pi-price">KSh ${formatNum(price)}</div>
            ${stock !== null && stock !== undefined ? `<div class="pi-stock">${stock} in stock</div>` : ''}
        </button>`;
    }

    // ---------- Add to cart ----------
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

    // ---------- Qty change ----------
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

    // ---------- Remove ----------
    cartPanel.addEventListener('click', (e) => {
        const btn = e.target.closest('.cart-remove');
        if (!btn) return;
        postJSON('{{ route('pos.remove') }}', { key: btn.dataset.key })
            .then(res => res.ok && renderCart(res.data));
    });

    // ---------- Clear ----------
    document.getElementById('clear-cart').addEventListener('click', () => {
        if (!confirm('Clear the cart?')) return;
        fetch('{{ route('pos.clear') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        }).then(r => r.json()).then(renderCart);
    });

    // ---------- Checkout modal ----------
    document.addEventListener('click', (e) => {
        if (e.target.id === 'open-checkout') { modal.classList.add('open'); }
    });
    document.getElementById('close-checkout').addEventListener('click', () => modal.classList.remove('open'));
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
    modal.querySelectorAll('input[name="payment_method"]').forEach(r => {
        r.addEventListener('change', () => {
            mpesaField.style.display = (r.value === 'mpesa' && r.checked) ? 'block' : 'none';
        });
    });

    // ---------- Helpers ----------
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
        const count   = data.count || 0;

        cartCount.textContent  = count;
        cartCountF.textContent = count;

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
        <button type="button" id="open-checkout" class="btn"
                style="width:100%; padding:0.9rem; margin-top:0.5rem; font-size:1rem;">
            Checkout
        </button>`;

        cartPanel.innerHTML = html;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    }
    function formatNum(n) {
        return Number(n).toLocaleString('en-KE', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    // Initial count on page load
    (function initCount() {
        let count = 0;
        document.querySelectorAll('#cart-panel .cart-qty').forEach(i => {
            count += parseInt(i.value, 10) || 0;
        });
        cartCount.textContent  = count;
        cartCountF.textContent = count;
    })();
})();
</script>

@endsection