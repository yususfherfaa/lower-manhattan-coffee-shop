<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class RecommendationAcceptance extends BaseController
{
    public function index()
    {
        $rows = [];
        $available = true;
        try {
            $rows = \Config\Database::connect()->table('recommendation_logs')
                ->select('jam, COUNT(*) AS ditampilkan, SUM(id_produk_dipilih IS NOT NULL) AS dipilih', false)
                ->groupBy('jam')
                ->orderBy('jam', 'ASC')
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            $available = false;
        }

        $totalDitampilkan = 0;
        $totalDipilih = 0;
        $jamTerbaik = '-';
        $maxTingkat = -1;

        foreach ($rows as &$row) {
            $row['tingkat'] = (int) $row['ditampilkan'] > 0
                ? 100 * (int) $row['dipilih'] / (int) $row['ditampilkan']
                : 0;
            $totalDitampilkan += (int) $row['ditampilkan'];
            $totalDipilih += (int) $row['dipilih'];

            if ($row['tingkat'] > $maxTingkat && (int) $row['ditampilkan'] > 0) {
                $maxTingkat = $row['tingkat'];
                $jamTerbaik = str_pad((string) $row['jam'], 2, '0', STR_PAD_LEFT) . '.00';
            }
        }
        unset($row);

        $tingkatRataRata = $totalDitampilkan > 0 ? (100 * $totalDipilih / $totalDitampilkan) : 0;

        // Ambil produk rekomendasi paling sering dipilih
        $topProducts = [];
        if ($available) {
            try {
                $topProducts = \Config\Database::connect()->table('recommendation_logs rl')
                    ->select('p.nama, p.gambar, p.kategori, p.deskripsi, COUNT(rl.id_produk_dipilih) as jumlah_terpilih', false)
                    ->join('products p', 'p.id_produk = rl.id_produk_dipilih')
                    ->where('rl.id_produk_dipilih IS NOT NULL')
                    ->groupBy('rl.id_produk_dipilih, p.nama, p.gambar, p.kategori, p.deskripsi')
                    ->orderBy('jumlah_terpilih', 'DESC')
                    ->limit(5)
                    ->get()->getResultArray();
            } catch (\Throwable $e) {
                $topProducts = [];
            }
        }

        return view('admin/recommendation_acceptance', [
            'rows'             => $rows,
            'available'        => $available,
            'totalDitampilkan' => $totalDitampilkan,
            'totalDipilih'     => $totalDipilih,
            'tingkatRataRata'  => $tingkatRataRata,
            'jamTerbaik'       => $jamTerbaik,
            'topProducts'      => $topProducts,
        ]);
    }
}
