<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'id_pay';
    protected $useAutoIncrement = false;

    protected $allowedFields = [
        'id_pay',
        'id_order',
        'id_user',
        'metode_pembayaran',
        'total_order',
        'uang_dibayar',
        'kembalian',
        'nama_kasir'
    ];
}