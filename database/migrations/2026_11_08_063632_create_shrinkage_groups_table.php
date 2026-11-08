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
        Schema::create('shrinkage_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_number')->index();
            $table->decimal('min_length_percentage', 8, 4);
            $table->decimal('adjust_length_percentage', 8, 4);
            $table->decimal('max_length_percentage', 8, 4);
            $table->decimal('min_width_percentage', 8, 4);
            $table->decimal('adjust_width_percentage', 8, 4);
            $table->decimal('max_width_percentage', 8, 4);
            $table->string('stage')->default('one')->index();
            $table->timestamps();

            $table->index(['stage', 'group_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shrinkage_groups');
    }
};
