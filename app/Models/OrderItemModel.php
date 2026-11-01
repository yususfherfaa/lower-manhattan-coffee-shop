<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'id_items';

    protected $allowedFields = [
        'id_items',
        'id_order',
        'id_produk',
        'nama',
        'kategori',
        'opsi',
        'catatan',
        'suhu',
        'qty',
        'harga',
        'subtotal'
    ];
}