"""
run_all.py
==========
Pipeline eksekusi tunggal ujung-ke-ujung (End-to-End) Lower Manhattan Coffee Shop dataset workflow.
Urutan proses:
1. Pengecekan Keberadaan Dataset Mentah (dataset/Coffee Shop Sales.xlsx)
2. Fase 1: Data Quality Gate (ml/data_quality.py) -> Berhenti jika tidak lolos gate
3. Fase 2: Pra-pemrosesan Data (ml/prepare_dataset.py)
4. Fase 3: Pelatihan & Evaluasi KNN 3 Tugas (ml/train_evaluate.py)
5. Fase 4: Pembangkitan File SQL Plan B (ml/export_sql.py)
6. Fase 5: Pengujian Unit Otomatis (pytest)
"""

import os
import sys
import subprocess
import time

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.abspath(os.path.join(BASE_DIR, '..'))

def run_step(step_name, command):
    print(f"\n>> MENJALANKAN: {step_name}...")
    start_t = time.time()
    res = subprocess.run(command, cwd=PROJECT_ROOT)
    dur = time.time() - start_t
    if res.returncode != 0:
        print(f"FAILED: {step_name} gagal dengan return code {res.returncode}! Pipeline dihentikan.")
        sys.exit(res.returncode)
    print(f"SUCCESS: {step_name} selesai dalam {dur:.2f} detik.")

def main():
    print("=" * 70)
    print("LOWER MANHATTAN COFFEE SHOP — PIPELINE DATASET (RUN ALL)")
    print("Dataset: Coffee Shop Sales (Maven Roasters)")
    print("Subset aktif: store_location = 'Lower Manhattan'")
    print("=" * 70)
    
    # 1. Cek Dataset
    dataset_file = os.path.join(PROJECT_ROOT, 'dataset', 'Coffee Shop Sales.xlsx')
    if not os.path.exists(dataset_file):
        print(f"ERROR: File dataset tidak ditemukan di: {dataset_file}")
        print("Silakan letakkan file 'Coffee Shop Sales.xlsx' di folder dataset/ lalu jalankan kembali.")
        sys.exit(1)
        
    # 2. Fase 1: Data Quality Gate
    python = sys.executable
    run_step("Fase 1: Data Quality Gate & Profiling", [python, "ml/data_quality.py"])
    
    # 3. Fase 2: Pra-pemrosesan & Rekayasa Fitur
    run_step("Fase 2: Pra-pemrosesan Data & Feature Engineering", [python, "ml/prepare_dataset.py"])
    
    # 4. Fase 3: Pelatihan Model KNN & Evaluasi
    run_step("Fase 3: Pelatihan Model KNN (3 Tugas) & Evaluasi Ilmiah", [python, "ml/train_evaluate.py"])
    
    # 5. Fase 4: Ekspor File SQL Plan B
    run_step("Fase 4: Ekspor Berkas SQL Plan B", [python, "ml/export_sql.py"])
    
    # 6. Pengujian Otomatis Pytest
    run_step(
        "Pengujian Otomatis Kualitas Data & Batas Waktu (Pytest)",
        [python, "-m", "pytest", "ml/tests/test_data_quality.py", "ml/tests/test_time_segmentation.py", "-v"],
    )
    
    print("\n" + "=" * 70)
    print("SELURUH TAHAPAN PIPELINE PLAN B BERHASIL DIJALANKAN 100%!")
    print("=" * 70)

if __name__ == '__main__':
    main()
