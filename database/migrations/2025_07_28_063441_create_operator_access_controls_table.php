<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operator_access_controls', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel 'users'
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Menghubungkan ke 'person' yang menjadi akar silsilah bagi operator
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            // Batas generasi ke atas (leluhur) yang bisa diakses
            $table->unsignedTinyInteger('generations_up')->default(0);
            // Batas generasi ke bawah (keturunan) yang bisa diakses
            $table->unsignedTinyInteger('generations_down')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_access_controls');
    }
};