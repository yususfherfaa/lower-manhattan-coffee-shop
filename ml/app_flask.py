"""
app_flask.py
============
Flask REST API Microservice untuk Sistem Rekomendasi Menu & Prediksi Keramaian.
Port default: 5001.

Endpoints:
- GET  /health           -> Status kesehatan service, DB status & timestamp
- GET  /model-info       -> Versi model, metrik evaluasi, tanggal latih, hash dataset
- GET  /api/rekomendasi  -> Rekomendasi menu Top-5 & estimasi keramaian (jam, menit)
- POST /predict-crowd    -> Prediksi volume keramaian (jam, hari, bulan)
"""

import os
import sys
import json
from datetime import datetime
from flask import Flask, request, jsonify
from flask_cors import CORS
from predict import prediksi, _t2_pipeline, get_crowd_label

app = Flask(__name__)

# Batasi CORS untuk origin aplikasi lokal
CORS(app, origins=[
    'http://localhost:8080',
    'http://127.0.0.1:8080',
    'http://localhost',
    'http://127.0.0.1'
])

BASE_ML = os.path.dirname(os.path.abspath(__file__))
METADATA_PATH = os.path.join(BASE_ML, 'metadata.json')

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        "status": "UP",
        "service": "Lower Manhattan Coffee Shop ML Microservice",
        "timestamp": datetime.now().isoformat(),
        "version": "2.0.0-planB"
    }), 200

@app.route('/model-info', methods=['GET'])
def model_info():
    if not os.path.exists(METADATA_PATH):
        return jsonify({"status": "error", "message": "Metadata model tidak ditemukan."}), 404
        
    with open(METADATA_PATH, 'r', encoding='utf-8') as f:
        meta = json.load(f)
    return jsonify({
        "status": "success",
        "metadata": meta
    }), 200

@app.route('/api/rekomendasi', methods=['GET'])
def rekomendasi():
    jam = request.args.get('jam')
    menit = request.args.get('menit')

    jam_val = None
    menit_val = None

    if jam is not None:
        try:
            jam_val = int(jam)
            if jam_val < 0 or jam_val > 23:
                return jsonify({"status": "error", "message": "Jam tidak valid. Gunakan angka 0-23."}), 400
        except ValueError:
            return jsonify({"status": "error", "message": "Format jam tidak valid."}), 400

    if menit is not None:
        try:
            menit_val = int(menit)
            if menit_val < 0 or menit_val > 59:
                return jsonify({"status": "error", "message": "Menit tidak valid. Gunakan angka 0-59."}), 400
        except ValueError:
            return jsonify({"status": "error", "message": "Format menit tidak valid."}), 400

    hasil = prediksi(jam=jam_val, menit=menit_val)
    return jsonify({
        "status": "success",
        "data": hasil,
        "kepercayaan": hasil['kepercayaan'],
        "n_tetangga": hasil['n_tetangga'],
        "prediksi_keramaian": hasil.get('prediksi_keramaian', 0.0),
        "label_keramaian": hasil.get('label_keramaian', 'sedang'),
        "sumber_model": hasil.get('sumber_model', 'public_dataset'),
        "versi_model": hasil.get('versi_model', '2.0.0-planB')
    }), 200

@app.route('/predict-crowd', methods=['POST'])
def predict_crowd():
    payload = request.get_json(silent=True) or {}
    jam = payload.get('jam')
    hari_minggu = payload.get('hari_minggu', 0)
    is_weekend = payload.get('is_weekend', 1 if hari_minggu in [5, 6] else 0)
    bulan = payload.get('bulan', datetime.now().month)
    
    if jam is None:
        return jsonify({"status": "error", "message": "Field 'jam' (0-23) wajib disertakan."}), 400
    try:
        jam = int(jam)
        if jam < 0 or jam > 23:
            return jsonify({"status": "error", "message": "Jam harus 0 s.d. 23."}), 400
    except ValueError:
        return jsonify({"status": "error", "message": "Format jam harus integer."}), 400
        
    w_dec = jam + 0.5
    ang_rad = 2.0 * 3.141592653589793 * w_dec / 24.0
    jam_sin = float(np.sin(ang_rad))
    jam_cos = float(np.cos(ang_rad))
    
    X_q = [[jam, int(hari_minggu), int(is_weekend), int(bulan), jam_sin, jam_cos]]
    pred_tx = float(_t2_pipeline.predict(X_q)[0])
    label_k = get_crowd_label(pred_tx)
    
    return jsonify({
        "status": "success",
        "jam": jam,
        "hari_minggu": hari_minggu,
        "estimasi_transaksi_per_jam": round(pred_tx, 1),
        "label_keramaian": label_k
    }), 200

if __name__ == '__main__':
    import numpy as np
    print("Mulai Flask API Server Plan B di port 5001 (host 127.0.0.1)...")
    app.run(host='127.0.0.1', port=5001, debug=False)
