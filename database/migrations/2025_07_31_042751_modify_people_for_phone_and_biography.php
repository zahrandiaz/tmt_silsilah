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
        Schema::table('people', function (Blueprint $table) {
            // Tambahkan kolom nomor hp setelah kolom gender
            $table->string('phone_number')->nullable()->after('gender');
            
            // Ubah tipe kolom biografi menjadi TEXT
            $table->text('biography')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn('phone_number');
            $table->string('biography')->nullable()->change(); // Kembalikan ke tipe string jika di-rollback
        });
    }
};