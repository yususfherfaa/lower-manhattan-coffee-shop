<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use FPDF;

class Laporan extends BaseController
{
    private function isValidDate(mixed $value): bool
    {
        if (!is_string($value) || !preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $value)) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        return $date !== false
            && $date->format('Y-m-d') === $value
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
    }

    public function index()
    {
        $db = \Config\Database::connect();

        $from = $this->request->getGet('from')
            ?? date('Y-m-01');

        $to = $this->request->getGet('to')
            ?? date('Y-m-d');

        if (!$this->isValidDate($from) || !$this->isValidDate($to) || $from > $to) {
            return $this->response->setStatusCode(400)->setBody('Rentang tanggal laporan tidak valid.');
        }

        $id_user = $this->request->getGet('id_user')
            ?? 'all';

        // LIST KASIR
        $kasirList = $db->table('users')
            ->where('role', 'kasir')
            ->get()
            ->getResultArray();

        // QUERY ORDER
        $builder = $db->table('orders o');

        $builder->select('
    o.*,
    p.id_user,
    p.metode_pembayaran,
    u.nama_kasir,
    o.id_meja
');

        $builder->join(
            'payments p',
            'p.id_order = o.id_order'
        );

        $builder->join(
            'users u',
            'u.id_user = p.id_user'
        );

        $builder->where('o.status', 'lunas');

        $toExclusive = date('Y-m-d 00:00:00', strtotime($to . ' +1 day'));

        $builder->where(
            'o.created_at >=',
            $from . ' 00:00:00'
        );

        $builder->where(
            'o.created_at <',
            $toExclusive
        );

        // FILTER ROLE
        if (session()->get('role') == 'kasir') {

            $builder->where(
                'p.id_user',
                session()->get('id_user')
            );

        } elseif ($id_user != 'all') {

            $builder->where(
                'p.id_user',
                $id_user
            );
        }

        $builder->orderBy('o.created_at', 'DESC');

        $orders = $builder->get()->getResultArray();


        // TOTAL
        $total_pesanan = count($orders);

        $total_pendapatan = array_sum(
            array_column($orders, 'total')
        );

        // MENU TERJUAL
        $menuBuilder = $db->table('order_items oi');

        $menuBuilder->select('
            CONCAT(
                oi.nama,
                CASE WHEN COALESCE(oi.opsi, \'\') <> \'\' THEN CONCAT(\' - \', oi.opsi) ELSE \'\' END,
                CASE WHEN COALESCE(oi.suhu, \'\') <> \'\' THEN CONCAT(\' (\', CASE WHEN oi.suhu = \'panas\' THEN \'Panas\' ELSE \'Es\' END, \')\') ELSE \'\' END
            ) as nama,
            SUM(oi.qty) as total_terjual,
            SUM(oi.subtotal) as total_pendapatan
        ');

        $menuBuilder->join(
            'orders o',
            'o.id_order = oi.id_order'
        );

        $menuBuilder->join(
            'payments p',
            'p.id_order = o.id_order'
        );

        $menuBuilder->where('o.status', 'lunas');

        $toExclusive = date('Y-m-d 00:00:00', strtotime($to . ' +1 day'));

        $menuBuilder->where(
            'o.created_at >=',
            $from . ' 00:00:00'
        );

        $menuBuilder->where(
            'o.created_at <',
            $toExclusive
        );

        if (session()->get('role') == 'kasir') {

            $menuBuilder->where(
                'p.id_user',
                session()->get('id_user')
            );

        } elseif ($id_user != 'all') {

            $menuBuilder->where(
                'p.id_user',
                $id_user
            );
        }

        $menuBuilder->groupBy(['oi.nama', 'oi.opsi', 'oi.suhu']);

        $menuBuilder->orderBy(
            'total_terjual',
            'DESC'
        );

        $menuList = $menuBuilder
            ->get()
            ->getResultArray();

        return view('kasir/laporan', [

            'orders' => $orders,

            'menuList' => $menuList,

            'from' => $from,

            'to' => $to,

            'id_user' => $id_user,

            'kasirList' => $kasirList,

            'total_pesanan' => $total_pesanan,

            'total_pendapatan' => $total_pendapatan
        ]);
    }

 public function pdf()
{
    date_default_timezone_set('Asia/Jakarta');

    $db = \Config\Database::connect();

    $from = $this->request->getGet('from') ?? date('Y-m-01');
    $to   = $this->request->getGet('to') ?? date('Y-m-d');

    if (!$this->isValidDate($from) || !$this->isValidDate($to) || $from > $to) {
        return $this->response->setStatusCode(400)->setBody('Rentang tanggal laporan tidak valid.');
    }

    $id_user = $this->request->getGet('id_user') ?? 'all';

    // =====================
    // QUERY ORDER
    // =====================
    $builder = $db->table('orders o');

    $builder->select('o.*, p.id_user, u.nama_kasir, o.id_meja');

    $builder->join('payments p', 'p.id_order = o.id_order');
    $builder->join('users u', 'u.id_user = p.id_user');

    $builder->where('o.status', 'lunas');

    $toExclusive = date('Y-m-d 00:00:00', strtotime($to . ' +1 day'));
    $builder->where('o.created_at >=', $from . ' 00:00:00');
    $builder->where('o.created_at <', $toExclusive);

    if (session()->get('role') == 'kasir') {
        $builder->where('p.id_user', session()->get('id_user'));
    } elseif ($id_user != 'all') {
        $builder->where('p.id_user', $id_user);
    }

    $orders = $builder->get()->getResultArray();

    $total_pendapatan = array_sum(array_column($orders, 'total'));

    // ======================
    // PDF INIT
    // ======================
    require_once APPPATH . 'ThirdParty/fpdf/fpdf.php';

    $pdf = new \FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 10);

    // ======================
    // TITLE
    // ======================
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'LAPORAN PENJUALAN', 0, 1, 'C');

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'Tanggal Cetak (WIB): ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    $pdf->Ln(5);

    // ======================
    // CENTER TABLE SETUP
    // ======================
    $pageWidth = 297;

$tableWidth =
    30 + // Order
    25 + // Meja
    50;  // Pelanggan

if (session()->get('role') == 'admin') {
    $tableWidth += 40; // Kasir
}

$tableWidth +=
    30 + // Metode
    35 + // Total
    35;  // Tanggal

$startX = ($pageWidth - $tableWidth) / 2;
    // ======================
    // HEADER TABLE
    // ======================
    $pdf->SetX($startX);
    $pdf->SetFont('Arial', 'B', 10);

   $pdf->Cell(30, 8, 'Order', 1, 0, 'C');
$pdf->Cell(25, 8, 'Meja', 1, 0, 'C');
$pdf->Cell(50, 8, 'Pelanggan', 1, 0, 'C');

if (session()->get('role') == 'admin') {
    $pdf->Cell(40, 8, 'Kasir', 1, 0, 'C');
}

$pdf->Cell(30, 8, 'Metode', 1, 0, 'C');
$pdf->Cell(35, 8, 'Total', 1, 0, 'C');
$pdf->Cell(35, 8, 'Tanggal', 1, 0, 'C');
    $pdf->Ln();

    // ======================
    // DATA ROW
    // ======================
    $pdf->SetFont('Arial', '', 10);

    foreach ($orders as $o) {

        $pdf->SetX($startX);

        $pdf->Cell(30, 8, $o['id_order'], 1, 0, 'C');
$pdf->Cell(25, 8, $o['id_meja'], 1, 0, 'C');
$pdf->Cell(50, 8, $o['nama_pelanggan'], 1, 0, 'C');

if (session()->get('role') == 'admin') {
    $pdf->Cell(40, 8, $o['nama_kasir'] ?? '-', 1, 0, 'C');
}

$pdf->Cell(30, 8, strtoupper($o['metode_pembayaran']), 1, 0, 'C');

$pdf->Cell(
    35,
    8,
    'Rp ' . number_format($o['total'], 0, ',', '.'),
    1,
    0,
    'C'
);

$pdf->Cell(
    35,
    8,
    date('d/m/Y', strtotime($o['created_at'])),
    1,
    0,
    'C'
);

        $pdf->Ln();
    }


$pdf->SetFont('Arial', '', 10);
$pdf->Ln(1);

// ======================
// MENU TERJUAL
// ======================

$menuBuilder = $db->table('order_items oi');

$menuBuilder->select('
    CONCAT(
        oi.nama,
        CASE WHEN COALESCE(oi.opsi, \'\') <> \'\' THEN CONCAT(\' - \', oi.opsi) ELSE \'\' END,
        CASE WHEN COALESCE(oi.suhu, \'\') <> \'\' THEN CONCAT(\' (\', CASE WHEN oi.suhu = \'panas\' THEN \'Panas\' ELSE \'Es\' END, \')\') ELSE \'\' END
    ) as nama,
    SUM(oi.qty) as total_terjual,
    SUM(oi.subtotal) as total_pendapatan
');

$menuBuilder->join('orders o', 'o.id_order = oi.id_order');
$menuBuilder->join('payments p', 'p.id_order = o.id_order');

$menuBuilder->where('o.status', 'lunas');
$toExclusive = date('Y-m-d 00:00:00', strtotime($to . ' +1 day'));
$menuBuilder->where('o.created_at >=', $from . ' 00:00:00');
$menuBuilder->where('o.created_at <', $toExclusive);

if (session()->get('role') == 'kasir') {
    $menuBuilder->where(
        'p.id_user',
        session()->get('id_user')
    );
} elseif ($id_user != 'all') {
    $menuBuilder->where(
        'p.id_user',
        $id_user
    );
}

$menuBuilder->groupBy(['oi.nama', 'oi.opsi', 'oi.suhu']);
$menuBuilder->orderBy('total_terjual', 'DESC');

$menuList = $menuBuilder->get()->getResultArray();

$pdf->Ln(10);

$menuTableWidth = 100 + 40 + 60;
$menuStartX = (297 - $menuTableWidth) / 2;

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'MENU TERJUAL',0,1,'C');

$pdf->SetX($menuStartX);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(100,8,'Nama Menu',1,0,'C');
$pdf->Cell(40,8,'Qty Terjual',1,0,'C');
$pdf->Cell(60,8,'Pendapatan',1,0,'C');
$pdf->Ln();

$pdf->SetFont('Arial','',10);

foreach($menuList as $m){

    $pdf->SetX($menuStartX);

    $pdf->Cell(100,8,$m['nama'],1,0,'C');

    $pdf->Cell(
        40,
        8,
        number_format($m['total_terjual']),
        1,
        0,
        'C'
    );

    $pdf->Cell(
        60,
        8,
        'Rp '.number_format(
            $m['total_pendapatan'],
            0,
            ',',
            '.'
        ),
        1,
        0,
        'C'
    );

    $pdf->Ln();
}

    // ======================
    // TOTAL
    // ======================
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(
        0,
        10,
        'TOTAL PENDAPATAN: Rp ' . number_format($total_pendapatan, 0, ',', '.'),
        0,
        1,
        'L'
    );

    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(
        0,
        0,
        'MENU TERJUAL: ' . count($menuList ),
        0,
        1,
        'L'
    );

    $pdf->Output();
    exit;
}
}
