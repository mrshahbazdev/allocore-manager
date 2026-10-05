<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('action_measure_id')->constrained()->cascadeOnDelete();
            $table->string('challenge_key')->index();
            $table->decimal('confidence', 5, 2)->nullable(); // % success rate in cohort
            $table->json('rationale')->nullable(); // adoption %, success %, sample sizes
            $table->string('status')->default('pending')->index(); // pending | accepted | implemented | dismissed
            $table->foreignId('signal_id')->nullable()->constrained()->nullOnDelete(); // triggering signal
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
