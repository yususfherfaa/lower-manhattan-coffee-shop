import pytest
import os
import sys

BASE_ML = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
if BASE_ML not in sys.path:
    sys.path.insert(0, BASE_ML)

from predict import jam_ke_segmen, prediksi

def test_jam_ke_segmen_boundaries():
    # Jam di luar operasional
    assert jam_ke_segmen(0) == 'di_luar_operasional'
    assert jam_ke_segmen(5) == 'di_luar_operasional'
    
    # Batas Pagi (06:00 - 10:59)
    assert jam_ke_segmen(6) == 'pagi'
    assert jam_ke_segmen(10) == 'pagi'
    
    # Batas Siang (11:00 - 14:59)
    assert jam_ke_segmen(11) == 'siang'
    assert jam_ke_segmen(14) == 'siang'
    
    # Batas Sore (15:00 - 17:59)
    assert jam_ke_segmen(15) == 'sore'
    assert jam_ke_segmen(17) == 'sore'
    
    # Batas Malam (18:00 - 20:59)
    assert jam_ke_segmen(18) == 'malam'
    assert jam_ke_segmen(20) == 'malam'
    
    # Batas Tutup Malam
    assert jam_ke_segmen(21) == 'di_luar_operasional'
    assert jam_ke_segmen(23) == 'di_luar_operasional'

def test_prediksi_luar_jam_operasional():
    res = prediksi(jam=22, menit=0)
    assert res['segmen'] == 'di_luar_operasional'
    assert len(res['rekomendasi']) == 0
    assert res['source'] == 'luar-jam-operasional'

def test_prediksi_jam_operasional():
    res = prediksi(jam=9, menit=15)
    assert res['segmen'] == 'pagi'
    assert res['menit'] == 15
    assert len(res['rekomendasi']) == 5
    assert res['source'] == 'knn-kategori-popularitas'
    assert 'prediksi_keramaian' in res
    assert res['label_keramaian'] in ['sepi', 'sedang', 'ramai']

def test_flask_api_uses_requested_wib_hour_and_minute():
    from app_flask import app

    response = app.test_client().get('/api/rekomendasi?jam=14&menit=37')
    payload = response.get_json()

    assert response.status_code == 200
    assert payload['data']['jam'] == 14
    assert payload['data']['menit'] == 37
    assert payload['data']['sumber_model'] == 'public_dataset'
