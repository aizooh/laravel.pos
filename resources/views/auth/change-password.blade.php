@extends('layouts.app')

@section('title', 'Change Password — Mamicar POS')

@section('content')
<div style="max-width:480px; margin:2rem auto;">
    <div class="card">
        <h2 style="margin-top:0;">Change Password</h2>

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf

            <div class="field">
                <label>Current Password</label>
                <input type="password" name="current_password" required autofocus>
            </div>

            <div class="field">
                <label>New Password</label>
                <input type="password" name="password" required minlength="6">
                <div class="muted" style="font-size:0.75rem; margin-top:0.35rem;">
                    Minimum 6 characters.
                </div>
            </div>

            <div class="field">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn" style="width:100%;">Update Password</button>
        </form>
    </div>
</div>
@endsection