@extends('layouts.app')

@section('title', 'Services — Mamicar POS')

@section('content')

<div class="card">
    <h2>Add Service</h2>
    <form method="POST" action="{{ route('services.store') }}"
          style="display:flex; gap:0.75rem; align-items:end;">
        @csrf
        <div class="field" style="flex:2; margin:0;">
            <label>Service Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   placeholder="e.g. Printing">
        </div>
        <div class="field" style="flex:1; margin:0;">
            <label>Price (KSh)</label>
            <input type="number" step="0.01" name="price" value="{{ old('price') }}" required>
        </div>
        <button type="submit">Add Service</button>
    </form>
</div>

<div class="card">
    <h2>Cyber Services</h2>

    <form method="GET" style="margin-bottom:1rem;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search services...">
    </form>

    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th style="width:140px;">Price (KSh)</th>
                <th style="width:110px;">Status</th>
                <th style="width:180px;"></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($services as $s)
            <tr>
                <form method="POST" action="{{ route('services.update', $s) }}">
                    @csrf
                    @method('PUT')
                    <td>
                        <input type="text" name="name" value="{{ $s->name }}" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="price" value="{{ $s->price }}" required>
                    </td>
                    <td>
                        <label style="display:flex; align-items:center; gap:0.4rem; margin:0; font-size:0.85rem;">
                            <input type="checkbox" name="active" value="1" {{ $s->active ? 'checked' : '' }}>
                            {{ $s->active ? 'Active' : 'Off' }}
                        </label>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-sm">Save</button>
                    </td>
                </form>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="muted" style="text-align:center; padding:2rem;">
                    No services yet. Add your first one above.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">{{ $services->links() }}</div>
</div>

@endsection