<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_requests', 'country_code')) {
                $table->string('country_code', 10)->nullable()->after('guest_phone');
            }
            if (!Schema::hasColumn('booking_requests', 'contact_method')) {
                $table->string('contact_method', 20)->nullable()->after('country_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'contact_method']);
        });
    }
};
