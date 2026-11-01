<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id_notif';

    protected $allowedFields = [
        'id_notif',
        'id_order',
        'id_meja',
        'nama_pelanggan',
        'total',
        'status',
        'created_at'
    ];
}
