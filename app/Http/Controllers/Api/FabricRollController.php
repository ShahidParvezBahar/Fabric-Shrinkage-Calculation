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

        $classification = $this->classificationService->classify(
            (float) $validated['l1'],
            (float) $validated['l2'],
            (float) $validated['l3'],
            (float) $validated['w1'],
            (float) $validated['w2'],
            (float) $validated['w3'],
            (string) ($validated['stage'] ?? 'one')
        );

        $fabricRoll->update([
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
}

