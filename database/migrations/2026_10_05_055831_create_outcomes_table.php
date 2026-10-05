<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recommendation_id')->constrained()->cascadeOnDelete();
            $table->string('result'); // success | failure | partial
            $table->string('failure_reason')->nullable();
            $table->json('metrics')->nullable(); // before/after measurements
            $table->timestamp('measured_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outcomes');
    }
};
