<?php

namespace App\Database\Migrations;

use App\Libraries\LowerManhattanProductDescriptions;
use CodeIgniter\Database\Migration;

class AddLowerManhattanProductDescriptions extends Migration
{
    public function up()
    {
        LowerManhattanProductDescriptions::fillMissing($this->db);
    }

    public function down()
    {
    }
}
