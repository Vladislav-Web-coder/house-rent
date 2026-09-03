<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // 'avito', 'sutochno', 'ostrovok', 'cian', 'yandex'
            $table->string('url'); // Ссылка на .ics файл
            $table->string('etag')->nullable(); // Для оптимизации (не скачивать файл, если он не менялся)
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'platform']); // Один объект - одна ссылка на площадку
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_links');
    }
};
