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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nama guru
            $table->string('major'); // jurusan guru
            $table->string('phone')->unique(); // nomor guru
            $table->enum('status', ['aktif', 'tidak_aktif', 'cuti'])->default('aktif');
            $table->string('position'); // jabatan guru (Kepala Sekolah, Guru Kelas, dll)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
