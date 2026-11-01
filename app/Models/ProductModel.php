<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id_produk';

    protected $allowedFields = [
        'id_produk',
        'nama',
        'kategori',
        'kategori_dataset',
        'tipe_dataset',
        'ukuran',
        'kode_produk_dataset',
        'harga',
        'harga_dolar_ref',
        'stok',
        'varian_suhu',
        'tambahan_harga_es',
        'gambar',
        'suhu_tetap',
        'punya_opsi',
        'label_opsi',
        'deskripsi',
        'aktif',
    ];
}