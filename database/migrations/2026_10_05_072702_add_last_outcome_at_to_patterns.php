<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->timestamp('last_outcome_at')->nullable()->after('failure_reasons');
        });
    }

    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->dropColumn('last_outcome_at');
        });
    }
};
