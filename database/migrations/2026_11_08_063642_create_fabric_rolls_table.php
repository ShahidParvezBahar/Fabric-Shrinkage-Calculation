<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fabric_rolls', function (Blueprint $table) {
            $table->id();
            $table->string('roll_number')->unique();
            $table->string('buyer');
            $table->string('style');
            $table->string('color');
            $table->string('lot');
            $table->decimal('quantity', 12, 2);
            $table->string('stage')->default('one')->index();
            $table->string('measurement_mode')->default('direct_percentage');
            $table->decimal('l1', 8, 4);
            $table->decimal('l2', 8, 4);
            $table->decimal('l3', 8, 4);
            $table->decimal('w1', 8, 4);
            $table->decimal('w2', 8, 4);
            $table->decimal('w3', 8, 4);
            $table->json('raw_measurements')->nullable();
            $table->decimal('avg_length', 8, 4);
            $table->decimal('avg_width', 8, 4);
            $table->string('match_status')->default('no_match')->index(); // matched, no_match, ambiguous
            $table->foreignId('matched_group_id')->nullable()->constrained('shrinkage_groups')->nullOnDelete();
            $table->json('matching_candidates')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fabric_rolls');
    }
};
