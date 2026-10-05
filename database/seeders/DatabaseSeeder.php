<?php

namespace Database\Seeders;

use App\Models\Signal;
use App\Models\Source;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Muhammad', 'email' => 'demo@allocore.de', 'password' => 'demo1234', 'role' => 'member', 'company_key' => 'developer']);
        User::create(['name' => 'Platform Manager', 'email' => 'manager@allocore.de', 'password' => 'demo1234', 'role' => 'platform_manager']);
        User::create(['name' => 'Allocore Team', 'email' => 'team@allocore.de', 'password' => 'demo1234', 'role' => 'allocore']);
        User::create(['name' => 'DISAVO', 'email' => 'disavo@allocore.de', 'password' => 'demo1234', 'role' => 'disavo']);

        Source::firstOrCreate(
            ['name' => 'allocore.de'],
            ['token' => env('ALLOCORE_INGEST_TOKEN') ?: 'allocore-'.Str::random(24)]
        );

        $source = Source::create(['name' => 'Demo suite', 'token' => 'demo-token-'.Str::random(8)]);

        $demo = [
            ['invoice.overdue', 'developer', 5],
            ['invoice.overdue', 'otherco', 3],
            ['order.overdue', 'developer', 2],
            ['machine.maintenance', 'developer', 1],
            ['deadline.due', 'otherco', 4],
            ['report.missing', 'developer', 1],
        ];
        foreach ($demo as [$type, $company, $daysAgo]) {
            Signal::create([
                'source_id' => $source->id,
                'type' => $type,
                'company_key' => $company,
                'occurred_at' => now()->subDays($daysAgo),
                'payload' => ['type' => $type, 'demo' => true],
            ]);
        }
    }
}
