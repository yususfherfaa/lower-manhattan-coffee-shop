"""
predict.py
==========
Modul inferensi rekomendasi menu dan estimasi volume transaksi Lower Manhattan Coffee Shop.
Menggunakan Model KNN Plan B (Dataset Coffee Shop Sales - Maven Roasters).
Menghasilkan:
- Top-5 menu rekomendasi berdasarkan probabilitas kategori dari KNN + ranking popularitas menu
- Estimasi volume keramaian transaksi per jam & label keramaian ('sepi', 'sedang', 'ramai')
- Kompatibel 100% dengan kontrak antarmuka API sebelumnya
"""

import os
import sys
import json
import argparse
from datetime import datetime
from zoneinfo import ZoneInfo
import numpy as np
import joblib

if hasattr(sys.stdout, 'reconfigure'):
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except Exception:
        pass

BASE_ML = os.path.dirname(os.path.abspath(__file__))
MODEL_REK_PATH = os.path.join(BASE_ML, 'model_rekomendasi.pkl')
MODEL_CROWD_PATH = os.path.join(BASE_ML, 'model_keramaian.pkl')
CONFIG_POPULARITAS = os.path.join(BASE_ML, 'config', 'popularitas_segmen.json')
CONFIG_MAPPING = os.path.join(BASE_ML, 'config', 'mapping_kategori.json')
CONFIG_KURS = os.path.join(BASE_ML, 'config', 'kurs.json')

# Muat model & artefak
_rek_data = joblib.load(MODEL_REK_PATH)
_t1_pipeline = _rek_data['pipeline']
_best_k = _rek_data['k']
_class_names = _rek_data['class_names']
_label_encoder = _rek_data['label_encoder']

_crowd_data = joblib.load(MODEL_CROWD_PATH)
_t2_pipeline = _crowd_data['pipeline']
_best_k_reg = _crowd_data['k']
_crowd_quantiles = _crowd_data['quantiles']

with open(CONFIG_POPULARITAS, 'r', encoding='utf-8') as f:
    _popularitas_segmen = json.load(f)

with open(CONFIG_MAPPING, 'r', encoding='utf-8') as f:
    _mapping_kategori = json.load(f)

with open(CONFIG_KURS, 'r', encoding='utf-8') as f:
    _kurs_cfg = json.load(f)
    _usd_to_idr = float(_kurs_cfg.get('usd_to_idr', 16000))
    _pembulatan = int(_kurs_cfg.get('pembulatan', 500))

EMOJI = {
    'pagi': '🌅',
    'siang': '☀️',
    'sore': '🌤️',
    'malam': '🌙',
    'di_luar_operasional': '💤'
}
HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']

def jam_ke_segmen(jam: int) -> str:
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

def get_crowd_label(predicted_tx: float) -> str:
    if predicted_tx <= _crowd_quantiles['q1']:
        return 'sepi'
    elif predicted_tx <= _crowd_quantiles['q3']:
        return 'sedang'
    else:
        return 'ramai'

