<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SupabaseAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private SupabaseAuthService $supabaseAuth
    ) {}

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $session = $this->supabaseAuth->signIn(
                $validated['email'],
                $validated['password']
            );

            $authUser = $session['user'] ?? null;

            if (!$authUser || empty($authUser['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'User information was not returned.',
                ], 401);
            }

            $profile = DB::table('profiles as p')
                ->join('roles as r', 'r.id', '=', 'p.role_id')
                ->select([
                    'p.id',
                    'p.full_name',
                    'p.email',
                    'p.institution_identifier',
                    'p.phone',
                    'p.is_active',
                    'r.code as role_code',
                    'r.name as role_name',
                ])
                ->where('p.id', $authUser['id'])
                ->first();

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'ATLAS profile was not found.',
                ], 403);
            }

            if (!$profile->is_active) {
                $this->supabaseAuth->logout(
                    $session['access_token']
                );

                return response()->json([
                    'success' => false,
                    'message' => 'User account is inactive.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'access_token' =>
                    $session['access_token'],
                    'refresh_token' =>
                    $session['refresh_token'] ?? null,
                    'expires_in' =>
                    $session['expires_in'] ?? null,
                    'token_type' =>
                    $session['token_type'] ?? 'bearer',
                    'user' => $profile,
                ],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('ATLAS authentication failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Email atau password tidak valid.',
            ], 401);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' =>
                $request->attributes->get('atlas_user'),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token =
            $request->attributes->get('access_token');

        $this->supabaseAuth->logout($token);

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
        ]);
    }
}
