<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KpiSoftSkill;
use App\Models\KpiHardSkill;

class KpiSkillSeeder extends Seeder
{
    /**
     * Seed data indikator KPI awal
     * Dijalankan dengan: php artisan db:seed --class=KpiSkillSeeder
     */
    public function run(): void
    {
        // Soft Skills - Indikator karakter dan sikap
        $softSkills = [
            ['nama_indikator' => 'Komunikasi', 'deskripsi' => 'Kemampuan menyampaikan informasi secara jelas dan efektif'],
            ['nama_indikator' => 'Kerja Tim', 'deskripsi' => 'Kemampuan bekerja sama dengan tim dan berkontribusi'],
            ['nama_indikator' => 'Disiplin', 'deskripsi' => 'Ketepatan waktu dan kepatuhan terhadap aturan'],
            ['nama_indikator' => 'Tanggung Jawab', 'deskripsi' => 'Kesungguhan dalam menyelesaikan tugas'],
            ['nama_indikator' => 'Adaptasi', 'deskripsi' => 'Kemampuan menyesuaikan diri dengan perubahan'],
        ];

        foreach ($softSkills as $skill) {
            KpiSoftSkill::firstOrCreate(
                ['nama_indikator' => $skill['nama_indikator']],
                $skill
            );
        }

        // Hard Skills - Indikator kemampuan teknis
        $hardSkills = [
            ['nama_indikator' => 'Penguasaan Teknis', 'deskripsi' => 'Kemampuan teknis sesuai bidang pekerjaan'],
            ['nama_indikator' => 'Problem Solving', 'deskripsi' => 'Kemampuan mengidentifikasi dan menyelesaikan masalah'],
            ['nama_indikator' => 'Analisis Data', 'deskripsi' => 'Kemampuan menganalisis dan menginterpretasi data'],
            ['nama_indikator' => 'Manajemen Waktu', 'deskripsi' => 'Efektivitas dalam mengelola waktu kerja'],
            ['nama_indikator' => 'Penguasaan Tools', 'deskripsi' => 'Kemampuan menggunakan software dan tools yang diperlukan'],
        ];

        foreach ($hardSkills as $skill) {
            KpiHardSkill::firstOrCreate(
                ['nama_indikator' => $skill['nama_indikator']],
                $skill
            );
        }

        $this->command->info('KPI Skills seeded successfully!');
    }
}
