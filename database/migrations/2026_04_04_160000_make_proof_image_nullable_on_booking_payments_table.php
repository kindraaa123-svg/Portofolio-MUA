<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE booking_payments MODIFY proof_image VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE booking_payments SET proof_image = '' WHERE proof_image IS NULL");
        DB::statement('ALTER TABLE booking_payments MODIFY proof_image VARCHAR(255) NOT NULL');
    }
};

