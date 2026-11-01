<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use FPDF;

class LaporanStok extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $produk = $db->table('products')
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResultArray();

        $aktivitas = $db->table('data_products')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/laporan_stok', [
            'produk'    => $produk,
            'aktivitas' => $aktivitas
        ]);
    }

  public function pdf()
{
    $db = \Config\Database::connect();

    $produk = $db->table('products')
        ->orderBy('nama', 'ASC')
        ->get()
        ->getResultArray();

    $aktivitas = $db->table('data_products')
        ->orderBy('created_at', 'DESC')
        ->get()
        ->getResultArray();

    require_once APPPATH . 'ThirdParty/fpdf/fpdf.php';

    $pdf = new \FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 10);

    date_default_timezone_set('Asia/Jakarta');

    // =========================
    // TITLE
    // =========================
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'LAPORAN STOK & AKTIVITAS PRODUK', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Tanggal Cetak: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(5);

    // =========================
    // HITUNG TOTAL WIDTH TABEL PRODUK
    // =========================
    $tableWidth = 30 + 80 + 40 + 40 + 30; // = 220mm
    $startX = (297 - $tableWidth) / 2; // A4 landscape width = 297mm

    // =========================
    // PRODUK TABLE
    // =========================
    $pdf->SetX($startX);

    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Cell(30, 8, 'ID', 1, 0, 'C');
    $pdf->Cell(80, 8, 'Nama Produk', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Kategori', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Harga', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Stok', 1, 0, 'C');
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);

    $total_stok = 0;

    foreach ($produk as $p) {

        $pdf->SetX($startX);

        $pdf->Cell(30, 8, $p['id_produk'], 1, 0, 'C');
        $pdf->Cell(80, 8, substr($p['nama'], 0, 40), 1, 0, 'C');
        $pdf->Cell(40, 8, $p['kategori'], 1, 0, 'C');
        $pdf->Cell(40, 8, 'Rp ' . number_format($p['harga'], 0, ',', '.'), 1, 0, 'C');
        $pdf->Cell(30, 8, $p['stok'], 1, 0, 'C');

        $pdf->Ln();

        $total_stok += (int) $p['stok'];
    }

    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'TOTAL STOK: ' . $total_stok, 0, 1, 'L');

    $pdf->Ln(8);

    // =========================
    // HITUNG TABLE WIDTH AKTIVITAS
    // =========================
    $tableWidth2 = 45 + 35 + 45 + 35 + 110; // = 270mm
    $startX2 = (297 - $tableWidth2) / 2;

    // =========================
    // AKTIVITAS TABLE
    // =========================
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'RIWAYAT AKTIVITAS PRODUK', 0, 1, 'C');

    $pdf->SetX($startX2);
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Cell(45, 8, 'Tanggal', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Kasir', 1, 0, 'C');
    $pdf->Cell(45, 8, 'Aktivitas', 1, 0, 'C');
    $pdf->Cell(35, 8, 'ID Produk', 1, 0, 'C');
    $pdf->Cell(110, 8, 'Nama Produk', 1, 0, 'C');

    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);

    foreach ($aktivitas as $a) {

        $pdf->SetX($startX2);

        $pdf->Cell(45, 8, $a['created_at'], 1, 0, 'C');
        $pdf->Cell(35, 8, substr($a['nama_kasir'], 0, 15), 1, 0, 'C');
        $pdf->Cell(45, 8, $a['aktivitas'], 1, 0, 'C');
        $pdf->Cell(35, 8, $a['id_produk'], 1, 0, 'C');
        $pdf->Cell(110, 8, substr($a['nama_produk'], 0, 50), 1, 0, 'C');

        $pdf->Ln();
    }

    $pdf->Output('I', 'Laporan_Stok_Produk.pdf');
    exit;
}
}