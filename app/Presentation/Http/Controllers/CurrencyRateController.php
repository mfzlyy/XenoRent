<?php

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Services\UsdIdrRateService;
use Illuminate\Http\JsonResponse;

class CurrencyRateController extends Controller
{
    public function show(UsdIdrRateService $rateService): JsonResponse
    {
        $rate = $rateService->latest();

        if ($rate === null) {
            return response()->json([
                'message' => 'Kurs USD/IDR belum tersedia.',
            ], 503);
        }

        return response()->json($rate)->header('Cache-Control', 'no-store');
    }
}
