<?php

/**
 * Helper untuk generate kode dengan logika isi celah (gap filling)
 * Jika ada kode yang dihapus, data baru akan mengisi celah tersebut
 * daripada melanjutkan ke nomor berikutnya
 *
 * ROBUST: Parser berbasis regex untuk menangani berbagai format kode
 * (dengan dash, spasi, atau format tidak konsisten lainnya)
 */

namespace App\Helpers;

class KodeGenerator
{
    /**
     * Extract nomor dari string kode menggunakan regex
     * Handles: SS001, SS-001, SS 001, SS_001, dll
     *
     * @param string $kode Kode lengkap (contoh: 'SS001', 'USR-001')
     * @param string $prefix Prefix kode (contoh: 'SS', 'USR')
     * @return int|null Nomor yang diekstrak, atau null jika gagal
     */
    public static function extractNumber(string $kode, string $prefix): ?int
    {
        // Escape prefix untuk regex safety
        $escapedPrefix = preg_quote($prefix, '/');

        // Regex: cari semua digit setelah prefix (memperbolehkan karakter non-digit di其间)
        // Pattern: SS001, SS-001, SS 001, SS_001 semua ditangkap
        if (preg_match("/{$escapedPrefix}[^0-9]*([0-9]+)/i", $kode, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Generate kode dengan prefix dan logika isi celah
     *
     * ROBUST: Menggunakan extractNumber() untuk parsing nomor,
     * tidak bergantung pada format string tertentu
     *
     * @param string $table Nama tabel
     * @param string $kodeColumn Nama kolom kode
     * @param string $prefix Prefix kode (contoh: 'SS', 'HS')
     * @param int $numberLength Panjang nomor (default: 3, jadi SS001)
     * @return string Kode baru (contoh: SS001)
     */
    public static function generateWithGapFilling(string $table, string $kodeColumn, string $prefix, int $numberLength = 3): string
    {
        // Ambil semua kode yang ada
        $existingKodes = \DB::table($table)
            ->whereNotNull($kodeColumn)
            ->whereRaw("LEFT({$kodeColumn}, ?) = ?", [strlen($prefix), $prefix])
            ->pluck($kodeColumn)
            ->map(fn($kode) => self::extractNumber($kode, $prefix))
            ->filter() // Remove null values (parsing gagal)
            ->sort()
            ->values()
            ->toArray();

        if (empty($existingKodes)) {
            // Tidak ada kode sama sekali, mulai dari 1
            $nextNumber = 1;
        } else {
            // Cari celah (gap)
            $nextNumber = null;

            for ($i = 1; $i <= count($existingKodes) + 1; $i++) {
                if (!in_array($i, $existingKodes)) {
                    // Ketemu celah
                    $nextNumber = $i;
                    break;
                }
            }

            // Fallback: jika tidak ada celah, gunakan nomor berikutnya
            if ($nextNumber === null) {
                $nextNumber = max($existingKodes) + 1;
            }
        }

        return $prefix . str_pad($nextNumber, $numberLength, '0', STR_PAD_LEFT);
    }
}
