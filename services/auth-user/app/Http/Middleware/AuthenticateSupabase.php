<?php

namespace App\Http\Middleware;

use App\Services\SupabaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSupabase
{
    public function __construct(
        private SupabaseAuthService $supabaseAuth
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token is required.',
            ], 401);
        }

        $authUser = $this->supabaseAuth->getUser($token);

        if (!$authUser || empty($authUser['id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token is invalid or expired.',
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
                'message' => 'ATLAS user profile was not found.',
            ], 403);
        }

        if (!$profile->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'User account is inactive.',
            ], 403);
        }

        $request->attributes->set('atlas_user', $profile);
        $request->attributes->set('supabase_user', $authUser);
        $request->attributes->set('access_token', $token);

        return $next($request);
    }
}