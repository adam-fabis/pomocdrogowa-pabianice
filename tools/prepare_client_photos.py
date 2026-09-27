#!/usr/bin/env python3
"""Kopiuje zdjęcia przesłane przez klienta (folder "poprawki i materiały", poza repo) do zrodla/klient/NN-<slug>.jpg,
rozmywając tablice rejestracyjne klientów i osób trzecich (tak jak na zdjęciach z wizytówki Google).
Tablice firmowe (laweta EL 3RV60) zostają. Numery NN kontynuują numerację zrodla/google (role w build_images.py)."""
import os, shutil
from PIL import Image, ImageFilter
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
IN = os.path.join(ROOT, 'poprawki i materiały')
OUT = os.path.join(ROOT, 'zrodla/klient')
# (idx, plik od klienta, slug, prostokąty do rozmycia (x0, y0, x1, y1) w pikselach oryginału)
PHOTOS = [
    (15, 'ca227e73-9d6c-4291-a432-6f9c2d0ff5e7.jpg', 'awaria-auta-droga-ekspresowa-laweta-pabianice',
     [(1414, 444, 1486, 470)]),                              # Skoda klienta
    (16, 'download (2).jpg', 'zolta-laweta-z-autem-na-parkingu-pabianice',
     [(1654, 464, 1740, 496), (1616, 392, 1660, 418)]),      # Opel i SUV na parkingu
    (17, 'download (1).jpg', 'auto-zastepcze-audi-pabianice',
     [(522, 562, 652, 628)]),                                # Audi (dla spójności z resztą zdjęć)
    (18, '57ac0e8b-39de-42a3-937c-bc1d054b7b6d.jpg', 'holowanie-rozbitego-auta-laweta-noca-pabianice', []),
    (19, 'eac5c4c9-33e5-4ef9-b55a-260c4f6b425c 1.jpg', 'naprawa-przebitej-opony-na-miejscu-pabianice', []),  # wersja bez ludzi z przodu
]
os.makedirs(OUT, exist_ok=True)
for idx, name, slug, boxes in PHOTOS:
    src, dst = os.path.join(IN, name), os.path.join(OUT, f'{idx:02d}-{slug}.jpg')
    if not boxes:
        shutil.copyfile(src, dst)
    else:
        with Image.open(src) as orig: icc = orig.info.get('icc_profile'); im = orig.convert('RGB')
        for b in boxes:
            im.paste(im.crop(b).filter(ImageFilter.GaussianBlur(8)), b[:2])
        im.save(dst, 'JPEG', quality=95, icc_profile=icc)   # profil (Display P3) zostaje; build_images.py konwertuje do sRGB
    print(dst)
