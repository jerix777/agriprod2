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
        Schema::create('producteurs', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 20)->unique();
            $table->string('nom', 255);
            $table->string('prenoms', 255);
            $table->foreignId('genre_id')->constrained('genres')->onDelete('cascade');
            $table->date('date_de_naissance')->nullable();
            $table->string('contact', 10)->nullable()->unique();
            $table->longText('lieu_de_residence')->nullable();
            $table->string('status', 20)->default('activer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producteurs');
    }
};
