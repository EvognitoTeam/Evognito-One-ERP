<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search =
            trim(
                (string) $request->get(
                    'search'
                )
            );

        $category =
            trim(
                (string) $request->get(
                    'category'
                )
            );

        $status =
            trim(
                (string) $request->get(
                    'status'
                )
            );

        $integrations =
            Integration::query()
            ->with('creator')

            ->when(
                $search !== '',
                function ($query) use (
                    $search
                ) {
                    $query->where(
                        function (
                            $subQuery
                        ) use (
                            $search
                        ) {
                            $subQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'provider',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $category !== '',
                fn($query) =>
                $query->where(
                    'category',
                    $category
                )
            )

            ->when(
                $status !== '',
                fn($query) =>
                $query->where(
                    'status',
                    $status
                )
            )

            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view(
            'superadmin.integrations.index',
            compact(
                'integrations',
                'search',
                'category',
                'status'
            )
        );
    }

    public function create(): View
    {
        return view(
            'superadmin.integrations.create'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this->validateIntegration(
                $request
            );

        $credentials =
            $this->credentialsFromRequest(
                $request
            );

        $integration =
            Integration::create([
                'ulid' =>
                (string) Str::ulid(),

                'code' =>
                $validated['code'],

                'name' =>
                $validated['name'],

                'category' =>
                $validated['category'],

                'provider' =>
                $validated['provider']
                    ?: null,

                'environment' =>
                $validated['environment'],

                'status' =>
                $validated['status'],

                'base_url' =>
                $validated['base_url']
                    ?? null,

                'webhook_url' =>
                $validated['webhook_url']
                    ?? null,

                'config' =>
                $this->decodeConfig(
                    $validated['config_json']
                        ?? null
                ),

                'credentials' =>
                $credentials ?: null,

                'notes' =>
                $validated['notes']
                    ?? null,

                'created_by' =>
                $request->user()->id,
            ]);

        return redirect()
            ->route(
                'superadmin.integrations.show',
                $integration
            )
            ->with(
                'success',
                'Integration berhasil dibuat.'
            );
    }

    public function show(
        Integration $integration
    ): View {
        $integration->load(
            'creator'
        );

        return view(
            'superadmin.integrations.show',
            compact('integration')
        );
    }

    public function edit(
        Integration $integration
    ): View {
        return view(
            'superadmin.integrations.edit',
            compact('integration')
        );
    }

    public function update(
        Request $request,
        Integration $integration
    ): RedirectResponse {
        $validated =
            $this->validateIntegration(
                $request,
                $integration
            );

        $credentials =
            $integration->credentials
            ?? [];

        if (
            $request->boolean(
                'clear_credentials'
            )
        ) {
            $credentials = [];
        }

        foreach (
            $this->credentialsFromRequest(
                $request
            )
            as $key => $value
        ) {
            $credentials[$key] =
                $value;
        }

        $integration->update([
            'code' =>
            $validated['code'],

            'name' =>
            $validated['name'],

            'category' =>
            $validated['category'],

            'provider' =>
            $validated['provider']
                ?: null,

            'environment' =>
            $validated['environment'],

            'status' =>
            $validated['status'],

            'base_url' =>
            $validated['base_url']
                ?? null,

            'webhook_url' =>
            $validated['webhook_url']
                ?? null,

            'config' =>
            $this->decodeConfig(
                $validated['config_json']
                    ?? null
            ),

            'credentials' =>
            $credentials ?: null,

            'notes' =>
            $validated['notes']
                ?? null,
        ]);

        return redirect()
            ->route(
                'superadmin.integrations.show',
                $integration
            )
            ->with(
                'success',
                'Integration berhasil diperbarui.'
            );
    }

    public function toggleStatus(
        Integration $integration
    ): RedirectResponse {
        $integration->update([
            'status' =>
            $integration->status
                === 'active'
                ? 'inactive'
                : 'active',
        ]);

        return back()->with(
            'success',
            'Status integration berhasil diperbarui.'
        );
    }

    protected function validateIntegration(
        Request $request,
        ?Integration $integration = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'integrations',
                    'code'
                )->ignore(
                    $integration?->id
                ),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'required',

                Rule::in([
                    'payment',
                    'messaging',
                    'email',
                    'storage',
                    'identity',
                    'analytics',
                    'webhook',
                    'other',
                ]),
            ],

            'provider' => [
                'nullable',
                'string',
                'max:100',
            ],

            'environment' => [
                'required',

                Rule::in([
                    'sandbox',
                    'production',
                    'internal',
                ]),
            ],

            'status' => [
                'required',

                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'base_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'webhook_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'config_json' => [
                'nullable',
                'json',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'api_key' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'secret_key' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'client_id' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'client_secret' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'username' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'integration_password' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'clear_credentials' => [
                'nullable',
                'boolean',
            ],
        ]);
    }

    protected function credentialsFromRequest(
        Request $request
    ): array {
        $mapping = [
            'api_key' =>
            'api_key',

            'secret_key' =>
            'secret_key',

            'client_id' =>
            'client_id',

            'client_secret' =>
            'client_secret',

            'username' =>
            'username',

            'integration_password' =>
            'password',
        ];

        $credentials = [];

        foreach (
            $mapping
            as $input => $key
        ) {
            $value =
                trim(
                    (string) $request->input(
                        $input,
                        ''
                    )
                );

            if ($value !== '') {
                $credentials[$key] =
                    $value;
            }
        }

        return $credentials;
    }

    protected function decodeConfig(
        ?string $config
    ): ?array {
        if (
            $config === null
            || trim($config) === ''
        ) {
            return null;
        }

        return json_decode(
            $config,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
