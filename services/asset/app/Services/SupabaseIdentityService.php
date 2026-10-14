<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SupabaseIdentityService
{
    private string $url;
    private string $publishableKey;

    public function __construct()
    {
        $this->url = rtrim(
            (string) config('services.supabase.url'),
            '/'
        );

        $this->publishableKey =
            (string) config(
                'services.supabase.publishable_key'
            );

        if (
            $this->url === '' ||
            $this->publishableKey === ''
        ) {
            throw new RuntimeException(
                'Supabase configuration is incomplete.'
            );
        }
    }

    public function getUser(
        string $accessToken
    ): ?array {
        $response = Http::acceptJson()
            ->withHeaders([
                'apikey' => $this->publishableKey,
                'Authorization' =>
                    'Bearer ' . $accessToken,
            ])
            ->get(
                $this->url . '/auth/v1/user'
            );

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }
}