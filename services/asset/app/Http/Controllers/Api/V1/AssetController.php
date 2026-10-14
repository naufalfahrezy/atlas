<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function index(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:200',
            ],
            'category' => [
                'nullable',
                'string',
                'max:120',
            ],
            'location' => [
                'nullable',
                'string',
                'max:200',
            ],
            'condition' => [
                'nullable',
                Rule::in([
                    'BAIK',
                    'RUSAK_RINGAN',
                    'RUSAK',
                    'PERLU_PEMERIKSAAN',
                ]),
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'TERSEDIA',
                    'DIPINJAM',
                    'TIDAK_TERSEDIA',
                    'PERLU_PEMERIKSAAN',
                ]),
            ],
            'batch_id' => [
                'nullable',
                'uuid',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $query = DB::table('assets as a')
            ->join(
                'asset_batches as b',
                'b.id',
                '=',
                'a.batch_id'
            )
            ->leftJoin(
                'profiles as creator',
                'creator.id',
                '=',
                'a.created_by'
            )
            ->select([
                'a.id',
                'a.batch_id',
                'a.name',
                'a.category',
                'a.location',
                'a.condition',
                'a.availability_status',
                'a.notes',
                'a.created_at',
                'a.updated_at',
                'b.input_method',
                'creator.full_name as created_by_name',
            ]);

        if (!empty($validated['search'])) {
            $search =
                '%' . $validated['search'] . '%';

            $query->where(
                function ($q) use ($search) {
                    $q->whereRaw(
                        'CAST(a.id AS TEXT) ILIKE ?',
                        [$search]
                    )
                        ->orWhereRaw(
                            'CAST(a.batch_id AS TEXT) ILIKE ?',
                            [$search]
                        )
                        ->orWhere(
                            'a.name',
                            'ilike',
                            $search
                        )
                        ->orWhere(
                            'a.category',
                            'ilike',
                            $search
                        )
                        ->orWhere(
                            'a.location',
                            'ilike',
                            $search
                        );
                }
            );
        }

        if (!empty($validated['category'])) {
            $query->where(
                'a.category',
                $validated['category']
            );
        }

        if (!empty($validated['location'])) {
            $query->where(
                'a.location',
                $validated['location']
            );
        }

        if (!empty($validated['condition'])) {
            $query->where(
                'a.condition',
                $validated['condition']
            );
        }

        if (!empty($validated['status'])) {
            $query->where(
                'a.availability_status',
                $validated['status']
            );
        }

        if (!empty($validated['batch_id'])) {
            $query->where(
                'a.batch_id',
                $validated['batch_id']
            );
        }

        $assets = $query
            ->orderByDesc('a.created_at')
            ->paginate(
                $validated['per_page'] ?? 15
            );

        return response()->json([
            'success' => true,
            'data' => $assets,
        ]);
    }

    public function show(
        string $id
    ): JsonResponse {
        $asset = $this->findAsset($id);

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Aset tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'asset' => $asset,
            ],
        ]);
    }

    public function store(
        Request $request
    ): JsonResponse {
        $validated =
            $this->validateAsset($request);

        $actor =
            $request->attributes->get(
                'atlas_user'
            );

        $result = DB::transaction(
            function () use (
                $validated,
                $actor
            ) {
                $batchId = DB::table(
                    'asset_batches'
                )->insertGetId(
                    [
                        'input_method' =>
                            'SINGLE',
                        'notes' =>
                            $validated[
                                'batch_notes'
                            ] ?? null,
                        'created_by' =>
                            $actor->id,
                        'created_at' => now(),
                    ],
                    'id'
                );

                $assetId = DB::table(
                    'assets'
                )->insertGetId(
                    [
                        'batch_id' => $batchId,
                        'name' =>
                            $validated['name'],
                        'category' =>
                            $validated['category'],
                        'location' =>
                            $validated['location']
                            ?? null,
                        'condition' =>
                            $validated['condition']
                            ?? 'BAIK',
                        'availability_status' =>
                            $validated[
                                'availability_status'
                            ] ?? 'TERSEDIA',
                        'notes' =>
                            $validated['notes']
                            ?? null,
                        'created_by' =>
                            $actor->id,
                        'updated_by' =>
                            $actor->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    'id'
                );

                return [
                    'batch_id' => $batchId,
                    'asset_id' => $assetId,
                ];
            }
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Aset berhasil ditambahkan.',
            'data' => [
                'batch_id' =>
                    $result['batch_id'],
                'asset' =>
                    $this->findAsset(
                        $result['asset_id']
                    ),
            ],
        ], 201);
    }

    public function bulkStore(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'batch_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'assets' => [
                'required',
                'array',
                'min:1',
                'max:500',
            ],

            'assets.*.name' => [
                'required',
                'string',
                'max:200',
            ],

            'assets.*.category' => [
                'required',
                'string',
                'max:120',
            ],

            'assets.*.location' => [
                'nullable',
                'string',
                'max:200',
            ],

            'assets.*.condition' => [
                'nullable',
                Rule::in([
                    'BAIK',
                    'RUSAK_RINGAN',
                    'RUSAK',
                    'PERLU_PEMERIKSAAN',
                ]),
            ],

            'assets.*.availability_status' => [
                'nullable',
                Rule::in([
                    'TERSEDIA',
                    'DIPINJAM',
                    'TIDAK_TERSEDIA',
                    'PERLU_PEMERIKSAAN',
                ]),
            ],

            'assets.*.notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $actor =
            $request->attributes->get(
                'atlas_user'
            );

        $result = DB::transaction(
            function () use (
                $validated,
                $actor
            ) {
                $batchId = DB::table(
                    'asset_batches'
                )->insertGetId(
                    [
                        'input_method' =>
                            'BULK_FORM',
                        'notes' =>
                            $validated[
                                'batch_notes'
                            ] ?? null,
                        'created_by' =>
                            $actor->id,
                        'created_at' => now(),
                    ],
                    'id'
                );

                $assetIds = [];

                foreach (
                    $validated['assets']
                    as $asset
                ) {
                    $assetIds[] =
                        DB::table('assets')
                            ->insertGetId(
                                [
                                    'batch_id' =>
                                        $batchId,
                                    'name' =>
                                        $asset[
                                            'name'
                                        ],
                                    'category' =>
                                        $asset[
                                            'category'
                                        ],
                                    'location' =>
                                        $asset[
                                            'location'
                                        ] ?? null,
                                    'condition' =>
                                        $asset[
                                            'condition'
                                        ] ?? 'BAIK',
                                    'availability_status' =>
                                        $asset[
                                            'availability_status'
                                        ] ?? 'TERSEDIA',
                                    'notes' =>
                                        $asset[
                                            'notes'
                                        ] ?? null,
                                    'created_by' =>
                                        $actor->id,
                                    'updated_by' =>
                                        $actor->id,
                                    'created_at' =>
                                        now(),
                                    'updated_at' =>
                                        now(),
                                ],
                                'id'
                            );
                }

                return [
                    'batch_id' => $batchId,
                    'asset_ids' => $assetIds,
                ];
            }
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Batch aset berhasil ditambahkan.',
            'data' => [
                'batch_id' =>
                    $result['batch_id'],
                'asset_count' =>
                    count($result['asset_ids']),
                'asset_ids' =>
                    $result['asset_ids'],
            ],
        ], 201);
    }

    public function update(
        Request $request,
        string $id
    ): JsonResponse {
        if (!$this->findAsset($id)) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Aset tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:200',
            ],
            'category' => [
                'sometimes',
                'required',
                'string',
                'max:120',
            ],
            'location' => [
                'nullable',
                'string',
                'max:200',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        if ($validated === []) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Tidak ada data yang diperbarui.',
            ], 422);
        }

        $actor =
            $request->attributes->get(
                'atlas_user'
            );

        $validated['updated_by'] =
            $actor->id;

        $validated['updated_at'] =
            now();

        DB::table('assets')
            ->where('id', $id)
            ->update($validated);

        return response()->json([
            'success' => true,
            'message' =>
                'Aset berhasil diperbarui.',
            'data' => [
                'asset' =>
                    $this->findAsset($id),
            ],
        ]);
    }

    public function updateStatus(
        Request $request,
        string $id
    ): JsonResponse {
        if (!$this->findAsset($id)) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Aset tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'condition' => [
                'sometimes',
                'required',
                Rule::in([
                    'BAIK',
                    'RUSAK_RINGAN',
                    'RUSAK',
                    'PERLU_PEMERIKSAAN',
                ]),
            ],
            'availability_status' => [
                'sometimes',
                'required',
                Rule::in([
                    'TERSEDIA',
                    'DIPINJAM',
                    'TIDAK_TERSEDIA',
                    'PERLU_PEMERIKSAAN',
                ]),
            ],
            'location' => [
                'sometimes',
                'nullable',
                'string',
                'max:200',
            ],
        ]);

        if ($validated === []) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Tidak ada status aset yang diperbarui.',
            ], 422);
        }

        $actor =
            $request->attributes->get(
                'atlas_user'
            );

        $validated['updated_by'] =
            $actor->id;

        $validated['updated_at'] =
            now();

        DB::table('assets')
            ->where('id', $id)
            ->update($validated);

        return response()->json([
            'success' => true,
            'message' =>
                'Status aset berhasil diperbarui.',
            'data' => [
                'asset' =>
                    $this->findAsset($id),
            ],
        ]);
    }

    public function history(
        string $id
    ): JsonResponse {
        if (!$this->findAsset($id)) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Aset tidak ditemukan.',
            ], 404);
        }

        $history = DB::table(
            'asset_status_history as h'
        )
            ->leftJoin(
                'profiles as p',
                'p.id',
                '=',
                'h.changed_by'
            )
            ->select([
                'h.id',
                'h.asset_id',
                'h.previous_condition',
                'h.new_condition',
                'h.previous_status',
                'h.new_status',
                'h.previous_location',
                'h.new_location',
                'h.notes',
                'h.created_at',
                'p.full_name as changed_by_name',
            ])
            ->where('h.asset_id', $id)
            ->orderByDesc('h.created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'history' => $history,
            ],
        ]);
    }

    public function availability(
        Request $request
    ): JsonResponse {
        $query = DB::table('assets')
            ->select([
                'id',
                'batch_id',
                'name',
                'category',
                'location',
                'condition',
                'availability_status',
            ])
            ->where(
                'availability_status',
                'TERSEDIA'
            )
            ->where(
                'condition',
                '!=',
                'RUSAK'
            );

        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->string(
                    'category'
                )->toString()
            );
        }

        if ($request->filled('location')) {
            $query->where(
                'location',
                $request->string(
                    'location'
                )->toString()
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'assets' =>
                    $query
                        ->orderBy('name')
                        ->get(),
            ],
        ]);
    }

    private function validateAsset(
        Request $request
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:200',
            ],
            'category' => [
                'required',
                'string',
                'max:120',
            ],
            'location' => [
                'nullable',
                'string',
                'max:200',
            ],
            'condition' => [
                'nullable',
                Rule::in([
                    'BAIK',
                    'RUSAK_RINGAN',
                    'RUSAK',
                    'PERLU_PEMERIKSAAN',
                ]),
            ],
            'availability_status' => [
                'nullable',
                Rule::in([
                    'TERSEDIA',
                    'DIPINJAM',
                    'TIDAK_TERSEDIA',
                    'PERLU_PEMERIKSAAN',
                ]),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'batch_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);
    }

    private function findAsset(
        string $id
    ): ?object {
        return DB::table('assets as a')
            ->join(
                'asset_batches as b',
                'b.id',
                '=',
                'a.batch_id'
            )
            ->leftJoin(
                'profiles as creator',
                'creator.id',
                '=',
                'a.created_by'
            )
            ->leftJoin(
                'profiles as updater',
                'updater.id',
                '=',
                'a.updated_by'
            )
            ->select([
                'a.id',
                'a.batch_id',
                'a.name',
                'a.category',
                'a.location',
                'a.condition',
                'a.availability_status',
                'a.notes',
                'a.created_at',
                'a.updated_at',
                'b.input_method',
                'b.notes as batch_notes',
                'creator.full_name as created_by_name',
                'updater.full_name as updated_by_name',
            ])
            ->where('a.id', $id)
            ->first();
    }
}