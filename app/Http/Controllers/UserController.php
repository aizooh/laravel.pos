<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::query();

        if ($search = $request->input('q')) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $q->where('role', $role);
        }

        $users = $q->orderBy('name')->paginate(20)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'alpha_dash'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', Rule::in(['admin', 'attendant'])],
            'active'   => ['nullable', 'boolean'],
        ]);

        User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'active'   => $request->boolean('active', true),
        ]);

        return redirect()->route('users.index')
            ->with('ok', "User '{$data['username']}' created.");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role'     => ['required', Rule::in(['admin', 'attendant'])],
            'active'   => ['nullable', 'boolean'],
        ]);

        $me       = $request->user();
        $wantsOff = ! $request->boolean('active');

        // Guard: can't disable yourself
        if ($me->id === $user->id && $wantsOff) {
            return back()->withErrors(['active' => 'You cannot deactivate your own account.']);
        }

        // Guard: can't demote yourself
        if ($me->id === $user->id && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        // Guard: don't allow the last admin to be deactivated / demoted
        if ($user->role === 'admin' && ($wantsOff || $data['role'] !== 'admin')) {
            $activeAdmins = User::where('role', 'admin')->where('active', true)->count();
            if ($activeAdmins <= 1) {
                return back()->withErrors(['role' => 'Cannot demote or deactivate the only remaining admin.']);
            }
        }

        $user->name     = $data['name'];
        $user->username = $data['username'];
        $user->role     = $data['role'];
        $user->active   = $request->boolean('active');

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('ok', "User '{$user->username}' updated.");
    }
}