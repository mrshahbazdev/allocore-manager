<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('token')->unique();
            $table->timestamps();
        });

        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->index();
            $table->string('company_key')->nullable()->index();
            $table->string('user_email')->nullable();
            $table->json('payload');
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });

        Schema::create('patterns', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('challenge');
            $table->json('evidence')->nullable();
            $table->unsignedInteger('companies_count')->default(0);
            $table->timestamps();
        });

        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company_key')->nullable()->index();
            $table->foreignId('pattern_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->index();
            $table->string('title');
            $table->text('description');
            $table->json('evidence')->nullable();
            $table->string('severity')->default('info'); // info|warning|critical
            $table->string('status')->default('open');   // open|accepted|done|dismissed
            $table->timestamps();
            $table->index(['company_key', 'code', 'status']);
        });

        Schema::create('outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recommendation_id')->constrained()->cascadeOnDelete();
            $table->string('result'); // success|failed|unknown|dismissed
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('member')->after('password'); // member|platform_manager|allocore|disavo
            $table->string('company_key')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'company_key']);
        });
        Schema::dropIfExists('outcomes');
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('patterns');
        Schema::dropIfExists('signals');
        Schema::dropIfExists('sources');
    }
};
