<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\OrganizationSlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function __construct(
        protected OrganizationSlugService $slugService
    ) {}

    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->get('search')
        );

        $status = trim(
            (string) $request->get('status')
        );

        $type = trim(
            (string) $request->get('type')
        );

        $organizations = Organization::query()
            ->withCount([
                'users',
                'employees',
                'modules',
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
                                    'legal_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'slug',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
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
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'superadmin.organizations.index',
            compact(
                'organizations',
                'search',
                'status',
                'type'
            )
        );
    }

    public function create(): View
    {
        return view(
            'superadmin.organizations.create'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique(
                    'organizations',
                    'slug'
                ),
            ],

            'type' => [
                'required',
                Rule::in([
                    'business',
                    'education',
                    'nonprofit',
                    'government',
                    'other',
                ]),
            ],

            'legal_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'timezone' => [
                'required',
                'string',
                'max:64',
            ],

            'locale' => [
                'required',
                'string',
                'max:10',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'suspended',
                ]),
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if (
            empty($validated['slug'])
        ) {
            $validated['slug'] =
                $this->slugService->generate(
                    $validated['name']
                );
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] =
                $request
                ->file('logo')
                ->store(
                    'organizations/logos',
                    'public'
                );
        }

        $validated['ulid'] =
            (string) Str::ulid();

        $organization =
            Organization::create(
                $validated
            );

        return redirect()
            ->route(
                'superadmin.organizations.show',
                $organization
            )
            ->with(
                'success',
                'Organization berhasil dibuat.'
            );
    }

    public function show(
        Organization $organization
    ): View {
        $organization->loadCount([
            'users',
            'employees',
            'branches',
            'departments',
            'positions',
            'modules',
        ]);

        $organization->load([
            'modules' => fn($query) =>
            $query->orderBy(
                'sort_order'
            ),
        ]);

        return view(
            'superadmin.organizations.show',
            compact('organization')
        );
    }

    public function edit(
        Organization $organization
    ): View {
        return view(
            'superadmin.organizations.edit',
            compact('organization')
        );
    }

    public function update(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',

                Rule::unique(
                    'organizations',
                    'slug'
                )->ignore(
                    $organization->id
                ),
            ],

            'type' => [
                'required',
                Rule::in([
                    'business',
                    'education',
                    'nonprofit',
                    'government',
                    'other',
                ]),
            ],

            'legal_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'timezone' => [
                'required',
                'string',
                'max:64',
            ],

            'locale' => [
                'required',
                'string',
                'max:10',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'suspended',
                ]),
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'remove_logo' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['slug'] =
            !empty($validated['slug'])
            ? $validated['slug']
            : $organization->slug;

        if (
            $request->boolean(
                'remove_logo'
            )
        ) {
            if ($organization->logo) {
                Storage::disk('public')
                    ->delete(
                        $organization->logo
                    );
            }

            $validated['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                Storage::disk('public')
                    ->delete(
                        $organization->logo
                    );
            }

            $validated['logo'] =
                $request
                ->file('logo')
                ->store(
                    'organizations/logos',
                    'public'
                );
        }

        unset(
            $validated['remove_logo']
        );

        $organization->update(
            $validated
        );

        return redirect()
            ->route(
                'superadmin.organizations.show',
                $organization
            )
            ->with(
                'success',
                'Organization berhasil diperbarui.'
            );
    }

    public function destroy(
        Organization $organization
    ): RedirectResponse {
        $organization->delete();

        return redirect()
            ->route(
                'superadmin.organizations.index'
            )
            ->with(
                'success',
                'Organization berhasil dihapus.'
            );
    }
}
