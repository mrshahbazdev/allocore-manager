<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_measures', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. implement_access_reviews
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('addresses_challenges')->nullable(); // challenge_keys it targets
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_measures');
    }
};
