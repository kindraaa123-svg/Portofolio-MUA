<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operational_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->unique();
            $table->string('day_name', 20);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        $now = now();
        $days = [
            ['day_of_week' => 1, 'day_name' => 'Senin'],
            ['day_of_week' => 2, 'day_name' => 'Selasa'],
            ['day_of_week' => 3, 'day_name' => 'Rabu'],
            ['day_of_week' => 4, 'day_name' => 'Kamis'],
            ['day_of_week' => 5, 'day_name' => 'Jumat'],
            ['day_of_week' => 6, 'day_name' => 'Sabtu'],
            ['day_of_week' => 0, 'day_name' => 'Minggu'],
        ];

        foreach ($days as $day) {
            DB::table('operational_hours')->insert([
                ...$day,
                'open_time' => '09:00:00',
                'close_time' => '17:00:00',
                'is_closed' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_hours');
    }
};
