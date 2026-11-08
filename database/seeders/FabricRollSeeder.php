<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FabricRoll;
use App\Services\ShrinkageClassificationService;
use Illuminate\Database\Seeder;

class FabricRollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = app(ShrinkageClassificationService::class);

        $buyers = [
            'Levi Strauss',
            'Zara Inditex',
            'H&M Hennes',
            'Uniqlo',
            'Gap Inc.',
            'Tommy Hilfiger',
            'Calvin Klein',
            'Nike Global',
            'Adidas Group',
            'Marks & Spencer',
            'American Eagle',
            'Ralph Lauren',
        ];

        $styles = [
            'Slim Fit Denim 511',
            'Linen Button Shirt',
            'Chino Trousers Casual',
            'Ultra Stretch Jersey',
            'Classic Trucker Jacket',
            'Heavyweight Cargo Pants',
            'Cotton Oxford Shirt',
            'Pique Polo Shirt',
            'Relaxed Fit Jeans 505',
            'Ripstop Workwear Pants',
            'French Terry Hoodie',
            'Twill Utility Overshirt',
        ];

        $colors = [
            'Dark Indigo',
            'Optic White',
            'Stone Wash Blue',
            'Khaki Beige',
            'Charcoal Heather',
            'Jet Black',
            'Olive Drab',
            'Crimson Red',
            'Vintage Fade',
            'Sandstone Tan',
            'Deep Navy',
            'Sage Green',
        ];

        // 50 specific, realistic reading profiles across multiple shrinkage groups, gaps, and expansion
        $readingProfiles = [
            // 1. Worked Example (Group 23)
            ['l' => [-3.20, -3.50, -3.10], 'w' => [-3.40, -3.80, -3.60], 'stage' => 'one'],
            // 2. Group 1
            ['l' => [-1.10, -0.90, -1.00], 'w' => [-0.70, -0.80, -0.60], 'stage' => 'one'],
            // 3. Group 2
            ['l' => [-0.80, -1.20, -1.00], 'w' => [-2.10, -2.30, -2.20], 'stage' => 'one'],
            // 4. Group 3
            ['l' => [-1.20, -1.50, -0.90], 'w' => [-3.60, -3.80, -3.70], 'stage' => 'one'],
            // 5. Group 4
            ['l' => [-1.40, -1.00, -1.20], 'w' => [-5.10, -5.30, -5.20], 'stage' => 'one'],
            // 6. Group 5
            ['l' => [-0.50, -0.90, -0.70], 'w' => [-6.60, -6.80, -6.70], 'stage' => 'one'],
            // 7. Group 6
            ['l' => [-1.10, -1.30, -1.20], 'w' => [-8.10, -8.30, -8.20], 'stage' => 'one'],
            // 8. Group 7
            ['l' => [-1.60, -1.40, -1.50], 'w' => [-9.60, -9.80, -9.70], 'stage' => 'one'],
            // 9. Group 8
            ['l' => [-0.90, -1.10, -1.00], 'w' => [-11.10, -11.30, -11.20], 'stage' => 'one'],
            // 10. Group 9
            ['l' => [-1.30, -1.50, -1.40], 'w' => [-12.60, -12.80, -12.70], 'stage' => 'one'],
            // 11. Group 10
            ['l' => [-1.00, -1.20, -1.10], 'w' => [-14.10, -14.30, -14.20], 'stage' => 'one'],
            // 12. Group 21
            ['l' => [-3.00, -3.20, -3.10], 'w' => [-0.60, -0.80, -0.70], 'stage' => 'one'],
            // 13. Group 22
            ['l' => [-2.90, -3.30, -3.10], 'w' => [-2.10, -2.30, -2.20], 'stage' => 'one'],
            // 14. Group 24
            ['l' => [-3.10, -3.20, -3.00], 'w' => [-5.10, -5.30, -5.20], 'stage' => 'one'],
            // 15. Group 25
            ['l' => [-2.80, -3.40, -3.10], 'w' => [-6.60, -6.80, -6.70], 'stage' => 'one'],
            // 16. Group 26
            ['l' => [-3.00, -3.10, -3.20], 'w' => [-8.00, -8.40, -8.20], 'stage' => 'one'],
            // 17. Group 27
            ['l' => [-3.20, -3.10, -3.00], 'w' => [-9.60, -9.80, -9.70], 'stage' => 'one'],
            // 18. Group 28
            ['l' => [-3.10, -3.30, -2.90], 'w' => [-11.00, -11.40, -11.20], 'stage' => 'one'],
            // 19. Group 29
            ['l' => [-3.00, -3.20, -3.10], 'w' => [-12.50, -12.90, -12.70], 'stage' => 'one'],
            // 20. Group 30
            ['l' => [-3.10, -3.20, -3.00], 'w' => [-14.00, -14.40, -14.20], 'stage' => 'one'],
            // 21. Group 41
            ['l' => [-5.10, -5.30, -5.20], 'w' => [-0.80, -1.00, -0.90], 'stage' => 'one'],
            // 22. Group 42
            ['l' => [-5.00, -5.40, -5.20], 'w' => [-2.00, -2.40, -2.20], 'stage' => 'one'],
            // 23. Group 43
            ['l' => [-5.10, -5.30, -5.20], 'w' => [-3.50, -3.90, -3.70], 'stage' => 'one'],
            // 24. Group 44
            ['l' => [-5.20, -5.10, -5.30], 'w' => [-5.00, -5.40, -5.20], 'stage' => 'one'],
            // 25. Group 45
            ['l' => [-5.00, -5.30, -5.30], 'w' => [-6.50, -6.90, -6.70], 'stage' => 'one'],
            // 26. Group 61
            ['l' => [-7.20, -7.40, -7.30], 'w' => [-0.60, -0.80, -0.70], 'stage' => 'one'],
            // 27. Group 62
            ['l' => [-7.10, -7.50, -7.30], 'w' => [-2.10, -2.30, -2.20], 'stage' => 'one'],
            // 28. Group 63
            ['l' => [-7.20, -7.40, -7.30], 'w' => [-3.60, -3.80, -3.70], 'stage' => 'one'],
            // 29. Group 64
            ['l' => [-7.00, -7.60, -7.30], 'w' => [-5.10, -5.30, -5.20], 'stage' => 'one'],
            // 30. Group 81
            ['l' => [-9.30, -9.50, -9.40], 'w' => [-0.70, -0.90, -0.80], 'stage' => 'one'],
            // 31. Group 82
            ['l' => [-9.20, -9.60, -9.40], 'w' => [-2.00, -2.40, -2.20], 'stage' => 'one'],
            // 32. Group 83
            ['l' => [-9.30, -9.50, -9.40], 'w' => [-3.50, -3.90, -3.70], 'stage' => 'one'],
            // 33. Group 101
            ['l' => [-11.40, -11.60, -11.50], 'w' => [-0.60, -0.80, -0.70], 'stage' => 'one'],
            // 34. Group 102
            ['l' => [-11.30, -11.70, -11.50], 'w' => [-2.10, -2.30, -2.20], 'stage' => 'one'],
            // 35. Group 121
            ['l' => [-13.50, -13.70, -13.60], 'w' => [-0.60, -0.80, -0.70], 'stage' => 'one'],
            // 36. Group 122
            ['l' => [-13.40, -13.80, -13.60], 'w' => [-2.10, -2.30, -2.20], 'stage' => 'one'],
            // 37. Positive Expansion - Group 141 (0.1 to 2.1 L, 0.1 to 1.4 W)
            ['l' => [1.00, 1.20, 1.10], 'w' => [0.70, 0.80, 0.60], 'stage' => 'one'],
            // 38. Positive Expansion - Group 142 (0.1 to 2.1 L, 1.5 to 2.9 W)
            ['l' => [1.10, 1.00, 1.20], 'w' => [2.10, 2.30, 2.20], 'stage' => 'one'],
            // 39. Positive Expansion - Group 143 (0.1 to 2.1 L, 3 to 4.4 W)
            ['l' => [0.90, 1.30, 1.10], 'w' => [3.60, 3.80, 3.70], 'stage' => 'one'],
            // 40. Positive Expansion - Group 161 (2.2 to 4.2 L, 0.1 to 1.4 W)
            ['l' => [3.10, 3.30, 3.20], 'w' => [0.60, 0.80, 0.70], 'stage' => 'one'],
            // 41. Positive Expansion - Group 162 (2.2 to 4.2 L, 1.5 to 2.9 W)
            ['l' => [3.00, 3.40, 3.20], 'w' => [2.10, 2.30, 2.20], 'stage' => 'one'],
            // 42. Positive Expansion - Group 181 (4.3 to 6.3 L, 0.1 to 1.4 W)
            ['l' => [5.20, 5.40, 5.30], 'w' => [0.70, 0.80, 0.60], 'stage' => 'one'],
            // 43. Mixed: Length Expansion & Width Contraction - Group 201 (0.1 to 2.1 L, 0 to -1.4 W)
            ['l' => [1.00, 1.20, 1.10], 'w' => [-0.60, -0.80, -0.70], 'stage' => 'one'],
            // 44. Mixed: Length Expansion & Width Contraction - Group 202 (0.1 to 2.1 L, -1.5 to -2.9 W)
            ['l' => [1.10, 0.90, 1.30], 'w' => [-2.10, -2.30, -2.20], 'stage' => 'one'],
            // 45. Mixed: Length Expansion & Width Contraction - Group 203 (0.1 to 2.1 L, -3 to -4.4 W)
            ['l' => [1.00, 1.10, 1.20], 'w' => [-3.60, -3.80, -3.70], 'stage' => 'one'],
            // 46. Gap Example 1: Width -1.45% (between -1.4% and -1.5%) -> No Match
            ['l' => [-1.00, -1.00, -1.00], 'w' => [-1.45, -1.45, -1.45], 'stage' => 'one'],
            // 47. Gap Example 2: Length -2.05% (between -2.0% and -2.1%) -> No Match
            ['l' => [-2.05, -2.05, -2.05], 'w' => [-3.50, -3.90, -3.70], 'stage' => 'one'],
            // 48. Gap Example 3: Width -2.95% (between -2.9% and -3.0%) -> No Match
            ['l' => [-3.10, -3.10, -3.10], 'w' => [-2.95, -2.95, -2.95], 'stage' => 'one'],
            // 49. Extreme Contraction Out of Range -> No Match
            ['l' => [-18.50, -18.20, -18.80], 'w' => [-32.00, -31.50, -32.50], 'stage' => 'one'],
            // 50. Extreme Expansion Out of Range -> No Match
            ['l' => [8.50, 9.00, 8.00], 'w' => [35.00, 36.00, 34.00], 'stage' => 'one'],
        ];

        for ($i = 0; $i < 50; $i++) {
            $index = $i + 1;
            $profile = $readingProfiles[$i];

            $rollNumber = sprintf('ROLL-2026-%03d', $index);
            $buyer = $buyers[$i % count($buyers)];
            $style = $styles[$i % count($styles)];
            $color = $colors[$i % count($colors)];
            $lot = sprintf('LOT-%04d', 1000 + $index);
            $quantity = round(450.0 + ($i * 65.5), 2);
            $stage = $profile['stage'];

            $l = $profile['l'];
            $w = $profile['w'];

            $classification = $service->classify(
                (float) $l[0],
                (float) $l[1],
                (float) $l[2],
                (float) $w[0],
                (float) $w[1],
                (float) $w[2],
                $stage
            );

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
                    'l1' => (float) $l[0],
                    'l2' => (float) $l[1],
                    'l3' => (float) $l[2],
                    'w1' => (float) $w[0],
                    'w2' => (float) $w[1],
                    'w3' => (float) $w[2],
                    'avg_length' => $classification['avg_length'],
                    'avg_width' => $classification['avg_width'],
                    'match_status' => $classification['match_status'],
                    'matched_group_id' => $classification['matched_group']?->id,
                    'matching_candidates' => $classification['matching_candidates'],
                ]
            );
        }

        if ($this->command) {
            $this->command->info('Successfully seeded 50 fabric rolls with calculations and classifications.');
        }
    }
}
