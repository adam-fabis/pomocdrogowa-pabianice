#!/usr/bin/env python3
"""Generuje public_html/assets/img/<slug>.jpg (max 1600) + AVIF/WebP {480,800,1200,1600},
thumbs/<slug>.jpg (800) + {400,800}, hero/<slug>.jpg (1920) + {960,1440,1920}, variants.json, manifest.json.
Źródła: zrodla/google/NN-<slug>.jpg (fetch_google_photos.py) i zrodla/klient/NN-<slug>.jpg (prepare_client_photos.py).
Wszystkie zdjęcia trafiają do galerii w kolejności GALLERY_ORDER. Listy szerokości są ograniczone do szerokości źródła
(zdjęcia z Google mają max 1080 px), więc hero dostaje np. {960, 1080}. Pliki aktualne (mtime >= źródło) są pomijane.
Zdjęcia ze starą białą lawetą (klient nie ma jej od ~2024) leżą w zrodla/archiwum-biala-laweta/ i nie są budowane,
zdjęcie z lawetą konkurencji (dawne 07) — w zrodla/archiwum-konkurencja/."""
import os, re, json, glob
import io
from PIL import Image, ImageOps, ImageCms
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SP = os.path.join(ROOT, 'tools')
SRCS = [os.path.join(ROOT, 'zrodla/google'), os.path.join(ROOT, 'zrodla/klient')]
OUT = os.path.join(ROOT, 'public_html/assets/img')
# idx -> role(s). Role z sufiksem -hero dostają dodatkowo wariant hero/ (4 role hero muszą leżeć na 4 różnych zdjęciach).
ROLES = {
    2: ['home-about', 'kontakt-hero'],
    14: ['oferta-naprawa', 'oferta-paliwo', 'home-svc-5'],   # paliwo tymczasowo: klient ma przysłać zdjęcie
    15: ['home-hero', 'home-svc-1'],
    16: ['cta-bg', 'oferta-hero'],
    17: ['home-oc', 'home-svc-8', 'oferta-oc'],
    18: ['galeria-hero', 'oferta-holowanie', 'home-svc-6'],
    19: ['oferta-opony', 'home-svc-4'],
    20: ['oferta-kolizja', 'home-svc-2'],
    21: ['oferta-transport', 'home-svc-7'],
    22: ['home-svc-3'],
    23: ['oferta-akumulator'],
}
# Kolejność kafelków w galerii (idx). Duże kafelki i liczba widocznych: BIG/VISIBLE w build_gallery.py.
GALLERY_ORDER = [16, 22, 17, 4, 25, 2, 24, 20, 11, 6, 18, 23, 19, 14, 10, 21, 15]
FULL_WIDTHS = [480, 800, 1200, 1600]; THUMB_WIDTHS = [400, 800]; HERO_WIDTHS = [960, 1440, 1920]
AVIF_Q, AVIF_SPEED, WEBP_Q, WEBP_METHOD = 55, 6, 78, 6

files = sorted((p for d in SRCS for p in glob.glob(d + '/*.jpg')), key=os.path.basename)
items = []
for p in files:
    m = re.match(r'(\d+)-(.+)\.jpg$', os.path.basename(p)); assert m, f'nazwa źródła musi mieć postać NN-slug.jpg: {p}'
    idx, slug = int(m.group(1)), m.group(2)
    items.append({'idx': idx, 'src': os.path.relpath(p, ROOT), 'slug': slug, 'roles': ROLES.get(idx, [])})
idxs = [it['idx'] for it in items]
assert sorted(idxs) == sorted(GALLERY_ORDER), f'źródła {sorted(idxs)} != GALLERY_ORDER {sorted(GALLERY_ORDER)}'
assert set(ROLES) <= set(idxs), f'role bez zdjęcia: {set(ROLES) - set(idxs)}'
assert len({it['slug'] for it in items}) == len(items), 'zduplikowany slug'
for it in items: it['gallery_pos'] = GALLERY_ORDER.index(it['idx'])

class LazyImage:
    def __init__(self, src): self.src = src; self._im = None
    def get(self):
        if self._im is None:
            im = ImageOps.exif_transpose(Image.open(self.src))
            icc = im.info.get('icc_profile')   # np. Display P3 z telefonu klienta; wyjście bez profilu = sRGB
            if icc:
                im = ImageCms.profileToProfile(im, ImageCms.ImageCmsProfile(io.BytesIO(icc)), ImageCms.createProfile('sRGB'), outputMode='RGB')
            self._im = im.convert('RGB')
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
# roles.json: rola -> slug (czytane przez partials/config.php pd_slug(); tools/ nie jest wgrywane na serwer)
json.dump({r: it['slug'] for it in items for r in it['roles']}, open(OUT + '/roles.json', 'w'), ensure_ascii=False, indent=1, sort_keys=True)
print('items', len(items), 'variants', len(variants), 'hero', sum(1 for k in variants if k.startswith('hero/')))
