<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Kasir extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $kasirBuilder = $db->table('users')->where('role', 'kasir');
        if ($db->fieldExists('aktif', 'users')) {
            $kasirBuilder->where('aktif', 1);
        }
        $kasir = $kasirBuilder->get()->getResultArray();

        return view('admin/kasir/list', [
            'kasir' => $kasir
        ]);
    }

    public function add()
    {
        return view('admin/kasir/add');
    }

    public function save()
    {
        if (!$this->validate([
            'nama' => 'required|max_length[100]',
            'username' => 'required|alpha_numeric_punct|min_length[3]|max_length[50]|is_unique[users.username]',
            'password' => 'required|min_length[8]|max_length[255]',
        ])) {
            return redirect()->back()->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $db = \Config\Database::connect();

        if (!$db->transBegin()) {
            return redirect()->back()->with('error', 'Kasir gagal disimpan. Silakan coba lagi.');
        }

        // ambil ID terakhir
        $last = $db->query("SELECT id_user FROM users WHERE id_user LIKE 'USR%' ORDER BY id_user DESC LIMIT 1 FOR UPDATE")->getRowArray();

        if ($last) {
            $num = (int) substr($last['id_user'], 3);
            $num++;
        } else {
            $num = 1;
        }

        // seragamkan format ID: USR + 7 digit angka
        $id_user = 'USR' . str_pad($num, 7, '0', STR_PAD_LEFT);

        // insert kasir
        $inserted = $db->table('users')->insert([

            'id_user'     => $id_user,

            'nama_kasir'  => $this->request->getPost('nama'),

            'username'    => $this->request->getPost('username'),

            'password'    => password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            ),

            'role'        => 'kasir'
        ]);

        if (!$inserted || !$db->transStatus() || !$db->transCommit()) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Kasir gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->to('/admin/kasir/list');
    }

    public function edit($id)
    {
        $db = \Config\Database::connect();

        $kasir = $db->table('users')
            ->where('id_user', $id)
            ->where('role', 'kasir')
            ->get()
            ->getRowArray();

        if (!$kasir) {
            return redirect()->to('/admin/kasir/list');
        }

        return view('admin/kasir/edit', [
            'kasir' => $kasir
        ]);
    }

    public function update($id)
    {
        if (!preg_match('/\A[A-Za-z0-9_-]+\z/', $id)) {
            return redirect()->to('/admin/kasir/list')->with('error', 'ID kasir tidak valid.');
        }

        if (!$this->validate([
            'nama' => 'required|max_length[100]',
            'username' => "required|alpha_numeric_punct|min_length[3]|max_length[50]|is_unique[users.username,id_user,{$id}]",
            'password' => 'permit_empty|min_length[8]|max_length[255]',
        ])) {
            return redirect()->back()->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $db = \Config\Database::connect();

        $data = [

            'nama_kasir' => $this->request->getPost('nama'),

            'username'   => $this->request->getPost('username')

        ];

        // update password jika diisi
        $password = $this->request->getPost('password');

        if (!empty($password)) {

            $data['password'] = password_hash(
                $password,
                PASSWORD_DEFAULT
            );
        }

        $db->table('users')
            ->where('id_user', $id)
            ->update($data);

        return redirect()->to('/admin/kasir/list');
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();

        if ($db->fieldExists('aktif', 'users')) {
            $db->table('users')
                ->where('id_user', $id)
                ->update(['aktif' => 0]);
        } else {
            $db->table('users')
                ->where('id_user', $id)
                ->delete();
        }

        return redirect()->to('/admin/kasir/list');
    }
}
