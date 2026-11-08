<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFabricRollRequest;
use App\Http\Requests\UpdateFabricRollRequest;
use App\Models\FabricRoll;
use App\Services\ShrinkageClassificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FabricRollController extends Controller
{
    public function __construct(
        private readonly ShrinkageClassificationService $classificationService
    ) {}

    /**
     * Display a listing of fabric rolls with filtering, search, and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FabricRoll::with('matchedGroup');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('roll_number', 'like', "%{$search}%")
                    ->orWhere('buyer', 'like', "%{$search}%")
                    ->orWhere('style', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('lot', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->string('stage'));
        }

        if ($request->filled('match_status')) {
            $query->where('match_status', $request->string('match_status'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = ['id', 'roll_number', 'buyer', 'style', 'lot', 'quantity', 'avg_length', 'avg_width', 'created_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $perPage = (int) $request->integer('per_page', 25);
        $rolls = $query->paginate($perPage);

        return response()->json($rolls);
    }

    /**
     * Store a newly created fabric roll.
     */
    public function store(StoreFabricRollRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $classification = $this->classificationService->classify(
            (float) $validated['l1'],
            (float) $validated['l2'],
            (float) $validated['l3'],
            (float) $validated['w1'],
            (float) $validated['w2'],
            (float) $validated['w3'],
            (string) ($validated['stage'] ?? 'one')
        );

        $roll = FabricRoll::create([
            'roll_number' => $validated['roll_number'],
            'buyer' => $validated['buyer'],
            'style' => $validated['style'],
            'color' => $validated['color'],
            'lot' => $validated['lot'],
            'quantity' => (float) $validated['quantity'],
            'stage' => $validated['stage'] ?? 'one',
            'measurement_mode' => $validated['measurement_mode'] ?? 'direct_percentage',
            'l1' => (float) $validated['l1'],
            'l2' => (float) $validated['l2'],
            'l3' => (float) $validated['l3'],
            'w1' => (float) $validated['w1'],
            'w2' => (float) $validated['w2'],
            'w3' => (float) $validated['w3'],
            'raw_measurements' => $validated['raw_measurements'] ?? null,
            'avg_length' => $classification['avg_length'],
            'avg_width' => $classification['avg_width'],
            'match_status' => $classification['match_status'],
            'matched_group_id' => $classification['matched_group']?->id,
            'matching_candidates' => $classification['matching_candidates'],
        ]);

        $roll->load('matchedGroup');

        return response()->json([
            'message' => 'Fabric roll recorded successfully.',
            'data' => $roll,
            'classification' => $classification,
        ], 201);
    }

    /**
     * Display the specified fabric roll.
     */
    public function show(FabricRoll $fabricRoll): JsonResponse
    {
        $fabricRoll->load('matchedGroup');

        return response()->json($fabricRoll);
    }

    /**
     * Update the specified fabric roll.
     */
    public function update(UpdateFabricRollRequest $request, FabricRoll $fabricRoll): JsonResponse
    {
        $validated = $request->validated();

        $stage = (string) ($validated['stage'] ?? $fabricRoll->stage ?? 'one');
        $l1 = (float) $validated['l1'];
        $l2 = (float) $validated['l2'];
        $l3 = (float) $validated['l3'];
        $w1 = (float) $validated['w1'];
        $w2 = (float) $validated['w2'];
        $w3 = (float) $validated['w3'];

        $classification = $this->classificationService->classify(
            $l1,
            $l2,
            $l3,
            $w1,
            $w2,
            $w3,
            $stage
        );

        $fabricRoll->update([
            'roll_number' => $validated['roll_number'] ?? $fabricRoll->roll_number,
            'buyer' => $validated['buyer'] ?? $fabricRoll->buyer,
            'style' => $validated['style'] ?? $fabricRoll->style,
            'color' => $validated['color'] ?? $fabricRoll->color,
            'lot' => $validated['lot'] ?? $fabricRoll->lot,
            'quantity' => isset($validated['quantity']) ? (float) $validated['quantity'] : (float) $fabricRoll->quantity,
            'stage' => $stage,
            'measurement_mode' => $validated['measurement_mode'] ?? $fabricRoll->measurement_mode,
            'l1' => $l1,
            'l2' => $l2,
            'l3' => $l3,
            'w1' => $w1,
            'w2' => $w2,
            'w3' => $w3,
            'raw_measurements' => $validated['raw_measurements'] ?? $fabricRoll->raw_measurements,
            'avg_length' => $classification['avg_length'],
            'avg_width' => $classification['avg_width'],
            'match_status' => $classification['match_status'],
            'matched_group_id' => $classification['matched_group']?->id,
            'matching_candidates' => $classification['matching_candidates'],
        ]);

        $fabricRoll->load('matchedGroup');

        return response()->json([
            'message' => 'Fabric roll updated successfully.',
            'data' => $fabricRoll,
            'classification' => $classification,
        ]);
    }

    /**
     * Remove the specified fabric roll.
     */
    public function destroy(FabricRoll $fabricRoll): JsonResponse
    {
        $fabricRoll->delete();

        return response()->json(['message' => 'Fabric roll deleted successfully.']);
    }

    /**
     * Get statistics and charts comparison data.
     */
    public function stats(): JsonResponse
    {
        $totalRolls = FabricRoll::count();
        $matchedCount = FabricRoll::where('match_status', 'matched')->count();
        $noMatchCount = FabricRoll::where('match_status', 'no_match')->count();
        $ambiguousCount = FabricRoll::where('match_status', 'ambiguous')->count();

        $overallAvgLength = FabricRoll::avg('avg_length') ?? 0.0;
        $overallAvgWidth = FabricRoll::avg('avg_width') ?? 0.0;
        $totalQuantity = FabricRoll::sum('quantity') ?? 0.0;

        // Roll comparisons for Chart.js
        $recentRolls = FabricRoll::with('matchedGroup')
            ->latest()
            ->limit(15)
            ->get()
            ->reverse()
            ->values();

        $chartLabels = $recentRolls->pluck('roll_number');
        $lengthAverages = $recentRolls->map(fn (FabricRoll $r) => round((float) $r->avg_length, 2));
        $widthAverages = $recentRolls->map(fn (FabricRoll $r) => round((float) $r->avg_width, 2));

        // Group distribution
        $groupDistribution = FabricRoll::with('matchedGroup')
            ->whereNotNull('matched_group_id')
            ->get()
            ->groupBy(fn (FabricRoll $r) => 'Group ' . ($r->matchedGroup?->group_number ?? 'Unknown'))
            ->map(fn ($group) => $group->count());

        return response()->json([
            'total_rolls' => $totalRolls,
            'matched_count' => $matchedCount,
            'no_match_count' => $noMatchCount,
            'ambiguous_count' => $ambiguousCount,
            'overall_avg_length' => round((float) $overallAvgLength, 2),
            'overall_avg_width' => round((float) $overallAvgWidth, 2),
            'total_quantity' => round((float) $totalQuantity, 2),
            'chart' => [
                'labels' => $chartLabels,
                'length_averages' => $lengthAverages,
                'width_averages' => $widthAverages,
                'group_distribution' => [
                    'labels' => $groupDistribution->keys(),
                    'counts' => $groupDistribution->values(),
                ],
            ],
        ]);
    }

    /**
     * Bulk import multiple fabric rolls from CSV.
     */
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $file = $request->file('csv_file');
        if (!$file) {
            return response()->json(['message' => 'No file uploaded.'], 422);
        }

        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return response()->json(['message' => 'Unable to read the uploaded CSV file.'], 500);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return response()->json(['message' => 'The uploaded CSV file is empty.'], 422);
        }

        // Map header indices
        $headerMap = [];
        foreach ($header as $index => $colName) {
            $cleaned = strtolower(trim((string) $colName));
            $headerMap[$cleaned] = $index;
        }

        $requiredCols = ['roll_number', 'buyer', 'style', 'color', 'lot', 'quantity', 'l1', 'l2', 'l3', 'w1', 'w2', 'w3'];
        foreach ($requiredCols as $col) {
            if (!array_key_exists($col, $headerMap)) {
                fclose($handle);
                return response()->json([
                    'message' => "Missing required CSV column: '{$col}'. Required columns are: " . implode(', ', $requiredCols) . ', stage',
                ], 422);
            }
        }

        $imported = 0;
        $updated = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (empty(array_filter($row, fn ($val) => trim((string) $val) !== ''))) {
                continue; // Skip empty rows
            }

            $getVal = fn (string $key, string $default = ''): string => isset($headerMap[$key], $row[$headerMap[$key]]) ? trim((string) $row[$headerMap[$key]]) : $default;

            $rollNumber = $getVal('roll_number');
            $buyer = $getVal('buyer');
            $style = $getVal('style');
            $color = $getVal('color');
            $lot = $getVal('lot');
            $quantityStr = $getVal('quantity');
            $stage = $getVal('stage', 'one') ?: 'one';
            $l1Str = $getVal('l1');
            $l2Str = $getVal('l2');
            $l3Str = $getVal('l3');
            $w1Str = $getVal('w1');
            $w2Str = $getVal('w2');
            $w3Str = $getVal('w3');

            if ($rollNumber === '') {
                $errors[] = "Row {$rowNumber}: Missing roll_number.";
                continue;
            }

            if (!is_numeric($quantityStr) || !is_numeric($l1Str) || !is_numeric($l2Str) || !is_numeric($l3Str) || !is_numeric($w1Str) || !is_numeric($w2Str) || !is_numeric($w3Str)) {
                $errors[] = "Row {$rowNumber} (Roll '{$rollNumber}'): Quantity and all 6 percentage readings (l1, l2, l3, w1, w2, w3) must be numeric.";
                continue;
            }

            $l1 = (float) $l1Str;
            $l2 = (float) $l2Str;
            $l3 = (float) $l3Str;
            $w1 = (float) $w1Str;
            $w2 = (float) $w2Str;
            $w3 = (float) $w3Str;
            $quantity = (float) $quantityStr;

            $classification = $this->classificationService->classify($l1, $l2, $l3, $w1, $w2, $w3, $stage);

            $existingRoll = FabricRoll::where('roll_number', $rollNumber)->first();
            $isNew = !$existingRoll;

            FabricRoll::updateOrCreate(
                ['roll_number' => $rollNumber],
                [
                    'buyer' => $buyer,
                    'style' => $style,
                    'color' => $color,
                    'lot' => $lot,
                    'quantity' => $quantity,
                    'stage' => $stage,
                    'measurement_mode' => 'direct_percentage',
                    'l1' => $l1,
                    'l2' => $l2,
                    'l3' => $l3,
                    'w1' => $w1,
                    'w2' => $w2,
                    'w3' => $w3,
                    'avg_length' => $classification['avg_length'],
                    'avg_width' => $classification['avg_width'],
                    'match_status' => $classification['match_status'],
                    'matched_group_id' => $classification['matched_group']?->id,
                    'matching_candidates' => $classification['matching_candidates'],
                ]
            );

            if ($isNew) {
                $imported++;
            } else {
                $updated++;
            }
        }

        fclose($handle);

        return response()->json([
            'message' => "CSV import complete: {$imported} rolls added, {$updated} updated.",
            'imported_count' => $imported,
            'updated_count' => $updated,
            'total_processed' => $imported + $updated,
            'errors' => $errors,
        ]);
    }

    /**
     * Download a sample CSV template for fabric rolls.
     */
    public function downloadSampleCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fabric_rolls_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['roll_number', 'buyer', 'style', 'color', 'lot', 'quantity', 'stage', 'l1', 'l2', 'l3', 'w1', 'w2', 'w3']);
            fputcsv($handle, ['ROLL-BATCH-001', 'Acme Corp', 'Denim 501', 'Dark Indigo', 'LOT-501', '1250.00', 'one', '-3.20', '-3.50', '-3.10', '-3.40', '-3.80', '-3.60']);
            fputcsv($handle, ['ROLL-BATCH-002', 'Zara', 'Linen Shirt', 'Optic White', 'LOT-502', '840.50', 'one', '-1.10', '-0.90', '-1.00', '-0.70', '-0.80', '-0.60']);
            fputcsv($handle, ['ROLL-BATCH-003', 'H&M', 'Chino Pants', 'Khaki', 'LOT-503', '2100.00', 'one', '-1.20', '-1.50', '-0.90', '-3.60', '-3.80', '-3.70']);
            fclose($handle);
        }, 200, $headers);
    }
}

