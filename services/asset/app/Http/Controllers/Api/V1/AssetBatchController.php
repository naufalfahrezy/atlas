<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetBatchController extends Controller
{
    public function index(
        Request $request
    ): JsonResponse {
        $perPage = min(
            max(
                (int) $request->input(
                    'per_page',
                    15
                ),
                1
            ),
            100
        );

        $query = DB::table(
            'asset_batches as b'
        )
            ->leftJoin(
                'assets as a',
                'a.batch_id',
                '=',
                'b.id'
            )
            ->leftJoin(
                'profiles as p',
                'p.id',
                '=',
                'b.created_by'
            )
            ->select([
                'b.id',
                'b.input_method',
                'b.notes',
                'b.created_at',
                'p.full_name as created_by_name',
            ])
            ->selectRaw(
                'COUNT(a.id) AS asset_count'
            )
            ->groupBy([
                'b.id',
                'b.input_method',
                'b.notes',
                'b.created_at',
                'p.full_name',
            ])
            ->orderByDesc('b.created_at');

        if ($request->filled('search')) {
            $search =
                '%' .
                $request->string(
                    'search'
                )->toString() .
                '%';

            $query->whereRaw(
                'CAST(b.id AS TEXT) ILIKE ?',
                [$search]
            );
        }

        return response()->json([
            'success' => true,
            'data' =>
                $query->paginate($perPage),
        ]);
    }

    public function show(
        string $id
    ): JsonResponse {
        $batch = DB::table(
            'asset_batches as b'
        )
            ->leftJoin(
                'profiles as p',
                'p.id',
                '=',
                'b.created_by'
            )
            ->select([
                'b.id',
                'b.input_method',
                'b.notes',
                'b.created_at',
                'p.full_name as created_by_name',
            ])
            ->where('b.id', $id)
            ->first();

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Batch aset tidak ditemukan.',
            ], 404);
        }

        $assets = DB::table('assets')
            ->select([
                'id',
                'name',
                'category',
                'location',
                'condition',
                'availability_status',
                'created_at',
            ])
            ->where('batch_id', $id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'batch' => $batch,
                'asset_count' =>
                    $assets->count(),
                'assets' => $assets,
            ],
        ]);
    }
}