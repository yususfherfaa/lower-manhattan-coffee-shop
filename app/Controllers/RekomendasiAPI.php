<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\FlaskRecommendationClient;

/**
 * RekomendasiAPI.php
 * ==================
 * Controller API untuk sistem rekomendasi menu berbasis waktu.
 * Memanggil script Python ML dan mengembalikan hasil dalam format JSON.
 *
 * Endpoint:
 *   GET /api/rekomendasi          → rekomendasi berdasarkan jam sekarang
 *   GET /api/rekomendasi/{jam}    → rekomendasi berdasarkan jam tertentu (0-23)
 *   GET /api/rekomendasi/jam/{jam}
 *
 * Cara install Python & dependencies:
 *   pip install scikit-learn pandas numpy joblib flask flask-cors
 */
class RekomendasiAPI extends BaseController
{
    /** Simpan satu impression secara best-effort; kegagalan tidak memutus rekomendasi. */
    private function logRecommendation(array $data): ?int
    {
        try {
            $ids = [];
            $collect = static function (array $items) use (&$collect, &$ids): void {
                foreach ($items as $item) {
                    if (($item['tipe'] ?? '') === 'kelompok') {
                        $collect($item['anggota'] ?? []);
                    } elseif (!empty($item['id_produk'])) {
                        $ids[] = (string) $item['id_produk'];
                    }
                }
            };
            $collect($data['rekomendasi'] ?? []);
            $ids = array_values(array_unique($ids));
            if ($ids === []) {
                return null;
            }

            $db = \Config\Database::connect();
            $logPayload = [
                'jam' => max(0, min(23, (int) ($data['jam'] ?? date('G')))),
                'source' => substr((string) ($data['source'] ?? 'unknown'), 0, 64),
                'menu_ditampilkan' => json_encode($ids, JSON_UNESCAPED_SLASHES),
            ];

            // Tambahkan kolom Plan B jika kolom ada di tabel
            $fields = $db->getFieldNames('recommendation_logs');
            if (in_array('sumber_model', $fields, true)) {
                $logPayload['sumber_model'] = substr((string) ($data['sumber_model'] ?? 'public_dataset'), 0, 50);
            }
            if (in_array('label_keramaian', $fields, true)) {
                $logPayload['label_keramaian'] = substr((string) ($data['label_keramaian'] ?? 'sedang'), 0, 20);
            }
            if (in_array('versi_model', $fields, true)) {
                $logPayload['versi_model'] = substr((string) ($data['versi_model'] ?? '2.0.0-planB'), 0, 30);
            }

            $inserted = $db->table('recommendation_logs')->insert($logPayload);
            if (!$inserted) {
                return null;
            }
            return (int) $db->insertID() ?: null;
        } catch (\Throwable $e) {
            log_message('warning', 'Pencatatan impression rekomendasi dilewati: {message}', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Ambil rekomendasi menu berdasarkan jam real-time atau query parameter (?jam=X).
     */
    public function index()
    {
        $jamParam = $this->request->getGet('jam');
        $menitParam = $this->request->getGet('menit');
        if ($jamParam !== null && $jamParam !== '') {
            $jam = (int) $jamParam;
            if ($jam < 0 || $jam > 23) {
                return $this->response
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Jam tidak valid. Gunakan angka 0-23.',
                    ])
                    ->setStatusCode(400);
            }
        }

        $menit = null;
        if ($menitParam !== null && $menitParam !== '') {
            $menit = filter_var($menitParam, FILTER_VALIDATE_INT);
            if ($menit === false || $menit < 0 || $menit > 59) {
                return $this->response
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'Menit tidak valid. Gunakan angka 0-59.',
                    ])
                    ->setStatusCode(400);
            }
        }

