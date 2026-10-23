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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('bib_number', 20)->unique()->index();
            $table->unsignedInteger('bib_sequence')->unique()->index();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('national_id', 30)->unique()->index();
            $table->string('national_id_clean', 30)->index();
            $table->string('phone', 30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
