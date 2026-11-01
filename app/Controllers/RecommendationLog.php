<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class RecommendationLog extends BaseController
{
    public function select()
    {
        $logId = filter_var($this->request->getPost('log_id'), FILTER_VALIDATE_INT);
        $productId = trim((string) $this->request->getPost('id_produk'));
        if (!$logId || !preg_match('/^[A-Za-z0-9_-]{1,32}$/', $productId)) {
            return $this->response->setJSON(['ok' => false])->setStatusCode(400);
        }

        try {
            $db = \Config\Database::connect();
            $log = $db->table('recommendation_logs')
                ->select('menu_ditampilkan, id_produk_dipilih')
                ->where('id_log', $logId)
                ->get()->getRowArray();
            $shown = $log ? json_decode($log['menu_ditampilkan'], true) : null;
            if (!$log || !is_array($shown) || !in_array($productId, $shown, true) || $log['id_produk_dipilih'] !== null) {
                return $this->response->setJSON(['ok' => false])->setStatusCode(404);
            }

            $db->table('recommendation_logs')
                ->where('id_log', $logId)
                ->where('id_produk_dipilih', null)
                ->update(['id_produk_dipilih' => $productId]);
            if ($db->affectedRows() !== 1) {
                return $this->response->setJSON(['ok' => false])->setStatusCode(409);
            }
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            log_message('warning', 'Pencatatan pilihan rekomendasi dilewati: {message}', ['message' => $e->getMessage()]);
            return $this->response->setJSON(['ok' => false])->setStatusCode(503);
        }
    }
}
