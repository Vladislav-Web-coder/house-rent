<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('date'); // Конкретная дата (праздник, особый день)
            $table->decimal('price', 10, 2);
            $table->string('reason')->nullable(); // Например, "Новый Год", "День города"
            $table->timestamps();

            // Уникальный индекс: одна дата - одна цена для объекта
            $table->unique(['property_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_exceptions');
    }
};
