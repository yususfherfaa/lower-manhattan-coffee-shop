import pytest
import requests

BASE_URL = "http://127.0.0.1:5001"

def test_flask_health_endpoint():
    res = requests.get(f"{BASE_URL}/health", timeout=5)
    assert res.status_code == 200
    data = res.json()
    assert data['status'] == "UP"
    assert data['version'] == "2.0.0-planB"

def test_flask_model_info_endpoint():
    res = requests.get(f"{BASE_URL}/model-info", timeout=5)
    assert res.status_code == 200
    data = res.json()
    assert data['status'] == "success"
    assert "metadata" in data
    assert data['metadata']['model_version'] == "2.0.0-planB"

def test_flask_rekomendasi_valid():
    res = requests.get(f"{BASE_URL}/api/rekomendasi?jam=12&menit=30", timeout=5)
    assert res.status_code == 200
    data = res.json()
    assert data['status'] == "success"
    assert data['data']['jam'] == 12
    assert data['data']['segmen'] == "siang"
    assert len(data['data']['rekomendasi']) == 5
    assert "prediksi_keramaian" in data
    assert "label_keramaian" in data

def test_flask_rekomendasi_invalid_hour():
    res = requests.get(f"{BASE_URL}/api/rekomendasi?jam=99", timeout=5)
    assert res.status_code == 400
    data = res.json()
    assert data['status'] == "error"

def test_flask_predict_crowd():
    res = requests.post(f"{BASE_URL}/predict-crowd", json={"jam": 10, "hari_minggu": 2}, timeout=5)
    assert res.status_code == 200
    data = res.json()
    assert data['status'] == "success"
    assert "estimasi_transaksi_per_jam" in data
    assert data['label_keramaian'] in ['sepi', 'sedang', 'ramai']
