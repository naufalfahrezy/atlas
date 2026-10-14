<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SupabaseAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        private SupabaseAuthService $supabaseAuth
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:50'],
            'status' => [
                'nullable',
                Rule::in(['active', 'inactive']),
            ],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = DB::table('profiles as p')
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
                'p.created_at',
                'p.updated_at',
            ]);

        if (!empty($validated['search'])) {
            $search = '%' . $validated['search'] . '%';

            $query->where(function ($q) use ($search) {
                $q->where('p.full_name', 'ilike', $search)
                    ->orWhere('p.email', 'ilike', $search)
                    ->orWhere(
                        'p.institution_identifier',
                        'ilike',
                        $search
                    );
            });
        }

        if (!empty($validated['role'])) {
            $query->where('r.code', $validated['role']);
        }

        if (!empty($validated['status'])) {
            $query->where(
                'p.is_active',
                $validated['status'] === 'active'
            );
        }

        $users = $query
            ->orderBy('p.created_at', 'desc')
            ->paginate($validated['per_page'] ?? 15);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->findUser($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:160',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:72',
            ],
            'role_code' => [
                'required',
                'string',
                'max:50',
            ],
            'institution_identifier' => [
                'nullable',
                'string',
                'max:100',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        if (
            DB::table('profiles')
                ->whereRaw(
                    'lower(email) = lower(?)',
                    [$validated['email']]
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'Email sudah terdaftar pada ATLAS.',
                ],
            ]);
        }

        $role = DB::table('roles')
            ->where('code', $validated['role_code'])
            ->where('is_active', true)
            ->first();

        if (!$role) {
            throw ValidationException::withMessages([
                'role_code' => [
                    'Role tidak valid atau tidak aktif.',
                ],
            ]);
        }

        $authUserId = null;

        try {
            $authUser = $this->supabaseAuth->createUser([
                'email' => $validated['email'],
                'password' => $validated['password'],
                'email_confirm' => true,
                'user_metadata' => [
                    'full_name' =>
                        $validated['full_name'],
                ],
            ]);

            $authUserId =
                $authUser['id']
                ?? $authUser['user']['id']
                ?? null;

            if (!$authUserId) {
                throw new \RuntimeException(
                    'Supabase tidak mengembalikan user ID.'
                );
            }

            DB::transaction(function () use (
                $validated,
                $role,
                $authUserId
            ) {
                DB::table('profiles')->updateOrInsert(
                    ['id' => $authUserId],
                    [
                        'role_id' => $role->id,
                        'full_name' =>
                            $validated['full_name'],
                        'email' =>
                            $validated['email'],
                        'institution_identifier' =>
                            $validated[
                                'institution_identifier'
                            ] ?? null,
                        'phone' =>
                            $validated['phone'] ?? null,
                        'is_active' => true,
                        'updated_at' => now(),
                    ]
                );
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Pengguna berhasil dibuat.',
                'data' => [
                    'user' =>
                        $this->findUser($authUserId),
                ],
            ], 201);
        } catch (Throwable $e) {
            if ($authUserId) {
                $this->supabaseAuth->deleteUser(
                    $authUserId
                );
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Gagal membuat pengguna.',
            ], 422);
        }
    }

    public function update(
        Request $request,
        string $id
    ): JsonResponse {
        $user = $this->findUser($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'full_name' => [
                'sometimes',
                'required',
                'string',
                'max:160',
            ],
            'institution_identifier' => [
                'nullable',
                'string',
                'max:100',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $payload = array_intersect_key(
            $validated,
            array_flip([
                'full_name',
                'institution_identifier',
                'phone',
            ])
        );

        if ($payload === []) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Tidak ada data yang diperbarui.',
            ], 422);
        }

        $payload['updated_at'] = now();

        DB::table('profiles')
            ->where('id', $id)
            ->update($payload);

        return response()->json([
            'success' => true,
            'message' =>
                'Data pengguna berhasil diperbarui.',
            'data' => [
                'user' => $this->findUser($id),
            ],
        ]);
    }

    public function updateStatus(
        Request $request,
        string $id
    ): JsonResponse {
        $validated = $request->validate([
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $user = $this->findUser($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        $actor =
            $request->attributes->get('atlas_user');

        if (
            $actor->id === $id
            && $validated['is_active'] === false
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Anda tidak dapat menonaktifkan akun sendiri.',
            ], 422);
        }

        DB::table('profiles')
            ->where('id', $id)
            ->update([
                'is_active' =>
                    $validated['is_active'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Status pengguna berhasil diperbarui.',
            'data' => [
                'user' => $this->findUser($id),
            ],
        ]);
    }

    public function updateRole(
        Request $request,
        string $id
    ): JsonResponse {
        $validated = $request->validate([
            'role_code' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        $user = $this->findUser($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        $role = DB::table('roles')
            ->where('code', $validated['role_code'])
            ->where('is_active', true)
            ->first();

        if (!$role) {
            throw ValidationException::withMessages([
                'role_code' => [
                    'Role tidak valid atau tidak aktif.',
                ],
            ]);
        }

        $actor =
            $request->attributes->get('atlas_user');

        if (
            $actor->id === $id
            && $validated['role_code']
                !== 'LOGISTICS_STAFF'
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Anda tidak dapat mengubah role akun sendiri.',
            ], 422);
        }

        DB::table('profiles')
            ->where('id', $id)
            ->update([
                'role_id' => $role->id,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Role pengguna berhasil diperbarui.',
            'data' => [
                'user' => $this->findUser($id),
            ],
        ]);
    }

    public function roles(): JsonResponse
    {
        $roles = DB::table('roles')
            ->select([
                'id',
                'code',
                'name',
                'description',
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles,
            ],
        ]);
    }

    private function findUser(string $id): ?object
    {
        return DB::table('profiles as p')
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
                'p.created_at',
                'p.updated_at',
            ])
            ->where('p.id', $id)
            ->first();
    }
}