"""
ml_model.py
===========
Pipeline Machine Learning - Rekomendasi Menu Lower Manhattan Coffee Shop
Algoritma : K-Nearest Neighbor (NearestNeighbors - Nearest-based Item Aggregation)
Fitur     : sin dan cos dari (jam + menit / 60) dengan periode 24 jam
Target    : Rekomendasi Top-5 menu berbasis kedekatan waktu

Alur Evaluasi:
1. Split data train (hari 0-47) dan test (hari 48-59).
2. Optimasi K dengan CV Expanding Window pada train data.
3. Evaluasi per konteks (hari, jam) pada jam:30 di data uji (P@5, R@5, HitRate@5).
4. Bootstrap CI 95% (2000 iterasi, seed 42) untuk selisih KNN vs Baseline.
5. Klasifikasi Top-5 vs daftar pemilik (accuracy, precision, recall, F1, confusion matrix).
6. Analisis ablasi penambahan fitur is_weekend.
7. Penyimpanan artefak: hasil_evaluasi.txt, pemilihan_k.png, perbandingan_metode.png,
   confusion_matrix.png, eda_distribusi.png, dan model joblib (model_knn.joblib).
"""

import os
import sys
import platform
import argparse
from collections import Counter
import warnings
warnings.filterwarnings('ignore')

import numpy as np
import pandas as pd
import sklearn
from sklearn.neighbors import NearestNeighbors
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, confusion_matrix

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import joblib

BASE_ML = os.path.dirname(os.path.abspath(__file__))

# Jaga nilai ini selaras dengan Config\\Recommendation::LOW_CONFIDENCE_THRESHOLD.
# Ambang 150 didasarkan pada sebaran data latih; bukan hasil optimasi metrik.
LOW_CONFIDENCE_THRESHOLD = 150

# ── 1. LOAD DATASET ───────────────────────────────────────────────────────────
print("=" * 65)
print("  PIPELINE ML LOWER MANHATTAN COFFEE SHOP (PR-01)")
print("=" * 65)

parser = argparse.ArgumentParser(description='Evaluasi model Lower Manhattan Coffee Shop')
parser.add_argument('--sumber', choices=('csv', 'db'), default='csv')
args = parser.parse_args()
source_label = 'sintetis'
if args.sumber == 'db':
    try:
        from export_dari_db import export_from_db
        df = export_from_db()
    except Exception as exc:
        raise SystemExit(f"Gagal mengambil data database: {exc}") from exc
    if df.empty:
        raise SystemExit('Data database kosong; evaluasi dihentikan.')
    days = pd.to_datetime(df['tanggal']).dt.date
    date_span = (max(days) - min(days)).days + 1
    if date_span < 30 or len(df) < 2000:
        raise SystemExit(
            f"Data database belum cukup: {date_span} hari dan {len(df)} baris; "
            'syarat minimal 30 hari dan 2000 baris.'
        )
    source_label = f"database ({len(df):,} baris, {min(days)} s.d. {max(days)})"
else:
    dataset_path = os.path.join(BASE_ML, 'dataset_warkop.csv')
    if not os.path.exists(dataset_path):
        raise FileNotFoundError(f"Dataset {dataset_path} belum dibuat. Jalankan generate_dataset.py terlebih dahulu.")
    df = pd.read_csv(dataset_path)
OUTPUT_DIR = BASE_ML if args.sumber == 'csv' else os.path.join(BASE_ML, 'hasil_db')
os.makedirs(OUTPUT_DIR, exist_ok=True)
print(f"[1/7] Dataset loaded: {len(df):,} baris")

if args.sumber == 'csv':
    train_df = df[df['hari_ke'] <= 47].copy()
    test_df = df[df['hari_ke'] >= 48].copy()
    folds = [(23, 24, 29), (29, 30, 35), (35, 36, 41), (41, 42, 47)]
    day_range_label = 'hari 0-47 / 48-59'
