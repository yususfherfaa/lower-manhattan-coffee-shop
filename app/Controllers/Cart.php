<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Cart extends BaseController
{
    private function suhuValid(?string $suhu): ?string
    {
        $suhu = strtolower((string) $suhu);
        return in_array($suhu, ['panas', 'es'], true) ? $suhu : null;
    }

    private function totalQtyProduk(array $cart, string $id_produk): int
    {
        $total = 0;
        foreach ($cart as $item) {
            if (($item['id_produk'] ?? null) === $id_produk) {
                $total += (int) ($item['qty'] ?? 0);
            }
        }
        return $total;
    }

    private function kembaliKeMenu(string $idMeja, string $pesan)
    {
        return redirect()->to('/order/' . $idMeja)->with('error', $pesan);
    }

    public function add($id_produk, $id_meja)
    {
        $db = \Config\Database::connect();
        $produk = $db->table('products')
            ->where('id_produk', $id_produk)
            ->get()
            ->getRowArray();

        if (!$produk || (int) $produk['stok'] <= 0) {
            return $this->kembaliKeMenu($id_meja, 'Menu sedang habis atau tidak tersedia.');
        }

        $opsi = null;
        $idOpsi = trim((string) $this->request->getGet('opsi'));
        $labelOpsi = in_array($produk['kategori_dataset'] ?? $produk['kategori'], ['Bakery', 'Branded', 'Coffee beans', 'Flavours', 'Loose Tea', 'Packaged Chocolate'], true)
            ? 'Pilih varian'
            : ($produk['label_opsi'] ?: 'Pilih varian');
        if (!empty($produk['punya_opsi'])) {
            if ($idOpsi === '') {
                return $this->kembaliKeMenu($id_meja, 'Silakan ' . strtolower($labelOpsi) . ' terlebih dahulu.');
            }

            $opsi = $db->table('product_options')
                ->where('id_opsi', $idOpsi)
                ->where('id_produk', $id_produk)
                ->where('aktif', 1)
                ->get()
                ->getRowArray();
            if (!$opsi) {
                return $this->kembaliKeMenu($id_meja, 'Varian yang dipilih sudah tidak tersedia. Silakan pilih kembali.');
            }
        } elseif ($idOpsi !== '') {
            return $this->kembaliKeMenu($id_meja, 'Menu ini tidak memiliki pilihan varian.');
        }

        $suhuTetap = $produk['suhu_tetap'] ?? null;
        if ($suhuTetap === 'panas' || $suhuTetap === 'dingin') {
            $suhu = $suhuTetap === 'dingin' ? 'es' : 'panas';
        } elseif (!empty($produk['varian_suhu'])) {
            $suhu = $this->suhuValid($this->request->getGet('suhu')) ?? 'panas';
        } else {
            $suhu = null;
        }

        $key = $id_produk;
        if ($suhu !== null) {
            $key .= '_' . $suhu;
        }
        if ($opsi) {
            $key .= '_' . $opsi['id_opsi'];
        }

        $cart = session()->get('cart_' . $id_meja) ?? [];
        $jumlah = filter_var($this->request->getGet('qty') ?? 1, FILTER_VALIDATE_INT);
        $jumlah = $jumlah === false ? 1 : max(1, min(50, $jumlah));
        if ($this->totalQtyProduk($cart, $id_produk) + $jumlah > (int) $produk['stok']) {
            return redirect()->to('/cart/' . $id_meja)->with('error', 'Jumlah menu melebihi stok yang tersedia.');
        }

        $harga = (float) $produk['harga'];
        if ($opsi) {
            $harga += (float) $opsi['tambahan_harga'];
        } elseif ($suhu === 'es' && empty($produk['suhu_tetap'])) {
            $harga += (float) ($produk['tambahan_harga_es'] ?? 0);
        }

        if (isset($cart[$key])) {
            $cart[$key]['qty'] += $jumlah;
            $cart[$key]['nama'] = catalog_product_display_name($produk);
            $cart[$key]['ukuran_label'] = catalog_product_size_label($produk);
        } else {
            $cart[$key] = [
                'id_produk' => $produk['id_produk'],
                'nama' => catalog_product_display_name($produk),
                'ukuran_label' => catalog_product_size_label($produk),
                'kategori' => $produk['kategori'],
                'kategori_dataset' => $produk['kategori_dataset'] ?? $produk['kategori'],
                'suhu' => $suhu,
                'opsi' => $opsi['nama_opsi'] ?? null,
                'id_opsi' => $opsi['id_opsi'] ?? null,
                'label_opsi' => $labelOpsi,
                'catatan' => '',
                'harga' => $harga,
                'qty' => $jumlah,
                'key' => $key,
            ];
        }

        session()->set('cart_' . $id_meja, $cart);
        return redirect()->to('/cart/' . $id_meja);
    }

    public function index($id_meja)
    {
        $cart = session()->get('cart_' . $id_meja) ?? [];
        $items = array_values($cart);
        $total = 0;

        foreach ($items as $item) {
            $total += $item['harga'] * $item['qty'];
        }

        return view('customer/cart', [
            'items' => $items,
            'total' => $total,
            'id_meja' => $id_meja,
        ]);
    }

    public function note($id_meja, $key)
    {
        $sessionKey = 'cart_' . $id_meja;
        $cart = session()->get($sessionKey) ?? [];
        if (!isset($cart[$key])) {
            return redirect()->to('/cart/' . $id_meja)->with('error', 'Item keranjang tidak ditemukan.');
        }

        $note = trim((string) $this->request->getPost('catatan'));
        if (mb_strlen($note) > 500) {
            return redirect()->to('/cart/' . $id_meja)->with('error', 'Catatan menu maksimal 500 karakter.');
        }

        $cart[$key]['catatan'] = $note;
        session()->set($sessionKey, $cart);
        return redirect()->to('/cart/' . $id_meja)->with('success', 'Catatan menu berhasil disimpan.');
    }

    public function qty($id_meja, $key, $type)
    {
        $sessionKey = 'cart_' . $id_meja;
        $cart = session()->get($sessionKey) ?? [];

        if (isset($cart[$key])) {
            $idProduk = $cart[$key]['id_produk'];
            if ($type === 'plus') {
                $produk = \Config\Database::connect()->table('products')
                    ->where('id_produk', $idProduk)
                    ->get()
                    ->getRowArray();
                if ($produk && $this->totalQtyProduk($cart, $idProduk) < (int) $produk['stok']) {
                    $cart[$key]['qty']++;
                }
            } elseif ($type === 'minus') {
                $cart[$key]['qty']--;
                if ($cart[$key]['qty'] <= 0) {
                    unset($cart[$key]);
                }
            }
        }

        session()->set($sessionKey, $cart);
        return redirect()->to('/cart/' . $id_meja);
    }

    public function remove($id_meja, $key)
    {
        $sessionKey = 'cart_' . $id_meja;
        $cart = session()->get($sessionKey) ?? [];
        if (isset($cart[$key])) {
            unset($cart[$key]);
        }
        session()->set($sessionKey, $cart);
        return redirect()->to('/cart/' . $id_meja);
    }

    public function reset($id_meja)
    {
        session()->remove('cart_' . $id_meja);
        return redirect()->to('/cart/' . $id_meja);
    }
}
