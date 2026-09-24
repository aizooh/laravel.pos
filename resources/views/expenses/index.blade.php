@extends('layouts.app')

@section('title', 'Expenses — Mamicar POS')

@section('content')

<div class="card">
    <h2>Record Expense</h2>

    <form method="POST" action="{{ route('expenses.store') }}">
        @csrf

        <div class="grid">
            <div class="field">
                <label>Description</label>
                <input type="text" name="description" value="{{ old('description') }}"
                       placeholder="e.g. Electricity tokens" required>
            </div>
            <div class="field">
                <label>Amount (KSh)</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required>
            </div>
        </div>

        <div class="grid">
            <div class="field">
                <label>Category</label>
                <select name="category" required>
                    @foreach ($categories as $c)
                        <option value="{{ $c }}" {{ old('category') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Date</label>
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required>
            </div>
        </div>

        <button type="submit">Save Expense</button>
    </form>
</div>

<div class="card">
    <h2>Expenses</h2>

    <form method="GET" class="grid-3" style="margin-bottom:1rem;">
        <div class="field" style="margin:0;">
            <label>From</label>
            <input type="date" name="from" value="{{ request('from') }}">
        </div>
        <div class="field" style="margin:0;">
            <label>To</label>
            <input type="date" name="to" value="{{ request('to') }}">
        </div>
        <div class="field" style="margin:0;">
            <label>Category</label>
            <select name="category">
                <option value="">Any</option>
                @foreach ($categories as $c)
                    <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div style="grid-column: span 3; display:flex; gap:0.5rem;">
            <button type="submit">Filter</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="muted" style="margin-bottom:0.5rem;">
        Filtered total: <strong>KSh {{ number_format((float) $total, 2) }}</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th>By</th>
                <th>Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($expenses as $e)
            <tr>
                <form method="POST" action="{{ route('expenses.update', $e) }}">
                    @csrf
                    @method('PUT')
                    <td style="width:130px;">
                        <input type="date" name="date" value="{{ $e->date->toDateString() }}" required>
                    </td>
                    <td>
                        <input type="text" name="description" value="{{ $e->description }}" required>
                    </td>
                    <td style="width:150px;">
                        <select name="category" required>
                            @foreach ($categories as $c)
                                <option value="{{ $c }}" {{ $e->category === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="muted">{{ $e->user->name ?? '—' }}</td>
                    <td style="width:130px;">
                        <input type="number" step="0.01" name="amount" value="{{ $e->amount }}" required>
                    </td>
                    <td style="white-space:nowrap;">
                        <button type="submit" class="btn btn-sm">Save</button>
                </form>
                        <form method="POST" action="{{ route('expenses.destroy', $e) }}"
                              style="display:inline;"
                              onsubmit="return confirm('Delete this expense?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">×</button>
                        </form>
                    </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted" style="text-align:center; padding:2rem;">No expenses yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">{{ $expenses->links() }}</div>
</div>

@endsection