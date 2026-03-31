<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('booking_payments', 'note')) {
            Schema::table('booking_payments', function (Blueprint $table) {
                $table->dropColumn('note');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('booking_payments', 'note')) {
            Schema::table('booking_payments', function (Blueprint $table) {
                $table->text('note')->nullable()->after('status');
            });
        }
    }
};
