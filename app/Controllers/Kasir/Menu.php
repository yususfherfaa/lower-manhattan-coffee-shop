<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\SettingModel;

class Menu extends BaseController
{
    private const KATEGORI_TETAP = [
        'Bakery',
        'Branded',
        'Coffee',
        'Coffee beans',
        'Drinking Chocolate',
        'Flavours',
        'Loose Tea',
        'Packaged Chocolate',
        'Tea',
    ];

    private function normalizeKategori($kategori)
    {
        $kategori = trim((string) $kategori);
        return in_array($kategori, self::KATEGORI_TETAP, true) ? $kategori : '';
    }

    private function isDrinkCategory(string $kategori): bool
    {
        return in_array($kategori, ['Coffee', 'Drinking Chocolate', 'Tea'], true);
    }

    private function getKategoriSuggestions(ProductModel $productModel): array
    {
        return self::KATEGORI_TETAP;
    }

    private function validateProductForm(): bool
    {
        $rules = [
            'nama' => 'required|max_length[200]',
            'kategori' => 'required|in_list[' . implode(',', self::KATEGORI_TETAP) . ']',
            'harga' => 'required|numeric|greater_than_equal_to[0]',
            'stok' => 'required|integer|greater_than_equal_to[0]',
            'deskripsi' => 'required|string|max_length[255]',
            'tambahan_harga_es' => 'permit_empty|numeric|greater_than_equal_to[0]',
        ];
        $gambar = $this->request->getFile('gambar');

        if ($gambar && $gambar->getError() !== UPLOAD_ERR_NO_FILE) {
            $rules['gambar'] = 'uploaded[gambar]|is_image[gambar]|mime_in[gambar,image/jpeg,image/png,image/webp]|max_size[gambar,2048]';
        }

        return $this->validate($rules);
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/');
        }

        $db = \Config\Database::connect();
        $productModel = new ProductModel();

        $settingModel = new SettingModel();

        $settings = [];

        foreach ($settingModel->findAll() as $s) {
            $settings[$s['nama']] = $s['value'];
        }

        $hasAktif = $db->fieldExists('aktif', 'products');
        $builder = $productModel->orderBy('kategori', 'ASC')->orderBy('nama', 'ASC');
        if ($hasAktif) {
            $builder->where('aktif', 1);
        }

