<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ShrinkageGroup;
use Illuminate\Support\Collection;

class ShrinkageClassificationService
{
    /**
     * Calculate averages and classify against shrinkage group records.
     *
     * @param float $l1
     * @param float $l2
     * @param float $l3
     * @param float $w1
     * @param float $w2
     * @param float $w3
     * @param string $stage
     * @return array{
     *     avg_length: float,
     *     avg_width: float,
     *     avg_length_display: float,
     *     avg_width_display: float,
     *     match_status: string,
     *     matched_group: ?ShrinkageGroup,
     *     matching_candidates: array<int, array<string, mixed>>,
     *     message: string
     * }
     */
    public function classify(
        float $l1,
        float $l2,
        float $l3,
        float $w1,
        float $w2,
        float $w3,
        string $stage = 'one'
    ): array {
        // Calculate averages using full precision
        $avgLength = ($l1 + $l2 + $l3) / 3.0;
        $avgWidth = ($w1 + $w2 + $w3) / 3.0;

        $avgLengthDisplay = round($avgLength, 2);
        $avgWidthDisplay = round($avgWidth, 2);

        // Fetch candidate groups filtered by stage
        /** @var Collection<int, ShrinkageGroup> $groups */
        $groups = ShrinkageGroup::query()
            ->where('stage', $stage)
            ->get();

        $matchingGroups = $groups->filter(fn (ShrinkageGroup $group): bool => $group->matches($avgLength, $avgWidth, $stage))->values();

        $matchCount = $matchingGroups->count();

        $candidatesData = $matchingGroups->map(fn (ShrinkageGroup $g): array => [
            'id' => $g->id,
            'group_number' => $g->group_number,
            'stage' => $g->stage,
            'min_length_percentage' => $g->min_length_percentage,
            'adjust_length_percentage' => $g->adjust_length_percentage,
            'max_length_percentage' => $g->max_length_percentage,
            'length_range' => sprintf(
                '[%s%% to %s%%]',
                min($g->min_length_percentage, $g->max_length_percentage),
                max($g->min_length_percentage, $g->max_length_percentage)
            ),
            'min_width_percentage' => $g->min_width_percentage,
            'adjust_width_percentage' => $g->adjust_width_percentage,
            'max_width_percentage' => $g->max_width_percentage,
            'width_range' => sprintf(
                '[%s%% to %s%%]',
                min($g->min_width_percentage, $g->max_width_percentage),
                max($g->min_width_percentage, $g->max_width_percentage)
            ),
        ])->toArray();

        if ($matchCount === 1) {
            $singleGroup = $matchingGroups->first();

            return [
                'avg_length' => $avgLength,
                'avg_width' => $avgWidth,
                'avg_length_display' => $avgLengthDisplay,
                'avg_width_display' => $avgWidthDisplay,
                'match_status' => 'matched',
                'matched_group' => $singleGroup,
                'matching_candidates' => $candidatesData,
                'message' => "Matched Group {$singleGroup->group_number} for stage '{$stage}'.",
            ];
        }

        if ($matchCount > 1) {
            $groupList = $matchingGroups->pluck('group_number')->join(', ');

            return [
                'avg_length' => $avgLength,
                'avg_width' => $avgWidth,
                'avg_length_display' => $avgLengthDisplay,
                'avg_width_display' => $avgWidthDisplay,
                'match_status' => 'ambiguous',
                'matched_group' => null,
                'matching_candidates' => $candidatesData,
                'message' => "Multiple matching groups found ({$groupList}). Boundary overlap detected.",
            ];
        }

        return [
            'avg_length' => $avgLength,
            'avg_width' => $avgWidth,
            'avg_length_display' => $avgLengthDisplay,
            'avg_width_display' => $avgWidthDisplay,
            'match_status' => 'no_match',
            'matched_group' => null,
            'matching_candidates' => [],
            'message' => "No matching group found for Length: {$avgLengthDisplay}% and Width: {$avgWidthDisplay}% in stage '{$stage}' (Range gap or out of chart bounds).",
        ];
    }

    /**
     * Helper to compute percentage from before and after measurements:
     * ((After - Before) / Before) * 100
     */
    public function calculatePercentageFromMeasurement(float $before, float $after): float
    {
        if ($before <= 0.0) {
            return 0.0;
        }

        return (($after - $before) / $before) * 100.0;
    }
}

