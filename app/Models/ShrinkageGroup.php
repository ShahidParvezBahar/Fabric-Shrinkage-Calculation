<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShrinkageGroup extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'group_number',
        'min_length_percentage',
        'adjust_length_percentage',
        'max_length_percentage',
        'min_width_percentage',
        'adjust_width_percentage',
        'max_width_percentage',
        'stage',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_length_percentage' => 'float',
            'adjust_length_percentage' => 'float',
            'max_length_percentage' => 'float',
            'min_width_percentage' => 'float',
            'adjust_width_percentage' => 'float',
            'max_width_percentage' => 'float',
        ];
    }

    /**
     * @return HasMany<FabricRoll, $this>
     */
    public function fabricRolls(): HasMany
    {
        return $this->hasMany(FabricRoll::class, 'matched_group_id');
    }

    /**
     * Check if a given length and width average falls within this group's boundaries.
     */
    public function matches(float $avgLength, float $avgWidth, string $stage): bool
    {
        if (strcasecmp($this->stage, $stage) !== 0) {
            return false;
        }

        $lengthMin = min($this->min_length_percentage, $this->max_length_percentage);
        $lengthMax = max($this->min_length_percentage, $this->max_length_percentage);

        $widthMin = min($this->min_width_percentage, $this->max_width_percentage);
        $widthMax = max($this->min_width_percentage, $this->max_width_percentage);

        // Allow micro-epsilon for floating point precision in boundary checks
        $epsilon = 0.0000001;

        $lengthMatches = ($avgLength >= ($lengthMin - $epsilon)) && ($avgLength <= ($lengthMax + $epsilon));
        $widthMatches = ($avgWidth >= ($widthMin - $epsilon)) && ($avgWidth <= ($widthMax + $epsilon));

        return $lengthMatches && $widthMatches;
    }
}

