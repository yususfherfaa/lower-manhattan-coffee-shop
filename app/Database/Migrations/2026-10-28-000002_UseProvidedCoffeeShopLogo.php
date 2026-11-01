<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UseProvidedCoffeeShopLogo extends Migration
{
    private const LOGO_PATH = 'uploads/manhattan-coffee-shop-logo.png';

    public function up()
    {
        if (!$this->db->tableExists('settings')
            || !$this->db->fieldExists('nama', 'settings')
            || !$this->db->fieldExists('value', 'settings')) {
            return;
        }

        $this->db->table('settings')
            ->where('nama', 'logo')
            ->update(['value' => self::LOGO_PATH]);
    }

    public function down()
    {
        if (!$this->db->tableExists('settings')
            || !$this->db->fieldExists('nama', 'settings')
            || !$this->db->fieldExists('value', 'settings')) {
            return;
        }

        $this->db->table('settings')
            ->where('nama', 'logo')
            ->where('value', self::LOGO_PATH)
            ->update(['value' => 'uploads/lower-manhattan-coffee-shop.svg']);
    }
}