        return $this->getRekomendasiByJam(isset($jam) ? $jam : null, $menit);
    }

    /**
     * Ambil rekomendasi menu berdasarkan jam tertentu.
     *
     * @param int|null $jam  Jam 0-23, null = jam sekarang
     */
    public function byJam($jam = null)
    {
        $jam = ($jam !== null) ? (int) $jam : null;

        if ($jam !== null && ($jam < 0 || $jam > 23)) {
            return $this->response
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Jam tidak valid. Gunakan angka 0-23.',
                ])
                ->setStatusCode(400);
        }

        return $this->getRekomendasiByJam($jam);
    }

    /**
     * Enrich items dengan field gambar (URL lengkap) dan stok dari tabel products.
     */
    private function enrichGambar(array $items): array
    {
        $db = \Config\Database::connect();
        foreach ($items as &$it) {
            if (($it['tipe'] ?? 'menu') === 'kelompok' && !empty($it['anggota'])) {
                $it['anggota'] = $this->enrichGambar($it['anggota']);
                $first = $it['anggota'][0];
                foreach (['id_produk','nama','kategori','kategori_dataset','tipe_dataset','harga','deskripsi','gambar','stok','varian_suhu','tambahan_harga_es','suhu_tetap','punya_opsi','label_opsi','options'] as $key) {
                    $it[$key] = $first[$key] ?? ($it[$key] ?? null);
                }
                continue;
            }

            $select = 'nama, kategori, harga, deskripsi, gambar, stok, varian_suhu, tambahan_harga_es, suhu_tetap, punya_opsi, label_opsi';
            foreach (['kategori_dataset', 'tipe_dataset', 'ukuran'] as $field) {
                if ($db->fieldExists($field, 'products')) {
                    $select .= ', ' . $field;
                }
            }
            $row = $db->table('products')
                ->select($select)
                ->where('id_produk', $it['id_produk'] ?? '')
                ->get()->getRowArray();

            if ($row) {
                $it['nama'] = catalog_product_display_name($row);
                $it['ukuran'] = $row['ukuran'] ?? null;
                $it['ukuran_label'] = catalog_product_size_label($row);
                $it['kategori'] = $row['kategori'];
                $it['kategori_dataset'] = $row['kategori_dataset'] ?? $it['kategori_dataset'] ?? $row['kategori'];
                $it['tipe_dataset'] = $row['tipe_dataset'] ?? $it['tipe_dataset'] ?? null;
                $it['harga'] = (float) $row['harga'];
                $it['deskripsi'] = $row['deskripsi'] ?? '';
                $it['varian_suhu'] = (int) $row['varian_suhu'];
                $it['tambahan_harga_es'] = (float) $row['tambahan_harga_es'];
                $it['suhu_tetap'] = $row['suhu_tetap'];
                $it['punya_opsi'] = (int) $row['punya_opsi'];
                $it['label_opsi'] = $row['label_opsi'];
                $it['options'] = $row['punya_opsi']
                    ? $db->table('product_options')
                        ->where('id_produk', $it['id_produk'])
                        ->where('aktif', 1)
                        ->orderBy('urutan', 'ASC')
                        ->get()
                        ->getResultArray()
                    : [];
            }
            $it['gambar'] = product_image_url(
                $row['gambar'] ?? null,
                $it['kategori_dataset'] ?? $it['kategori'] ?? null
            );
            $it['stok'] = isset($row['stok']) ? (int) $row['stok'] : null;
        }
        unset($it);
        return $items;
    }

    private function formatKategoriRekomendasi(array $items, ?string $segmen): array
    {
        $produkKopi = ['PRD0000023','PRD0000024','PRD0000025','PRD0000031','PRD0000032','PRD0000033','PRD0000034','PRD0000035','PRD0000036','PRD0000037','PRD0000038','PRD0000039','PRD0000040','PRD0000041'];
        $produkPanas = ['PRD0000031','PRD0000032','PRD0000033','PRD0000034','PRD0000035','PRD0000036'];
        $produkDingin = ['PRD0000037','PRD0000038','PRD0000039','PRD0000040','PRD0000041','PRD0000042'];
        $hasil = [];

        foreach ($items as $item) {
            if (($item['tipe'] ?? 'menu') === 'kelompok') {
                $item['anggota'] = $this->formatKategoriRekomendasi($item['anggota'] ?? [], $segmen);
                if ($item['anggota'] === []) {
                    continue;
                }
                $hasil[] = $item;
                continue;
            }

            if (!empty($item['kategori_dataset'])) {
                $item['kategori_rekomendasi'] = $item['kategori_dataset'];
                $hasil[] = $item;
                continue;
            }

            $id = $item['id_produk'] ?? '';
            if (in_array($id, ['PRD0000024', 'PRD0000025'], true)) {
                continue;
            }

            $kategori = strtolower((string) ($item['kategori'] ?? ''));
            $isMinuman = str_contains($kategori, 'minuman') || str_contains($kategori, 'kopi') || str_contains($kategori, 'nutrisari');
            if ($isMinuman) {
                $suhu = $item['suhu_tetap'] ?? null;
                if ($suhu === 'dingin') {
                    $suhu = 'es';
                }
                if (!$suhu) {
                    $suhu = in_array($id, $produkPanas, true) ? 'panas' : (in_array($id, $produkDingin, true) ? 'es' : ($segmen === 'siang' || $segmen === 'malam' ? 'es' : 'panas'));
                }
                $item['suhu_default'] = $suhu;
                $labelSuhu = $suhu === 'es' || $suhu === 'dingin' ? 'Minuman Dingin' : 'Minuman Panas';
                $labelJenis = str_contains($kategori, 'non kopi')
                    ? 'Non Kopi'
                    : (in_array($id, $produkKopi, true) || str_contains($kategori, 'kopi') ? 'Kopi' : 'Non Kopi');
                $item['kategori_rekomendasi'] = $labelSuhu . ' · ' . $labelJenis;
            }

            $hasil[] = $item;
        }

        return $hasil;
    }

    /**
     * Core: request ke Flask API server (localhost:5001) dan return JSON.
     */
    private function getRekomendasiByJam(?int $jam, ?int $menit = null)
    {
        $hasil = (new FlaskRecommendationClient())->fetch($jam, $menit);
        if ($hasil === null) {
            return $this->fallbackRuleBased($jam, $menit);
        }

        $kepercayaan = $hasil['data']['kepercayaan'] ?? null;
        if (is_numeric($kepercayaan)
            && $kepercayaan < \Config\Recommendation::LOW_CONFIDENCE_THRESHOLD) {
            return $this->fallbackRuleBased($jam, $menit, 'dataset-lower-manhattan');
        }

        if (isset($hasil['data']['rekomendasi']) && is_array($hasil['data']['rekomendasi'])) {
            $hasil['data']['rekomendasi'] = $this->enrichGambar($hasil['data']['rekomendasi']);
            $hasil['data']['rekomendasi'] = $this->formatKategoriRekomendasi($hasil['data']['rekomendasi'], $hasil['data']['segmen'] ?? null);
        }

        $hasil['data']['log_id'] = $this->logRecommendation($hasil['data']);

        return $this->response->setJSON($hasil);
    }

    /**
     * Fallback rule-based jika Python/model tidak tersedia.
     * Tetap memberikan rekomendasi tanpa ML.
     */
    private function fallbackRuleBased($jam = null, ?int $menit = null, string $source = 'dataset-lower-manhattan')
    {
        $now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
        $jam = $jam ?? (int) $now->format('H');
        $menit = $menit ?? (int) $now->format('i');
        $hari = (int) $now->format('N') - 1;
        $namaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][$hari];

        if ($jam < 6 || $jam > 20) {
            $data = [
                'jam' => $jam,
                'menit' => $menit,
                'segmen' => 'di_luar_operasional',
                'emoji' => '💤',
                'hari' => $namaHari,
                'source' => 'luar-jam-operasional',
                'sumber_model' => 'public_dataset',
                'rentang_segmen' => '06.00-20.59',
                'total_rekomendasi' => 0,
                'rekomendasi' => [],
                'pesan' => 'Lower Manhattan Coffee Shop beroperasi pukul 06:00 s.d. 21:00 WIB. Silakan kembali pada jam operasional.',
            ];
            return $this->response->setJSON(['status' => 'success', 'data' => $data]);
        }

        if ($jam <= 10) {
            $segmen = 'pagi';
            $rentang = '06.00-10.59';
        } elseif ($jam <= 14) {
            $segmen = 'siang';
            $rentang = '11.00-14.59';
        } elseif ($jam <= 17) {
            $segmen = 'sore';
            $rentang = '15.00-17.59';
        } else {
            $segmen = 'malam';
            $rentang = '18.00-20.59';
        }

        $db = \Config\Database::connect();
        $popularityPath = ROOTPATH . 'ml/config/popularitas_segmen.json';
        if (!is_file($popularityPath)) {
            throw new \RuntimeException('Artefak popularitas per segmen dari dataset Lower Manhattan tidak ditemukan.');
        }
        $popularityJson = file_get_contents($popularityPath);
        $popularityBySegment = $popularityJson === false ? null : json_decode($popularityJson, true);
        if (!is_array($popularityBySegment) || !isset($popularityBySegment[$segmen]) || !is_array($popularityBySegment[$segmen])) {
            throw new \RuntimeException("Data popularitas dataset untuk segmen {$segmen} tidak valid.");
        }

        $builder = $db->table('products');
        if ($db->fieldExists('aktif', 'products')) {
            $builder->where('aktif', 1);
        }
        $hasDatasetProducts = $db->table('products')->like('id_produk', 'PRDM%')->countAllResults() > 0;
        if ($hasDatasetProducts) {
            $builder->like('id_produk', 'PRDM%');
        }
        $orderField = $db->fieldExists('kode_produk_dataset', 'products') ? 'kode_produk_dataset' : 'id_produk';
        $products = $builder->orderBy($orderField, 'ASC')->get()->getResultArray();
        if (!$products && $hasDatasetProducts) {
            throw new \RuntimeException('Produk dataset Lower Manhattan aktif tidak ditemukan untuk rekomendasi fallback.');
        }

        $productsById = [];
        foreach ($products as $product) {
            if ((int) ($product['stok'] ?? 0) <= 0) {
                continue;
            }
            $productsById[(string) $product['id_produk']] = $product;
        }

        $rekomendasi = [];
        foreach ($popularityBySegment[$segmen] as $datasetProduct) {
            $productId = 'PRDM' . str_pad((string) ($datasetProduct['product_id'] ?? ''), 4, '0', STR_PAD_LEFT);
            $product = $productsById[$productId] ?? null;
            if ($product === null) {
                continue;
            }

            $rekomendasi[] = [
                'peringkat' => count($rekomendasi) + 1,
                'tipe' => 'menu',
                'id_produk' => $productId,
                'kategori' => $product['kategori'],
                'kategori_dataset' => $product['kategori_dataset'] ?? $product['kategori'],
            ];
            if (count($rekomendasi) >= 5) {
                break;
            }
        }

        $rekomendasi = $this->enrichGambar($rekomendasi);
        $rekomendasi = $this->formatKategoriRekomendasi($rekomendasi, $segmen);
        $emojiSegmen = ['pagi' => '🌅', 'siang' => '☀️', 'sore' => '🌤️', 'malam' => '🌙'];
        $data = [
            'jam' => $jam,
            'menit' => $menit,
            'segmen' => $segmen,
            'emoji' => $emojiSegmen[$segmen],
            'hari' => $namaHari,
            'source' => $source,
            'sumber_model' => 'public_dataset',
            'rentang_segmen' => $rentang,
            'total_rekomendasi' => count($rekomendasi),
            'rekomendasi' => $rekomendasi,
        ];
        $data['log_id'] = $this->logRecommendation($data);

        return $this->response
            ->setJSON([
                'status' => 'success',
                'data'   => $data,
            ]);
    }
}
