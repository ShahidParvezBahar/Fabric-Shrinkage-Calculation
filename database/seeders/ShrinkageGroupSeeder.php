<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShrinkageGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ShrinkageGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('data/shrinkage_groups.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        $file = fopen($csvPath, 'r');
        if ($file === false) {
            $this->command->error("Unable to open CSV file.");
            return;
        }

        $header = fgetcsv($file); // skip header: group,min_length_percentage,adjust_length_percentage,max_length_percentage,min_width_percentage,adjust_width_percentage,max_width_percentage,stage

        $records = [];
        $batchSize = 100;

        Schema::disableForeignKeyConstraints();
        ShrinkageGroup::query()->delete();
        Schema::enableForeignKeyConstraints();

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 8) {
                continue;
            }

            $records[] = [
                'group_number' => trim((string)$row[0]),
                'min_length_percentage' => (float)$row[1],
                'adjust_length_percentage' => (float)$row[2],
                'max_length_percentage' => (float)$row[3],
                'min_width_percentage' => (float)$row[4],
                'adjust_width_percentage' => (float)$row[5],
                'max_width_percentage' => (float)$row[6],
                'stage' => trim((string)$row[7]),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($records) >= $batchSize) {
                ShrinkageGroup::insert($records);
                $records = [];
            }
        }

        if (!empty($records)) {
            ShrinkageGroup::insert($records);
        }

        fclose($file);

        $total = ShrinkageGroup::count();
        if ($this->command) {
            $this->command->info("Successfully imported {$total} shrinkage groups.");
        }
    }
}