def prediksi(jam: int = None, menit: int = None) -> dict:
    tz = ZoneInfo('Asia/Jakarta')
    now = datetime.now(tz)
    
    if jam is None:
        jam = now.hour
    if menit is None:
        menit = now.minute if jam == now.hour else 30
        
    hari_idx = now.weekday()
    is_weekend = 1 if hari_idx in (5, 6) else 0
    segmen = jam_ke_segmen(jam)
    bulan = now.month
    
    # 1. Fallback jika di luar jam operasional (21:00 s.d. 05:59)
    if segmen == 'di_luar_operasional':
        return {
            'jam': jam,
            'menit': menit,
            'segmen': segmen,
            'emoji': EMOJI[segmen],
            'hari': HARI[hari_idx],
            'is_weekend': bool(is_weekend),
            'kepercayaan': 0,
            'n_tetangga': int(_best_k),
            'total_rekomendasi': 0,
            'rekomendasi': [],
            'source': 'luar-jam-operasional',
            'sumber_model': 'fallback',
            'pesan': 'Lower Manhattan Coffee Shop beroperasi pukul 06:00 s.d. 21:00 WIB. Silakan kembali pada jam operasional.',
            'prediksi_keramaian': 0.0,
            'label_keramaian': 'tutup',
            'versi_model': '2.0.0-planB'
        }
        
    # 2. Fitur Vektor Query
    waktu_desimal = jam + menit / 60.0
    sudut_rad = 2.0 * np.pi * waktu_desimal / 24.0
    jam_sin = np.sin(sudut_rad)
    jam_cos = np.cos(sudut_rad)
    
    X_query_t1 = np.array([[jam, hari_idx, is_weekend, jam_sin, jam_cos]])
    
    # Probabilitas kategori dari KNN
    probs = _t1_pipeline.predict_proba(X_query_t1)[0]
    top_cat_indices = np.argsort(probs)[::-1]
    
    # 3. Prediksi Keramaian (Tugas 2)
    # Fitur: ['jam', 'hari_minggu', 'is_weekend', 'bulan', 'jam_sin', 'jam_cos']
    X_query_t2 = np.array([[jam, hari_idx, is_weekend, bulan, jam_sin, jam_cos]])
    pred_crowd_tx = float(_t2_pipeline.predict(X_query_t2)[0])
    label_crowd = get_crowd_label(pred_crowd_tx)
    
    # 4. Ambil Menu Rekomendasi berdasarkan Kategori Teratas & Popularitas Segmen
    populer_di_segmen = _popularitas_segmen.get(segmen, [])
    
    # Urutkan kategori berdasarkan probabilitas KNN
    rekomendasi_items = []
    seen_products = set()
    
    rank = 1
    for cat_idx in top_cat_indices:
        cat_name = _class_names[cat_idx]
        cat_prob = float(probs[cat_idx])
        
        # Ambil produk teratas di kategori ini untuk segmen ini
        prods_in_cat = [p for p in populer_di_segmen if p['product_category'] == cat_name]
        
        for p in prods_in_cat:
            pid = p['product_id']
            if pid in seen_products:
                continue
                
            seen_products.add(pid)
            id_produk_str = f"PRDM{pid:04d}"
            nama_menu = f"{p['nama_bersih']}" + (f" ({p['ukuran']})" if p['ukuran'] != 'none' else '')
            kategori_lokal = _mapping_kategori.get(cat_name, cat_name)
            
            harga_usd = float(p['harga_katalog'])
            harga_idr = round((harga_usd * _usd_to_idr) / _pembulatan) * _pembulatan
            
            rekomendasi_items.append({
                'peringkat': rank,
                'id_produk': id_produk_str,
                'kode_dataset': pid,
                'nama': nama_menu,
                'nama_produk': nama_menu,
                'kategori': kategori_lokal,
                'kategori_dataset': cat_name,
                'ukuran': p['ukuran'],
                'harga': harga_idr,
                'skor': round(cat_prob, 4),
                'confidence': round(cat_prob * 100.0, 1),
                'source': 'knn-kategori-popularitas',
                'sumber_model': 'public_dataset'
            })
            rank += 1
            if len(rekomendasi_items) >= 5:
                break
                
        if len(rekomendasi_items) >= 5:
            break
            
    # Nilai kepercayaan: probabilitas kategori tertinggi * 100
    kepercayaan_val = int(probs[top_cat_indices[0]] * 100.0)
    
    return {
        'jam': jam,
        'menit': menit,
        'segmen': segmen,
        'emoji': EMOJI.get(segmen, '☕'),
        'hari': HARI[hari_idx],
        'is_weekend': bool(is_weekend),
        'kepercayaan': kepercayaan_val,
        'n_tetangga': int(_best_k),
        'total_rekomendasi': len(rekomendasi_items),
        'rekomendasi': rekomendasi_items,
        'source': 'knn-kategori-popularitas',
        'sumber_model': 'public_dataset',
        'prediksi_keramaian': round(pred_crowd_tx, 1),
        'label_keramaian': label_crowd,
        'versi_model': '2.0.0-planB'
    }

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description="Inferensi Rekomendasi Menu & Volume Transaksi Lower Manhattan Coffee Shop")
    parser.add_argument('--jam', type=int, default=None, help="Jam (0-23)")
    parser.add_argument('--menit', type=int, default=None, help="Menit (0-59)")
    parser.add_argument('--json', action='store_true', help="Output format JSON")
    args = parser.parse_args()
    
    hasil = prediksi(jam=args.jam, menit=args.menit)
    
    if args.json:
        print(json.dumps(hasil, ensure_ascii=False, indent=2))
    else:
        print("=" * 65)
        print("  REKOMENDASI MENU & VOLUME TRANSAKSI LOWER MANHATTAN COFFEE SHOP")
        print("=" * 65)
        print(f"  {hasil['emoji']} Segmen       : {hasil['segmen'].upper()} (Jam {hasil['jam']:02d}:{hasil['menit']:02d} WIB)")
        print(f"  📅 Hari         : {hasil['hari']}")
        print(f"  ⚡ Model        : KNN Kategori (K={hasil['n_tetangga']}) + KNN Regresi Keramaian")
        print(f"  👥 Keramaian    : {hasil['label_keramaian'].upper()} (~{hasil.get('prediksi_keramaian', 0)} transaksi/jam)")
        print(f"  📊 Sumber Data  : {hasil['sumber_model']}")
        print("=" * 65)
        for it in hasil.get('rekomendasi', []):
            print(f"  #{it['peringkat']} [{it['id_produk']}] {it['nama']:<28} Rp {it['harga']:>7,} (Skor: {it['skor']:.4f})")
        print("=" * 65)
