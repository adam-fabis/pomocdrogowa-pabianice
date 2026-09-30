#!/usr/bin/env python3
"""Pobiera zdjęcia z wizytówki Google (ID z designu) do zrodla/google/NN-<slug>.jpg.
=s0 zwraca oryginał; fallback =s1600. Pusty/niepełny plik = błąd, skrypt się zatrzymuje.
Pobieranie przez curl (python.org 3.12 nie ma certyfikatów CA dla urllib)."""
import os, sys, subprocess
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'zrodla/google')
PHOTOS = [  # (idx, google_id, slug) — slug musi być zgodny z nazwami plików, które czyta build_images.py
    # 1, 3, 5, 8, 9, 12, 13 usunięte: stara biała laweta (pliki w zrodla/archiwum-biala-laweta/)
    # 7 usunięte: laweta konkurencji (zgłoszenie klienta 2026-09-30, plik w zrodla/archiwum-konkurencja/)
    (2, 'AF1QipMv1TCeNhEn7CsgdXJ8HbEe-ug58zUUvaXUlpMC', 'zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice'),
    (4, 'AF1QipNxSBvqzk0MvD3v3z-K-M2_fPR08xxLUrvz5cv6', 'transport-quada-na-lawecie-pabianice'),
    (6, 'AF1QipOhg9yQtUQb0wI4jMKIpNABYi1o758Xv0dhyVUz', 'holowanie-auta-po-awarii-z-osiedla-pabianice'),
    (10, 'AF1QipO6uXMIkJtD7I1DQ-P4ZBrFWaJNLWplPSPBNF18', 'awaryjna-wymiana-kola-na-miejscu-pabianice'),
    (11, 'AF1QipPNQ_Vl7A6VJy9VQYt0VJ4j0VskjFPRj8-YAYSF', 'laweta-z-przyczepa-transport-ulica-pabianice'),
    (14, 'AF1QipOHdjErDhgeuPOzcloXnD7w3A34Mc2y4CaZGlrk', 'mobilny-serwis-samochodowy-van-pabianice'),
]
os.makedirs(OUT, exist_ok=True)

def fetch(url):
    r = subprocess.run(['curl', '-fsSL', '-A', 'Mozilla/5.0', '--max-time', '60', url], capture_output=True)
    if r.returncode != 0:
        raise RuntimeError(f'curl exit {r.returncode}: {r.stderr.decode().strip()}')
    return r.stdout

for idx, gid, slug in PHOTOS:
    path = os.path.join(OUT, f'{idx:02d}-{slug}.jpg')
    if os.path.exists(path) and os.path.getsize(path) > 10_000:
        print('ok  ', path); continue
    data = None
    for size in ('s0', 's1600'):
        try:
            data = fetch(f'https://lh3.googleusercontent.com/p/{gid}={size}')
            if len(data) > 10_000: break
        except RuntimeError as e:
            print(f'warn {gid}={size}: {e}')
    if not data or len(data) <= 10_000:
        sys.exit(f'BŁĄD: nie pobrano {gid} ({slug})')
    with open(path, 'wb') as f: f.write(data)
    print('got ', path, len(data))
print('done', len(PHOTOS))
