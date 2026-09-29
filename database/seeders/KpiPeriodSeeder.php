<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KpiPeriod;
use App\Models\KpiAssessment;
use App\Models\User;

class KpiPeriodSeeder extends Seeder
{
    /**
     * Seed periode KPI contoh
     */
    public function run(): void
    {
        // Buat periode aktif untuk testing
        $activePeriod = KpiPeriod::firstOrCreate(
            ['nama' => 'September 2024'],
            [
                'tanggal_mulai' => '2024-09-01',
                'tanggal_selesai' => '2024-09-30',
                'status' => 'aktif',
            ]
        );

        // Buat assessments untuk semua user aktif
        $users = User::where('status', true)->get();
        foreach ($users as $user) {
            KpiAssessment::firstOrCreate(
                [
                    'user_id' => $user->id_user,
                    'kpi_period_id' => $activePeriod->id,
                ],
                [
                    'atasan_id' => $user->atasan_id,
                    'status' => 'pending',
                ]
            );
        }

        // Buat juga periode selesai untuk histori
        $finishedPeriod = KpiPeriod::firstOrCreate(
            ['nama' => 'Agustus 2024'],
            [
                'tanggal_mulai' => '2024-08-01',
                'tanggal_selesai' => '2024-08-31',
                'status' => 'selesai',
            ]
        );

        $this->command->info('KPI Periods seeded successfully!');
    }
}
