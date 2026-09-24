@php $total = 0; @endphp

@if (empty($cart))
    <p class="muted" style="text-align:center; padding:2rem 0;">Cart is empty</p>
@else
    @foreach ($cart as $key => $it)
        @php $total += $it['price'] * $it['qty']; @endphp
        <div class="cart-row">
            <div>
                <div class="cr-name">{{ $it['name'] }}</div>
                <div class="cr-price">KSh {{ number_format($it['price']) }} × {{ $it['qty'] }}</div>
            </div>
            <input type="number" class="cart-qty" data-key="{{ $key }}" value="{{ $it['qty'] }}" min="1">
            <div style="font-weight:600;">{{ number_format($it['price'] * $it['qty']) }}</div>
            <button type="button" class="btn btn-danger btn-sm cart-remove" data-key="{{ $key }}">×</button>
        </div>
    @endforeach

    <div class="cart-total">
        <span>TOTAL</span>
        <span>KSh {{ number_format($total) }}</span>
    </div>

    <button type="button" id="open-checkout" class="btn" style="width:100%; padding:0.9rem; margin-top:0.5rem;">
        Checkout
    </button>
@endif