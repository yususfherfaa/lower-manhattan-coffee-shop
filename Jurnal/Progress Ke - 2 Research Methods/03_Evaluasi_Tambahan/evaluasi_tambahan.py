"""
evaluasi_tambahan.py
Evaluasi tambahan (TIDAK mengubah ml/): baseline top-3 untuk Tugas 1 dan split temporal + baseline musiman untuk Tugas 2.
Pemakaian (dari root repo):  python evaluasi_tambahan.py ml/data/processed/dataset_bersih.csv
Keluaran: evaluasi_tambahan.json dan evaluasi_tambahan.md di folder yang sama dengan skrip ini.
"""
import sys, os, json
import numpy as np, pandas as pd
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.neighbors import KNeighborsClassifier, KNeighborsRegressor
from sklearn.model_selection import train_test_split, KFold
from sklearn.metrics import accuracy_score, mean_absolute_error, mean_squared_error, r2_score
import sklearn
OUT=os.path.dirname(os.path.abspath(__file__))
df=pd.read_csv(sys.argv[1])
assert df['store_location'].eq('Lower Manhattan').all()
R={'sklearn':sklearn.__version__,'baris':len(df)}
# ---- Tugas 1
f1=['jam','hari_minggu','is_weekend','jam_sin','jam_cos']
le=LabelEncoder(); y=le.fit_transform(df.product_category); X=df[f1].values
Xtr,Xte,ytr,yte=train_test_split(X,y,test_size=.2,random_state=42,stratify=y)
K=20  # K terpilih CV di train_evaluate.py (metadata.json)
pipe=Pipeline([('s',StandardScaler()),('k',KNeighborsClassifier(K))]).fit(Xtr,ytr)
prob=pipe.predict_proba(Xte)
def topk(p,yt,k): return float(np.mean([t in np.argsort(r)[-k:] for t,r in zip(yt,p)]))
cnt=np.bincount(ytr,minlength=len(le.classes_))
g3=np.argsort(cnt)[-3:]; g2=np.argsort(cnt)[-2:]
seg=lambda j:0 if j<=10 else 1 if j<=14 else 2 if j<=17 else 3
s_tr=np.array([seg(j) for j in Xtr[:,0]]); s_te=np.array([seg(j) for j in Xte[:,0]])
tops={s:np.argsort(np.bincount(ytr[s_tr==s],minlength=len(le.classes_)))[-3:] for s in range(4)}
R['tugas1']={'K':K,'n_uji':int(len(yte)),
 'akurasi_knn':float(accuracy_score(yte,pipe.predict(Xte))),
 'akurasi_baseline_mayoritas':float(np.mean(yte==np.argmax(cnt))),
 'top3_knn':topk(prob,yte,3),'top3_baseline_populer_global':float(np.mean([t in g3 for t in yte])),
 'top3_baseline_populer_per_segmen':float(np.mean([t in tops[s] for t,s in zip(yte,s_te)])),
 'top2_knn':topk(prob,yte,2),'top2_baseline_populer_global':float(np.mean([t in g2 for t in yte])),
 'tiga_kategori_terpopuler_train':[le.classes_[i] for i in g3]}
# ---- Tugas 2
a=df.groupby(['parsed_date','jam','hari_minggu','is_weekend','bulan']).agg(tx=('transaction_id','count')).reset_index()
w=a.jam+.5; a['jam_sin']=np.sin(2*np.pi*w/24); a['jam_cos']=np.cos(2*np.pi*w/24)
F_ALL=['jam','hari_minggu','is_weekend','bulan','jam_sin','jam_cos']; F_NOM=[c for c in F_ALL if c!='bulan']
def pilih_k(tr,feat):
    s=StandardScaler().fit(tr[feat]); best=None
    for k in range(1,21):
        m=[]
        for i,j in KFold(5,shuffle=True,random_state=42).split(tr):
            r=KNeighborsRegressor(k).fit(s.transform(tr[feat].iloc[i]),tr.tx.iloc[i])
            m.append(mean_absolute_error(tr.tx.iloc[j],r.predict(s.transform(tr[feat].iloc[j]))))
        if best is None or np.mean(m)<best[1]: best=(k,float(np.mean(m)))
    return best[0]
def met(yt,p): return {'MAE':float(mean_absolute_error(yt,p)),'RMSE':float(mean_squared_error(yt,p)**.5),'R2':float(r2_score(yt,p))}
def evaluasi(tr,te,label):
    out={'n_latih':int(len(tr)),'n_uji':int(len(te))}
    for nama,feat in [('knn_dengan_bulan',F_ALL),('knn_tanpa_bulan',F_NOM)]:
        k=pilih_k(tr,feat); s=StandardScaler().fit(tr[feat])
        p=KNeighborsRegressor(k).fit(s.transform(tr[feat]),tr.tx).predict(s.transform(te[feat]))
        out[nama]=dict(K=k,**met(te.tx,p))
    hm=tr.groupby('jam').tx.mean(); out['baseline_rata_per_jam']=met(te.tx,te.jam.map(hm).fillna(tr.tx.mean()))
    last=tr[tr.bulan==tr.bulan.max()]; hl=last.groupby('jam').tx.mean()
    out['baseline_rata_per_jam_bulan_terakhir']=met(te.tx,te.jam.map(hl).fillna(last.tx.mean()))
    return out
tr,te=train_test_split(a,test_size=.2,random_state=42)
R['tugas2_acak']=evaluasi(tr,te,'acak')
R['tugas2_temporal_JanMei_Jun']=evaluasi(a[a.bulan<=5],a[a.bulan==6],'temporal')
R['rata_transaksi_per_jam_per_bulan']={int(k):round(float(v),2) for k,v in a.groupby('bulan').tx.mean().items()}
json.dump(R,open(os.path.join(OUT,'evaluasi_tambahan.json'),'w'),indent=2,ensure_ascii=False)
t1=R['tugas1']
L=['# Evaluasi tambahan (dihitung ulang dari dataset_bersih.csv)','',
'## Tugas 1 — klasifikasi kategori',
f"- Akurasi KNN (K={K}): {t1['akurasi_knn']*100:.2f}% | baseline mayoritas: {t1['akurasi_baseline_mayoritas']*100:.2f}%",
f"- Top-3: KNN {t1['top3_knn']*100:.2f}% vs baseline tiga kategori terpopuler {t1['top3_baseline_populer_global']*100:.2f}% (per segmen: {t1['top3_baseline_populer_per_segmen']*100:.2f}%)",
f"- Top-2: KNN {t1['top2_knn']*100:.2f}% vs baseline {t1['top2_baseline_populer_global']*100:.2f}%",'',
'## Tugas 2 — keramaian','','| Skema | Model | MAE | RMSE | R² |','|---|---|---|---|---|']
for sk in ['tugas2_acak','tugas2_temporal_JanMei_Jun']:
    for m in ['knn_dengan_bulan','knn_tanpa_bulan','baseline_rata_per_jam','baseline_rata_per_jam_bulan_terakhir']:
        v=R[sk][m]; L.append(f"| {sk} | {m}{' (K=%d)'%v['K'] if 'K' in v else ''} | {v['MAE']:.2f} | {v['RMSE']:.2f} | {v['R2']:.3f} |")
open(os.path.join(OUT,'evaluasi_tambahan.md'),'w').write('\n'.join(L)+'\n')
print('\n'.join(L))
