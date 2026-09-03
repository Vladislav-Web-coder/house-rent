<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_by_day_of_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0 = воскресенье, 1 = понедельник, ..., 6 = суббота
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['property_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_by_day_of_weeks');
    }
};
