<?php

namespace Database\Seeders;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Process;
use App\Models\Signal;
use App\Models\Source;
use App\Services\LearningLoop;
use App\Services\RecommendationEngine;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo ecosystem: two source platforms, a measure catalog, companies in
     * two cohorts, historic signals (so trends/patterns exist), and a few
     * recommendations with measured outcomes feeding the learning loop.
     */
    public function run(): void
    {
        $allocore = Source::create(['key' => 'allocore-de', 'name' => 'Allocore', 'type' => 'saas_platform']);
        $ct = Source::create(['key' => 'compliancetermine-de', 'name' => 'ComplianceTermine', 'type' => 'saas_platform']);

        $measures = collect([
            ['key' => 'quarterly_access_reviews', 'name' => 'Quarterly access reviews', 'addresses_challenges' => ['missing_access_review', 'stale_permissions']],
            ['key' => 'documented_incident_runbook', 'name' => 'Documented incident runbook', 'addresses_challenges' => ['no_incident_process']],
            ['key' => 'automated_backups', 'name' => 'Automated verified backups', 'addresses_challenges' => ['unverified_backups']],
            ['key' => 'compliance_calendar', 'name' => 'Compliance deadline calendar', 'addresses_challenges' => ['missed_deadlines', 'missing_access_review']],
        ])->map(fn ($m) => ActionMeasure::create($m + ['description' => 'Catalog measure.']));

        $companyDefs = [
            [$allocore, 'dentallab-1', 'Dental Labor Müller', 'dental', 'growing', ['compliance_backlog', 'no_it_team']],
            [$allocore, 'dentallab-2', 'Zahntechnik Schmidt', 'dental', 'growing', ['compliance_backlog', 'no_it_team']],
            [$ct, 'handwerk-1', 'Musterhandwerk GmbH', 'crafts', 'early', ['compliance_backlog', 'paper_processes']],
            [$ct, 'handwerk-2', 'Elektro Braun', 'crafts', 'early', ['compliance_backlog', 'paper_processes']],
            [$ct, 'kanzlei-1', 'Kanzlei Weber', 'legal', 'established', ['gdpr_risk', 'no_it_team']],
        ];

        $companies = collect($companyDefs)->map(fn ($d) => Company::create([
            'source_id' => $d[0]->id, 'external_id' => $d[1], 'name' => $d[2],
            'industry' => $d[3], 'maturity' => $d[4], 'situation' => $d[5],
        ]));

        // Historic signals: access-review issue rising recently.
        $challenges = ['missing_access_review', 'missed_deadlines', 'no_incident_process', 'unverified_backups'];
        foreach ($companies as $i => $company) {
            foreach ([55, 40] as $daysAgo) {
                Signal::create([
                    'source_id' => $company->source_id, 'company_id' => $company->id,
                    'type' => 'risk.detected',
                    'challenge_key' => $challenges[$i % count($challenges)],
                    'occurred_at' => now()->subDays($daysAgo),
                ]);
            }
            foreach ([10, 5] as $daysAgo) {
                Signal::create([
                    'source_id' => $company->source_id, 'company_id' => $company->id,
                    'type' => 'risk.detected',
                    'challenge_key' => 'missing_access_review',
                    'occurred_at' => now()->subDays($daysAgo),
                ]);
            }
        }

        // Prior ecosystem learning: comparable companies already ran the loop.
        // recommendFor only proposes measures with measured pattern history,
        // so seed the patterns that history would have produced.
        $loop = app(LearningLoop::class);
        $engine = app(RecommendationEngine::class);
        foreach (['global', 'growing:compliance_backlog+no_it_team', 'early:compliance_backlog+paper_processes'] as $cohort) {
            Pattern::create([
                'cohort' => $cohort,
                'challenge_key' => 'missing_access_review',
                'action_measure_id' => $measures[0]->id,
                'attempts' => 4, 'successes' => 3, 'failures' => 1,
                'failure_reasons' => ['no_budget'],
            ]);
            Pattern::create([
                'cohort' => $cohort,
                'challenge_key' => 'unverified_backups',
                'action_measure_id' => $measures[2]->id,
                'attempts' => 2, 'successes' => 2, 'failures' => 0,
            ]);
        }

        // Fresh recommendations for the current companies, then some run the
        // loop to completion so decision-path maturity has measured evidence.
        foreach ($companies as $company) {
            $engine->refreshFor($company);
        }
        foreach ($companies->take(4) as $i => $company) {
            $rec = $company->recommendations()->whereIn('status', ['pending', 'accepted'])->first();
            if ($rec) {
                $rec->update(['status' => 'implemented']);
                $loop->recordOutcome($rec, $i === 3 ? 'failure' : 'success', $i === 3 ? 'no_budget' : null);
            }
        }

        Process::create(['name' => 'Signal triage', 'scope' => 'ecosystem', 'automation_stage' => 'assisted', 'description' => 'Classify incoming signals to challenge keys.']);
        Process::create(['name' => 'Recommendation generation', 'scope' => 'ecosystem', 'automation_stage' => 'semi_automated', 'description' => 'Engine proposes best next action per challenge.']);
        Process::create(['name' => 'Outcome collection', 'scope' => 'platform', 'automation_stage' => 'manual', 'description' => 'Managers report whether a measure worked.']);
    }
}
