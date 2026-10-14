<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->attributes->get('atlas_user');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated ATLAS user is required.',
            ], 401);
        }

        $allowed = DB::table('profiles as p')
            ->join('roles as r', 'r.id', '=', 'p.role_id')
            ->join(
                'role_permissions as rp',
                'rp.role_id',
                '=',
                'r.id'
            )
            ->join(
                'permissions as perm',
                'perm.id',
                '=',
                'rp.permission_id'
            )
            ->where('p.id', $user->id)
            ->where('p.is_active', true)
            ->where('r.is_active', true)
            ->where('perm.code', $permission)
            ->exists();

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}