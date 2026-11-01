"""
generate_dataset.py
===================
Generator dataset sintetis pola pemesanan Lower Manhattan Coffee Shop berbasis waktu.
Dibuat sesuai spesifikasi PR-01 untuk menggantikan dataset lama yang berlabel sirkular.

Parameter Simulasi:
- Durasi            : 60 hari simulasi mulai 2026-08-03 (Senin) sampai 2026-10-01
- Seed              : 42 (numpy.random.default_rng(42))
- Tarif Dasar Jam   : HOUR_RATE = [4,3,2,2,3,8,12,16,15,12,9,11,16,14,9,7,7,8,12,15,16,14,11,7] (24 jam)
- Pengali Aktivitas : 0.6 untuk hari kerja, 0.6 * 1.25 = 0.75 untuk akhir pekan (Sabtu & Minggu)
- Jumlah Transaksi  : per (hari, jam) ~ Poisson(lambda)
- Noise Menu        : Peluang 0.12 seragam dari seluruh 41 menu aktif
- Bobot Segmen      : Berdasarkan slot pemilik di RekomendasiAPI.php (fallbackRuleBased):
                      * Bobot slot: [10, 9, 8, 7, 6] sesuai urutan slot 1-5
                      * Anggota kelompok menu menggunakan bobot slotnya
                      * Menu lain di luar slot segmen berbobot 1 (dinormalisasi)
- Batas Segmen      :
                      * Pagi  : 05.00 - 10.59 (05.00 - 10)
                      * Siang : 11.00 - 14.59 (11.00 - 14)
                      * Sore  : 15.00 - 17.59 (15.00 - 17)
                      * Malam : 18.00 - 04.59 (18.00 - 04)
- Blending Batas    : Dalam rentang +-30 menit dari batas segmen (05.00, 11.00, 15.00, 18.00):
                      a = 0.5 * (1 - jarak_menit / 30)
                      bobot_campuran = (1 - a) * bobot_segmen_saat_ini + a * bobot_segmen_tetangga
- Qty               : 1-3 porsi (bobot [0.70, 0.20, 0.10])
- Kolom Output      : tanggal, hari_ke, jam, menit, id_produk, nama_produk, qty, segmen, is_weekend
"""

import os
from datetime import datetime, timedelta
import numpy as np
import pandas as pd

BASE_ML = os.path.dirname(os.path.abspath(__file__))

# ── 1. DEFINISI KATALOG MENU (41 PRODUK COFFEE SHOP) ─────────────────────────
MENU = {
    'PRD0000001': ('Nasi Goreng', 'Makanan Berat', 16000),
    'PRD0000002': ('Indomie Goreng Telor', 'Makanan Berat', 14000),
    'PRD0000003': ('Indomie Rebus Telor', 'Makanan Berat', 14000),
    'PRD0000004': ('Bubur Kacang Ijo', 'Makanan Berat', 12000),
    'PRD0000005': ('Nasi Kucing', 'Makanan Berat', 8000),
    'PRD0000006': ('Pancong Keju', 'Cemilan', 10000),
    'PRD0000007': ('Roti Bakar Coklat', 'Cemilan', 8000),
    'PRD0000008': ('Kentang Goreng', 'Cemilan', 10000),
    'PRD0000009': ('Bakwan 1 Pcs', 'Cemilan', 2000),
    'PRD0000010': ('Tempe 1 Pcs', 'Cemilan', 2000),
    'PRD0000011': ('Tahu 1 Pcs', 'Cemilan', 2000),
    'PRD0000012': ('Dimsum Mentai', 'Cemilan', 15000),
    'PRD0000013': ('Sate Telor 1 Pcs', 'Cemilan', 3000),
    'PRD0000014': ('Sate Usus 1 Pcs', 'Cemilan', 3000),
    'PRD0000015': ('Sate Ati Ampela 1 Pcs', 'Cemilan', 3000),
    'PRD0000016': ('Milo', 'Minuman Non Kopi', 7000),
    'PRD0000017': ('Jeruk', 'Minuman Non Kopi', 6000),
    'PRD0000018': ('Teh Manis', 'Minuman Non Kopi', 5000),
    'PRD0000019': ('Lemon Tea', 'Minuman Non Kopi', 7000),
    'PRD0000021': ('Susu Putih', 'Minuman Non Kopi', 6000),
    'PRD0000022': ('Air Mineral', 'Minuman Non Kopi', 2000),
    'PRD0000023': ('Capucinno', 'Minuman Kopi', 8000),
    'PRD0000024': ('Kopi Susu', 'Minuman Kopi', 5000),
    'PRD0000025': ('Kopi Hitam', 'Minuman Kopi', 3000),
    'PRD0000026': ('Nasi Kuning', 'Makanan Berat', 10000),
    'PRD0000027': ('Nasi Uduk', 'Makanan Berat', 10000),
    'PRD0000028': ('Lontong Sayur', 'Makanan Berat', 10000),
    'PRD0000029': ('Bubur Ayam', 'Makanan Berat', 12000),
    'PRD0000030': ('Ayam Geprek + Nasi', 'Makanan Berat', 15000),
    'PRD0000031': ('ABC Susu', 'Kopi Panas', 5000),
    'PRD0000032': ('Indocafe', 'Kopi Panas', 5000),
    'PRD0000033': ('Kapal Api Special Mix', 'Kopi Panas', 5000),
    'PRD0000034': ('Good Day Biasa', 'Kopi Panas', 6000),
    'PRD0000035': ('Luwak White Coffee', 'Kopi Panas', 5000),
    'PRD0000036': ('Torabika Creamy Latte', 'Kopi Panas', 6000),
    'PRD0000037': ('Good Day Freeze', 'Es Kopi', 8000),
    'PRD0000038': ('Nescafe Ice Roast', 'Es Kopi', 7000),
    'PRD0000039': ('ABC Klepon', 'Es Kopi', 8000),
    'PRD0000040': ('Good Day Biasa', 'Es Kopi', 7000),
    'PRD0000041': ('Tora Cafe', 'Es Kopi', 7000),
    'PRD0000042': ('Es Nutrisari', 'Es Nutrisari', 5000),
}

