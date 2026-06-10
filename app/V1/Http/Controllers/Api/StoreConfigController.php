<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class StoreConfigController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'app_name' => setting('app_name', config('app.name')),
                'currency' => app_currency(),
                'logo_url' => brand_logo_url(),
            ],
        ]);
    }
}
