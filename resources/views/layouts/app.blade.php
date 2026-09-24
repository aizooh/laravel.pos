<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Mamicar')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #0f172a; color: #e2e8f0;
            margin: 0; min-height: 100vh;
        }
        header {
            background: #1e293b; padding: 1rem 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid #334155;
        }
        header h1 { margin: 0; font-size: 1.1rem; color: #fff; }
        header nav { display: flex; gap: 1rem; align-items: center; }
        header nav a {
            color: #cbd5e1; text-decoration: none;
            font-size: 0.9rem; padding: 0.35rem 0.7rem; border-radius: 6px;
        }
        header nav a:hover { background: #334155; color: #fff; }
        header nav a.active { background: #38bdf8; color: #0f172a; font-weight: 600; }
        main { max-width: 2000px; margin: 0 auto; padding: 1.5rem; }
        .card {
            background: #1e293b; padding: 1.5rem;
            border-radius: 10px; margin-bottom: 1.25rem;
            border: 1px solid #334155;
        }
        h2 { margin: 0 0 1rem; font-size: 1.25rem; color: #fff; }
        h3 { margin: 1.5rem 0 0.75rem; font-size: 1rem; color: #cbd5e1; }
        label { display: block; margin-bottom: 0.35rem; font-size: 0.85rem; color: #cbd5e1; }
        input[type=text], input[type=number], input[type=password], input[type=email], select {
            width: 100%; padding: 0.6rem 0.75rem; border-radius: 6px;
            border: 1px solid #334155; background: #0f172a; color: #fff;
            font-size: 0.95rem; outline: none;
        }
        input:focus, select:focus { border-color: #38bdf8; }
        .field { margin-bottom: 1rem; }
        .grid { display: grid; gap: 1rem; grid-template-columns: 1fr 1fr; }
        .grid-3 { display: grid; gap: 1rem; grid-template-columns: 1fr 1fr 1fr; }
        button, .btn {
            padding: 0.6rem 1rem; border-radius: 6px; border: none;
            background: #38bdf8; color: #0f172a; font-weight: 600;
            font-size: 0.9rem; cursor: pointer; text-decoration: none;
            display: inline-block;
        }
        button:hover, .btn:hover { background: #0ea5e9; }
        .btn-secondary { background: #334155; color: #e2e8f0; }
        .btn-secondary:hover { background: #475569; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 0.35rem 0.7rem; font-size: 0.8rem; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { padding: 0.6rem 0.5rem; text-align: left; border-bottom: 1px solid #334155; }
        th { color: #94a3b8; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; }
        tr:hover td { background: #243449; }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-ok { background: #064e3b; color: #a7f3d0; }
        .alert-err { background: #7f1d1d; color: #fecaca; }
        .badge {
            display: inline-block; padding: 0.15rem 0.55rem; border-radius: 999px;
            font-size: 0.75rem; font-weight: 600;
        }
        .badge-ok { background: #064e3b; color: #a7f3d0; }
        .badge-warn { background: #78350f; color: #fed7aa; }
        .badge-danger { background: #7f1d1d; color: #fecaca; }
        .badge-muted { background: #334155; color: #cbd5e1; }
        .muted { color: #94a3b8; font-size: 0.85rem; }
        .row-actions { display: flex; gap: 0.4rem; }
    </style>
</head>
<body>
    <header>
        <h1>Mamicar</h1>
       <nav>
    <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">POS</a>
    <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">Sales</a>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
    <a href="{{ route('positions.index') }}" class="{{ request()->routeIs('positions.*') ? 'active' : '' }}">Position</a>
   @if (auth()->user()->isAdmin())
    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">Inventory</a>
    <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'active' : '' }}">Services</a>
    <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">Expenses</a>
    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Reports</a>
    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">Users</a>
    <a href="{{ route('positions.index') }}" class="btn btn-secondary">Position</a>
    @endif
    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn-secondary btn-sm">Log Out</button>
    </form>
</nav>
    </header>

    <main>
        @if (session('ok'))
            <div class="alert alert-ok">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-err">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>