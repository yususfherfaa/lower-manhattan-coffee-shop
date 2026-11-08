<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStoreIdentityAndQrisSettings extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('settings');

        $currentSettings = $builder->get()->getResultArray();
        $existingKeys = array_column($currentSettings, 'nama');

        $defaults = [
            'nama_toko' => function () use ($currentSettings) {
                foreach ($currentSettings as $s) {
                    if ($s['nama'] === 'nama_warkop' && !empty($s['value'])) {
                        return $s['value'];
                    }
                }
                return 'Lower Manhattan Coffee Shop';
            },
            'alamat_lengkap' => function () use ($currentSettings) {
                foreach ($currentSettings as $s) {
                    if ($s['nama'] === 'alamat' && !empty($s['value'])) {
                        return $s['value'];
                    }
                }
                return '';
            },
            'nmid' => function () {
                return '';
            },
            'qris_mode' => function () {
                return 'demo';
            },
        ];

        foreach ($defaults as $key => $resolver) {
            if (!in_array($key, $existingKeys, true)) {
                $last = $builder->orderBy('id_setting', 'DESC')->first();
                $next = $last ? ((int) substr($last['id_setting'], 3) + 1) : 1;
                $idSetting = 'SET' . str_pad($next, 3, '0', STR_PAD_LEFT);

                $builder->insert([
                    'id_setting' => $idSetting,
                    'nama'       => $key,
                    'value'      => $resolver(),
                ]);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('settings');

        $builder->whereIn('nama', ['nama_toko', 'alamat_lengkap', 'nmid', 'qris_mode'])->delete();
    }
}

