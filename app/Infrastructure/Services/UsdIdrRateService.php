<?php

namespace App\Infrastructure\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class UsdIdrRateService
{
    private const CACHE_KEY = 'currency.usd_idr.latest';

    private const LAST_KNOWN_KEY = 'currency.usd_idr.last_known';

    public function latest(): ?array
    {
        $cachedRate = $this->cachedRate(self::CACHE_KEY);
        if ($cachedRate !== null) {
            return $cachedRate;
        }

        $caBundle = config('currency.ca_bundle');

        try {
            $request = Http::acceptJson()->timeout(5);

            if (is_string($caBundle) && $caBundle !== '') {
                if (! is_file($caBundle)) {
                    throw new \RuntimeException('Configured currency CA bundle does not exist.');
                }

                $request = $request->withOptions(['verify' => $caBundle]);
            }

            $response = $request->get(config('currency.api_url'));
            $response->throw();

            $rate = $response->json('rates.IDR');
            $date = $response->json('date');

            if (! is_numeric($rate) || (float) $rate <= 0 || ! is_string($date)) {
                throw new \UnexpectedValueException('Provider returned an invalid USD/IDR rate.');
            }
        } catch (Throwable $exception) {
            Log::warning('Failed to retrieve the USD/IDR rate from the provider.', [
                'exception_type' => $exception::class,
                'reason' => preg_replace('~https?://\\S+~', '[URL]', $exception->getMessage()),
                'ca_bundle_configured' => is_string($caBundle) && $caBundle !== '',
                'ca_bundle_exists' => is_string($caBundle) && is_file($caBundle),
            ]);

            return $this->cachedRate(self::LAST_KNOWN_KEY);
        }

        $data = [
            'rate' => (float) $rate,
            'date' => $date,
        ];

        try {
            Cache::store('file')->put(self::CACHE_KEY, $data, now()->addMinutes(config('currency.cache_minutes')));
            Cache::store('file')->put(self::LAST_KNOWN_KEY, $data, now()->addDays(7));
        } catch (Throwable $exception) {
            Log::warning('Unable to cache the USD/IDR rate.', [
                'exception_type' => $exception::class,
            ]);
        }

        return $data;
    }

    private function cachedRate(string $key): ?array
    {
        try {
            $data = Cache::store('file')->get($key);
        } catch (Throwable $exception) {
            Log::warning('Unable to read the cached USD/IDR rate.', [
                'exception_type' => $exception::class,
            ]);

            return null;
        }

        if (! is_array($data)
            || ! is_numeric($data['rate'] ?? null)
            || (float) $data['rate'] <= 0
            || ! is_string($data['date'] ?? null)) {
            return null;
        }

        return $data;
    }
}
