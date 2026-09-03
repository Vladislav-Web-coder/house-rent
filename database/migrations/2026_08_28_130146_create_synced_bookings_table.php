<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('synced_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('calendar_link_id')->constrained()->cascadeOnDelete();
            $table->string('external_uid')->nullable(); // UID из .ics файла
            $table->date('check_in');
            $table->date('check_out');
            $table->string('summary')->nullable(); // Название события из .ics (например, "Бронь Авито")
            $table->timestamps();

            // Индекс для быстрого поиска пересечений
            $table->index(['property_id', 'check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('synced_bookings');
    }
};
