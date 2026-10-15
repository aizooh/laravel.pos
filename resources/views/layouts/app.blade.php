<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Mamicar POS')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            margin: 0;
            min-height: 100vh;
            -webkit-text-size-adjust: 100%;
        }

        /* ---------- HEADER ---------- */
        header {
            background: #1e293b;
            border-bottom: 1px solid #334155;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .header-inner {
            max-width: 100%;
            margin: 0 auto;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            position: relative;
        }
        header h1 {
            margin: 0;
            font-size: 1.1rem;
            color: #fff;
            white-space: nowrap;
        }
        header nav {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        header nav a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 0.4rem 0.7rem;
            border-radius: 6px;
            white-space: nowrap;
        }
        header nav a:hover { background: #334155; color: #fff; }
        header nav a.active { background: #38bdf8; color: #0f172a; font-weight: 600; }
        header nav form { display: inline; }

        /* Hamburger button (hidden on desktop) */
        .nav-toggle {
            display: none;
            background: transparent;
            border: 1px solid #334155;
            border-radius: 6px;
            width: 42px;
            height: 42px;
            padding: 8px;
            cursor: pointer;
            flex-direction: column;
            justify-content: space-around;
            flex-shrink: 0;
        }
        .nav-toggle span {
            display: block;
            height: 2px;
            background: #cbd5e1;
            border-radius: 2px;
            transition: 0.2s;
        }
        .nav-toggle:hover { border-color: #38bdf8; }

        /* ---------- MAIN ---------- */
        main {
            max-width: 2000px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* ---------- CARDS & TYPOGRAPHY ---------- */
        .card {
            background: #1e293b;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            border: 1px solid #334155;
        }
        h2 { margin: 0 0 1rem; font-size: 1.25rem; color: #fff; }
        h3 { margin: 1.5rem 0 0.75rem; font-size: 1rem; color: #cbd5e1; }

        /* ---------- FORMS ---------- */
        label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.85rem;
            color: #cbd5e1;
        }
        input[type=text], input[type=number], input[type=password],
        input[type=email], input[type=date], input[type=month], select {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #334155;
            background: #0f172a;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
        }
        input:focus, select:focus { border-color: #38bdf8; }
        .field { margin-bottom: 1rem; }
        .grid { display: grid; gap: 1rem; grid-template-columns: 1fr 1fr; }
        .grid-3 { display: grid; gap: 1rem; grid-template-columns: 1fr 1fr 1fr; }

        /* ---------- BUTTONS ---------- */
        button, .btn {
            padding: 0.6rem 1rem;
            border-radius: 6px;
            border: none;
            background: #38bdf8;
            color: #0f172a;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-family: inherit;
        }
        button:hover, .btn:hover { background: #0ea5e9; }
        .btn-secondary { background: #334155; color: #e2e8f0; }
        .btn-secondary:hover { background: #475569; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 0.35rem 0.7rem; font-size: 0.8rem; }

        /* ---------- TABLES ---------- */
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td {
            padding: 0.6rem 0.5rem;
            text-align: left;
            border-bottom: 1px solid #334155;
        }
        th {
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        tr:hover td { background: #243449; }

        /* ---------- ALERTS & BADGES ---------- */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .alert-ok { background: #064e3b; color: #a7f3d0; }
        .alert-err { background: #7f1d1d; color: #fecaca; }

        .badge {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-ok { background: #064e3b; color: #a7f3d0; }
        .badge-warn { background: #78350f; color: #fed7aa; }
        .badge-danger { background: #7f1d1d; color: #fecaca; }
        .badge-muted { background: #334155; color: #cbd5e1; }

        .muted { color: #94a3b8; font-size: 0.85rem; }
        .row-actions { display: flex; gap: 0.4rem; }

        /* ==================================================
           MOBILE  ( <= 900px )
           ================================================== */
        @media (max-width: 900px) {
            .nav-toggle { display: flex; }

            header nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #1e293b;
                border-bottom: 1px solid #334155;
                flex-direction: column;
                align-items: stretch;
                padding: 0.5rem;
                gap: 0.25rem;
                max-height: calc(100vh - 60px);
                overflow-y: auto;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            }
            header nav.open { display: flex; }

            header nav a {
                padding: 0.75rem 1rem;
                border-radius: 6px;
                font-size: 1rem;
                text-align: left;
            }
            header nav form button {
                width: 100%;
                padding: 0.75rem 1rem;
                font-size: 1rem;
            }

            .header-inner { padding: 0.75rem 1rem; }

            main { padding: 0.75rem; }

            /* Stack 2 and 3 column grids into a single column */
            .grid, .grid-3 { grid-template-columns: 1fr; }

            .card { padding: 1rem; }

            h2 { font-size: 1.1rem; }

            /* Tables get tighter, and scrollable if needed */
            table { font-size: 0.85rem; }
            th, td { padding: 0.5rem 0.4rem; }

            /* Any wrapper around tables can scroll horizontally */
            .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        }

        /* Extra small screens */
        @media (max-width: 400px) {
            .header-inner { padding: 0.6rem 0.75rem; }
            header h1 { font-size: 1rem; }
            main { padding: 0.5rem; }
            .card { padding: 0.85rem; border-radius: 8px; }
        }
        
        /* ================================================================
   GLOBAL MOBILE RESPONSIVE OVERRIDES
   Fixes inline grid styles across all views without editing each one
   ================================================================ */

 @media (max-width: 900px) {

    /* Any inline 3- or 4-column grid → 2 columns on mobile */
    main [style*="repeat(4"],
    main [style*="repeat(3"] {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.6rem !important;
    }

    /* Any inline 2-column grid → 1 column */
    main [style*="1fr 1fr 1fr"],
    main [style*="1fr 1fr"]:not([style*="1fr 1fr 1fr"]) {
        grid-template-columns: 1fr !important;
    }

    /* KPI cards — smaller fonts, tighter padding */
    .kpi { padding: 0.9rem !important; }
    .kpi-value { font-size: 1.15rem !important; line-height: 1.2 !important; }
    .kpi-label { font-size: 0.68rem !important; letter-spacing: 0.03em !important; }
    .kpi-sub { font-size: 0.7rem !important; margin-top: 0.15rem !important; }

    /* Payment breakdown boxes — full width */
    .pay-box { padding: 0.75rem !important; }
    .pay-amount { font-size: 1rem !important; }

    /* Cards allow horizontal table scroll if needed */
    .card { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    /* Tables — tighter and scrollable */
    table { font-size: 0.8rem !important; }
    th, td { padding: 0.45rem 0.35rem !important; }

    /* Modals fill more of the screen */
    .modal-box {
        max-width: calc(100vw - 1.5rem) !important;
        padding: 1rem !important;
    }

    /* Report tiles - 4 cols → 2 cols */
    div[style*="grid-template-columns: repeat(4"] {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 600px) {

    /* All inline grids → single column */
    main [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
        gap: 0.5rem !important;
    }

    /* Even smaller KPI on tiny phones */
    .kpi-value { font-size: 1rem !important; }
    .kpi { padding: 0.7rem !important; }

    /* Squeeze the header */
    .header-inner { padding: 0.6rem 0.75rem !important; }
    header h1 { font-size: 1rem !important; }

    /* Cart rows tighten up */
    .cart-row {
        grid-template-columns: 1fr 55px auto auto !important;
        gap: 0.35rem !important;
    }

    /* Section titles smaller */
    .section-title { font-size: 0.85rem !important; }
}
        /* ================================================================
           Laravel pagination — constrain SVGs and make it look decent
           ================================================================ */
        nav[role="navigation"] {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            gap: 0.5rem;
            flex-wrap: wrap;
            font-size: 0.85rem;
        }

        nav[role="navigation"] > div {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        nav[role="navigation"] svg {
            width: 16px !important;
            height: 16px !important;
            display: inline-block;
            flex-shrink: 0;
        }

        nav[role="navigation"] a,
        nav[role="navigation"] span[aria-current="page"] > span,
        nav[role="navigation"] span[aria-disabled="true"] > span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            min-height: 36px;
            padding: 0.35rem 0.6rem;
            border: 1px solid #334155;
            border-radius: 6px;
            background: #0f172a;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.85rem;
        }

        nav[role="navigation"] a:hover {
            background: #334155;
            color: #fff;
        }

        nav[role="navigation"] span[aria-current="page"] > span {
            background: #38bdf8;
            color: #0f172a;
            border-color: #38bdf8;
            font-weight: 700;
        }

        nav[role="navigation"] span[aria-disabled="true"] > span {
            opacity: 0.35;
            cursor: not-allowed;
        }

        @media (max-width: 700px) {
            nav[role="navigation"] > div:first-child {
                display: none;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="header-inner">
            <h1>Mamicar POS</h1>

            <button type="button" id="nav-toggle" class="nav-toggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>

            <nav id="main-nav">
                <a href="{{ route('pos.index') }}"        class="{{ request()->routeIs('pos.*')        ? 'active' : '' }}">POS</a>
                <a href="{{ route('sales.index') }}"      class="{{ request()->routeIs('sales.*')      ? 'active' : '' }}">Sales</a>
                <a href="{{ route('dashboard') }}"        class="{{ request()->routeIs('dashboard')    ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('positions.index') }}"  class="{{ request()->routeIs('positions.*')  ? 'active' : '' }}">Position</a>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">Inventory</a>
                    <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'active' : '' }}">Services</a>
                    <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">Expenses</a>
                    <a href="{{ route('users.index') }}"    class="{{ request()->routeIs('users.*')    ? 'active' : '' }}">Users</a>
                    <a href="{{ route('reports.index') }}"  class="{{ request()->routeIs('reports.*')  ? 'active' : '' }}">Reports</a>
                @endif


                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary btn-sm">Log Out</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="@yield('main-class')">
        @if (session('ok'))
            <div class="alert alert-ok">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-err">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>

    <script>
    (function () {
        const btn = document.getElementById('nav-toggle');
        const nav = document.getElementById('main-nav');
        if (!btn || !nav) return;

        btn.addEventListener('click', () => nav.classList.toggle('open'));

        // Close menu when a link is tapped
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => nav.classList.remove('open'));
        });

        // Close menu if user taps outside
        document.addEventListener('click', (e) => {
            if (!nav.classList.contains('open')) return;
            if (e.target.closest('header')) return;
            nav.classList.remove('open');
        });
    })();
    </script>

</body>
</html>