"""
prepare_dataset.py
==================
Fase 2: Pra-pemrosesan & Rekayasa Fitur.
Input: ml/data/interim/transaksi_valid.csv
Output: ml/data/processed/dataset_bersih.csv
Fitur yang diturunkan:
- jam (0-23)
- hari_minggu (0-6)
- is_weekend (0/1)
- bulan (1-6)
- segmen_waktu ('pagi', 'siang', 'sore', 'malam')
- jam_sin, jam_cos (siklis waktu 24 jam)
- ukuran ('Sm', 'Rg', 'Lg', 'none')
- nama_bersih (product_detail tanpa akhiran ukuran)
- harga_katalog (modus unit_price per product_id)
Target:
- product_category (9 kelas)
- product_type (29 kelas)
- product_detail (80 kelas)
- volume transaksi per jam (untuk model keramaian)
"""

import os
import re
import json
import hashlib
import pandas as pd
import numpy as np

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.abspath(os.path.join(BASE_DIR, '..'))
INTERIM_CSV = os.path.join(BASE_DIR, 'data', 'interim', 'transaksi_valid.csv')
PROCESSED_CSV = os.path.join(BASE_DIR, 'data', 'processed', 'dataset_bersih.csv')
DOCS_DIR = os.path.join(PROJECT_ROOT, 'docs')
TABEL_JURNAL_DIR = os.path.join(DOCS_DIR, 'tabel_jurnal')

def assign_segmen(jam):
    if 6 <= jam <= 10:
        return 'pagi'
    elif 11 <= jam <= 14:
        return 'siang'
    elif 15 <= jam <= 17:
        return 'sore'
    elif 18 <= jam <= 20:
        return 'malam'
    else:
        return 'di_luar_operasional'

def extract_size_and_clean_name(detail_str):
    detail_str = str(detail_str).strip()
    match = re.search(r'\b(Sm|Rg|Lg)\b$', detail_str)
    if match:
        size = match.group(1)
        clean_name = re.sub(r'\s*\b(Sm|Rg|Lg)\b$', '', detail_str).strip()
        return size, clean_name
    return 'none', detail_str

def prepare_dataset():
    print("=" * 70)
    print("FASE 2: PRA-PEMROSESAN & REKAYASA FITUR")
    print("=" * 70)
    
    if not os.path.exists(INTERIM_CSV):
        raise FileNotFoundError(f"File interim {INTERIM_CSV} belum ada. Jalankan data_quality.py dulu.")
    
    print(f"Membaca {INTERIM_CSV}...")
    df = pd.read_csv(INTERIM_CSV)
    df = df[df['store_location'].eq('Lower Manhattan')].copy()
    if df.empty:
        raise ValueError("Dataset interim tidak berisi transaksi Lower Manhattan.")
    print(f"Total baris dimuat setelah filter Lower Manhattan: {len(df):,}")
    
    # 1. Pastikan tanggal bertipe datetime
    df['parsed_date'] = pd.to_datetime(df['transaction_date'])
    df['hari_minggu'] = df['parsed_date'].dt.dayofweek
    df['is_weekend'] = df['hari_minggu'].apply(lambda d: 1 if d in [5, 6] else 0)
    df['bulan'] = df['parsed_date'].dt.month
    
    # 2. Segmen waktu
    df['segmen_waktu'] = df['jam'].apply(assign_segmen)
    
    # 3. Fitur Siklis Waktu (jam + menit/60)
    waktu_desimal = df['jam'] + df['menit'] / 60.0
    sudut_radian = 2.0 * np.pi * waktu_desimal / 24.0
    df['jam_sin'] = np.sin(sudut_radian)
    df['jam_cos'] = np.cos(sudut_radian)
    
    # 4. Ukuran & Nama Bersih
    extracted = [extract_size_and_clean_name(name) for name in df['product_detail']]
    df['ukuran'] = [e[0] for e in extracted]
    df['nama_bersih'] = [e[1] for e in extracted]
    
    # 5. Hitung harga katalog (modus per product_id)
    price_modes = df.groupby('product_id')['unit_price'].apply(lambda s: s.mode().iloc[0]).to_dict()
    df['harga_katalog'] = df['product_id'].map(price_modes)
    
    # Simpan ke processed
    df.to_csv(PROCESSED_CSV, index=False)
    file_hash = hashlib.sha256(open(PROCESSED_CSV, 'rb').read()).hexdigest()
    print(f"Dataset bersih tersimpan di: {PROCESSED_CSV}")
    print(f"SHA-256 dataset_bersih.csv: {file_hash}")
    
    # Ekspor tabel pra-pemrosesan ke tabel_jurnal
    prep_table = pd.DataFrame([
        {"Nama Fitur": "jam", "Tipe": "Numerik (int)", "Rentang": "6 - 20", "Deskripsi": "Jam transaksi"},
        {"Nama Fitur": "hari_minggu", "Tipe": "Numerik (int)", "Rentang": "0 - 6", "Deskripsi": "0=Senin, 6=Minggu"},
        {"Nama Fitur": "is_weekend", "Tipe": "Biner (0/1)", "Rentang": "0 atau 1", "Deskripsi": "1 jika Sabtu/Minggu"},
        {"Nama Fitur": "bulan", "Tipe": "Numerik (int)", "Rentang": "1 - 6", "Deskripsi": "Bulan transaksi (Jan - Jun)"},
        {"Nama Fitur": "segmen_waktu", "Tipe": "Kategorikal", "Nilai": "pagi, siang, sore, malam", "Deskripsi": "4 segmen waktu Lower Manhattan"},
        {"Nama Fitur": "jam_sin, jam_cos", "Tipe": "Numerik (float)", "Rentang": "[-1, 1]", "Deskripsi": "Representasi siklis jam 24 jam"},
        {"Nama Fitur": "store_id", "Tipe": "Konstan (int)", "Nilai": "5", "Deskripsi": "ID gerai Lower Manhattan; tidak digunakan sebagai fitur model"},
        {"Nama Fitur": "ukuran", "Tipe": "Kategorikal", "Nilai": "Sm, Rg, Lg, none", "Deskripsi": "Ukuran porsi menu"},
        {"Target Utama": "product_category", "Tipe": "Kategorikal", "Nilai": "9 kategori", "Deskripsi": "Target klasifikasi rekomendasi KNN"},
    ])
    prep_table.to_csv(os.path.join(TABEL_JURNAL_DIR, 'tabel_prapemrosesan.csv'), index=False)
    prep_table.to_markdown(os.path.join(TABEL_JURNAL_DIR, 'tabel_prapemrosesan.md'), index=False)
    
    print("=" * 70)
    print("FASE 2 SELESAI BERHASIL.")
    print("=" * 70)
    return file_hash

if __name__ == '__main__':
    prepare_dataset()
