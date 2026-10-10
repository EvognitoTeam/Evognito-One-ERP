<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisEmployeeCompensation;
use App\Modules\HRIS\Models\HrisPayroll;
use App\Modules\HRIS\Models\HrisPayrollPeriod;
use App\Modules\HRIS\Services\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(
        private readonly PayrollService $payrollService
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Payroll
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        Organization $organization
    ): View {
        $periods =
            HrisPayrollPeriod::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->withCount(
                'payrolls'
            )
            ->orderByDesc(
                'period_start'
            )
            ->get();

        $selectedPeriod =
            null;

        if (
            $request->filled(
                'period'
            )
        ) {
            $selectedPeriod =
                HrisPayrollPeriod::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->findOrFail(
                    $request->integer(
                        'period'
                    )
                );
        }

        if (
            !$selectedPeriod
        ) {
            $selectedPeriod =
                $periods->first();
        }

        $payrolls =
            collect();

        if ($selectedPeriod) {
            $payrolls =
                HrisPayroll::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'payroll_period_id',
                    $selectedPeriod->id
                )
                ->orderBy(
                    'employee_name'
                )
                ->get();
        }

        $totalGross =
            $payrolls->sum(
                'gross_salary'
            );

        $totalDeductions =
            $payrolls->sum(
                'total_deductions'
            );

        $totalNet =
            $payrolls->sum(
                'net_salary'
            );

        $paidCount =
            $payrolls
            ->where(
                'status',
                'paid'
            )
            ->count();

        return view(
            'modules.hris.payroll.index',
            compact(
                'organization',

                'periods',
                'selectedPeriod',
                'payrolls',

                'totalGross',
                'totalDeductions',
                'totalNet',
                'paidCount'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Period
    |--------------------------------------------------------------------------
    */

    public function storePeriod(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'period_start' => [
                    'required',
                    'date',
                ],

                'period_end' => [
                    'required',
                    'date',
                    'after_or_equal:period_start',
                ],

                'pay_date' => [
                    'nullable',
                    'date',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ]);

        $duplicate =
            HrisPayrollPeriod::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereDate(
                'period_start',
                $validated['period_start']
            )
            ->whereDate(
                'period_end',
                $validated['period_end']
            )
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors([
                    'period_start' =>
                    'Payroll period dengan rentang tanggal tersebut sudah ada.',
                ]);
        }

        $period =
            HrisPayrollPeriod::query()
            ->create([
                'organization_id' =>
                $organization->id,

                'name' =>
                $validated['name'],

                'period_start' =>
                $validated['period_start'],

                'period_end' =>
                $validated['period_end'],

                'pay_date' =>
                $validated['pay_date']
                    ?? null,

                'status' =>
                'draft',

                'notes' =>
                $validated['notes']
                    ?? null,

                'created_by' =>
                $request->user()?->id,
            ]);

        return redirect()
            ->route(
                'hris.payroll.index',
                [
                    'organization' =>
                    $organization->slug,

                    'period' =>
                    $period->id,
                ]
            )
            ->with(
                'success',
                'Payroll period berhasil dibuat.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate
    |--------------------------------------------------------------------------
    */

    public function generate(
        Request $request,
        Organization $organization,
        HrisPayrollPeriod $payrollPeriod
    ): RedirectResponse {
        $this->guardPeriod(
            $organization,
            $payrollPeriod
        );

        $this
            ->payrollService
            ->generate(
                $organization,
                $payrollPeriod,
                $request->user()?->id
            );

        return redirect()
            ->route(
                'hris.payroll.index',
                [
                    'organization' =>
                    $organization->slug,

                    'period' =>
                    $payrollPeriod->id,
                ]
            )
            ->with(
                'success',
                'Payroll berhasil di-generate.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Paid
    |--------------------------------------------------------------------------
    */

    public function markPaid(
        Request $request,
        Organization $organization,
        HrisPayroll $payroll
    ): RedirectResponse {
        if (
            (int) $payroll->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }

        $this
            ->payrollService
            ->markPaid(
                $organization,
                $payroll,
                $request->user()?->id
            );

        return back()
            ->with(
                'success',
                'Payroll employee ditandai Paid.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Compensation
    |--------------------------------------------------------------------------
    */

    public function compensation(
        Organization $organization
    ): View {
        $employees =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'status',
                'active'
            )
            ->with([
                'user',
                'position',
                'department',
                'branch',
            ])
            ->orderBy('name')
            ->get();

        $compensations =
            HrisEmployeeCompensation::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereIn(
                'employee_id',
                $employees->pluck('id')
            )
            ->get()
            ->keyBy(
                'employee_id'
            );

        return view(
            'modules.hris.payroll.compensation',
            compact(
                'organization',
                'employees',
                'compensations'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Save Compensation
    |--------------------------------------------------------------------------
    */

    public function saveCompensation(
        Request $request,
        Organization $organization,
        Employee $employee
    ): RedirectResponse {
        if (
            (int) $employee->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }

        $validated =
            $request->validate([
                'basic_salary' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'fixed_allowance' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'daily_bonus' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'annual_bonus' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'late_deduction_per_minute' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'absence_deduction_per_day' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'other_fixed_deduction' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'currency' => [
                    'required',

                    Rule::in([
                        'IDR',
                    ]),
                ],

                'effective_from' => [
                    'nullable',
                    'date',
                ],
            ]);

        HrisEmployeeCompensation::query()
            ->updateOrCreate(
                [
                    'organization_id' =>
                    $organization->id,

                    'employee_id' =>
                    $employee->id,
                ],
                [
                    ...$validated,

                    'is_active' =>
                    true,
                ]
            );

        return back()
            ->with(
                'success',
                'Employee compensation berhasil diperbarui.'
            );
    }


    private function guardPeriod(
        Organization $organization,
        HrisPayrollPeriod $period
    ): void {
        if (
            (int) $period->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }
    }
}
