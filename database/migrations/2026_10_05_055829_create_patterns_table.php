<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patterns', function (Blueprint $table) {
            $table->id();
            $table->string('challenge_key')->index();
            $table->foreignId('action_measure_id')->constrained()->cascadeOnDelete();
            $table->string('cohort')->default('global'); // similarity cohort key
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('successes')->default(0);
            $table->unsignedInteger('failures')->default(0);
            $table->json('failure_reasons')->nullable(); // reason => count
            $table->timestamps();
            $table->unique(['challenge_key', 'action_measure_id', 'cohort'], 'patterns_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patterns');
    }
};
