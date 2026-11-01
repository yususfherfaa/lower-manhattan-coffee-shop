<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameCoffeeShopBrand extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('settings')) {
            return;
        }

        if ($this->db->fieldExists('nama', 'settings') && $this->db->fieldExists('value', 'settings')) {
            $this->db->table('settings')
                ->where('nama', 'nama_warkop')
                ->update(['value' => 'Lower Manhattan Coffee Shop']);
        }

        if ($this->db->fieldExists('nama', 'settings') && $this->db->fieldExists('value', 'settings')) {
            $this->db->table('settings')
                ->where('nama', 'logo')
                ->update(['value' => 'uploads/lower-manhattan-coffee-shop.svg']);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('settings')) {
            return;
        }

        if ($this->db->fieldExists('nama', 'settings') && $this->db->fieldExists('value', 'settings')) {
            $this->db->table('settings')
                ->where('nama', 'logo')
                ->where('value', 'uploads/lower-manhattan-coffee-shop.svg')
                ->update(['value' => 'uploads/default-logo.png']);
        }
    }
}
