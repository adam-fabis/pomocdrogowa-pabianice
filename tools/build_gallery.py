#!/usr/bin/env python3
"""Przepisuje kafelki galerii w public_html/galeria.php (blok GALLERY:START..END) z manifest.json + alts.json + variants.json."""
import json, os, html, re
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SP = os.path.join(ROOT, 'tools')
m = json.load(open(SP + '/manifest.json'))
alts = json.load(open(SP + '/alts.json'))
variants = json.load(open(ROOT + '/public_html/assets/img/variants.json'))
gal = sorted(m['items'], key=lambda x: x['gallery_pos'])
BIG = {0, 7}; VISIBLE = 10
SIZES_BIG = '(max-width:699px) 100vw, 50vw'; SIZES = '(max-width:699px) 50vw, 25vw'
def srcset(slug, widths, ext): return ', '.join(f'/assets/img/thumbs/{slug}-{w}.{ext} {w}w' for w in widths)
tiles = []
for n, it in enumerate(gal):
    slug, alt = it['slug'], alts[str(it['idx'])]
    v = variants['thumbs/' + slug]; sizes = SIZES_BIG if n in BIG else SIZES
    hidden = ' hidden' if n >= VISIBLE else ''; big = ' is-big' if n in BIG else ''
    lazy = '' if n < 4 else ' loading="lazy"'
    tiles.append(f'''      <button type="button" class="gal-cell{big}" data-shot="{n}" data-full="/assets/img/{slug}.jpg" aria-label="Powiększ zdjęcie: {html.escape(alt, quote=True)}"{hidden}>
        <picture style="display:contents"><source type="image/avif" srcset="{srcset(slug, v['widths'], 'avif')}" sizes="{sizes}"><source type="image/webp" srcset="{srcset(slug, v['widths'], 'webp')}" sizes="{sizes}"><img src="/assets/img/thumbs/{slug}.jpg" alt="{html.escape(alt, quote=True)}" width="{v['w']}" height="{v['h']}"{lazy} decoding="async"></picture>
      </button>
''')
p = ROOT + '/public_html/galeria.php'
s = open(p, encoding='utf-8').read()
s, n1 = re.subn(r'(<!-- GALLERY:START -->\n).*?(<!-- GALLERY:END -->)', lambda mm: mm.group(1) + ''.join(tiles) + mm.group(2), s, flags=re.S)
s, n2 = re.subn(r'<span data-more-count>\d+</span>', f'<span data-more-count>{max(0, len(gal) - VISIBLE)}</span>', s)
assert n1 == 1 and n2 == 1, (n1, n2)
open(p, 'w', encoding='utf-8').write(s)
print('tiles', len(tiles), 'hidden', max(0, len(gal) - VISIBLE))
