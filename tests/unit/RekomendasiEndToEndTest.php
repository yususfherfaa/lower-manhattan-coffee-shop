<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * End-to-end integration test untuk Rekomendasi CI4 -> Flask API -> DB Test
 */
class RekomendasiEndToEndTest extends CIUnitTestCase
{
    public function testFlaskAndRecommendationLogIntegration()
    {
        // 1. Hubungkan ke database uji warkop_bahagia_test
        $config = config('Database');
        $config->default['database'] = 'warkop_bahagia_test';
        $db = \Config\Database::connect($config->default, false);

        $initialLogCount = $db->table('recommendation_logs')->countAllResults();

        // 2. Lakukan request ke Flask microservice di port 5001
        $client = \Config\Services::curlrequest([
            'timeout'  => 5.0,
            'http_errors' => false,
        ]);

        $response = $client->get('http://127.0.0.1:5001/api/rekomendasi?jam=10&menit=0');

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertSame('success', $body['status']);
        $this->assertNotEmpty($body['data']['rekomendasi']);

        // 3. Simpan log ke database test secara end-to-end
        $rekData = $body['data'];
        $ids = array_map(fn($item) => $item['id_produk'], $rekData['rekomendasi']);

        $inserted = $db->table('recommendation_logs')->insert([
            'jam' => $rekData['jam'],
            'source' => $rekData['source'],
            'sumber_model' => $rekData['sumber_model'],
            'label_keramaian' => $rekData['label_keramaian'],
            'versi_model' => $rekData['versi_model'],
            'menu_ditampilkan' => json_encode($ids),
        ]);

        $this->assertTrue((bool)$inserted);

        $finalLogCount = $db->table('recommendation_logs')->countAllResults();
        $this->assertSame($initialLogCount + 1, $finalLogCount);

        // Verifikasi record log tersimpan dengan metadata Plan B yang benar
        $latestLog = $db->table('recommendation_logs')
            ->orderBy('id_log', 'DESC')
            ->get(1)
            ->getRowArray();

        $this->assertSame('public_dataset', $latestLog['sumber_model']);
        $this->assertSame('2.0.0-planB', $latestLog['versi_model']);
        $this->assertSame('knn-kategori-popularitas', $latestLog['source']);
    }
}
