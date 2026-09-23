#!/usr/bin/env python3
"""Pobiera zdjęcia z wizytówki Google (ID z designu) do zrodla/google/NN-<slug>.jpg.
=s0 zwraca oryginał; fallback =s1600. Pusty/niepełny plik = błąd, skrypt się zatrzymuje.
Pobieranie przez curl (python.org 3.12 nie ma certyfikatów CA dla urllib)."""
import os, sys, subprocess
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'zrodla/google')
PHOTOS = [  # (idx, google_id, slug) — slug musi być zgodny z nazwami plików, które czyta build_images.py
    (1, 'AF1QipMCuRG4erkL39pLL-v0rNfz0yXzroni7Qpzx6v6', 'laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice'),
    (2, 'AF1QipMv1TCeNhEn7CsgdXJ8HbEe-ug58zUUvaXUlpMC', 'zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice'),
    (3, 'AF1QipMzHpitGAQvdsdOpMms7mvUbVlFEQAZWRbQFfzp', 'holowanie-sedana-laweta-noca-pabianice'),
    (4, 'AF1QipNxSBvqzk0MvD3v3z-K-M2_fPR08xxLUrvz5cv6', 'transport-quada-na-lawecie-pabianice'),
    (5, 'AF1QipN3rS421J-tXZyiCLpLVrnyjECo27osUiNnQ6lm', 'czarny-suv-na-lawecie-pomoc-drogowa-pabianice'),
    (6, 'AF1QipOhg9yQtUQb0wI4jMKIpNABYi1o758Xv0dhyVUz', 'holowanie-auta-po-awarii-z-osiedla-pabianice'),
    (7, 'AF1QipO3xxEQ2QUWqHNvawCpDGmIp-zbr9nG7JyX4HBX', 'zaladunek-auta-na-lawete-noca-pabianice'),
    (8, 'AF1QipMit_frYn13qotrJ1NEQ5AIg388AubGXEJEkQ8t', 'laweta-z-autem-na-parkingu-pomoc-drogowa-pabianice'),
    (9, 'AF1QipOws8D63tK6NA5h08hihNIxxIbEUF61ad3O0_n7', 'laweta-z-malym-autem-na-poboczu-pabianice'),
    (10, 'AF1QipO6uXMIkJtD7I1DQ-P4ZBrFWaJNLWplPSPBNF18', 'awaryjna-wymiana-kola-na-miejscu-pabianice'),
    (11, 'AF1QipPNQ_Vl7A6VJy9VQYt0VJ4j0VskjFPRj8-YAYSF', 'laweta-z-przyczepa-transport-ulica-pabianice'),
    (12, 'AF1QipPK5EdP7Uxt1uwfHafz7lGTDpIy4ZodLjEe_Bj7', 'czerwone-auto-na-lawecie-osiedle-pabianice'),
    (13, 'AF1QipMB2_dWtK4utmrc26AdqSzd-Q9oYM0msVSYuR5k', 'laweta-transport-auta-droga-ekspresowa-pabianice'),
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
