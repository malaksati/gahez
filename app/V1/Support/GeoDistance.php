<?php

namespace App\V1\Support;

final class GeoDistance
{
    public static function kilometers(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }

        if (! self::isValidLatitude($lat1) || ! self::isValidLongitude($lng1)
            || ! self::isValidLatitude($lat2) || ! self::isValidLongitude($lng2)) {
            return null;
        }

        $earthRadiusKm = 6371.0;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }

    public static function parseLatitude(mixed $value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $latitude = (float) $value;

        return self::isValidLatitude($latitude) ? $latitude : null;
    }

    public static function parseLongitude(mixed $value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $longitude = (float) $value;

        return self::isValidLongitude($longitude) ? $longitude : null;
    }

    private static function isValidLatitude(float $latitude): bool
    {
        return $latitude >= -90 && $latitude <= 90;
    }

    private static function isValidLongitude(float $longitude): bool
    {
        return $longitude >= -180 && $longitude <= 180;
    }
}
