@extends('layouts.app')

@section('title', 'New Product — Mamicar POS')

@section('content')
<div class="card">
    <h2>New Product</h2>

    <form method="POST" action="{{ route('products.store') }}">
        @csrf

        <div class="grid">
            <div class="field">
                <label>Product Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="field">
                <label>SKU</label>
                <input type="text" name="sku" value="{{ old('sku') }}" required>
            </div>
        </div>

        <div class="field">
            <label>Category</label>
            <input type="text" name="category" value="{{ old('category') }}"
                   placeholder="e.g. Cables, Phone Accessories">
        </div>

        <div class="grid-3">
            <div class="field">
                <label>Buying Price</label>
                <input type="number" step="0.01" name="buying_price" value="{{ old('buying_price', 0) }}" required>
            </div>
            <div class="field">
                <label>Selling Price</label>
                <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', 0) }}" required>
            </div>
            <div class="field">
                <label>Opening Stock</label>
                <input type="number" name="stock" value="{{ old('stock', 0) }}" required>
            </div>
        </div>

        <div class="field">
            <label>Low Stock Alert (threshold)</label>
            <input type="number" name="low_stock" value="{{ old('low_stock', 5) }}" required>
        </div>

        <div style="display:flex; gap:0.5rem; margin-top:1rem;">
            <button type="submit">Save Product</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection