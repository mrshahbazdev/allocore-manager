<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->string('external_id'); // id on the source platform
            $table->string('name')->nullable();
            $table->string('industry')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('maturity')->nullable(); // e.g. early | growing | established
            $table->json('situation')->nullable(); // challenge/context tags
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['source_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
