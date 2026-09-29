<?php

namespace Database\Seeders;

use App\Models\KpiHardSkill;
use App\Models\User;
use Illuminate\Database\Seeder;

class KpiHardSkillSeeder extends Seeder
{
    public function run(): void
    {
        $positions = User::query()
            ->whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->select('divisi', 'jabatan')
            ->distinct()
            ->orderBy('divisi')
            ->orderBy('jabatan')
            ->get();

        $codeNumber = 1;

        foreach ($positions as $position) {
            $jobTitle = strtolower($position->jabatan);
            $indicators = str_contains($jobTitle, 'program')
                ? [
                    [
                        'kpi' => 'Ketepatan waktu penyelesaian task',
                        'responsibilities' => 'Menyelesaikan task pemrograman sesuai estimasi dan prioritas.',
                        'target' => '100% task selesai sesuai jadwal',
                    ],
                    [
                        'kpi' => 'Kualitas kode dan bug rate',
                        'responsibilities' => 'Menulis kode yang teruji, mudah dipelihara, dan meminimalkan bug.',
                        'target' => 'Maksimal 2 bug kritis per rilis',
                    ],
                    [
                        'kpi' => 'Dokumentasi teknis',
                        'responsibilities' => 'Membuat dan memperbarui dokumentasi teknis untuk perubahan aplikasi.',
                        'target' => '100% perubahan terdokumentasi',
                    ],
                ]
                : [
                    [
                        'kpi' => 'Penyelesaian pekerjaan sesuai jadwal',
                        'responsibilities' => 'Menyelesaikan pekerjaan utama sesuai target dan prioritas jabatan.',
                        'target' => '100% pekerjaan selesai sesuai jadwal',
                    ],
                    [
                        'kpi' => 'Kualitas hasil pekerjaan',
                        'responsibilities' => 'Menjaga hasil pekerjaan sesuai standar dan meminimalkan kesalahan.',
                        'target' => 'Maksimal 2 kesalahan per periode',
                    ],
                    [
                        'kpi' => 'Pelaporan pekerjaan',
                        'responsibilities' => 'Menyampaikan laporan pekerjaan secara lengkap dan tepat waktu.',
                        'target' => '100% laporan tepat waktu',
                    ],
                ];

            foreach ($indicators as $indicator) {
                $kode = 'HS' . str_pad((string) $codeNumber++, 3, '0', STR_PAD_LEFT);

                KpiHardSkill::firstOrCreate(
                    ['kode' => $kode],
                    [
                        'nama_indikator' => $indicator['kpi'],
                        'divisi' => $position->divisi,
                        'jabatan' => $position->jabatan,
                        'responsibilities' => $indicator['responsibilities'],
                        'kpi' => $indicator['kpi'],
                        'target' => $indicator['target'],
                        'weight' => count($indicators) === 3 && $indicator['kpi'] === $indicators[0]['kpi'] ? 34 : 33,
                    ]
                );
            }

            $this->command->info("Hard Skill checked for {$position->divisi}/{$position->jabatan}: total weight 100%.");
        }

        if ($positions->isEmpty()) {
            $this->command->warn('No employee divisi/jabatan combinations found; no Hard Skill records inserted.');
        }
    }
}
