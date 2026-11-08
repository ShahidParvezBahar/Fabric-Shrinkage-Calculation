<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FabricRoll;
use App\Models\ShrinkageGroup;
use Database\Seeders\ShrinkageGroupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FabricShrinkageCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShrinkageGroupSeeder::class);
    }

    /**
     * Test the exact worked example from Section 6 of the assessment:
     * Length readings: -3.20, -3.50, -3.10 => Avg: -3.2666... (display: -3.27)
     * Width readings: -3.40, -3.80, -3.60 => Avg: -3.60
     * Expected match: Group 23, stage "one".
     * Adjustments: Length: -3.10, Width: -3.70.
     */
    public function test_worked_example_matches_group_23(): void
    {
        $response = $this->postJson('/api/fabric-rolls', [
            'roll_number' => 'ROLL-TEST-001',
            'buyer' => 'Acme Corp',
            'style' => 'Denim Jacket',
            'color' => 'Indigo Blue',
            'lot' => 'LOT-2026-X',
            'quantity' => 1500.50,
            'stage' => 'one',
            'measurement_mode' => 'direct_percentage',
            'l1' => -3.20,
            'l2' => -3.50,
            'l3' => -3.10,
            'w1' => -3.40,
            'w2' => -3.80,
            'w3' => -3.60,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.match_status', 'matched');
        $response->assertJsonPath('data.matched_group.group_number', '23');
        $response->assertJsonPath('data.matched_group.adjust_length_percentage', -3.1);
        $response->assertJsonPath('data.matched_group.adjust_width_percentage', -3.7);

        // Verify precision
        $data = $response->json('data');
        $this->assertEquals(-3.27, round($data['avg_length'], 2));
        $this->assertEquals(-3.60, round($data['avg_width'], 2));

        $this->assertDatabaseHas('fabric_rolls', [
            'roll_number' => 'ROLL-TEST-001',
            'match_status' => 'matched',
        ]);
    }

    /**
     * Test preview endpoint without storing to DB.
     */
    public function test_preview_match_endpoint(): void
    {
        $response = $this->postJson('/api/shrinkage-groups/preview-match', [
            'stage' => 'one',
            'l1' => -3.20,
            'l2' => -3.50,
            'l3' => -3.10,
            'w1' => -3.40,
            'w2' => -3.80,
            'w3' => -3.60,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('match_status', 'matched');
        $response->assertJsonPath('matched_group.group_number', '23');
        $response->assertJsonPath('avg_length_display', -3.27);
        $response->assertJsonPath('avg_width_display', -3.60);
    }

    /**
     * Test Range Gap (e.g. Width -1.45% between Group 1 [-1.4%] and Group 2 [-1.5%]) results in "no_match".
     */
    public function test_range_gap_results_in_no_match(): void
    {
        // Length within Group 1 (0 to -2): -1.0
        // Width -1.45% (gap between -1.40% and -1.50%)
        $response = $this->postJson('/api/fabric-rolls', [
            'roll_number' => 'ROLL-GAP-001',
            'buyer' => 'GAP Buyer',
            'style' => 'T-Shirt',
            'color' => 'White',
            'lot' => 'LOT-GAP-1',
            'quantity' => 500,
            'stage' => 'one',
            'l1' => -1.0,
            'l2' => -1.0,
            'l3' => -1.0,
            'w1' => -1.45,
            'w2' => -1.45,
            'w3' => -1.45,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.match_status', 'no_match');
        $response->assertJsonPath('data.matched_group_id', null);
    }

    /**
     * Test rejection of non-numeric or missing inputs.
     */
    public function test_validation_rejects_missing_or_invalid_inputs(): void
    {
        $response = $this->postJson('/api/fabric-rolls', [
            'roll_number' => 'ROLL-FAIL',
            'l1' => 'not-a-number',
            // missing buyer, style, etc.
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['buyer', 'style', 'color', 'lot', 'quantity', 'l1', 'l2', 'l3', 'w1', 'w2', 'w3']);
    }

    /**
     * Test ambiguous multiple matching groups when boundaries overlap.
     */
    public function test_multiple_matching_records_returns_ambiguous(): void
    {
        // Create an overlapping dummy group
        ShrinkageGroup::create([
            'group_number' => '23-DUPLICATE',
            'min_length_percentage' => -2.1,
            'adjust_length_percentage' => -3.1,
            'max_length_percentage' => -4.1,
            'min_width_percentage' => -3.0,
            'adjust_width_percentage' => -3.7,
            'max_width_percentage' => -4.4,
            'stage' => 'one',
        ]);

        $response = $this->postJson('/api/shrinkage-groups/preview-match', [
            'stage' => 'one',
            'l1' => -3.20,
            'l2' => -3.50,
            'l3' => -3.10,
            'w1' => -3.40,
            'w2' => -3.80,
            'w3' => -3.60,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('match_status', 'ambiguous');
        $response->assertJsonPath('matched_group', null);
        $this->assertCount(2, $response->json('matching_candidates'));
    }

    /**
     * Test roll statistics endpoint.
     */
    public function test_roll_statistics_endpoint(): void
    {
        $this->postJson('/api/fabric-rolls', [
            'roll_number' => 'ROLL-STAT-1',
            'buyer' => 'Zara',
            'style' => 'Polo',
            'color' => 'Navy',
            'lot' => 'LOT-Z',
            'quantity' => 1000,
            'stage' => 'one',
            'l1' => -3.20,
            'l2' => -3.50,
            'l3' => -3.10,
            'w1' => -3.40,
            'w2' => -3.80,
            'w3' => -3.60,
        ]);

        $response = $this->getJson('/api/fabric-rolls/stats');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_rolls',
            'matched_count',
            'no_match_count',
            'ambiguous_count',
            'overall_avg_length',
            'overall_avg_width',
            'total_quantity',
            'chart' => [
                'labels',
                'length_averages',
                'width_averages',
                'group_distribution',
            ],
        ]);
    }

    /**
     * Test bulk CSV import for multiple rolls at once.
     */
    public function test_bulk_csv_import_for_multiple_rolls(): void
    {
        $csvHeader = "roll_number,buyer,style,color,lot,quantity,stage,l1,l2,l3,w1,w2,w3\n";
        $csvRow1 = "ROLL-BULK-01,Acme,Denim,Blue,LOT-A,1000,one,-3.20,-3.50,-3.10,-3.40,-3.80,-3.60\n";
        $csvRow2 = "ROLL-BULK-02,Zara,Linen,White,LOT-B,500,one,-1.10,-0.90,-1.00,-0.70,-0.80,-0.60\n";
        $csvContent = $csvHeader . $csvRow1 . $csvRow2;

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('rolls.csv', $csvContent);

        $response = $this->postJson('/api/fabric-rolls/import-csv', [
            'csv_file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('imported_count', 2);
        $response->assertJsonPath('total_processed', 2);

        $this->assertDatabaseHas('fabric_rolls', [
            'roll_number' => 'ROLL-BULK-01',
            'match_status' => 'matched',
        ]);

        $this->assertDatabaseHas('fabric_rolls', [
            'roll_number' => 'ROLL-BULK-02',
            'match_status' => 'matched',
        ]);
    }

    /**
     * Test updating readings recalculates averages and updates group classification.
     */
    public function test_updating_roll_readings_recalculates_averages_and_reclassifies_group(): void
    {
        // 1. Create roll initially matching Group 23
        $roll = FabricRoll::create([
            'roll_number' => 'ROLL-UPDATE-TEST',
            'buyer' => 'Test Buyer',
            'style' => 'Test Style',
            'color' => 'Navy',
            'lot' => 'LOT-01',
            'quantity' => 100,
            'stage' => 'one',
            'measurement_mode' => 'direct_percentage',
            'l1' => -3.20,
            'l2' => -3.50,
            'l3' => -3.10,
            'w1' => -3.40,
            'w2' => -3.80,
            'w3' => -3.60,
            'avg_length' => -3.266667,
            'avg_width' => -3.600000,
            'match_status' => 'matched',
            'matched_group_id' => ShrinkageGroup::where('group_number', '23')->value('id'),
        ]);

        // 2. Update readings to match Group 1 (L: -1.00%, W: -0.70%)
        $response = $this->putJson("/api/fabric-rolls/{$roll->id}", [
            'l1' => -1.10,
            'l2' => -0.90,
            'l3' => -1.00,
            'w1' => -0.70,
            'w2' => -0.80,
            'w3' => -0.60,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.match_status', 'matched');
        $response->assertJsonPath('data.matched_group.group_number', '1');

        $data = $response->json('data');
        $this->assertEquals(-1.00, round($data['avg_length'], 2));
        $this->assertEquals(-0.70, round($data['avg_width'], 2));

        $this->assertDatabaseHas('fabric_rolls', [
            'id' => $roll->id,
            'l1' => -1.10,
            'match_status' => 'matched',
        ]);
    }

    /**
     * Test sample CSV download endpoint.
     */
    public function test_sample_csv_download_endpoint(): void
    {
        $response = $this->get('/api/fabric-rolls/sample-csv');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('roll_number,buyer,style,color,lot,quantity,stage,l1,l2,l3,w1,w2,w3', $response->streamedContent());
    }
}

