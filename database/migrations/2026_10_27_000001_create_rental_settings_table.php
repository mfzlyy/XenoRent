<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('max_active_rentals')->default(2);
            $table->timestamps();
        });

        DB::table('rental_settings')->insert([
            'id' => 1,
            'max_active_rentals' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_settings');
    }
};
