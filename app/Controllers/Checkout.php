<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\CheckoutRules;

class Checkout extends BaseController
{
    public function index($id_meja)
    {
        $db = \Config\Database::connect();
        $cart = session()->get('cart_' . $id_meja) ?? [];
        if ($cart === []) {
            return redirect()->to('/cart/' . $id_meja)->with('error', 'Keranjang Anda masih kosong.');
        }

        $method = $this->request->getPost('metode_pembayaran') ?? 'cash';
        if (!is_string($method) || !CheckoutRules::isValidPaymentMethod($method)) {
            return redirect()->to('/cart/' . $id_meja)->with('error', 'Metode pembayaran tidak valid.');
        }
        $customerName = trim((string) $this->request->getPost('nama_pelanggan'));
        if ($customerName === '') {
            return redirect()->to('/cart/' . $id_meja)->with('error', 'Nama pemesan wajib diisi.');
        }

        $postedNotes = $this->request->getPost('catatan');
        if (!is_array($postedNotes)) {
            $postedNotes = [];
        }
        foreach ($cart as $key => &$cartItem) {
            $note = array_key_exists($key, $postedNotes)
                ? $postedNotes[$key]
                : ($cartItem['catatan'] ?? '');
            if (!is_string($note)) {
                return redirect()->to('/cart/' . $id_meja)->with('error', 'Catatan menu tidak valid.');
            }
            $note = trim($note);
            if (mb_strlen($note) > 500) {
                return redirect()->to('/cart/' . $id_meja)->with('error', 'Catatan menu maksimal 500 karakter.');
            }
            $cartItem['catatan'] = $note;
        }
        unset($cartItem);
        session()->set('cart_' . $id_meja, $cart);

        $items = [];
        $quantitiesByProduct = [];
        foreach ($cart as $key => $item) {
            $product = $db->table('products')
                ->where('id_produk', $item['id_produk'] ?? '')
                ->get()
                ->getRowArray();
            $qty = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);
            if (!$product || $qty === false || $qty < 1) {
                return redirect()->to('/cart/' . $id_meja)->with('error', 'Ada item keranjang yang tidak valid. Hapus item tersebut dan coba lagi.');
            }

            $option = null;
            if (!empty($product['punya_opsi'])) {
                $option = $db->table('product_options')
                    ->where('id_opsi', $item['id_opsi'] ?? '')
                    ->where('id_produk', $product['id_produk'])
                    ->where('aktif', 1)
                    ->get()
                    ->getRowArray();
                if (!$option) {
                    return redirect()->to('/cart/' . $id_meja)->with('error', 'Pilihan rasa untuk ' . $product['nama'] . ' sudah tidak tersedia. Silakan pilih ulang.');
                }
            } elseif (!empty($item['id_opsi'])) {
                return redirect()->to('/cart/' . $id_meja)->with('error', 'Pilihan varian pada keranjang tidak valid.');
            }

            $suhu = null;
            if (($product['suhu_tetap'] ?? null) === 'panas') {
                $suhu = 'panas';
            } elseif (($product['suhu_tetap'] ?? null) === 'dingin') {
                $suhu = 'es';
            } elseif (!empty($product['varian_suhu'])) {
                $suhu = in_array($item['suhu'] ?? null, ['panas', 'es'], true) ? $item['suhu'] : 'panas';
            }

            $price = (float) $product['harga'];
            if ($option) {
                $price += (float) $option['tambahan_harga'];
            } elseif ($suhu === 'es' && empty($product['suhu_tetap'])) {
                $price += (float) ($product['tambahan_harga_es'] ?? 0);
            }

            $item['id_produk'] = $product['id_produk'];
            $productName = catalog_product_display_name($product);
            $sizeLabel = catalog_product_size_label($product);
            $item['nama'] = $productName . ($sizeLabel !== null ? ' - ' . $sizeLabel : '');
            $item['ukuran_label'] = $sizeLabel;
            $item['kategori'] = $product['kategori'];
            $item['suhu'] = $suhu;
            $item['opsi'] = $option['nama_opsi'] ?? null;
            $item['id_opsi'] = $option['id_opsi'] ?? null;
            $item['catatan'] = trim((string) ($item['catatan'] ?? ''));
            $item['harga'] = $price;
            $item['qty'] = $qty;
            $items[$key] = $item;
            $quantitiesByProduct[$product['id_produk']] = ($quantitiesByProduct[$product['id_produk']] ?? 0) + $qty;
        }

