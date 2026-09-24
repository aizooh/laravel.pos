@extends('layouts.app')

@section('title', 'Users — Mamicar POS')

@section('content')

<div class="card">
    <h2>Add User</h2>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div class="grid">
            <div class="field">
                <label>Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="field">
                <label>Username</label>
                <input type="text" name="username" value="{{ old('username') }}"
                       pattern="[A-Za-z0-9_-]+" title="Letters, numbers, underscores, dashes only" required>
            </div>
        </div>

        <div class="grid">
            <div class="field">
                <label>Password (min 6)</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <div class="field">
                <label>Role</label>
                <select name="role" required>
                    <option value="attendant" {{ old('role') === 'attendant' ? 'selected' : '' }}>Attendant</option>
                    <option value="admin"     {{ old('role') === 'admin'     ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
        </div>

        <div class="field">
            <label style="display:flex; align-items:center; gap:0.5rem;">
                <input type="checkbox" name="active" value="1" checked>
                Active (can log in)
            </label>
        </div>

        <button type="submit">Create User</button>
    </form>
</div>

<div class="card">
    <h2>Users</h2>

    <form method="GET" style="display:flex; gap:0.5rem; align-items:end; margin-bottom:1rem;">
        <div class="field" style="flex:1; margin:0;">
            <label>Search</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name or username...">
        </div>
        <div class="field" style="margin:0;">
            <label>Role</label>
            <select name="role">
                <option value="">Any</option>
                <option value="admin"     {{ request('role') === 'admin'     ? 'selected' : '' }}>Admin</option>
                <option value="attendant" {{ request('role') === 'attendant' ? 'selected' : '' }}>Attendant</option>
            </select>
        </div>
        <button type="submit">Filter</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th style="width:150px;">Role</th>
                <th style="width:150px;">New Password</th>
                <th style="width:120px;">Status</th>
                <th style="width:140px;"></th>
            </tr>
        </thead>
        <tbody>
        @foreach ($users as $u)
            <tr>
                <form method="POST" action="{{ route('users.update', $u) }}">
                    @csrf
                    @method('PUT')
                    <td>
                        <input type="text" name="name" value="{{ $u->name }}" required>
                    </td>
                    <td>
                        <input type="text" name="username" value="{{ $u->username }}"
                               pattern="[A-Za-z0-9_-]+" required>
                    </td>
                    <td>
                        <select name="role" required {{ auth()->id() === $u->id ? 'disabled' : '' }}>
                            <option value="attendant" {{ $u->role === 'attendant' ? 'selected' : '' }}>Attendant</option>
                            <option value="admin"     {{ $u->role === 'admin'     ? 'selected' : '' }}>Admin</option>
                        </select>
                        @if (auth()->id() === $u->id)
                            <input type="hidden" name="role" value="{{ $u->role }}">
                        @endif
                    </td>
                    <td>
                        <input type="password" name="password" placeholder="leave blank" minlength="6">
                    </td>
                    <td>
                        @php $self = auth()->id() === $u->id; @endphp
                        <label style="display:flex; align-items:center; gap:0.4rem; margin:0; font-size:0.85rem;">
                            <input type="checkbox" name="active" value="1"
                                   {{ $u->active ? 'checked' : '' }}
                                   {{ $self ? 'disabled' : '' }}>
                            {{ $u->active ? 'Active' : 'Off' }}
                        </label>
                        @if ($self)
                            <input type="hidden" name="active" value="1">
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        <button type="submit" class="btn btn-sm">Save</button>
                    </td>
                </form>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div style="margin-top:1rem;">{{ $users->links() }}</div>
</div>

@endsection