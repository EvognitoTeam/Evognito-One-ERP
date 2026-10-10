<?php

namespace App\Modules\Performance\Services;

class PerformanceKpiCalculator
{
    public function calculate(
        float $target,
        float $targetTime,
        float $actual,
        float $actualTime,
        string $calculationType,
        float $quantityWeight = 70,
        float $timeWeight = 30,
        float $maxAchievement = 150
    ): array {
        $quantity = $this->quantityAchievement($target, $actual, $calculationType, $maxAchievement);
        $time = $this->timeAchievement($targetTime, $actualTime, $maxAchievement);
        $totalWeight = $quantityWeight + $timeWeight;

        if ($totalWeight <= 0) {
            $quantityWeight = 100;
            $timeWeight = 0;
            $totalWeight = 100;
        }

        $achievement = (($quantity * $quantityWeight) + ($time * $timeWeight)) / $totalWeight;

        return [
            'quantity_achievement' => round($quantity, 2),
            'time_achievement' => round($time, 2),
            'achievement' => round(min($maxAchievement, max(0, $achievement)), 2),
        ];
    }

    private function quantityAchievement(float $target, float $actual, string $type, float $max): float
    {
        if ($target <= 0) return 0;
        if (strtoupper($type) === 'LOWER_BETTER') {
            if ($actual <= 0) return $max;
            return min($max, ($target / $actual) * 100);
        }
        return min($max, ($actual / $target) * 100);
    }

    private function timeAchievement(float $targetTime, float $actualTime, float $max): float
    {
        if ($targetTime <= 0) return 100;
        if ($actualTime <= 0) return 0;
        return min($max, ($targetTime / $actualTime) * 100);
    }
}
