<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModuleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->get(
                'search'
            )
        );

        $status = trim(
            (string) $request->get(
                'status'
            )
        );

        $type = trim(
            (string) $request->get(
                'type'
            )
        );

        $category = trim(
            (string) $request->get(
                'category'
            )
        );

        $modules = Module::query()
            ->withCount([
                'organizations',
                'permissions',
                'plans',
            ])

            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($subQuery) use ($search) {
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
                                    'version',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'category',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $status !== '',
                fn($query) =>
                $query->where(
                    'status',
                    $status
                )
            )

            ->when(
                $type !== '',
                fn($query) =>
                $query->where(
                    'type',
                    $type
                )
            )

            ->when(
                $category !== '',
                fn($query) =>
                $query->where(
                    'category',
                    $category
                )
            )

            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Module::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view(
            'superadmin.modules.index',
            compact(
                'modules',
                'search',
                'status',
                'type',
                'category',
                'categories'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view(
            'superadmin.modules.create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this->validateModule(
                $request
            );

        $module = Module::create([
            'ulid' =>
            (string) Str::ulid(),

            'code' =>
            $validated['code'],

            'name' =>
            $validated['name'],

            'version' =>
            $validated['version'],

            'description' =>
            $validated['description']
                ?? null,

            'category' =>
            $validated['category']
                ?? null,

            'icon' =>
            $validated['icon']
                ?? null,

            'workspace_path' =>
            $validated['workspace_path']
                ?? null,

            'type' =>
            $validated['type'],

            'entry_url' =>
            $validated['entry_url']
                ?? null,

            'api_url' =>
            $validated['api_url']
                ?? null,

            'status' =>
            $validated['status'],

            'pricing_type' =>
            $validated['pricing_type'],

            'fixed_price' =>
            $validated['fixed_price']
                ?? 0,

            'price_per_user' =>
            $validated['price_per_user']
                ?? 0,

            'included_users' =>
            $validated['included_users']
                ?? 0,

            'minimum_users' =>
            $validated['minimum_users']
                ?? null,

            'maximum_users' =>
            $validated['maximum_users']
                ?? null,

            'billing_user_scope' =>
            $validated['billing_user_scope'],

            'billing_interval' =>
            $validated['billing_interval'],

            'sort_order' =>
            $validated['sort_order']
                ?? 0,

            'metadata' =>
            null,
        ]);

        return redirect()
            ->route(
                'superadmin.modules.show',
                $module
            )
            ->with(
                'success',
                'Module berhasil dibuat.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Module $module
    ): View {
        $module->loadCount([
            'organizations',
            'permissions',
            'plans',
        ]);

        $organizations =
            $module
            ->organizations()
            ->orderBy(
                'organizations.name'
            )
            ->get();

        $plans =
            $module
            ->plans()
            ->orderBy(
                'subscription_plans.sort_order'
            )
            ->orderBy(
                'subscription_plans.name'
            )
            ->get();

        $permissions =
            $module
            ->permissions()
            ->orderBy('name')
            ->get();

        return view(
            'superadmin.modules.show',
            compact(
                'module',
                'organizations',
                'plans',
                'permissions'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Module $module
    ): View {
        return view(
            'superadmin.modules.edit',
            compact('module')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Module $module
    ): RedirectResponse {
        $validated =
            $this->validateModule(
                $request,
                $module
            );

        $module->update([
            'code' =>
            $validated['code'],

            'name' =>
            $validated['name'],

            'version' =>
            $validated['version'],

            'description' =>
            $validated['description']
                ?? null,

            'category' =>
            $validated['category']
                ?? null,

            'icon' =>
            $validated['icon']
                ?? null,

            'type' =>
            $validated['type'],

            'workspace_path' =>
            $validated['workspace_path']
                ?? null,

            'entry_url' =>
            $validated['entry_url']
                ?? null,

            'api_url' =>
            $validated['api_url']
                ?? null,

            'status' =>
            $validated['status'],

            'pricing_type' =>
            $validated['pricing_type'],

            'fixed_price' =>
            $validated['fixed_price']
                ?? 0,

            'price_per_user' =>
            $validated['price_per_user']
                ?? 0,

            'included_users' =>
            $validated['included_users']
                ?? 0,

            'minimum_users' =>
            $validated['minimum_users']
                ?? null,

            'maximum_users' =>
            $validated['maximum_users']
                ?? null,

            'billing_user_scope' =>
            $validated['billing_user_scope'],

            'billing_interval' =>
            $validated['billing_interval'],

            'sort_order' =>
            $validated['sort_order']
                ?? 0,
        ]);

        return redirect()
            ->route(
                'superadmin.modules.show',
                $module
            )
            ->with(
                'success',
                'Module berhasil diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        Module $module
    ): RedirectResponse {
        $module->update([
            'status' =>
            $module->status
                === 'active'
                ? 'inactive'
                : 'active',
        ]);

        return back()->with(
            'success',
            $module->status === 'active'
                ? 'Module berhasil diaktifkan.'
                : 'Module berhasil dinonaktifkan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    protected function validateModule(
        Request $request,
        ?Module $module = null
    ): array {
        return $request->validate([
            /*
            |--------------------------------------------------------------------------
            | General
            |--------------------------------------------------------------------------
            */

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',

                Rule::unique(
                    'modules',
                    'code'
                )->ignore(
                    $module?->id
                ),
            ],

            'workspace_path' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\/_-]+$/',
            ],

            'version' => [
                'required',
                'string',
                'max:50',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | Application
            |--------------------------------------------------------------------------
            */

            'type' => [
                'required',

                Rule::in([
                    'internal',
                    'external',
                ]),
            ],

            'entry_url' => [
                'nullable',
                'string',
                'max:2048',
            ],

            'api_url' => [
                'nullable',
                'string',
                'max:2048',
            ],

            'status' => [
                'required',

                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            'pricing_type' => [
                'required',

                Rule::in([
                    'fixed',
                    'per_user',
                    'hybrid',
                ]),
            ],

            'fixed_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'price_per_user' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'included_users' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'minimum_users' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'maximum_users' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'billing_user_scope' => [
                'required',

                Rule::in([
                    'licensed_users',
                    'all_users',
                    'employees',
                    'active_employees',
                    'active_module_users',
                    'students',
                    'teachers',
                ]),
            ],

            'billing_interval' => [
                'required',

                Rule::in([
                    'monthly',
                    'yearly',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Display
            |--------------------------------------------------------------------------
            */

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);
    }
}
