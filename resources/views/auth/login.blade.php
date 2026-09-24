<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mamicar POS — Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #0f172a; color: #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; margin: 0;
        }
        .card {
            background: #1e293b; padding: 2rem; border-radius: 10px;
            width: 100%; max-width: 380px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }
        h1 { margin: 0 0 0.25rem; font-size: 1.5rem; color: #fff; }
        p.sub { margin: 0 0 1.5rem; color: #94a3b8; font-size: 0.9rem; }
        label { display: block; margin-bottom: 0.35rem; font-size: 0.85rem; color: #cbd5e1; }
        input[type=text], input[type=password] {
            width: 100%; padding: 0.65rem 0.75rem; border-radius: 6px;
            border: 1px solid #334155; background: #0f172a; color: #fff;
            font-size: 0.95rem; outline: none;
        }
        input:focus { border-color: #38bdf8; }
        .field { margin-bottom: 1rem; }
        .row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.85rem; color: #cbd5e1; }
        button {
            width: 100%; padding: 0.75rem; border: none; border-radius: 6px;
            background: #38bdf8; color: #0f172a; font-weight: 600;
            font-size: 0.95rem; cursor: pointer;
        }
        button:hover { background: #0ea5e9; }
        .error {
            background: #7f1d1d; color: #fecaca; padding: 0.6rem 0.75rem;
            border-radius: 6px; font-size: 0.85rem; margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Mamicar POS</h1>
        <p class="sub">Sign in to continue</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="{{ old('username') }}" autofocus required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="row">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember" style="margin:0;">Remember me</label>
            </div>

            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>