        return view('kasir/menu/index', [
            'products' => $builder->findAll(),
            'kategori_suggestions' => $this->getKategoriSuggestions($productModel),
            'nama_warkop' => $settings['nama_warkop'] ?? 'Lower Manhattan Coffee Shop'
        ]);
    }

    public function add()
    {
        if (!$this->validateProductForm()) {
            return redirect()->back()->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $db = \Config\Database::connect();
        $productModel = new ProductModel();

        $kategori = $this->normalizeKategori($this->request->getPost('kategori'));
        if ($kategori === '') {
            return redirect()->back()->with('error', 'Pilih kategori menu yang tersedia.');
        }
        $isDrink = $this->isDrinkCategory($kategori);
        $varianSuhu = $isDrink && (bool) $this->request->getPost('varian_suhu');
        $suhuTetap = $isDrink ? ($this->request->getPost('suhu_tetap') ?? '') : '';
        if (!in_array($suhuTetap, ['', 'panas', 'dingin'], true)) {
            return redirect()->back()->with('error', 'Pilihan suhu menu tidak valid.');
        }
        if ($suhuTetap !== '') {
            $varianSuhu = false;
        }
        $deskripsi = trim((string) $this->request->getPost('deskripsi'));
        if (mb_strlen($deskripsi) > 255) {
            return redirect()->back()->withInput()->with('error', 'Deskripsi menu maksimal 255 karakter.');
        }

        $file = $this->request->getFile('gambar');
        $gambar = null;

        if (!$db->transBegin()) {
            return redirect()->back()->with('error', 'Menu gagal disimpan. Silakan coba lagi.');
        }

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $gambar = $file->getRandomName();
            $file->move(FCPATH . 'uploads', $gambar);
        }

        $lastProductNumber = $db->query(
            "SELECT MAX(CAST(SUBSTRING(id_produk, 4) AS UNSIGNED)) AS max_num
             FROM products WHERE id_produk REGEXP '^PRD[0-9]{7}$' FOR UPDATE"
        )->getRowArray();
        $num = (int) ($lastProductNumber['max_num'] ?? 0) + 1;

        $id_produk = 'PRD' .
            str_pad($num, 7, '0', STR_PAD_LEFT);

        $inserted = $productModel->insert([
            'id_produk' => $id_produk,
            'nama'      => $this->request->getPost('nama'),
            'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
            'kategori'  => $kategori,
            'harga'     => $this->request->getPost('harga'),
            'stok'      => $this->request->getPost('stok'),
            'varian_suhu' => $varianSuhu ? 1 : 0,
            'tambahan_harga_es' => $isDrink ? (float) ($this->request->getPost('tambahan_harga_es') ?? 0) : 0,
            'gambar'    => $gambar ?? '',
            'suhu_tetap' => $suhuTetap !== '' ? $suhuTetap : null,
            'punya_opsi' => 0,
            'label_opsi' => null,
        ]);

        // Log aktivitas
        $lastData = $db->query('SELECT id_data FROM data_products ORDER BY id_data DESC LIMIT 1 FOR UPDATE')->getRowArray();

        $numData = $lastData
            ? ((int) substr($lastData['id_data'], 3) + 1)
            : 1;

        $id_data = 'DAT' .
            str_pad($numData, 7, '0', STR_PAD_LEFT);

        $logged = $db->table('data_products')->insert([
            'id_data'      => $id_data,
            'aktivitas'    => 'Tambah Produk',
            'id_produk'    => $id_produk,
            'nama_produk'  => $this->request->getPost('nama'),
            'nama_kasir'   => session()->get('nama_kasir')
        ]);

        if (!$inserted || !$logged || !$db->transStatus() || !$db->transCommit()) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Menu gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->to('/kasir/menu');
    }

    public function edit($id)
    {
        $productModel = new ProductModel();

        $categories = $productModel
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori', 'ASC')
            ->findColumn('kategori');

        return view('kasir/menu/edit', [
            'product' => $productModel->find($id),
            'categories' => $categories,
            'options' => \Config\Database::connect()->table('product_options')
                ->where('id_produk', $id)
                ->orderBy('urutan', 'ASC')
                ->get()
                ->getResultArray(),
        ]);
    }

    public function update($id)
    {
        if (!$this->validateProductForm()) {
            return redirect()->back()->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $db = \Config\Database::connect();
        $productModel = new ProductModel();

        $kategori = $this->normalizeKategori($this->request->getPost('kategori'));
        if ($kategori === '') {
            return redirect()->back()->with('error', 'Pilih kategori menu yang tersedia.');
        }
        $isDrink = $this->isDrinkCategory($kategori);
        $varianSuhu = $isDrink && (bool) $this->request->getPost('varian_suhu');
        $suhuTetap = $isDrink ? ($this->request->getPost('suhu_tetap') ?? '') : '';
        if (!in_array($suhuTetap, ['', 'panas', 'dingin'], true)) {
            return redirect()->back()->with('error', 'Pilihan suhu menu tidak valid.');
        }
        if ($suhuTetap !== '') {
            $varianSuhu = false;
        }
        $deskripsi = trim((string) $this->request->getPost('deskripsi'));
        if (mb_strlen($deskripsi) > 255) {
            return redirect()->back()->withInput()->with('error', 'Deskripsi menu maksimal 255 karakter.');
        }

        $data = [
            'nama'     => $this->request->getPost('nama'),
            'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
            'kategori' => $kategori,
            'harga'    => $this->request->getPost('harga'),
            'stok'     => $this->request->getPost('stok'),
            'varian_suhu' => $varianSuhu ? 1 : 0,
            'tambahan_harga_es' => $isDrink ? (float) ($this->request->getPost('tambahan_harga_es') ?? 0) : 0,
            'suhu_tetap' => $suhuTetap !== '' ? $suhuTetap : null,
        ];

        if (!$db->transBegin()) {
            return redirect()->back()->with('error', 'Menu gagal diperbarui. Silakan coba lagi.');
        }

        $file = $this->request->getFile('gambar');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $gambar = $file->getRandomName();
            $file->move(FCPATH . 'uploads', $gambar);
            $data['gambar'] = $gambar;
        }

        $updated = $productModel->update($id, $data);

        $lastData = $db->query('SELECT id_data FROM data_products ORDER BY id_data DESC LIMIT 1 FOR UPDATE')->getRowArray();

        $numData = $lastData
            ? ((int) substr($lastData['id_data'], 3) + 1)
            : 1;

        $id_data = 'DAT' .
            str_pad($numData, 7, '0', STR_PAD_LEFT);

        $logged = $db->table('data_products')->insert([
            'id_data'      => $id_data,
            'aktivitas'    => 'Edit Produk',
            'id_produk'    => $id,
            'nama_produk'  => $this->request->getPost('nama'),
            'nama_kasir'   => session()->get('nama_kasir')
        ]);

        if (!$updated || !$logged || !$db->transStatus() || !$db->transCommit()) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Menu gagal diperbarui. Silakan coba lagi.');
        }

        return redirect()->to('/kasir/menu');
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        $productModel = new ProductModel();

        $transactionStarted = false;

        try {
            $db->transBegin();
            $transactionStarted = true;
            $produk = $productModel->find($id);

            if ($produk) {
                $lastData = $db->query('SELECT id_data FROM data_products ORDER BY id_data DESC LIMIT 1 FOR UPDATE')->getRowArray();

                $numData = $lastData
                    ? ((int) substr($lastData['id_data'], 3) + 1)
                    : 1;

                $id_data = 'DAT' . str_pad($numData, 7, '0', STR_PAD_LEFT);

                $db->table('data_products')->insert([
                    'id_data'      => $id_data,
                    'aktivitas'    => 'Nonaktifkan Produk',
                    'id_produk'    => $produk['id_produk'],
                    'nama_produk'  => $produk['nama'],
                    'nama_kasir'   => session()->get('nama_kasir')
                ]);

                if ($db->fieldExists('aktif', 'products')) {
                    $productModel->update($id, ['aktif' => 0]);
                } else {
                    $productModel->delete($id);
                }
            }

            if (!$db->transStatus()) {
                throw new \RuntimeException('The product deletion transaction failed.');
            }

            $db->transCommit();
        } catch (\Throwable $exception) {
            if ($transactionStarted) {
                $db->transRollback();
            }
            log_message('error', 'Failed to delete product ' . $id . ': ' . $exception->getMessage());

            return redirect()->to('/kasir/menu')
                ->with('error', 'Menu gagal dihapus. Periksa koneksi database atau keterkaitan pesanan.');
        }

        return redirect()->to('/kasir/menu');
    }
}
