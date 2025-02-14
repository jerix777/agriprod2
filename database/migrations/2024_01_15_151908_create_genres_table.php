<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 10);
            $table->string('status', 20)->default('activer');
            $table->timestamps();
        });

        // Insérer des données après la création de la table
        DB::table('genres')->insert([
            ['libelle' => 'Masculin', 'status' => 'activer'],
            ['libelle' => 'Feminin', 'status' => 'activer'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genres');
    }
};
