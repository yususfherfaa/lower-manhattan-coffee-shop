<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;

class ProductOption extends BaseController
{
    private function ensureKasir()
    {
        if (session()->get('role') !== 'kasir') {
            return redirect()->to('/');
        }
        return null;
    }

    private function logActivity(string $productId, string $activity, string $name): void
    {
        $db = \Config\Database::connect();
        $last = $db->table('data_products')
            ->select('id_data')
            ->orderBy('id_data', 'DESC')
            ->get(1)
            ->getRowArray();
        $number = $last ? (int) substr($last['id_data'], 3) + 1 : 1;
        $db->table('data_products')->insert([
            'id_data' => 'DAT' . str_pad((string) $number, 7, '0', STR_PAD_LEFT),
            'aktivitas' => $activity,
            'id_produk' => $productId,
            'nama_produk' => $name,
            'nama_kasir' => session()->get('nama_kasir') ?? 'Kasir',
        ]);
    }

    private function syncProductOptionState(string $productId): void
    {
        $db = \Config\Database::connect();
        $activeCount = $db->table('product_options')
            ->where('id_produk', $productId)
            ->where('aktif', 1)
            ->countAllResults();
        $db->table('products')
            ->where('id_produk', $productId)
            ->update([
                'punya_opsi' => $activeCount > 0 ? 1 : 0,
                'label_opsi' => $activeCount > 0 ? 'Pilih rasa' : null,
            ]);
    }

    public function add($productId)
    {
        if ($redirect = $this->ensureKasir()) {
            return $redirect;
        }

        $db = \Config\Database::connect();
        $product = $db->table('products')->where('id_produk', $productId)->get()->getRowArray();
        $name = trim((string) $this->request->getPost('nama_opsi'));
        $price = filter_var($this->request->getPost('tambahan_harga'), FILTER_VALIDATE_INT);
        if (!$product || $name === '' || $price === false || $price < 0) {
            return redirect()->back()->with('error', 'Nama varian dan tambahan harga yang valid wajib diisi.');
        }

        $last = $db->table('product_options')
            ->select('id_opsi')
            ->orderBy('id_opsi', 'DESC')
            ->get(1)
            ->getRowArray();
        $number = $last ? (int) substr($last['id_opsi'], 3) + 1 : 1;
        $id = 'OPT' . str_pad((string) $number, 7, '0', STR_PAD_LEFT);
        $maxOrder = $db->table('product_options')
            ->selectMax('urutan')
            ->where('id_produk', $productId)
            ->get()
            ->getRowArray();

        $db->table('product_options')->insert([
            'id_opsi' => $id,
            'id_produk' => $productId,
            'nama_opsi' => $name,
            'tambahan_harga' => $price,
            'urutan' => (int) ($maxOrder['urutan'] ?? 0) + 1,
            'aktif' => 1,
        ]);
        $this->syncProductOptionState($productId);
        $this->logActivity($productId, 'Tambah Varian', $product['nama'] . ' - ' . $name);

        return redirect()->to('/kasir/menu/edit/' . $productId)->with('success', 'Varian berhasil ditambahkan.');
    }

    public function update($optionId)
    {
        if ($redirect = $this->ensureKasir()) {
            return $redirect;
        }

        $db = \Config\Database::connect();
        $option = $db->table('product_options')->where('id_opsi', $optionId)->get()->getRowArray();
        $name = trim((string) $this->request->getPost('nama_opsi'));
        $price = filter_var($this->request->getPost('tambahan_harga'), FILTER_VALIDATE_INT);
        if (!$option || $name === '' || $price === false || $price < 0) {
            return redirect()->back()->with('error', 'Data varian tidak valid.');
        }
        $activeInput = $this->request->getPost('aktif');
        $active = $activeInput === null ? (int) $option['aktif'] : ($activeInput === '1' ? 1 : 0);

        $db->table('product_options')->where('id_opsi', $optionId)->update([
            'nama_opsi' => $name,
            'tambahan_harga' => $price,
            'aktif' => $active,
        ]);
        $product = $db->table('products')->where('id_produk', $option['id_produk'])->get()->getRowArray();
        $this->syncProductOptionState($option['id_produk']);
        $this->logActivity($option['id_produk'], 'Edit Varian', ($product['nama'] ?? $option['id_produk']) . ' - ' . $name);

        return redirect()->to('/kasir/menu/edit/' . $option['id_produk'])->with('success', 'Varian berhasil diperbarui.');
    }

    public function delete($optionId)
    {
        if ($redirect = $this->ensureKasir()) {
            return $redirect;
        }

        $db = \Config\Database::connect();
        $option = $db->table('product_options')->where('id_opsi', $optionId)->get()->getRowArray();
        if (!$option) {
            return redirect()->to('/kasir/menu')->with('error', 'Varian tidak ditemukan.');
        }

        $product = $db->table('products')->where('id_produk', $option['id_produk'])->get()->getRowArray();
        $db->table('product_options')->where('id_opsi', $optionId)->update(['aktif' => 0]);
        $this->syncProductOptionState($option['id_produk']);
        $this->logActivity($option['id_produk'], 'Nonaktifkan Varian', ($product['nama'] ?? $option['id_produk']) . ' - ' . $option['nama_opsi']);

        return redirect()->to('/kasir/menu/edit/' . $option['id_produk'])->with('success', 'Varian berhasil dinonaktifkan.');
    }

    public function activate($optionId)
    {
        if ($redirect = $this->ensureKasir()) {
            return $redirect;
        }

        $db = \Config\Database::connect();
        $option = $db->table('product_options')->where('id_opsi', $optionId)->get()->getRowArray();
        if (!$option) {
            return redirect()->to('/kasir/menu')->with('error', 'Varian tidak ditemukan.');
        }

        $product = $db->table('products')->where('id_produk', $option['id_produk'])->get()->getRowArray();
        $db->table('product_options')->where('id_opsi', $optionId)->update(['aktif' => 1]);
        $this->syncProductOptionState($option['id_produk']);
        $this->logActivity($option['id_produk'], 'Aktifkan Varian', ($product['nama'] ?? $option['id_produk']) . ' - ' . $option['nama_opsi']);

        return redirect()->to('/kasir/menu/edit/' . $option['id_produk'])->with('success', 'Varian berhasil diaktifkan kembali.');
    }
}
