<?php

use Carbon\Carbon;
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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('campagne_id')->constrained('campagnes')->onDelete('cascade');
            $table->foreignId('culture_id')->constrained('cultures')->onDelete('cascade');
            $table->foreignId('parcelle_id')->constrained('parcelles')->onDelete('cascade');
            $table->date('date_de_production')->default(Carbon::now());
            $table->integer('quantite')->default(0);
            $table->string('qualite', 20)->nullable();
            $table->string('status', 20)->default('activer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
