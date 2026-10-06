<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    private const array ROLES = ['member', 'platform_manager', 'allocore', 'disavo'];

    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'allocore', 403);

        return view('users', [
            'user' => $request->user(),
            'users' => User::orderBy('name')->get(),
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->role === 'allocore', 403);

        $data = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
            'company_key' => ['nullable', 'string', 'max:120'],
        ]);

        if ($user->id === $request->user()->id && $data['role'] !== 'allocore') {
            return back()->withErrors(['role' => __('ui.cannot_demote_self')]);
        }

        $user->update([
            'role' => $data['role'],
            'company_key' => $data['company_key'] ?: null,
        ]);

        return back()->with('status', __('ui.user_updated'));
    }
}