        foreach ($quantitiesByProduct as $productId => $quantity) {
            $product = $db->table('products')->where('id_produk', $productId)->get()->getRowArray();
            if (!$product || !CheckoutRules::hasEnoughStock($quantity, (int) $product['stok'])) {
                return redirect()->to('/cart/' . $id_meja)->with('error', 'Stok salah satu menu berubah. Kurangi jumlah sebelum melanjutkan.');
            }
        }

        $total = array_sum(array_map(
            static fn(array $item): float => $item['harga'] * $item['qty'],
            $items
        ));

        if (!$db->transBegin()) {
            log_message('error', 'Checkout transaction could not start for table {tableId}.', ['tableId' => $id_meja]);
            return redirect()->to('/cart/' . $id_meja)->with('error', 'Pesanan gagal disimpan. Silakan coba lagi.');
        }

        $lastOrder = $db->query('SELECT id_order FROM orders ORDER BY id_order DESC LIMIT 1 FOR UPDATE')->getRowArray();
        $number = $lastOrder ? (int) substr($lastOrder['id_order'], 3) + 1 : 1;
        $orderId = 'ORD' . str_pad((string) $number, 7, '0', STR_PAD_LEFT);
        $orderToken = bin2hex(random_bytes(8));

        $db->table('orders')->insert([
            'id_order' => $orderId,
            'id_meja' => $id_meja,
            'nama_pelanggan' => $customerName,
            'metode_pembayaran' => $method,
            'total' => $total,
            'status' => 'menunggu_pembayaran',
            'token' => $orderToken,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $lastItem = $db->query('SELECT id_items FROM order_items ORDER BY id_items DESC LIMIT 1 FOR UPDATE')->getRowArray();
        $itemNumber = $lastItem ? (int) substr($lastItem['id_items'], 3) + 1 : 1;

        foreach ($items as $item) {
            $itemId = 'ITM' . str_pad((string) $itemNumber++, 7, '0', STR_PAD_LEFT);
            $db->table('order_items')->insert([
                'id_items' => $itemId,
                'id_order' => $orderId,
                'id_produk' => $item['id_produk'],
                'nama' => $item['nama'],
                'kategori' => $item['kategori'] ?? '',
                'suhu' => $item['suhu'],
                'opsi' => $item['opsi'],
                'catatan' => $item['catatan'],
                'harga' => $item['harga'],
                'qty' => $item['qty'],
                'subtotal' => $item['harga'] * $item['qty'],
            ]);
        }

        foreach ($quantitiesByProduct as $productId => $quantity) {
            $db->table('products')
                ->set('stok', 'stok - ' . (int) $quantity, false)
                ->where('id_produk', $productId)
                ->where('stok >=', $quantity)
                ->update();
            if ($db->affectedRows() !== 1) {
                $db->transRollback();
                log_message('warning', 'Checkout dibatalkan karena stok berubah untuk produk {productId}.', [
                    'productId' => $productId,
                ]);
                return redirect()->to('/cart/' . $id_meja)->with('error', 'Stok salah satu menu berubah. Silakan periksa kembali keranjang.');
            }
        }

        $db->table('notifications')->insert([
            'id_order' => $orderId,
            'id_meja' => $id_meja,
            'nama_pelanggan' => $customerName,
            'total' => $total,
            'status' => 'unread',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$db->transStatus()) {
            $db->transRollback();
            log_message('error', 'Checkout transaction failed for table {tableId}.', ['tableId' => $id_meja]);
            return redirect()->to('/cart/' . $id_meja)->with('error', 'Pesanan gagal disimpan. Stok tidak berubah; silakan coba lagi.');
        }
        if (!$db->transCommit()) {
            $db->transRollback();
            log_message('error', 'Checkout transaction could not commit for table {tableId}.', ['tableId' => $id_meja]);
            return redirect()->to('/cart/' . $id_meja)->with('error', 'Pesanan gagal disimpan. Silakan coba lagi.');
        }

        session()->remove('cart_' . $id_meja);
        session()->set('order_access_' . $orderId, $orderToken);

        if ($method === 'qris') {
            return redirect()->to('/payment-qris/' . $orderId . '/' . $id_meja . '?token=' . $orderToken);
        }

        return redirect()->to('/order-success/' . $orderId . '/' . $id_meja . '?token=' . $orderToken);
    }
}
