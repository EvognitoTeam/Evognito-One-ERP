<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'login' => [
                'required',
                'string',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],

            'remember' => [
                'nullable',
                'boolean',
            ],
        ]);

        $login = trim(
            $validated['login']
        );

        $field = filter_var(
            $login,
            FILTER_VALIDATE_EMAIL
        )
            ? 'email'
            : 'username';

        $credentials = [
            $field => $login,
            'password' => $validated['password'],
            'status' => 'active',
        ];

        if (
            !Auth::attempt(
                $credentials,
                $request->boolean('remember')
            )
        ) {
            return back()
                ->withErrors([
                    'login' =>
                    'Email/username atau password tidak valid.',
                ])
                ->onlyInput('login');
        }

        $request->session()
            ->regenerate();

        $user = $request->user();

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        /*
        |--------------------------------------------------------------------------
        | Platform Superadmin
        |--------------------------------------------------------------------------
        */

        if ($user->isSuperAdmin()) {
            return redirect()
                ->intended(
                    route(
                        'superadmin.dashboard'
                    )
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Organization User
        |--------------------------------------------------------------------------
        |
        | Prioritas:
        |
        | 1. Default organization
        | 2. Organization aktif pertama
        |
        */

        $organization = $user
            ->organizations()
            ->wherePivot(
                'status',
                'active'
            )
            ->where(
                'organizations.status',
                'active'
            )
            ->orderByPivot(
                'is_default',
                'desc'
            )
            ->orderBy(
                'organizations.name'
            )
            ->first();

        if (!$organization) {
            Auth::logout();

            $request->session()
                ->invalidate();

            $request->session()
                ->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'login' =>
                    'Akun ini belum terhubung dengan organization aktif.',
                ]);
        }

        return redirect()
            ->intended(
                route(
                    'workspace.dashboard',
                    [
                        'organization' =>
                        $organization->slug,
                    ]
                )
            );
    }

    public function destroy(
        Request $request
    ): RedirectResponse {
        Auth::logout();

        $request->session()
            ->invalidate();

        $request->session()
            ->regenerateToken();

        return redirect()
            ->route('login');
    }
}
