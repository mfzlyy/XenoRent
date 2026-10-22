<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->date('rented_at');
            $table->date('due_at');
            $table->date('returned_at')->nullable();
            $table->unsignedInteger('fine_amount')->default(0);
            $table->enum('status', ['disewa', 'dikembalikan'])->default('disewa');
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
