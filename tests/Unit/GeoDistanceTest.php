<?php

namespace Tests\Unit;

use App\V1\Support\GeoDistance;
use Tests\TestCase;

class GeoDistanceTest extends TestCase
{
    public function test_kilometers_between_two_points(): void
    {
        $distance = GeoDistance::kilometers(30.0444, 31.2357, 30.0561, 31.3300);

        $this->assertNotNull($distance);
        $this->assertGreaterThan(8, $distance);
        $this->assertLessThan(12, $distance);
    }

    public function test_kilometers_returns_null_for_invalid_coordinates(): void
    {
        $this->assertNull(GeoDistance::kilometers(null, 31.0, 30.0, 31.0));
        $this->assertNull(GeoDistance::kilometers(30.0, null, 30.0, 31.0));
    }
}
