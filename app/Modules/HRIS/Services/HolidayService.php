<?php

namespace App\Modules\HRIS\Services;

use App\Models\Organization;
use App\Modules\HRIS\Models\HrisHoliday;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class HolidayService
{
    /*
    |--------------------------------------------------------------------------
    | Find Holiday
    |--------------------------------------------------------------------------
    */

    public function findForDate(
        Organization $organization,
        CarbonInterface|string $date
    ): ?HrisHoliday {
        $target =
            $date instanceof CarbonInterface
            ? Carbon::instance(
                $date->toDateTime()
            )->startOfDay()
            : Carbon::parse(
                $date
            )->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Non Recurring Holiday
        |--------------------------------------------------------------------------
        */

        $holiday =
            HrisHoliday::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'is_active',
                true
            )
            ->where(
                'is_recurring',
                false
            )
            ->whereDate(
                'holiday_date',
                '<=',
                $target->toDateString()
            )
            ->where(
                function ($query) use (
                    $target
                ) {
                    $query
                        ->whereNull(
                            'end_date'
                        )
                        ->orWhereDate(
                            'end_date',
                            '>=',
                            $target
                                ->toDateString()
                        );
                }
            )
            ->orderBy('holiday_date')
            ->first();

        if ($holiday) {
            return $holiday;
        }

        /*
        |--------------------------------------------------------------------------
        | Recurring Holiday
        |--------------------------------------------------------------------------
        |
        | Recurring menggunakan bulan/tanggal.
        |
        | Contoh:
        |
        | 2026-12-25 recurring
        |
        | akan berlaku:
        |
        | 2027-12-25
        | 2028-12-25
        | dst.
        |
        */

        $recurring =
            HrisHoliday::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'is_active',
                true
            )
            ->where(
                'is_recurring',
                true
            )
            ->orderBy(
                'holiday_date'
            )
            ->get();

        foreach (
            $recurring
            as $item
        ) {
            if (
                $this->recurringMatches(
                    $item,
                    $target
                )
            ) {
                return $item;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Is Holiday
    |--------------------------------------------------------------------------
    */

    public function isHoliday(
        Organization $organization,
        CarbonInterface|string $date
    ): bool {
        return $this
            ->findForDate(
                $organization,
                $date
            ) !== null;
    }


    /*
    |--------------------------------------------------------------------------
    | Recurring Match
    |--------------------------------------------------------------------------
    */

    private function recurringMatches(
        HrisHoliday $holiday,
        CarbonInterface $target
    ): bool {
        $originalStart =
            $holiday
            ->holiday_date
            ->copy()
            ->startOfDay();

        $originalEnd =
            (
                $holiday->end_date
                ??
                $holiday->holiday_date
            )
            ->copy()
            ->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Check current target year and previous year
        |--------------------------------------------------------------------------
        |
        | Previous year diperlukan jika holiday recurring
        | melewati pergantian tahun.
        |
        | Contoh:
        |
        | 30 Dec - 2 Jan
        |
        */

        foreach (
            [
                $target->year,
                $target->year - 1,
            ]
            as $year
        ) {
            $start =
                $this->safeDate(
                    $year,
                    $originalStart->month,
                    $originalStart->day
                );

            if (!$start) {
                continue;
            }

            $crossYear =
                $originalEnd
                ->format('md')
                <
                $originalStart
                ->format('md');

            $endYear =
                $crossYear
                ? $year + 1
                : $year;

            $end =
                $this->safeDate(
                    $endYear,
                    $originalEnd->month,
                    $originalEnd->day
                );

            if (!$end) {
                continue;
            }

            if (
                $target->betweenIncluded(
                    $start,
                    $end
                )
            ) {
                return true;
            }
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Safe Date
    |--------------------------------------------------------------------------
    */

    private function safeDate(
        int $year,
        int $month,
        int $day
    ): ?Carbon {
        try {
            if (
                !checkdate(
                    $month,
                    $day,
                    $year
                )
            ) {
                return null;
            }

            return Carbon::create(
                $year,
                $month,
                $day,
                0,
                0,
                0
            );
        } catch (\Throwable) {
            return null;
        }
    }
}
