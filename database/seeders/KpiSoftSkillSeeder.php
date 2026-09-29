<?php

namespace Database\Seeders;

use App\Models\KpiSoftSkill;
use Illuminate\Database\Seeder;

class KpiSoftSkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            'Commitment',
            'Discipline',
            'Loyal',
            'Trustworthy',
            'Reliable',
            'Competence',
            'Effective',
            'Communication',
            'Sense of Belonging',
            'Work with Interest',
            'Proactive',
        ];

        foreach ($skills as $index => $name) {
            KpiSoftSkill::firstOrCreate(
                ['kode' => 'SS' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'nama_indikator' => $name,
                    'deskripsi' => null,
                ]
            );
        }

        $this->command->info('Soft Skill seed completed: ' . count($skills) . ' records checked.');
    }
}
