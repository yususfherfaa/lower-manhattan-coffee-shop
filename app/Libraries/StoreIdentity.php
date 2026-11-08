<?php

namespace App\Libraries;

/**
 * StoreIdentity
 * Helper library to fetch store identity and QRIS configuration
 * with clean fallbacks and strict validation.
 */
class StoreIdentity
{
    /**
     * Get validated store identity information.
     *
     * @return array{
     *   nama_toko: string,
     *   alamat_lengkap: string,
     *   nmid: string|null,
     *   qris_image: string,
     *   qris_mode: string,
     *   is_demo: bool
     * }
     */
    public static function get(): array
    {
        // 1. Nama Toko
        $namaToko = trim((string) (setting('nama_toko') ?? ''));
        if ($namaToko === '') {
            $namaToko = trim((string) (setting('nama_warkop') ?? ''));
        }
        if ($namaToko === '') {
            $namaToko = 'Lower Manhattan Coffee Shop';
        }
        // Sanitize control characters and limit length
        $namaToko = preg_replace('/[\x00-\x1F\x7F]/u', '', $namaToko);
        $namaToko = mb_substr($namaToko, 0, 100);

        // 2. Alamat Lengkap
        $alamat = trim((string) (setting('alamat_lengkap') ?? ''));
        if ($alamat === '') {
            $alamat = trim((string) (setting('alamat') ?? ''));
        }
        $alamat = preg_replace('/[\x00-\x1F\x7F]/u', '', $alamat);
        $alamat = mb_substr($alamat, 0, 255);

        // 3. NMID
        $rawNmid = trim((string) (setting('nmid') ?? ''));
        $rawNmid = strtoupper(preg_replace('/\s+/', '', $rawNmid));
        $validNmid = null;
        if ($rawNmid !== '') {
            if (preg_match('/^[A-Z]{2}[0-9A-Z]{8,20}$/', $rawNmid)) {
                $validNmid = $rawNmid;
            } else {
                log_message('warning', 'Format NMID tidak valid diabaikan: length={len}', ['len' => strlen($rawNmid)]);
            }
        }

        // 4. Mode QRIS
        $rawMode = strtolower(trim((string) (setting('qris_mode') ?? 'demo')));
        $qrisMode = ($rawMode === 'live') ? 'live' : 'demo';

        // 5. Resolve a demo asset independently from any previously uploaded image.
        $rawImage = trim((string) (setting('qris') ?? ''));
        $placeholder = 'uploads/qris-placeholder.svg';
        $demoImage = 'uploads/qris-demo-lowermanhattan.png';
        $qrisImage = $qrisMode === 'demo' ? $demoImage : $placeholder;

        if ($qrisMode !== 'demo' && $rawImage !== '') {
            $realPath = FCPATH . ltrim($rawImage, '/\\');
            if (is_file($realPath)) {
                $qrisImage = $rawImage;
            }
        }

        if (!is_file(FCPATH . ltrim($qrisImage, '/\\'))) {
            $qrisImage = $placeholder;
        }

        $isDemo = $qrisMode === 'demo'
            || $qrisImage === $placeholder
            || $qrisImage === $demoImage;
        if ($isDemo) {
            $validNmid = null; // Pada mode demo NMID tidak ditampilkan
        }

        return [
            'nama_toko'      => $namaToko,
            'alamat_lengkap' => $alamat,
            'nmid'           => $validNmid,
            'qris_image'     => $qrisImage,
            'qris_mode'      => $qrisMode,
            'is_demo'        => $isDemo,
        ];
    }
}
