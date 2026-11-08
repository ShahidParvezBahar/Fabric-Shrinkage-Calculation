<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ShrinkageGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ImportShrinkageGroupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-shrinkage-groups {file? : Optional path to CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import shrinkage group chart definitions from CSV file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file') ?? database_path('data/shrinkage_groups.csv');

        if (!File::exists($filePath)) {
            $this->error("CSV file not found at: {$filePath}");
            return self::FAILURE;
        }

        $file = fopen($filePath, 'r');
        if ($file === false) {
            $this->error("Failed to open file: {$filePath}");
            return self::FAILURE;
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

        $this->info("Import complete. Inserted {$count} shrinkage groups.");
        return self::SUCCESS;
    }
}