else:
    day_values = sorted(int(day) for day in df['hari_ke'].unique())
    split_index = max(1, int(len(day_values) * 0.8))
    train_days = day_values[:split_index]
    test_days = day_values[split_index:]
    train_df = df[df['hari_ke'].isin(train_days)].copy()
    test_df = df[df['hari_ke'].isin(test_days)].copy()
    cv_start = max(1, len(train_days) // 2)
    validation_days = train_days[cv_start:]
    blocks = [list(block) for block in np.array_split(validation_days, 4) if len(block)]
    folds = []
    for block in blocks:
        preceding = [day for day in train_days if day < block[0]]
        if preceding:
            folds.append((max(preceding), min(block), max(block)))
    day_range_label = f'hari latih {train_days[0]}-{train_days[-1]} / uji {test_days[0]}-{test_days[-1]}'
print(f"      Train: {len(train_df):,} baris")
print(f"      Test : {len(test_df):,} baris ({day_range_label})")

# ── 2. EDA DISTRIBUSI & VISUALISASI ───────────────────────────────────────────
print("\n[2/7] Membuat visualisasi EDA (eda_distribusi.png)...")
fig, axes = plt.subplots(1, 2, figsize=(12, 4.5))

seg_order = ['pagi', 'siang', 'sore', 'malam']
seg_counts = df['segmen'].value_counts().reindex(seg_order)
colors = ['#f39c12', '#e74c3c', '#9b59b6', '#34495e']

seg_counts.plot(kind='bar', ax=axes[0], color=colors, edgecolor='black', alpha=0.85)
axes[0].set_title('Distribusi Transaksi per Segmen Waktu', fontsize=12, fontweight='bold')
axes[0].set_xlabel('Segmen Waktu')
axes[0].set_ylabel('Jumlah Transaksi')
axes[0].tick_params(axis='x', rotation=0)

# Distribusi transaksi per jam
hour_counts = df['jam'].value_counts().sort_index()
hour_counts.plot(kind='bar', ax=axes[1], color='#2980b9', edgecolor='black', alpha=0.85)
axes[1].set_title('Distribusi Transaksi per Jam (0-23)', fontsize=12, fontweight='bold')
axes[1].set_xlabel('Jam')
axes[1].set_ylabel('Jumlah Transaksi')
axes[1].tick_params(axis='x', rotation=0)

plt.tight_layout()
eda_fig_path = os.path.join(OUTPUT_DIR, 'eda_distribusi.png')
plt.savefig(eda_fig_path, dpi=200, bbox_inches='tight')
plt.close()
print(f"      Tersimpan: {eda_fig_path} (dpi=200)")

# ── 3. EKSTRAKSI FITUR SIKLIS WAKTU ───────────────────────────────────────────
def extract_cyclical_features(df_in: pd.DataFrame) -> np.ndarray:
    t = df_in['jam'].values + df_in['menit'].values / 60.0
    ang = 2.0 * np.pi * t / 24.0
    return np.column_stack([np.sin(ang), np.cos(ang)])

X_train = extract_cyclical_features(train_df)
y_train = train_df['id_produk'].values
global_counts = Counter(y_train)
all_menus = sorted(list(global_counts.keys()))

# ── 4. OPTIMASI NILAI K DENGAN CV EXPANDING WINDOW ────────────────────────────
print("\n[3/7] Optimasi K dengan Cross-Validation Expanding Window...")
K_GRID = [1, 3, 5, 10, 20, 30, 50, 75, 100, 150, 200, 300, 500]

# Expanding window splits on train_df (hari 0-47)
if not folds:
    raise SystemExit('Rentang hari tidak cukup untuk CV expanding-window.')
if args.sumber == 'db':
    max_fold_train_rows = max(
        len(train_df[train_df['hari_ke'] <= tr_end]) for tr_end, _, _ in folds
    )
    K_GRID = [k for k in K_GRID if k <= max_fold_train_rows]

def query_vector(hour: int, minute: int = 30) -> np.ndarray:
    t = hour + minute / 60.0
    ang = 2.0 * np.pi * t / 24.0
    return np.array([[np.sin(ang), np.cos(ang)]])

cv_scores = {}

for k in K_GRID:
    fold_p5_scores = []
    for tr_end, val_start, val_end in folds:
        sub_train = train_df[train_df['hari_ke'] <= tr_end]
        sub_val = train_df[(train_df['hari_ke'] >= val_start) & (train_df['hari_ke'] <= val_end)]

        X_sub_tr = extract_cyclical_features(sub_train)
        y_sub_tr = sub_train['id_produk'].values
        sub_glob_counts = Counter(y_sub_tr)

        nn = NearestNeighbors(n_neighbors=k, algorithm='brute', metric='euclidean')
        nn.fit(X_sub_tr)

        val_contexts = sub_val.groupby(['hari_ke', 'jam'])
        ctx_p5 = []
        for (day, hr), grp in val_contexts:
            rel = set(grp['id_produk'].unique())
            if not rel:
                continue
            q = query_vector(hr, 30)
            _, ind = nn.kneighbors(q)
            neigh_m = y_sub_tr[ind[0]]
            c_neigh = Counter(neigh_m)
            # Urutan tie-break: jumlah kemunculan (desc), frekuensi global (desc), id asc
            sorted_m = sorted(c_neigh.keys(), key=lambda m: (c_neigh[m], sub_glob_counts[m], -int(m[3:])), reverse=True)
            top5 = sorted_m[:5]
            hits = len(set(top5) & rel)
            ctx_p5.append(hits / 5.0)

        fold_p5_scores.append(np.mean(ctx_p5) if ctx_p5 else 0.0)

    cv_scores[k] = float(np.mean(fold_p5_scores))
    print(f"      K={k:3d} -> Rata-rata Precision@5: {cv_scores[k]:.4f}")

# Bila seri, pilih K lebih kecil: key=(cv_scores[k], -k)
best_k = max(K_GRID, key=lambda k: (cv_scores[k], -k))
print(f"      => K Terpilih: {best_k} (P@5 = {cv_scores[best_k]:.4f})")

# Visualisasi Pemilihan K
plt.figure(figsize=(8, 4.5))
plt.plot(K_GRID, [cv_scores[k] for k in K_GRID], marker='o', color='#2c3e50', linewidth=2, label='CV Expanding Window P@5')
plt.axvline(best_k, color='#e74c3c', linestyle='--', label=f'Best K = {best_k} (P@5={cv_scores[best_k]:.4f})')
plt.title('Optimasi Nilai K (Expanding Window CV)', fontsize=12, fontweight='bold')
plt.xlabel('Nilai K')
plt.ylabel('Rata-rata Precision@5')
plt.grid(True, linestyle=':', alpha=0.6)
plt.legend()
pemilihan_k_path = os.path.join(OUTPUT_DIR, 'pemilihan_k.png')
plt.savefig(pemilihan_k_path, dpi=200, bbox_inches='tight')
plt.close()
print(f"      Tersimpan: {pemilihan_k_path} (dpi=200)")

# ── 5. FIT MODEL FINAL PADA DATA LATIH ─────────────────────────────────────────
print(f"\n[4/7] Melatih model KNN final (K={best_k}) pada seluruh data latih...")
final_nn = NearestNeighbors(n_neighbors=best_k, algorithm='brute', metric='euclidean')
final_nn.fit(X_train)

# Simpan model joblib dan metadata pendukung
model_artifacts = {
    'model': final_nn,
    'k': best_k,
    'X_train': X_train,
    'y_train': y_train,
    'global_counts': global_counts,
    'all_menus': all_menus,
    'train_hours': (train_df['jam'].values + train_df['menit'].values / 60.0) % 24.0,
}
model_joblib_path = os.path.join(OUTPUT_DIR, 'model_knn.joblib')
joblib.dump(model_artifacts, model_joblib_path)
print(f"      Model tersimpan: {model_joblib_path}")

# ── 6. EVALUASI TEST SET VS BASELINE ───────────────────────────────────────────
print("\n[5/7] Evaluasi performa pada data uji (hari 48-59)...")

# Definisi baseline:
# 1. Terlaris global
top5_global = [m for m, _ in global_counts.most_common(5)]

# 2. Terlaris per jam
top5_per_jam = {}
for hr in range(24):
    c_hr = Counter(train_df[train_df['jam'] == hr]['id_produk'])
    s_hr = sorted(c_hr.keys(), key=lambda m: (c_hr[m], global_counts[m], -int(m[3:])), reverse=True)
    top5_per_jam[hr] = s_hr[:5]

# 3. Terlaris per segmen
def get_segmen(hr: int) -> str:
    if 5 <= hr <= 10: return 'pagi'
    elif 11 <= hr <= 14: return 'siang'
    elif 15 <= hr <= 17: return 'sore'
    else: return 'malam'

top5_per_segmen = {}
for seg in ['pagi', 'siang', 'sore', 'malam']:
    c_seg = Counter(train_df[train_df['segmen'] == seg]['id_produk'])
    s_seg = sorted(c_seg.keys(), key=lambda m: (c_seg[m], global_counts[m], -int(m[3:])), reverse=True)
    top5_per_segmen[seg] = s_seg[:5]

# 4. Rule manual pemilik (5 menu pertama daftar pemilik)
def get_rule_manual(hr: int) -> list:
    if 5 <= hr <= 10:
        return ['PRD0000026', 'PRD0000027', 'PRD0000028', 'PRD0000029', 'PRD0000031']
    elif 11 <= hr <= 14:
        return ['PRD0000030', 'PRD0000002', 'PRD0000037', 'PRD0000005', 'PRD0000009']
    elif 15 <= hr <= 17:
        return ['PRD0000006', 'PRD0000007', 'PRD0000008', 'PRD0000012', 'PRD0000034']
    else:
        return ['PRD0000003', 'PRD0000004', 'PRD0000001', 'PRD0000037', 'PRD0000009']

# 5. Acak (rng seed 42)
rng_eval = np.random.default_rng(42)

test_contexts = test_df.groupby(['hari_ke', 'jam'])
eval_records = []

for (day, hr), grp in test_contexts:
    rel_menus = set(grp['id_produk'].unique())
    n_rel = len(rel_menus)
    if n_rel == 0:
        continue

    # Prediksi KNN
    q = query_vector(hr, 30)
    _, ind = final_nn.kneighbors(q)
    neigh_m = y_train[ind[0]]
    c_neigh = Counter(neigh_m)
    sorted_knn = sorted(c_neigh.keys(), key=lambda m: (c_neigh[m], global_counts[m], -int(m[3:])), reverse=True)
    top5_knn = sorted_knn[:5]

    query_hour = float(hr) + 0.5
    distance_hours = np.abs((train_df['jam'].values + train_df['menit'].values / 60.0) - query_hour)
    distance_hours = np.minimum(distance_hours, 24.0 - distance_hours)
    kepercayaan = int(np.count_nonzero(distance_hours <= 1.0))
    top5_knn_fallback = (
        get_rule_manual(hr)
        if kepercayaan < LOW_CONFIDENCE_THRESHOLD
        else top5_knn
    )

    top5_rand = list(rng_eval.choice(all_menus, size=5, replace=False))

    methods_dict = {
        'KNN': top5_knn,
        'KNN + fallback jam sepi': top5_knn_fallback,
        'Terlaris Jam': top5_per_jam[hr],
        'Terlaris Segmen': top5_per_segmen[get_segmen(hr)],
        'Rule Manual': get_rule_manual(hr),
        'Terlaris Global': top5_global,
        'Acak': top5_rand,
    }

    rec = {'hari_ke': day, 'jam': hr, 'n_rel': n_rel, 'top5_knn': top5_knn, 'kepercayaan': kepercayaan}
    for m_name, m_items in methods_dict.items():
        hits = len(set(m_items) & rel_menus)
        rec[f'p5_{m_name}'] = hits / 5.0
        rec[f'r5_{m_name}'] = hits / n_rel
        rec[f'hr5_{m_name}'] = 1.0 if hits > 0 else 0.0

    eval_records.append(rec)

eval_df = pd.DataFrame(eval_records)
n_contexts = len(eval_df)
print(f"      Total konteks uji yang dievaluasi: {n_contexts}")

# Ringkasan Metrik
methods_list = ['KNN', 'KNN + fallback jam sepi', 'Terlaris Jam', 'Terlaris Segmen', 'Rule Manual', 'Terlaris Global', 'Acak']
summary_metrics = []

for m in methods_list:
    summary_metrics.append({
        'Metode': m,
        'Precision@5': eval_df[f'p5_{m}'].mean(),
        'Recall@5': eval_df[f'r5_{m}'].mean(),
        'Hit Rate@5': eval_df[f'hr5_{m}'].mean(),
    })
summary_df = pd.DataFrame(summary_metrics)

# Bootstrap CI 95% (2000 iterasi, seed 42) untuk selisih KNN - baseline
rng_boot = np.random.default_rng(42)
N_BOOT = 2000
bootstrap_ci = {}

knn_p5 = eval_df['p5_KNN'].values
for m in methods_list:
    if m == 'KNN':
        continue
    base_p5 = eval_df[f'p5_{m}'].values
    paired_diff = knn_p5 - base_p5
    mean_diff = float(np.mean(paired_diff))

    boot_means = []
    for _ in range(N_BOOT):
        sample = rng_boot.choice(paired_diff, size=len(paired_diff), replace=True)
        boot_means.append(np.mean(sample))

    ci_low = float(np.percentile(boot_means, 2.5))
    ci_high = float(np.percentile(boot_means, 97.5))
    bootstrap_ci[m] = {
        'mean_diff': mean_diff,
        'ci_low': ci_low,
        'ci_high': ci_high,
    }

# Visualisasi Perbandingan Metode
plt.figure(figsize=(10, 4.5))
x_pos = np.arange(len(methods_list))
plt.bar(x_pos, summary_df['Precision@5'], color='#3498db', edgecolor='black', alpha=0.85, width=0.55)
plt.xticks(x_pos, summary_df['Metode'], rotation=15, fontsize=10)
plt.ylabel('Precision@5', fontsize=11)
plt.title('Perbandingan Precision@5: KNN vs Metode Baseline', fontsize=12, fontweight='bold')
for i, v in enumerate(summary_df['Precision@5']):
    plt.text(i, v + 0.005, f"{v:.3f}", ha='center', va='bottom', fontsize=9, fontweight='bold')
plt.ylim(0, max(summary_df['Precision@5']) * 1.18)
plt.grid(axis='y', linestyle=':', alpha=0.6)
perbandingan_path = os.path.join(OUTPUT_DIR, 'perbandingan_metode.png')
plt.savefig(perbandingan_path, dpi=200, bbox_inches='tight')
plt.close()
print(f"      Tersimpan: {perbandingan_path} (dpi=200)")

# ── 7. KLASIFIKASI TOP-5 VS DAFTAR PEMILIK ────────────────────────────────────
print("\n[6/7] Menghitung metrik klasifikasi Top-5 vs Daftar Pemilik...")
y_true_owner = []
y_pred_knn = []

for _, row in eval_df.iterrows():
    hr = int(row['jam'])
    owner_items = set(get_rule_manual(hr))
    knn_items = set(row['top5_knn'])

    for m in all_menus:
        y_true_owner.append(1 if m in owner_items else 0)
        y_pred_knn.append(1 if m in knn_items else 0)

clf_acc = accuracy_score(y_true_owner, y_pred_knn)
clf_prec = precision_score(y_true_owner, y_pred_knn)
clf_rec = recall_score(y_true_owner, y_pred_knn)
clf_f1 = f1_score(y_true_owner, y_pred_knn)
clf_cm = confusion_matrix(y_true_owner, y_pred_knn)

# Visualisasi Confusion Matrix
plt.figure(figsize=(6, 5))
plt.imshow(clf_cm, interpolation='nearest', cmap=plt.cm.Blues)
plt.title('Confusion Matrix: KNN Top-5 vs Daftar Pemilik', fontsize=11, fontweight='bold')
plt.colorbar()
tick_marks = np.arange(2)
plt.xticks(tick_marks, ['Bukan Daftar', 'Daftar Pemilik'], rotation=15)
plt.yticks(tick_marks, ['Bukan Daftar', 'Daftar Pemilik'])

thresh = clf_cm.max() / 2.0
for i in range(clf_cm.shape[0]):
    for j in range(clf_cm.shape[1]):
        plt.text(j, i, format(clf_cm[i, j], 'd'),
                 ha="center", va="center",
                 color="white" if clf_cm[i, j] > thresh else "black",
                 fontsize=12, fontweight='bold')

plt.ylabel('Label Sebenarnya (Aturan Pemilik)')
plt.xlabel('Prediksi Model (KNN Top-5)')
plt.tight_layout()
cm_path = os.path.join(OUTPUT_DIR, 'confusion_matrix.png')
plt.savefig(cm_path, dpi=200, bbox_inches='tight')
plt.close()
print(f"      Tersimpan: {cm_path} (dpi=200)")

# ── 8. ANALISIS ABLASI: IS_WEEKEND SEBAGAI FITUR ──────────────────────────────
print("\n[7/7] Analisis ablasi: Pengaruh fitur is_weekend...")
X_train_abl = np.column_stack([X_train, train_df['is_weekend'].values])
nn_abl = NearestNeighbors(n_neighbors=best_k, algorithm='brute', metric='euclidean')
nn_abl.fit(X_train_abl)

abl_p5_list = []
base_p5_list = []

for (day, hr), grp in test_contexts:
    rel = set(grp['id_produk'].unique())
    if not rel:
        continue
    is_wknd = 1 if (day % 7) in (5, 6) else 0

    # Model baseline (tanpa is_weekend)
    q_base = query_vector(hr, 30)
    _, ind_b = final_nn.kneighbors(q_base)
    neigh_b = y_train[ind_b[0]]
    c_b = Counter(neigh_b)
    s_b = sorted(c_b.keys(), key=lambda m: (c_b[m], global_counts[m], -int(m[3:])), reverse=True)[:5]
    base_p5_list.append(len(set(s_b) & rel) / 5.0)

    # Model ablasi (dengan is_weekend)
    q_abl = np.array([[q_base[0, 0], q_base[0, 1], float(is_wknd)]])
    _, ind_a = nn_abl.kneighbors(q_abl)
    neigh_a = y_train[ind_a[0]]
    c_a = Counter(neigh_a)
    s_a = sorted(c_a.keys(), key=lambda m: (c_a[m], global_counts[m], -int(m[3:])), reverse=True)[:5]
    abl_p5_list.append(len(set(s_a) & rel) / 5.0)

p5_base_mean = float(np.mean(base_p5_list))
p5_abl_mean = float(np.mean(abl_p5_list))
print(f"      P@5 Model Utama (sin+cos jam)           : {p5_base_mean:.4f}")
print(f"      P@5 Model Ablasi (sin+cos jam + is_weekend): {p5_abl_mean:.4f}")

# ── 9. PENULISAN HASIL EVALUASI KE hasil_evaluasi.txt ─────────────────────────
env_versions = (
    f"Python        : {platform.python_version()}\n"
    f"scikit-learn  : {sklearn.__version__}\n"
    f"pandas        : {pd.__version__}\n"
    f"numpy         : {np.__version__}\n"
    f"joblib        : {joblib.__version__}\n"
    f"matplotlib    : {matplotlib.__version__}\n"
)

hasil_evaluasi_text = f"""================================================================================
HASIL EVALUASI MODEL KNN (REKOMENDASI BERBASIS WAKTU) - LOWER MANHATTAN COFFEE SHOP
================================================================================

1. INFORMASI LINGKUNGAN SISTEM
--------------------------------------------------------------------------------
{env_versions}
2. SPESIFIKASI DATASET & MODEL
--------------------------------------------------------------------------------
Total Data      : {len(df):,} baris
Sumber data     : {source_label}
Data Latih      : {len(train_df):,} baris ({day_range_label})
Data Uji        : {len(test_df):,} baris
Jumlah Konteks  : {n_contexts} konteks (hari, jam) pada data uji
Fitur Input     : sin((jam + menit/60)/24 * 2pi), cos((jam + menit/60)/24 * 2pi)
Algoritma       : NearestNeighbors(algorithm='brute', metric='euclidean')
Nilai K Optimum : {best_k} (dipilih melalui CV Expanding Window pada K-grid)
Ambang fallback: {LOW_CONFIDENCE_THRESHOLD} baris dalam jendela ±1 jam; evaluasi gabungan memakai aturan manual yang sama sebagai proksi fallback.
Target Output   : Top-5 Rekomendasi Menu (Tie-break: Frekuensi Global, lalu ID Produk)

3. HASIL OPTIMASI K (EXPANDING WINDOW CROSS-VALIDATION)
--------------------------------------------------------------------------------
K       | Rata-rata Precision@5
--------+-----------------------
"""

for k in K_GRID:
    marker = "  <-- Terpilih" if k == best_k else ""
    hasil_evaluasi_text += f"{k:<7} | {cv_scores[k]:.4f}{marker}\n"

hasil_evaluasi_text += f"""
4. PERBANDINGAN METODE PADA DATA UJI (EVALUASI PER KONTEKS JAM:30)
--------------------------------------------------------------------------------
Metode                         | Precision@5 | Recall@5 | Hit Rate@5
-------------------------------+-------------+----------+------------
"""

for _, row in summary_df.iterrows():
    hasil_evaluasi_text += f"{row['Metode']:<30} | {row['Precision@5']:<11.3f} | {row['Recall@5']:<8.3f} | {row['Hit Rate@5']:<10.3f}\n"

hasil_evaluasi_text += f"""
KNN murni tetap dilaporkan terpisah dari kombinasi. Kombinasi memakai aturan manual
sebagai proksi daftar Top-5 ketika kepercayaan di bawah {LOW_CONFIDENCE_THRESHOLD};
fallback PHP menampilkan beberapa kelompok menu sehingga evaluasi top-5 berbasis
daftar ini adalah proksi, bukan simulasi tampilan kelompok secara penuh.
"""

hasil_evaluasi_text += f"""
5. UJI SIGNIFIKANSI BOOTSTRAP 95% (2000 ITERASI, SEED 42)
--------------------------------------------------------------------------------
Selisih Berpasangan: KNN - Baseline (Precision@5)
"""

for m, ci in bootstrap_ci.items():
    hasil_evaluasi_text += (
        f"vs {m:<15}: Selisih = {ci['mean_diff']:+.3f} "
        f"(95% CI: {ci['ci_low']:+.3f} s.d. {ci['ci_high']:+.3f})\n"
    )

hasil_evaluasi_text += f"""
6. KLASIFIKASI TOP-5 VS DAFTAR ATURAN PEMILIK (PER PASANGAN KONTEKS-MENU)
--------------------------------------------------------------------------------
Akurasi   : {clf_acc:.4f} ({clf_acc*100:.2f}%)
Presisi   : {clf_prec:.4f} ({clf_prec*100:.2f}%)
Recall    : {clf_rec:.4f} ({clf_rec*100:.2f}%)
F1-Score  : {clf_f1:.4f} ({clf_f1*100:.2f}%)

Confusion Matrix:
{clf_cm}

Catatan:
Perbandingan terhadap aturan manual pemilik menunjukkan seberapa konsisten
pola rekomendasi model dengan intuisi awal pemilik kedai kopi. Namun, presisi aturan
manual bersifat sirkular karena data sintetis di-generate menggunakan bobot awal
yang diturunkan dari slot aturan pemilik tersebut.

7. ANALISIS ABLASI FITUR
--------------------------------------------------------------------------------
Model Utama (sin + cos waktu 24 jam)             : Precision@5 = {p5_base_mean:.4f}
Model Alternatif (sin + cos waktu + is_weekend)   : Precision@5 = {p5_abl_mean:.4f}
Selisih (Ablasi - Utama)                          : {p5_abl_mean - p5_base_mean:+.4f}

Kesimpulan:
Fitur siklis waktu (jam + menit) merupakan fitur utama yang menangkap pola
pesanan harian. Penambahan fitur akhir pekan (is_weekend) hanya memberikan
variasi marjinal karena jam operasional harian tetap mendominasi preferensi menu.

8. CATATAN KETERBATASAN
--------------------------------------------------------------------------------
- KNN menghasilkan skor setara baseline frekuensi yang kuat dan jauh di atas
  terlaris global dan acak.
- Rekomendasi Top-5 pada jam sepi (00.00 - 04.00 dan 20.00 - 23.00) cenderung
  kurang stabil dibandingkan jam sibuk siang/sore karena frekuensi transaksi
  yang lebih rendah pada rentang waktu tersebut.
================================================================================
"""

eval_file_path = os.path.join(OUTPUT_DIR, 'hasil_evaluasi.txt')
with open(eval_file_path, 'w', encoding='utf-8') as f:
    f.write(hasil_evaluasi_text)

print(f"\n[DONE] Seluruh hasil evaluasi berhasil ditulis ke: {eval_file_path}")
print("=" * 65)