MENU_IDS = list(MENU.keys())
N_MENUS = len(MENU_IDS)

# ── 2. BOBOT SEGMEN WAKTU BERDASARKAN ATURAN PEMILIK (RekomendasiAPI) ──────────
# Bobot slot: peringkat 1=10, 2=9, 3=8, 4=7, 5=6. Menu lainnya = 1.
SLOT_WEIGHTS = [10.0, 9.0, 8.0, 7.0, 6.0]

MINUMAN_PANAS = [
    'PRD0000031', 'PRD0000032', 'PRD0000033', 'PRD0000034',
    'PRD0000035', 'PRD0000036', 'PRD0000016', 'PRD0000017',
    'PRD0000018', 'PRD0000019', 'PRD0000021'
]
MINUMAN_DINGIN = [
    'PRD0000037', 'PRD0000038', 'PRD0000039', 'PRD0000040',
    'PRD0000041', 'PRD0000042', 'PRD0000016', 'PRD0000017',
    'PRD0000018', 'PRD0000019', 'PRD0000021'
]
NASI_KUCING_SATE = ['PRD0000005', 'PRD0000013', 'PRD0000014', 'PRD0000015']
GORENGAN = ['PRD0000009', 'PRD0000010', 'PRD0000011']

SEGMEN_SLOTS = {
    'pagi': [
        ['PRD0000026'],   # Slot 1: Nasi Kuning (10)
        ['PRD0000027'],   # Slot 2: Nasi Uduk (9)
        ['PRD0000028'],   # Slot 3: Lontong Sayur (8)
        ['PRD0000029'],   # Slot 4: Bubur Ayam (7)
        MINUMAN_PANAS     # Slot 5: Minuman Panas (6)
    ],
    'siang': [
        ['PRD0000030'],   # Slot 1: Ayam Geprek + Nasi (10)
        ['PRD0000002'],   # Slot 2: Indomie Goreng Telor (9)
        MINUMAN_DINGIN,   # Slot 3: Minuman Dingin (8)
        NASI_KUCING_SATE, # Slot 4: Nasi Kucing & Sate (7)
        GORENGAN          # Slot 5: Gorengan (6)
    ],
    'sore': [
        ['PRD0000006'],   # Slot 1: Pancong Keju (10)
        ['PRD0000007'],   # Slot 2: Roti Bakar Coklat (9)
        ['PRD0000008'],   # Slot 3: Kentang Goreng (8)
        ['PRD0000012'],   # Slot 4: Dimsum Mentai (7)
    ],
    'malam': [
        ['PRD0000003'],   # Slot 1: Indomie Rebus Telor (10)
        ['PRD0000004'],   # Slot 2: Bubur Kacang Ijo (9)
        ['PRD0000001'],   # Slot 3: Nasi Goreng (8)
        MINUMAN_DINGIN,   # Slot 4: Minuman Dingin (7)
        GORENGAN          # Slot 5: Gorengan (6)
    ]
}

def get_base_segment_weights(segmen: str) -> np.ndarray:
    w = np.ones(N_MENUS, dtype=float)
    slots = SEGMEN_SLOTS[segmen]
    for idx_slot, members in enumerate(slots):
        weight_val = SLOT_WEIGHTS[idx_slot]
        for m_id in members:
            if m_id in MENU_IDS:
                i = MENU_IDS.index(m_id)
                w[i] = weight_val
    return w / np.sum(w)

SEGMENT_BASE_PROBS = {
    s: get_base_segment_weights(s) for s in ['pagi', 'siang', 'sore', 'malam']
}

