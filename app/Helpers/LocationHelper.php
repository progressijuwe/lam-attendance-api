<?php

namespace App\Helpers;

class LocationHelper
{
    public static function distanceMeters(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public static function isWithinRadius(float $lat, float $lng, float $tolerance = 0): bool
    {
        $distance = self::distanceMeters(
            $lat, $lng,
            (float) env('CHURCH_LAT'),
            (float) env('CHURCH_LNG')
        );
        return $distance <= ((float) env('CHURCH_RADIUS_METERS', 80) + $tolerance);
    }
}