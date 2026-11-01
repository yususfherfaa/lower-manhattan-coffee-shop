"""Ekspor transaksi lunas untuk evaluasi ML (koneksi hanya-baca)."""

import os
from datetime import date, datetime, timezone
from zoneinfo import ZoneInfo

import pandas as pd
import pymysql


COLUMNS = [
    'tanggal', 'hari_ke', 'jam', 'menit', 'id_produk', 'nama_produk',
    'qty', 'segmen', 'is_weekend',
]


def export_from_db(output_path=None):
    """Ambil item pesanan lunas, tanpa menjalankan statement perubahan data."""
    required = ('DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME')
    missing = [name for name in required if not os.getenv(name)]
    if missing:
        raise RuntimeError('Env koneksi DB belum lengkap: ' + ', '.join(missing))

    conn = pymysql.connect(
        host=os.environ['DB_HOST'], port=int(os.getenv('DB_PORT', '3306')),
        user=os.environ['DB_USER'], password=os.environ['DB_PASSWORD'],
        database=os.environ['DB_NAME'], charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor, autocommit=False,
        read_timeout=30, write_timeout=30,
        init_command="SET time_zone = '+00:00'",
    )
    try:
        with conn.cursor() as cursor:
            cursor.execute('SET TRANSACTION READ ONLY')
            cursor.execute('START TRANSACTION READ ONLY')
            cursor.execute("""
                SELECT o.created_at AS waktu, oi.id_produk,
                       oi.nama AS nama_produk, oi.qty
                FROM orders o
                INNER JOIN order_items oi ON oi.id_order = o.id_order
                WHERE o.status = %s
                ORDER BY o.created_at ASC, o.id_order ASC, oi.id_items ASC
            """, ('lunas',))
            rows = cursor.fetchall()
        conn.rollback()
    finally:
        conn.close()

    jakarta = ZoneInfo('Asia/Jakarta')
    records = []
    for row in rows:
        stamp = row['waktu']
        if not isinstance(stamp, datetime):
            stamp = datetime.fromisoformat(str(stamp))
        # TIMESTAMP MariaDB lazim dikembalikan naive pada zona server; anggap UTC
        # agar hasil selalu ditafsirkan konsisten lalu konversi ke waktu Jakarta.
        if stamp.tzinfo is None:
            stamp = stamp.replace(tzinfo=timezone.utc)
        local = stamp.astimezone(jakarta)
        records.append({
            'tanggal_dt': local.date(), 'jam': local.hour, 'menit': local.minute,
            'id_produk': str(row['id_produk']), 'nama_produk': row['nama_produk'],
            'qty': int(row['qty'] or 0),
            'segmen': ('pagi' if 5 <= local.hour <= 10 else
                       'siang' if 11 <= local.hour <= 14 else
                       'sore' if 15 <= local.hour <= 17 else 'malam'),
            'is_weekend': int(local.weekday() >= 5),
        })

    df = pd.DataFrame(records)
    if df.empty:
        return pd.DataFrame(columns=COLUMNS)
    first_day = df['tanggal_dt'].min()
    df['tanggal'] = df['tanggal_dt'].map(date.isoformat)
    df['hari_ke'] = (df['tanggal_dt'] - first_day).map(lambda delta: delta.days)
    df = df[COLUMNS]
    if output_path:
        df.to_csv(output_path, index=False)
    return df


if __name__ == '__main__':
    target = os.path.join(os.path.dirname(__file__), 'dataset_db.csv')
    exported = export_from_db(target)
    print(f"Ekspor selesai: {len(exported):,} baris -> {target}")
