<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('user_name')->nullable()->after('user_id');
            $table->string('user_level')->nullable()->after('user_name');
            $table->string('geo_location')->nullable()->after('ip_address');
            $table->decimal('latitude', 10, 7)->nullable()->after('geo_location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['user_name', 'user_level', 'geo_location', 'latitude', 'longitude']);
        });
    }
};
