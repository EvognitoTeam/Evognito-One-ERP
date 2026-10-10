<?php

namespace App\Modules\Sales\Services;

class SalesGeofenceService
{
    public function distanceMeters(
        float $latitudeA,
        float $longitudeA,
        float $latitudeB,
        float $longitudeB
    ): float {
        $earthRadius = 6371000;

        $latFrom = deg2rad($latitudeA);
        $latTo = deg2rad($latitudeB);
        $latDelta = deg2rad($latitudeB - $latitudeA);
        $lonDelta = deg2rad($longitudeB - $longitudeA);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function isInside(
        float $latitude,
        float $longitude,
        float $targetLatitude,
        float $targetLongitude,
        int $radiusMeters
    ): bool {
        return $this->distanceMeters(
            $latitude,
            $longitude,
            $targetLatitude,
            $targetLongitude
        ) <= $radiusMeters;
    }
}
