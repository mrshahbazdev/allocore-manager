<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    /**
     * Signed single-sign-on from a DISAVO app (e.g. allocore.de).
     * The app builds /sso/{provider}?email&name&company&role&ts&sig where
     * sig = HMAC-SHA256 of "email|name|company|role|ts" using the shared secret.
     * The Manager creates/links the user on first sign-in.
     */
    public function login(Request $request, string $provider): RedirectResponse
    {
        $secret = (string) config('services.sso.secret', '');
        abort_if($secret === '', 403);

        $data = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'role' => 'nullable|in:member,platform_manager,allocore,disavo',
            'ts' => 'required|integer',
            'sig' => 'required|string|size:64',
        ]);

        abort_if(abs(time() - (int) $data['ts']) > 600, 403);

        $payload = implode('|', [
            $data['email'],
            $data['name'],
            $data['company'] ?? '',
            $data['role'] ?? '',
            $data['ts'],
        ]);

        abort_unless(hash_equals(hash_hmac('sha256', $payload, $secret), $data['sig']), 403);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Str::password(32),
                'role' => $data['role'] ?? 'member',
                'company_key' => $data['company'] ?? null,
            ]);
        } else {
            $user->fill([
                'name' => $data['name'],
                'company_key' => $data['company'] ?? $user->company_key,
            ])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect('/app');
    }
}
