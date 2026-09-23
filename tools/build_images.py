#!/usr/bin/env python3
"""Generuje public_html/assets/img/<slug>.jpg (max 1600) + AVIF/WebP {480,800,1200,1600},
thumbs/<slug>.jpg (800) + {400,800}, hero/<slug>.jpg (1920) + {960,1440,1920}, variants.json, manifest.json.
Źródło: zrodla/google/NN-<slug>.jpg (pobrane przez fetch_google_photos.py). Wszystkie zdjęcia trafiają do galerii
(gallery_pos = idx). Listy szerokości są ograniczone do szerokości źródła (zdjęcia z Google mają max 1080 px),
więc hero dostaje np. {960, 1080}. Pliki aktualne (mtime >= źródło) są pomijane."""
import os, re, json, glob
from PIL import Image, ImageOps
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SP = os.path.join(ROOT, 'tools')
SRC = os.path.join(ROOT, 'zrodla/google')
OUT = os.path.join(ROOT, 'public_html/assets/img')
# idx -> role(s). Role z sufiksem -hero dostają dodatkowo wariant hero/.
ROLES = {
    1: ['home-hero'],
    2: ['home-about', 'kontakt-hero'],
    3: ['oferta-hero', 'oferta-holowanie', 'home-svc-6'],
    4: ['oferta-transport', 'home-svc-7'],
    5: ['oferta-oc', 'home-svc-8'],
    6: ['oferta-akumulator', 'home-svc-1'],
    7: ['oferta-kolizja', 'home-svc-2'],
    8: ['home-oc'],
    9: ['cta-bg', 'galeria-hero'],
    10: ['oferta-opony', 'home-svc-4'],
    13: ['oferta-paliwo', 'home-svc-5'],
    14: ['oferta-naprawa', 'home-svc-3'],
}
FULL_WIDTHS = [480, 800, 1200, 1600]; THUMB_WIDTHS = [400, 800]; HERO_WIDTHS = [960, 1440, 1920]
AVIF_Q, AVIF_SPEED, WEBP_Q, WEBP_METHOD = 55, 6, 78, 6

files = sorted(glob.glob(SRC + '/*.jpg'))
assert len(files) == 14, f'oczekiwano 14 zdjęć w {SRC}, jest {len(files)}'
items = []
for p in files:
    m = re.match(r'(\d+)-(.+)\.jpg$', os.path.basename(p))
    idx, slug = int(m.group(1)), m.group(2)
    items.append({'idx': idx, 'src': os.path.relpath(p, ROOT), 'slug': slug, 'roles': ROLES.get(idx, []), 'gallery_pos': idx})
assert len({it['slug'] for it in items}) == 14, 'zduplikowany slug'

class LazyImage:
    def __init__(self, src): self.src = src; self._im = None
    def get(self):
        if self._im is None: self._im = ImageOps.exif_transpose(Image.open(self.src)).convert('RGB')
        return self._im

def up_to_date(path, src_mtime): return os.path.exists(path) and os.path.getmtime(path) >= src_mtime
def fit(im, box):
    if im.width <= box[0] and im.height <= box[1]: return im
    return ImageOps.contain(im, box, Image.LANCZOS)
def save_jpg(lazy, path, box, q, src_mtime):
    if up_to_date(path, src_mtime):
        with Image.open(path) as ex: return ex.width, ex.height
    im2 = fit(lazy.get(), box); im2.save(path, 'JPEG', quality=q, optimize=True, progressive=True)
    return im2.width, im2.height
def widths_for(std, actual_w):
    ws = [w for w in std if w <= actual_w]
    if actual_w not in ws: ws.append(actual_w)
    return sorted(set(ws))
def save_nextgen(lazy, prefix, widths, src_mtime):
    for w in widths:
        ap, wp = f'{prefix}-{w}.avif', f'{prefix}-{w}.webp'
        na, nw = not up_to_date(ap, src_mtime), not up_to_date(wp, src_mtime)
        if na or nw:
            im2 = ImageOps.contain(lazy.get(), (w, w), Image.LANCZOS)
            if na: im2.save(ap, 'AVIF', quality=AVIF_Q, speed=AVIF_SPEED)
            if nw: im2.save(wp, 'WEBP', quality=WEBP_Q, method=WEBP_METHOD)

os.makedirs(OUT + '/thumbs', exist_ok=True); os.makedirs(OUT + '/hero', exist_ok=True)
variants = {}
for it in items:
    src = os.path.join(ROOT, it['src'])
    mt = os.path.getmtime(src); lazy = LazyImage(src)
    with Image.open(src) as h: it['w'], it['h'] = h.size
    fw, fh = save_jpg(lazy, f"{OUT}/{it['slug']}.jpg", (1600, 1600), 82, mt)
    ws = widths_for(FULL_WIDTHS, fw); save_nextgen(lazy, f"{OUT}/{it['slug']}", ws, mt)
    variants[it['slug']] = {'w': fw, 'h': fh, 'widths': ws}
    tw, th = save_jpg(lazy, f"{OUT}/thumbs/{it['slug']}.jpg", (800, 800), 78, mt)
    tws = widths_for(THUMB_WIDTHS, tw); save_nextgen(lazy, f"{OUT}/thumbs/{it['slug']}", tws, mt)
    variants['thumbs/' + it['slug']] = {'w': tw, 'h': th, 'widths': tws}
    if any(r.endswith('-hero') for r in it['roles']):
        hw, hh = save_jpg(lazy, f"{OUT}/hero/{it['slug']}.jpg", (1920, 1920), 80, mt)
        hws = widths_for(HERO_WIDTHS, hw); save_nextgen(lazy, f"{OUT}/hero/{it['slug']}", hws, mt)
        variants['hero/' + it['slug']] = {'w': hw, 'h': hh, 'widths': hws}

VAR_RE = re.compile(r'^(.*)-(\d+)\.(?:avif|webp)$')
def clean_dir(d, keep):
    for f in sorted(os.listdir(d)):
        fp = os.path.join(d, f)
        if os.path.isdir(fp) or f.startswith('.') or f in ('variants.json', 'logo.png'): continue
        base = f[:-4] if f.endswith('.jpg') else (VAR_RE.match(f).group(1) if VAR_RE.match(f) else None)
        if base is not None and base not in keep: os.remove(fp); print('usunięto', fp)
clean_dir(OUT, {it['slug'] for it in items})
clean_dir(OUT + '/thumbs', {it['slug'] for it in items})
clean_dir(OUT + '/hero', {it['slug'] for it in items if any(r.endswith('-hero') for r in it['roles'])})
json.dump({'items': items}, open(SP + '/manifest.json', 'w'), ensure_ascii=False, indent=1)
json.dump(variants, open(OUT + '/variants.json', 'w'), ensure_ascii=False, indent=1, sort_keys=True)
print('items', len(items), 'variants', len(variants), 'hero', sum(1 for k in variants if k.startswith('hero/')))
