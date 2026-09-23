#!/usr/bin/env python3
"""Pobiera Barlow + Barlow Condensed (latin, latin-ext) z Google Fonts jako woff2 do public_html/assets/fonts/
i generuje tools/fonts.css z blokiem @font-face (ścieżki absolutne /assets/fonts/...)."""
import re, os, subprocess

def http_get(url):
    """curl zamiast urllib: python.org 3.12 nie ma certyfikatów CA (CERTIFICATE_VERIFY_FAILED)."""
    return subprocess.run(['curl', '-fsSL', '-A', UA, url], check=True, capture_output=True).stdout
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'public_html/assets/fonts')
CSS_URL = ('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800'
           '&family=Barlow:wght@400;500;600;700&display=swap')
UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36'
os.makedirs(OUT, exist_ok=True)
css = http_get(CSS_URL).decode()
blocks = re.findall(r'/\* (\w[\w-]*) \*/\s*@font-face \{(.*?)\}', css, re.S)
out = []
for subset, body in blocks:
    if subset not in ('latin', 'latin-ext'):
        continue
    fam = re.search(r"font-family: '([^']+)'", body).group(1)
    weight = re.search(r'font-weight: (\d+)', body).group(1)
    url = re.search(r'url\((https://[^)]+\.woff2)\)', body).group(1)
    urange = re.search(r'unicode-range: ([^;]+);', body).group(1)
    fname = f"{fam.lower().replace(' ', '-')}-{weight}-{subset}.woff2"
    path = os.path.join(OUT, fname)
    if not os.path.exists(path):
        open(path, 'wb').write(http_get(url))
    out.append(f"@font-face {{\n  font-family: '{fam}';\n  font-style: normal;\n  font-weight: {weight};\n  font-display: swap;\n  src: url('/assets/fonts/{fname}') format('woff2');\n  unicode-range: {urange};\n}}")
open(os.path.join(ROOT, 'tools/fonts.css'), 'w').write('\n'.join(out) + '\n')
print(len(out), 'font faces,', len(os.listdir(OUT)), 'files')
