<?php

namespace Database\Seeders;

use App\Models\KpiSoftSkill;
use App\Models\KpiHardSkill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenerateKpiSkillKodeSeeder extends Seeder
{
    /**
     * Generate kode untuk data KPI Soft/Hard Skills yang sudah ada
     * Run: php artisan db:seed --class=GenerateKpiSkillKodeSeeder
     */
    public function run(): void
    {
        $this->command->info('Generating kode for existing KPI skills...');

        // Soft Skills
        $softSkills = KpiSoftSkill::whereNull('kode')->orderBy('id')->get();
        foreach ($softSkills as $index => $skill) {
            $kode = 'SS' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $skill->update(['kode' => $kode]);
            $this->command->info("Soft Skill: {$skill->nama_indikator} -> {$kode}");
        }

        // Hard Skills
        $hardSkills = KpiHardSkill::whereNull('kode')->orderBy('id')->get();
        foreach ($hardSkills as $index => $skill) {
            $kode = 'HS' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $skill->update(['kode' => $kode]);
            $this->command->info("Hard Skill: {$skill->nama_indikator} -> {$kode}");
        }

        $this->command->info('Done! Soft: ' . $softSkills->count() . ', Hard: ' . $hardSkills->count());
    }
}
