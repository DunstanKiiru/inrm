<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('Testing@2025'),
                'email_verified_at' => now(),
            ]
        );
        


     $this->call(\Database\Seeders\AssessmentKRISeed::class);
        $this->call(\Database\Seeders\AuditDemoSeed::class);
        $this->call(\Database\Seeders\BcmSeeder::class);
        $this->call(\Database\Seeders\ComplianceSeed::class);
        $this->call(\Database\Seeders\ControlLibrarySeed::class);
        $this->call(\Database\Seeders\ControlTestingDemoSeed::class);
        $this->call(\Database\Seeders\DashboardsDemoSeed::class);
        $this->call(\Database\Seeders\DemoRisksSeed::class);
        $this->call(\Database\Seeders\RiskAppetiteSeed::class);
        $this->call(\Database\Seeders\RiskTaxonomySeed::class);

    }
}
