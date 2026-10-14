<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SupabaseAuthService
{
    private string $url;
    private string $publishableKey;
    private string $secretKey;

    public function __construct()
    {
        $this->url = rtrim(
            (string) config('services.supabase.url'),
            '/'
        );

        $this->publishableKey =
            (string) config('services.supabase.publishable_key');

        $this->secretKey =
            (string) config('services.supabase.secret_key');

        if (
            $this->url === '' ||
            $this->publishableKey === '' ||
            $this->secretKey === ''
        ) {
            throw new RuntimeException(
                'Supabase Auth configuration is incomplete.'
            );
        }
    }

    public function signIn(
        string $email,
        string $password
    ): array {
        $response = Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'apikey' => $this->publishableKey,
            ])
            ->post(
                $this->url .
                '/auth/v1/token?grant_type=password',
                [
                    'email' => $email,
                    'password' => $password,
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('msg')
                ?? $response->json('message')
                ?? $response->json('error_description')
                ?? $response->body()
                ?? 'Supabase sign-in failed.'
            );
        }

        return $response->json();
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

    public function logout(
        string $accessToken
    ): bool {
        $response = Http::acceptJson()
            ->withHeaders([
                'apikey' => $this->publishableKey,
                'Authorization' =>
                    'Bearer ' . $accessToken,
            ])
            ->post(
                $this->url . '/auth/v1/logout'
            );

        return $response->successful();
    }

    public function createUser(
        array $payload
    ): array {
        $response = Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'apikey' => $this->secretKey,
            ])
            ->post(
                $this->url . '/auth/v1/admin/users',
                $payload
            );

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('msg')
                ?? $response->json('message')
                ?? $response->body()
                ?? 'Gagal membuat pengguna.'
            );
        }

        return $response->json();
    }

    public function deleteUser(
        string $userId
    ): bool {
        $response = Http::acceptJson()
            ->withHeaders([
                'apikey' => $this->secretKey,
            ])
            ->delete(
                $this->url .
                '/auth/v1/admin/users/' .
                $userId
            );

        return $response->successful();
    }
}