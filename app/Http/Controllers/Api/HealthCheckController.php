<?php

namespace App\Http\Controllers\Api;

use App\Enums\CheckStatus;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckController extends Controller
{
    public function general(): JsonResponse
    {
        return response()->json([
            'status' => CheckStatus::Ok,
            'timestamp' => now()->toIso8601String(),
        ], Response::HTTP_OK);
    }

    public function database(): JsonResponse
    {
        $healthy = $this->isDatabaseHealthy();

        return response()->json([
            'status' => $healthy ? CheckStatus::Ok->value : CheckStatus::Fail->value,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function cache(): JsonResponse
    {
        $healthy = $this->isCacheHealthy();

        return response()->json([
            'status' => $healthy ? CheckStatus::Ok->value : CheckStatus::Fail->value,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }

    protected function isDatabaseHealthy(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    protected function isCacheHealthy(): bool
    {
        try {
            $key = 'health_check_'.time();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            return $value === 'test';
        } catch (Exception) {
            return false;
        }
    }
}
