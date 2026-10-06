<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'allocore', 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(self::ROLES)],
            'company_key' => ['nullable', 'string', 'max:120'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'company_key' => $data['company_key'] ?: null,
        ]);

        return back()->with('status', __('ui.user_created'));
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

    /**
     * Rename (or merge) a company_key across users, signals and recommendations.
     */
    public function renameCompany(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'allocore', 403);

        $data = $request->validate([
            'from' => ['required', 'string', 'max:120'],
            'to' => ['required', 'string', 'max:120', 'different:from'],
        ]);

        $from = $data['from'];
        $to = $data['to'];

        $counts = [
            'users' => User::where('company_key', $from)->update(['company_key' => $to]),
            'signals' => Signal::where('company_key', $from)->update(['company_key' => $to]),
            'recommendations' => Recommendation::where('company_key', $from)->update(['company_key' => $to]),
        ];

        return back()->with('status', __('ui.company_renamed', [
            'from' => $from, 'to' => $to, 'n' => array_sum($counts),
        ]));
    }
}
