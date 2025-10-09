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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('place_of_birth');
            $table->date('date_of_birth');
            $table->enum('gender', ['laki-laki', 'perempuan']);

            $table->text('address');
            $table->string('religion');

            $table->string('father_name');
            $table->string('father_phone', 20)->nullable();
            $table->string('mother_name');
            $table->string('mother_phone', 20)->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 20)->nullable();

            $table->string('paud')->nullable();

            $table->string('file_kk');
            $table->string('file_akta');
            $table->string('file_foto');

            $table->enum('status', ['pending', 'diterima', 'ditolak'])->default('pending');
            $table->year('year');
            $table->string('admission_code', 20)->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
