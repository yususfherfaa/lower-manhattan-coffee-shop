<?php

if (!function_exists('setting')) {
    function setting($key)
    {
        static $cache = [];

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $db = db_connect();

        $data = $db->table('settings')
            ->where('nama', $key)
            ->get()
            ->getRow();

        $cache[$key] = $data->value ?? null;

        return $cache[$key];
    }
}