def jam_menit_ke_segmen(jam: int) -> str:
    if 5 <= jam <= 10:
        return 'pagi'
    elif 11 <= jam <= 14:
        return 'siang'
    elif 15 <= jam <= 17:
        return 'sore'
    else:
        return 'malam'

def hitung_probabilitas_waktu(jam: int, menit: int) -> tuple[np.ndarray, str]:
    """
    Menghitung distribusi probabilitas menu untuk jam dan menit tertentu,
    memperhitungkan pencampuran segmen tetangga jika berada dalam +-30 menit
    dari batas segmen: 05:00, 11:00, 15:00, 18:00.
    """
    segmen_saat_ini = jam_menit_ke_segmen(jam)
    w_curr = SEGMENT_BASE_PROBS[segmen_saat_ini]

    # Cek jarak ke 4 batas transisi (dalam menit dari batas)
    # Batas 05:00 (malam -> pagi)
    # Batas 11:00 (pagi -> siang)
    # Batas 15:00 (siang -> sore)
    # Batas 18:00 (sore -> malam)
    boundaries = [
        (5, 0, 'malam', 'pagi'),
        (11, 0, 'pagi', 'siang'),
        (15, 0, 'siang', 'sore'),
        (18, 0, 'sore', 'malam'),
    ]

    total_menit = jam * 60 + menit

    for b_jam, b_menit, seg_sebelum, seg_sesudah in boundaries:
        b_total = b_jam * 60 + b_menit
        selisih = total_menit - b_total
        # tangani kasus batas 05:00 yang melintasi tengah malam jika diperlukan
        if abs(selisih) <= 30:
            jarak_menit = abs(selisih)
            a = 0.5 * (1.0 - (jarak_menit / 30.0))
            if selisih < 0:
                # Masih sebelum batas (seg_sebelum) mendekati seg_sesudah
                w_tetangga = SEGMENT_BASE_PROBS[seg_sesudah]
            else:
                # Sudah melewati batas (seg_sesudah) baru lepas dari seg_sebelum
                w_tetangga = SEGMENT_BASE_PROBS[seg_sebelum]
            w_campuran = (1.0 - a) * w_curr + a * w_tetangga
            return (w_campuran / np.sum(w_campuran), segmen_saat_ini)

    return (w_curr, segmen_saat_ini)

# ── 3. GENERASI DATASET SINTETIS ───────────────────────────────────────────────
HOUR_RATE = [4, 3, 2, 2, 3, 8, 12, 16, 15, 12, 9, 11, 16, 14, 9, 7, 7, 8, 12, 15, 16, 14, 11, 7]
START_DATE = datetime(2026, 8, 3)  # Senin
N_DAYS = 60
SEED = 42

def generate_dataset():
    rng = np.random.default_rng(SEED)
    rows = []

    uniform_prob = np.ones(N_MENUS) / N_MENUS
    qty_options = [1, 2, 3]
    qty_probs = [0.70, 0.20, 0.10]

    for hari_ke in range(N_DAYS):
        tgl = START_DATE + timedelta(days=hari_ke)
        tgl_str = tgl.strftime('%Y-%m-%d')
        # 0=Senin ... 5=Sabtu, 6=Minggu
        is_weekend = 1 if (hari_ke % 7) in (5, 6) else 0
        mult = 0.6 * (1.25 if is_weekend else 1.0)

        for jam in range(24):
            lam = HOUR_RATE[jam] * mult
            n_transaksi = rng.poisson(lam)

            for _ in range(n_transaksi):
                menit = int(rng.integers(0, 60))
                prob_menu, segmen = hitung_probabilitas_waktu(jam, menit)

                # Noise 0.12 seragam dari 41 menu
                if rng.random() < 0.12:
                    p_pilih = uniform_prob
                else:
                    p_pilih = prob_menu

                idx_menu = rng.choice(N_MENUS, p=p_pilih)
                id_produk = MENU_IDS[idx_menu]
                nama_produk, _, _ = MENU[id_produk]

                qty = int(rng.choice(qty_options, p=qty_probs))

                rows.append({
                    'tanggal': tgl_str,
                    'hari_ke': hari_ke,
                    'jam': jam,
                    'menit': menit,
                    'id_produk': id_produk,
                    'nama_produk': nama_produk,
                    'qty': qty,
                    'segmen': segmen,
                    'is_weekend': is_weekend,
                })

    df = pd.DataFrame(rows)
    output_path = os.path.join(BASE_ML, 'dataset_warkop.csv')
    df.to_csv(output_path, index=False)
    print(f"Dataset berhasil dibuat: {len(df):,} baris")
    print(f"Train (hari 0-47): {len(df[df['hari_ke'] <= 47]):,} baris")
    print(f"Test  (hari 48-59): {len(df[df['hari_ke'] >= 48]):,} baris")
    print(f"File tersimpan di: {output_path}")
    return df

if __name__ == '__main__':
    generate_dataset()
