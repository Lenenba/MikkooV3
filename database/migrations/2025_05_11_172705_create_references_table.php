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
        // Changement ici: 'reference_numeros' devient 'reference'
        Schema::create('reference', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->unsignedBigInteger('referenceable_id');
            $table->string('referenceable_type');

            $table->string('prefixe', 10);
            $table->unsignedInteger('numero_sequence');
            $table->string('chaine_reference');

            $table->timestamps();

            $table->index(['referenceable_id', 'referenceable_type']);
            $table->index('prefixe');
            $table->index('chaine_reference');

            $table->unique(['referenceable_id', 'referenceable_type'], 'referenceable_unique_idx');
            $table->unique(['user_id', 'prefixe', 'numero_sequence'], 'user_prefixe_sequence_unique_idx');
            $table->unique(['user_id', 'chaine_reference'], 'user_chaine_reference_unique_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference');
    }
};
