<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->enum('payment_type', ['dp', 'final'])->default('dp');
            $table->string('payer_name');
            $table->string('bank_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->dateTime('paid_at')->nullable();
            $table->string('proof_image');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
