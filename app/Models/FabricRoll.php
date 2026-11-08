<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FabricRoll extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'roll_number',
        'buyer',
        'style',
        'color',
        'lot',
        'quantity',
        'stage',
        'measurement_mode',
        'l1',
        'l2',
        'l3',
        'w1',
        'w2',
        'w3',
        'raw_measurements',
        'avg_length',
        'avg_width',
        'match_status',
        'matched_group_id',
        'matching_candidates',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'l1' => 'float',
            'l2' => 'float',
            'l3' => 'float',
            'w1' => 'float',
            'w2' => 'float',
            'w3' => 'float',
            'raw_measurements' => 'array',
            'avg_length' => 'float',
            'avg_width' => 'float',
            'matching_candidates' => 'array',
        ];
    }

    /**
     * @return BelongsTo<ShrinkageGroup, $this>
     */
    public function matchedGroup(): BelongsTo
    {
        return $this->belongsTo(ShrinkageGroup::class, 'matched_group_id');
    }
}

