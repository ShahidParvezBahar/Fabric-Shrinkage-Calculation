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

        $sampleRolls = [
            // 1. Worked Example (Group 23)
            [
                'roll_number' => 'ROLL-2026-001',
                'buyer' => 'Acme Apparel',
                'style' => 'Classic Denim 501',
                'color' => 'Dark Indigo',
                'lot' => 'LOT-7821',
                'quantity' => 1250.00,
                'stage' => 'one',
                'measurement_mode' => 'direct_percentage',
                'l1' => -3.20,
                'l2' => -3.50,
                'l3' => -3.10,
                'w1' => -3.40,
                'w2' => -3.80,
                'w3' => -3.60,
            ],
            // 2. Group 1 match (0 to -2 length, 0 to -1.4 width)
            [
                'roll_number' => 'ROLL-2026-002',
                'buyer' => 'Zara Inditex',
                'style' => 'Linen Shirt',
                'color' => 'Optic White',
                'lot' => 'LOT-3321',
                'quantity' => 840.50,
                'stage' => 'one',
                'measurement_mode' => 'direct_percentage',
                'l1' => -1.10,
                'l2' => -0.90,
                'l3' => -1.00,
                'w1' => -0.70,
                'w2' => -0.80,
                'w3' => -0.60,
            ],
            // 3. Group 3 match (0 to -2 length, -3 to -4.4 width)
            [
                'roll_number' => 'ROLL-2026-003',
                'buyer' => 'H&M Hennes',
                'style' => 'Chino Trousers',
                'color' => 'Khaki Beige',
                'lot' => 'LOT-9912',
                'quantity' => 2100.00,
                'stage' => 'one',
                'measurement_mode' => 'direct_percentage',
                'l1' => -1.20,
                'l2' => -1.50,
                'l3' => -0.90,
                'w1' => -3.60,
                'w2' => -3.80,
                'w3' => -3.70,
            ],
            // 4. Group 41 match (-4.2 to -6.2 length, 0 to -1.4 width)
            [
                'roll_number' => 'ROLL-2026-004',
                'buyer' => 'Levi Strauss',
                'style' => 'Trucker Jacket',
                'color' => 'Stone Wash',
                'lot' => 'LOT-5510',
                'quantity' => 620.00,
                'stage' => 'one',
                'measurement_mode' => 'direct_percentage',
                'l1' => -5.10,
                'l2' => -5.30,
                'l3' => -5.20,
                'w1' => -0.80,
                'w2' => -1.00,
                'w3' => -0.90,
            ],
            // 5. Gap / No Match Example (Width -1.45% between Group 1 [-1.4%] and Group 2 [-1.5%])
            [
                'roll_number' => 'ROLL-2026-005',
                'buyer' => 'Gap Inc.',
                'style' => 'Oxford Button-down',
                'color' => 'Light Blue',
                'lot' => 'LOT-8811',
                'quantity' => 1100.00,
                'stage' => 'one',
                'measurement_mode' => 'direct_percentage',
                'l1' => -1.00,
                'l2' => -1.00,
                'l3' => -1.00,
                'w1' => -1.45,
                'w2' => -1.45,
                'w3' => -1.45,
            ],
            // 6. Positive expansion readings (Group 141: 0.1 to 2.1 length, 0.1 to 1.4 width)
            [
                'roll_number' => 'ROLL-2026-006',
                'buyer' => 'Uniqlo',
                'style' => 'Ultra Stretch Jersey',
                'color' => 'Charcoal Heather',
                'lot' => 'LOT-4419',
                'quantity' => 1750.00,
                'stage' => 'one',
                'measurement_mode' => 'direct_percentage',
                'l1' => 1.00,
                'l2' => 1.20,
                'l3' => 1.10,
                'w1' => 0.70,
                'w2' => 0.80,
                'w3' => 0.60,
            ],
        ];

        foreach ($sampleRolls as $rollData) {
            $classification = $service->classify(
                (float) $rollData['l1'],
                (float) $rollData['l2'],
                (float) $rollData['l3'],
                (float) $rollData['w1'],
                (float) $rollData['w2'],
                (float) $rollData['w3'],
                (string) $rollData['stage']
            );

            FabricRoll::updateOrCreate(
                ['roll_number' => $rollData['roll_number']],
                [
                    'buyer' => $rollData['buyer'],
                    'style' => $rollData['style'],
                    'color' => $rollData['color'],
                    'lot' => $rollData['lot'],
                    'quantity' => $rollData['quantity'],
                    'stage' => $rollData['stage'],
                    'measurement_mode' => $rollData['measurement_mode'],
                    'l1' => $rollData['l1'],
                    'l2' => $rollData['l2'],
                    'l3' => $rollData['l3'],
                    'w1' => $rollData['w1'],
                    'w2' => $rollData['w2'],
                    'w3' => $rollData['w3'],
                    'avg_length' => $classification['avg_length'],
                    'avg_width' => $classification['avg_width'],
                    'match_status' => $classification['match_status'],
                    'matched_group_id' => $classification['matched_group']?->id,
                    'matching_candidates' => $classification['matching_candidates'],
                ]
            );
        }
    }
}

