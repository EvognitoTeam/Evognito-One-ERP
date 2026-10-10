<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Services\HrisCalendarService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(
        private readonly HrisCalendarService $calendarService
    ) {}

    public function index(
        Request $request,
        Organization $organization
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Current Month
        |--------------------------------------------------------------------------
        */

        try {
            $month =
                Carbon::createFromFormat(
                    'Y-m',
                    $request->input(
                        'month',
                        now()->format(
                            'Y-m'
                        )
                    )
                )->startOfMonth();
        } catch (\Throwable) {
            $month =
                now()->startOfMonth();
        }

        /*
        |--------------------------------------------------------------------------
        | Calendar Grid
        |--------------------------------------------------------------------------
        */

        $gridStart =
            $month
            ->copy()
            ->startOfWeek(
                Carbon::MONDAY
            );

        $gridEnd =
            $month
            ->copy()
            ->endOfMonth()
            ->endOfWeek(
                Carbon::SUNDAY
            );

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = [
            'type' =>
            $request->input(
                'type',
                'all'
            ),

            'branch_id' =>
            $request->input(
                'branch_id'
            ),

            'department_id' =>
            $request->input(
                'department_id'
            ),

            'employee_id' =>
            $request->input(
                'employee_id'
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        */

        $events =
            $this
            ->calendarService
            ->events(
                $organization,
                $gridStart,
                $gridEnd,
                $filters
            );

        /*
        |--------------------------------------------------------------------------
        | Calendar Days
        |--------------------------------------------------------------------------
        */

        $calendarDays =
            collect(
                CarbonPeriod::create(
                    $gridStart,
                    $gridEnd
                )
            )
            ->map(
                function (
                    Carbon $date
                ) use (
                    $events,
                    $month
                ) {
                    $dayEvents =
                        $events
                        ->filter(
                            function (
                                array $event
                            ) use (
                                $date
                            ) {
                                return $date
                                    ->betweenIncluded(
                                        Carbon::parse(
                                            $event['start']
                                        ),
                                        Carbon::parse(
                                            $event['end']
                                        )
                                    );
                            }
                        )
                        ->values();

                    return [
                        'date' =>
                        $date->copy(),

                        'current_month' =>
                        $date->month
                            ===
                            $month->month,

                        'today' =>
                        $date->isToday(),

                        'events' =>
                        $dayEvents,
                    ];
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        $branches =
            $organization
            ->branches()
            ->orderBy('name')
            ->get();

        $departments =
            $organization
            ->departments()
            ->orderBy('name')
            ->get();

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
            ->with('user')
            ->orderBy('name')
            ->get();

        $previousMonth =
            $month
            ->copy()
            ->subMonth()
            ->format('Y-m');

        $nextMonth =
            $month
            ->copy()
            ->addMonth()
            ->format('Y-m');

        return view(
            'modules.hris.calendar.index',
            compact(
                'organization',

                'month',
                'calendarDays',

                'branches',
                'departments',
                'employees',

                'filters',

                'previousMonth',
                'nextMonth'
            )
        );
    }
}
