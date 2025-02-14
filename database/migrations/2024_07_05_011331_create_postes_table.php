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
        Schema::create('postes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers');
            $table->foreignId('departement_id')->constrained('departements');
            $table->foreignId('role_id')->constrained('roles');
            $table->string('nom_reseau', 255);
            $table->string('numero_de_serie', 255)->unique();
            $table->string('description', 255)->nullable();
            // $table->string('proprietaire', 255)->nullable();
            $table->string('status', 20)->default('activer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postes');
    }
};
