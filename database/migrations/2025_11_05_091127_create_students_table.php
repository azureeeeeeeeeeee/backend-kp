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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('nisn')->unique();
            $table->string('full_name');
            $table->string('place_of_birth');
            $table->date('date_of_birth');
            $table->enum('gender', ['laki-laki', 'perempuan']);
            $table->text('address');
            $table->string('religion');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('file_foto');
            $table->year('year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
