<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'area')) {
                $table->decimal('area', 8, 2)->nullable()->after('category');
            }
            if (!Schema::hasColumn('properties', 'rooms')) {
                $table->integer('rooms')->nullable()->after('area');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['area', 'rooms']);
        });
    }
};
