<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'id_setting';

    protected $allowedFields = [
        'id_setting',
        'nama',
        'value'
    ];
}