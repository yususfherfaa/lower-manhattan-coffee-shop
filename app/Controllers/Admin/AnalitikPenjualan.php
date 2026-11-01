<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AnalitikPenjualan extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Ambil data agregat keramaian jam (global store_id=0 jika ada, atau rata-rata)
        $hasAggJam = $db->tableExists('agg_keramaian_jam');
        $aggJam = $hasAggJam 
            ? $db->table('agg_keramaian_jam')->where('store_id', 0)->orderBy('jam', 'ASC')->get()->getResultArray()
            : [];
            
        // Ambil data agregat kategori per segmen
        $hasAggKat = $db->tableExists('agg_kategori_segmen');
        $aggKat = $hasAggKat
            ? $db->table('agg_kategori_segmen')->orderBy('segmen', 'ASC')->orderBy('total_penjualan', 'DESC')->get()->getResultArray()
            : [];

        // Baca metadata ML jika ada
        $metaPath = ROOTPATH . 'ml/metadata.json';
        $metadata = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];

        $profilePath = ROOTPATH . 'ml/data/profil_dataset.json';
        $profile = file_exists($profilePath)
            ? json_decode(file_get_contents($profilePath), true, 512, JSON_THROW_ON_ERROR)
            : [];
        $categoryCounts = array_map('intval', $profile['distribusi_kategori'] ?? []);
        arsort($categoryCounts);
        $topCategory = $categoryCounts === [] ? null : array_key_first($categoryCounts);
        $hourCounts = array_map('intval', $profile['distribusi_jam'] ?? []);
        $peakHour = $hourCounts === [] ? null : array_search(max($hourCounts), $hourCounts, true);
        $quietHour = $hourCounts === [] ? null : array_search(min($hourCounts), $hourCounts, true);
        $totalTransactions = (int) ($profile['total_baris_bersih'] ?? 0);
        $totalDays = (int) ($profile['total_hari'] ?? 0);

        $data = [
            'title' => 'Analitik Penjualan & Keramaian (Dataset Coffee Shop)',
            'current_uri' => 'admin/analitik-penjualan',
            'aggJam' => $aggJam,
            'aggKat' => $aggKat,
            'metadata' => $metadata,
            'summary' => [
                'total_transactions' => $totalTransactions,
                'top_category' => $topCategory,
                'top_category_share' => $topCategory !== null && $totalTransactions > 0
                    ? 100 * $categoryCounts[$topCategory] / $totalTransactions
                    : null,
                'peak_hour' => $peakHour,
                'peak_hour_transactions' => $peakHour !== null ? $hourCounts[$peakHour] : null,
                'quiet_hour' => $quietHour,
                'quiet_hour_average' => $quietHour !== null && $totalDays > 0
                    ? $hourCounts[$quietHour] / $totalDays
                    : null,
            ],
        ];

        return view('admin/analitik_penjualan', $data);
    }
}
