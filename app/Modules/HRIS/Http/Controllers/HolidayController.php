<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisHoliday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HolidayController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        Organization $organization
    ): View {
        $year =
            (int) $request->integer(
                'year',
                now()->year
            );

        if (
            $year < 2000
            ||
            $year > 2200
        ) {
            $year =
                now()->year;
        }

        $query =
            HrisHoliday::query()
            ->where(
                'organization_id',
                $organization->id
            );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'search'
            )
        ) {
            $search =
                trim(
                    $request
                        ->string('search')
                        ->toString()
                );

            $query->where(
                function ($query) use (
                    $search
                ) {
                    $query
                        ->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'description',
                            'like',
                            '%' . $search . '%'
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | State
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'status'
            )
        ) {
            if (
                $request->input(
                    'status'
                ) === 'active'
            ) {
                $query->where(
                    'is_active',
                    true
                );
            }

            if (
                $request->input(
                    'status'
                ) === 'inactive'
            ) {
                $query->where(
                    'is_active',
                    false
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Year
        |--------------------------------------------------------------------------
        |
        | Recurring selalu ditampilkan karena berlaku
        | pada setiap tahun.
        |
        */

        $query->where(
            function ($query) use (
                $year
            ) {
                $query
                    ->where(
                        'is_recurring',
                        true
                    )
                    ->orWhereYear(
                        'holiday_date',
                        $year
                    )
                    ->orWhere(
                        function ($range) use (
                            $year
                        ) {
                            $range
                                ->whereDate(
                                    'holiday_date',
                                    '<=',
                                    $year . '-12-31'
                                )
                                ->whereDate(
                                    'end_date',
                                    '>=',
                                    $year . '-01-01'
                                );
                        }
                    );
            }
        );

        $holidays =
            $query
            ->orderBy(
                'holiday_date'
            )
            ->paginate(25)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $base =
            HrisHoliday::query()
            ->where(
                'organization_id',
                $organization->id
            );

        $totalHolidays =
            (clone $base)
            ->count();

        $activeHolidays =
            (clone $base)
            ->where(
                'is_active',
                true
            )
            ->count();

        $recurringHolidays =
            (clone $base)
            ->where(
                'is_recurring',
                true
            )
            ->count();

        return view(
            'modules.hris.holidays.index',
            compact(
                'organization',
                'holidays',
                'year',

                'totalHolidays',
                'activeHolidays',
                'recurringHolidays'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $this->validateHoliday(
                $request
            );

        HrisHoliday::query()
            ->create([
                'organization_id' =>
                $organization->id,

                'holiday_date' =>
                $validated['holiday_date'],

                'end_date' =>
                $validated['end_date']
                    ?? null,

                'name' =>
                $validated['name'],

                'color' =>
                $validated['color']
                    ?? '#e8742a',

                'is_recurring' =>
                $request->boolean(
                    'is_recurring'
                ),

                'description' =>
                $validated['description']
                    ?? null,

                'is_active' =>
                $request->boolean(
                    'is_active'
                ),
            ]);

        return back()
            ->with(
                'success',
                'Holiday berhasil ditambahkan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Organization $organization,
        HrisHoliday $holiday
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $holiday
        );

        $validated =
            $this->validateHoliday(
                $request
            );

        $holiday->update([
            'holiday_date' =>
            $validated['holiday_date'],

            'end_date' =>
            $validated['end_date']
                ?? null,

            'name' =>
            $validated['name'],

            'color' =>
            $validated['color']
                ?? '#e8742a',

            'is_recurring' =>
            $request->boolean(
                'is_recurring'
            ),

            'description' =>
            $validated['description']
                ?? null,

            'is_active' =>
            $request->boolean(
                'is_active'
            ),
        ]);

        return back()
            ->with(
                'success',
                'Holiday berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Organization $organization,
        HrisHoliday $holiday
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $holiday
        );

        $holiday->delete();

        return back()
            ->with(
                'success',
                'Holiday berhasil dihapus.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateHoliday(
        Request $request
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'holiday_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:holiday_date',
            ],

            'color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'is_recurring' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Organization Guard
    |--------------------------------------------------------------------------
    */

    private function guardOrganization(
        Organization $organization,
        HrisHoliday $holiday
    ): void {
        if (
            (int) $holiday->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }
    }
}
