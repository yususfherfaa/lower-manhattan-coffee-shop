import os
import json
import re
import pytest
import pandas as pd

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.abspath(os.path.join(BASE_DIR, '..', '..'))
DATASET_PATH = os.path.join(PROJECT_ROOT, 'dataset', 'Coffee Shop Sales.xlsx')
INTERIM_CSV = os.path.join(PROJECT_ROOT, 'ml', 'data', 'interim', 'transaksi_valid.csv')
PROCESSED_CSV = os.path.join(PROJECT_ROOT, 'ml', 'data', 'processed', 'dataset_bersih.csv')
PROFIL_JSON = os.path.join(PROJECT_ROOT, 'ml', 'data', 'profil_dataset.json')
CATALOG_SQL = os.path.join(PROJECT_ROOT, 'database', 'planB', '02_seed_katalog.sql')
AGGREGATE_SQL = os.path.join(PROJECT_ROOT, 'database', 'planB', '03_seed_agregat.sql')
IMAGE_BY_CATEGORY = {
    'Bakery': 'bakery.svg',
    'Branded': 'branded.svg',
    'Coffee': '1782892671_5d17140debb9adc1a0f7.jpg',
    'Coffee beans': '1782893018_3852702b5d883e8b42a6.jpg',
    'Drinking Chocolate': '1782892953_7355e2cffff88b69963b.png',
    'Flavours': 'flavours.svg',
    'Loose Tea': '1782892849_cca365c575d52226cdfc.jpeg',
    'Packaged Chocolate': 'packaged-chocolate.svg',
    'Tea': '1782892739_5710e7e00431f6e601b9.jpg',
}
EXPECTED_CATEGORIES = {
    'Bakery',
    'Branded',
    'Coffee',
    'Coffee beans',
    'Drinking Chocolate',
    'Flavours',
    'Loose Tea',
    'Packaged Chocolate',
    'Tea',
}

def test_dataset_exists():
    assert os.path.exists(DATASET_PATH), "Dataset Coffee Shop Sales.xlsx harus ada di folder dataset/"

def test_interim_data_exists():
    assert os.path.exists(INTERIM_CSV), "File transaksi_valid.csv harus ada di interim"

def test_profile_json_status():
    assert os.path.exists(PROFIL_JSON), "File profil_dataset.json harus ada"
    with open(PROFIL_JSON, 'r', encoding='utf-8') as f:
        data = json.load(f)
    assert data['status_data_layak'] == 'YA'
    assert data['total_baris_mentah'] == 149116
    assert data['total_baris_lokasi_target'] == 47782
    assert data['baris_dikecualikan_lokasi_lain'] == 101334
    assert data['total_baris_bersih'] == 47782
    assert data['total_baris_dibuang'] == 0
    assert data['lokasi_target'] == 'Lower Manhattan'
    assert data['store_id_target'] == 5

def test_interim_row_count_and_no_missing():
    df = pd.read_csv(INTERIM_CSV)
    assert len(df) == 47782, "Jumlah baris harus 47.782 setelah filter Lower Manhattan"
    assert df['store_location'].nunique() == 1, "Dataset harus hanya berasal dari Lower Manhattan"
    assert df['store_location'].eq('Lower Manhattan').all(), "Semua transaksi harus punya lokasi Lower Manhattan"
    assert df.isna().sum().sum() == 0, "Missing values harus 0"

def test_operating_hours_range():
    df = pd.read_csv(INTERIM_CSV)
    assert df['jam'].min() >= 6, "Jam operasional minimal 06:00"
    assert df['jam'].max() <= 20, "Jam operasional maksimal 20:59"

def test_processed_data_has_dataset_categories_and_single_location():
    df = pd.read_csv(PROCESSED_CSV)
    assert df['store_location'].eq('Lower Manhattan').all()
    assert set(df['product_category'].unique()) == EXPECTED_CATEGORIES
    assert df['product_id'].nunique() == 80

def test_exported_catalog_has_all_products_categories_and_images():
    with open(CATALOG_SQL, 'r', encoding='utf-8') as file:
        catalog_sql = file.read()
    product_ids = re.findall(r"\('PRDM\d{4}',", catalog_sql)
    expected_ids = {
        f"('PRDM{int(product_id):04d}',"
        for product_id in pd.read_csv(PROCESSED_CSV)['product_id'].unique()
    }
    assert set(product_ids) == expected_ids
    assert len(expected_ids) == 80
    assert "WHERE `id_produk` NOT IN (" in catalog_sql

    for category, image in IMAGE_BY_CATEGORY.items():
        assert f"'{category}', '{category}'" in catalog_sql
        assert f"'{image}'" in catalog_sql
        assert os.path.isfile(os.path.join(PROJECT_ROOT, 'public', 'uploads', image))

    with open(AGGREGATE_SQL, 'r', encoding='utf-8') as file:
        aggregate_sql = file.read()
    assert "DELETE FROM `agg_keramaian_jam`;" in aggregate_sql
    assert "DELETE FROM `agg_kategori_segmen`;" in aggregate_sql
