<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationAPI extends BaseController
{
    public function getNotifications()
    {
        $notifModel = new NotificationModel();
        
        // Ambil notifikasi yang belum dibaca (unread), diurutkan terbaru
        $notifications = $notifModel
            ->where('status', 'unread')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        if ($notifications !== []) {
            $orderIds = array_column($notifications, 'id_order');
            $itemsByOrder = [];
            $items = \Config\Database::connect()
                ->table('order_items')
                ->select('id_order, nama, qty, opsi, suhu, catatan')
                ->whereIn('id_order', $orderIds)
                ->orderBy('id_items', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($items as $item) {
                $itemsByOrder[$item['id_order']][] = $item;
            }
            foreach ($notifications as &$notification) {
                $notification['items'] = $itemsByOrder[$notification['id_order']] ?? [];
            }
            unset($notification);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $notifications,
            'count' => count($notifications)
        ]);
    }

    public function markAsRead()
    {
        $notifModel = new NotificationModel();
        $id_notif = $this->request->getPost('id_notif');

        if (!$id_notif) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID notifikasi tidak ditemukan'
            ]);
        }

        $notifModel->update($id_notif, ['status' => 'read']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Notifikasi sudah dibaca'
        ]);
    }

    public function markAllAsRead()
    {
        $updated = \Config\Database::connect()
            ->table('notifications')
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        if (!$updated) {
            log_message('error', 'Unable to mark notifications as read.');
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Notifikasi gagal diperbarui.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Semua notifikasi sudah dibaca'
        ]);
    }
}

