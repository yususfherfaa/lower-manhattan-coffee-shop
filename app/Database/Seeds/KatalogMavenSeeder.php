<?php

namespace App\Database\Seeds;

use App\Libraries\LowerManhattanProductDescriptions;
use CodeIgniter\Database\Seeder;

/**
 * Seeder Katalog 80 Produk Dataset Maven Roasters
 * Menyinkronkan katalog dan agregat pada database aktif yang dikonfigurasi.
 */
class KatalogMavenSeeder extends Seeder
{
    public function run()
    {
        foreach (['agg_keramaian_jam', 'agg_kategori_segmen'] as $table) {
            if (!$this->db->tableExists($table)) {
                throw new \RuntimeException("Tabel {$table} belum tersedia. Jalankan php spark migrate sebelum seed.");
            }
        }

        if (!$this->db->transBegin()) {
            throw new \RuntimeException('Tidak dapat memulai transaksi sinkronisasi dataset.');
        }

        try {
            foreach (['02_seed_katalog.sql', '03_seed_agregat.sql'] as $file) {
                $sqlPath = ROOTPATH . 'database/planB/' . $file;
                if (!is_file($sqlPath)) {
                    throw new \RuntimeException("File seed dataset tidak ditemukan: {$sqlPath}");
                }

                $sql = file_get_contents($sqlPath);
                if ($sql === false) {
                    throw new \RuntimeException("Tidak dapat membaca file seed dataset: {$sqlPath}");
                }

                $sqlClean = preg_replace('/--.*$/m', '', $sql);
                $sqlClean = preg_replace('/\/\*.*?\*\//s', '', $sqlClean);
                if ($sqlClean === null) {
                    throw new \RuntimeException("Tidak dapat membersihkan komentar SQL: {$file}");
                }

                foreach (array_filter(array_map('trim', explode(';', $sqlClean))) as $statement) {
                    if ($this->db->query($statement) === false) {
                        throw new \RuntimeException("Gagal menjalankan pernyataan SQL pada {$file}.");
                    }
                }
            }

            LowerManhattanProductDescriptions::fillMissing($this->db);

            $hasPlanBProduct = $this->db->table('products')
                ->like('id_produk', 'PRDM%')
                ->countAllResults() > 0;
            if ($hasPlanBProduct) {
                $updated = $this->db->table('products')
                    ->where('id_produk NOT LIKE', 'PRDM%')
                    ->update(['aktif' => 0]);
                if (!$updated) {
                    throw new \RuntimeException('Gagal menonaktifkan katalog lama.');
                }
            }

            if (!$this->db->transCommit()) {
                throw new \RuntimeException('Gagal menyimpan perubahan dataset.');
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        echo "Katalog dan agregat dataset Lower Manhattan berhasil disinkronkan.\n";
    }
}
