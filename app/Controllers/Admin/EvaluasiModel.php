<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class EvaluasiModel extends BaseController
{
    public function index()
    {
        $metaPath = ROOTPATH . 'ml/metadata.json';
        $metadata = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];

        $evalTxtPath = ROOTPATH . 'ml/hasil_evaluasi.txt';
        $evalTxt = file_exists($evalTxtPath) ? file_get_contents($evalTxtPath) : 'Belum ada data evaluasi.';

        $data = [
            'title' => 'Evaluasi Model Rekomendasi & Keramaian (KNN)',
            'current_uri' => 'admin/evaluasi-model',
            'metadata' => $metadata,
            'evalTxt' => $evalTxt
        ];

        return view('admin/evaluasi_model', $data);
    }
}
