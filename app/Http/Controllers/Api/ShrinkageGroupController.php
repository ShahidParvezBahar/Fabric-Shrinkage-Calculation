<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PreviewMatchRequest;
use App\Models\ShrinkageGroup;
use App\Services\ShrinkageClassificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ShrinkageGroupController extends Controller
{
    public function __construct(
        private readonly ShrinkageClassificationService $classificationService
    ) {}

    /**
     * List all shrinkage groups with optional stage filter and search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ShrinkageGroup::query();

        if ($request->filled('stage')) {
            $query->where('stage', $request->string('stage'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('group_number', 'like', "%{$search}%");
        }

        $perPage = (int) $request->integer('per_page', 50);
        $groups = $query->orderByRaw('CAST(group_number AS UNSIGNED) ASC')->paginate($perPage);

        $stages = ShrinkageGroup::query()->select('stage')->distinct()->pluck('stage');

        return response()->json([
            'data' => $groups->items(),
            'current_page' => $groups->currentPage(),
            'last_page' => $groups->lastPage(),
            'total' => $groups->total(),
            'stages' => $stages,
        ]);
    }

    /**
     * Preview calculation and matching before saving.
     */
    public function previewMatch(PreviewMatchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->classificationService->classify(
            (float) $validated['l1'],
            (float) $validated['l2'],
            (float) $validated['l3'],
            (float) $validated['w1'],
            (float) $validated['w2'],
            (float) $validated['w3'],
            (string) ($validated['stage'] ?? 'one')
        );

        return response()->json($result);
    }

    /**
     * Import shrinkage groups from uploaded CSV or default CSV.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $filePath = null;

        if ($request->hasFile('csv_file')) {
            $filePath = $request->file('csv_file')?->getRealPath();
        } else {
            $filePath = database_path('data/shrinkage_groups.csv');
        }

        if (!$filePath || !File::exists($filePath)) {
            return response()->json(['message' => 'No CSV file provided or default file missing.'], 422);
        }

        $file = fopen($filePath, 'r');
        if ($file === false) {
            return response()->json(['message' => 'Unable to read the CSV file.'], 500);
        }

        fgetcsv($file); // skip header

        Schema::disableForeignKeyConstraints();
        ShrinkageGroup::query()->delete();
        Schema::enableForeignKeyConstraints();

        $records = [];
        $batchSize = 100;
        $count = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 8) {
                continue;
            }

            $records[] = [
                'group_number' => trim((string) $row[0]),
                'min_length_percentage' => (float) $row[1],
                'adjust_length_percentage' => (float) $row[2],
                'max_length_percentage' => (float) $row[3],
                'min_width_percentage' => (float) $row[4],
                'adjust_width_percentage' => (float) $row[5],
                'max_width_percentage' => (float) $row[6],
                'stage' => trim((string) $row[7]),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $count++;

            if (count($records) >= $batchSize) {
                ShrinkageGroup::insert($records);
                $records = [];
            }
        }

        if (!empty($records)) {
            ShrinkageGroup::insert($records);
        }

        fclose($file);

        return response()->json([
            'message' => "Successfully imported {$count} shrinkage groups.",
            'total' => $count,
        ]);
    }
}

