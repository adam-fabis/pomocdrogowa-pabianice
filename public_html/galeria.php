<?php
if (!defined('PD_APP')) { define('PD_APP', true); } // bezpośrednio (Apache) lub przez dispatcher w index.php (nginx)
$page = 'galeria';
$title = 'Galeria: laweta w akcji | Pomoc Drogowa Pabianice';
$desc = 'Zdjęcia z realizacji: holowanie, transport pojazdów i pomoc na trasie w Pabianicach i okolicy. Pomoc Drogowa Łukasz Rogowski — zadzwoń 24/7: +48 517 574 330.';
$path = '/galeria/';
$preloadHero = false; // galeria nie ma obrazu hero na stronie (og:image = zdjęcie roli galeria-hero)
$pageJs = 'assets/js/galeria.js';
include __DIR__ . '/partials/config.php';
$ogImage = 'assets/img/hero/' . pd_slug('galeria-hero') . '.jpg';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <!-- NAGŁÓWEK -->
  <section id="top" style="background:#141414;border-bottom:1px solid #333;scroll-margin-top:var(--navh)">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(48px,8vw,72px) 20px clamp(36px,6vw,48px);display:flex;justify-content:space-between;align-items:flex-end;gap:24px;flex-wrap:wrap">
      <div style="display:flex;flex-direction:column;gap:16px;max-width:640px">
        <nav aria-label="Okruszki" style="font-size:14px;color:#aaa"><a href="/" style="color:#aaa">Strona główna</a> <span style="color:#666">/</span> <span style="color:#f5c518">Galeria</span></nav>
        <h1 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(44px,7vw,80px);line-height:.95">Galeria realizacji</h1>
        <p style="margin:0;font-size:18px;line-height:1.55;color:#ccc;text-wrap:pretty">Laweta w akcji — holowania, transporty i pomoc na trasie w Pabianicach i okolicy. Kliknij zdjęcie, żeby je powiększyć.</p>
      </div>
      <a href="<?php echo $GMAPS_PLACE; ?>" target="_blank" rel="noopener" class="hov-brand hov-lift-flat" style="border:2px solid #f5c518;color:#f5c518;font-weight:700;padding:12px 18px;font-size:15px;transition:background-color .2s,color .2s,transform .2s">Więcej zdjęć w wizytówce Google →</a>
    </div>
  </section>
  <div style="height:16px;background:repeating-linear-gradient(135deg,#f5c518 0 22px,#111 22px 44px)"></div>

  <!-- ZDJĘCIA (kafelki generuje tools/build_gallery.py) -->
  <section id="zdjecia" aria-label="Zdjęcia z realizacji" style="max-width:1200px;margin:0 auto;padding:clamp(32px,6vw,56px) 20px clamp(52px,9vw,80px);scroll-margin-top:var(--navh)">
    <div class="gal-grid">
<!-- GALLERY:START -->
      <button type="button" class="gal-cell is-big" data-shot="0" data-full="/assets/img/laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice.jpg" aria-label="Powiększ zdjęcie: Laweta z samochodem osobowym na drodze ekspresowej — Pomoc Drogowa Pabianice">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice-400.avif 400w, /assets/img/thumbs/laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice-600.avif 600w" sizes="(max-width:699px) 100vw, 50vw"><source type="image/webp" srcset="/assets/img/thumbs/laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice-400.webp 400w, /assets/img/thumbs/laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice-600.webp 600w" sizes="(max-width:699px) 100vw, 50vw"><img src="/assets/img/thumbs/laweta-z-samochodem-droga-ekspresowa-pomoc-drogowa-pabianice.jpg" alt="Laweta z samochodem osobowym na drodze ekspresowej — Pomoc Drogowa Pabianice" width="600" height="800" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="1" data-full="/assets/img/zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice.jpg" aria-label="Powiększ zdjęcie: Żółta laweta Pomocy Drogowej Łukasz Rogowski z SUV-em na platformie, Pabianice">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice-400.avif 400w, /assets/img/thumbs/zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice-800.avif 800w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice-400.webp 400w, /assets/img/thumbs/zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice-800.webp 800w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/zolta-laweta-pomoc-drogowa-lukasz-rogowski-pabianice.jpg" alt="Żółta laweta Pomocy Drogowej Łukasz Rogowski z SUV-em na platformie, Pabianice" width="800" height="583" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="2" data-full="/assets/img/holowanie-sedana-laweta-noca-pabianice.jpg" aria-label="Powiększ zdjęcie: Holowanie sedana na lawecie nocą — pomoc drogowa Pabianice">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/holowanie-sedana-laweta-noca-pabianice-400.avif 400w, /assets/img/thumbs/holowanie-sedana-laweta-noca-pabianice-600.avif 600w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/holowanie-sedana-laweta-noca-pabianice-400.webp 400w, /assets/img/thumbs/holowanie-sedana-laweta-noca-pabianice-600.webp 600w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/holowanie-sedana-laweta-noca-pabianice.jpg" alt="Holowanie sedana na lawecie nocą — pomoc drogowa Pabianice" width="600" height="800" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="3" data-full="/assets/img/transport-quada-na-lawecie-pabianice.jpg" aria-label="Powiększ zdjęcie: Transport quada na lawecie — Pomoc Drogowa Pabianice">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/transport-quada-na-lawecie-pabianice-400.avif 400w, /assets/img/thumbs/transport-quada-na-lawecie-pabianice-800.avif 800w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/transport-quada-na-lawecie-pabianice-400.webp 400w, /assets/img/thumbs/transport-quada-na-lawecie-pabianice-800.webp 800w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/transport-quada-na-lawecie-pabianice.jpg" alt="Transport quada na lawecie — Pomoc Drogowa Pabianice" width="800" height="450" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="4" data-full="/assets/img/czarny-suv-na-lawecie-pomoc-drogowa-pabianice.jpg" aria-label="Powiększ zdjęcie: Czarny SUV na lawecie przy ulicy — holowanie Pabianice">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/czarny-suv-na-lawecie-pomoc-drogowa-pabianice-400.avif 400w, /assets/img/thumbs/czarny-suv-na-lawecie-pomoc-drogowa-pabianice-600.avif 600w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/czarny-suv-na-lawecie-pomoc-drogowa-pabianice-400.webp 400w, /assets/img/thumbs/czarny-suv-na-lawecie-pomoc-drogowa-pabianice-600.webp 600w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/czarny-suv-na-lawecie-pomoc-drogowa-pabianice.jpg" alt="Czarny SUV na lawecie przy ulicy — holowanie Pabianice" width="600" height="800" loading="lazy" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="5" data-full="/assets/img/holowanie-auta-po-awarii-z-osiedla-pabianice.jpg" aria-label="Powiększ zdjęcie: Laweta zabiera auto po awarii z osiedla — Pabianice">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/holowanie-auta-po-awarii-z-osiedla-pabianice-400.avif 400w, /assets/img/thumbs/holowanie-auta-po-awarii-z-osiedla-pabianice-800.avif 800w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/holowanie-auta-po-awarii-z-osiedla-pabianice-400.webp 400w, /assets/img/thumbs/holowanie-auta-po-awarii-z-osiedla-pabianice-800.webp 800w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/holowanie-auta-po-awarii-z-osiedla-pabianice.jpg" alt="Laweta zabiera auto po awarii z osiedla — Pabianice" width="800" height="450" loading="lazy" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="6" data-full="/assets/img/zaladunek-auta-na-lawete-noca-pabianice.jpg" aria-label="Powiększ zdjęcie: Załadunek samochodu na lawetę nocą — pomoc drogowa 24h Pabianice">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/zaladunek-auta-na-lawete-noca-pabianice-400.avif 400w, /assets/img/thumbs/zaladunek-auta-na-lawete-noca-pabianice-600.avif 600w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/zaladunek-auta-na-lawete-noca-pabianice-400.webp 400w, /assets/img/thumbs/zaladunek-auta-na-lawete-noca-pabianice-600.webp 600w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/zaladunek-auta-na-lawete-noca-pabianice.jpg" alt="Załadunek samochodu na lawetę nocą — pomoc drogowa 24h Pabianice" width="600" height="800" loading="lazy" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell is-big" data-shot="7" data-full="/assets/img/laweta-z-autem-na-parkingu-pomoc-drogowa-pabianice.jpg" aria-label="Powiększ zdjęcie: Laweta z samochodem na parkingu — Pomoc Drogowa Łukasz Rogowski">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/laweta-z-autem-na-parkingu-pomoc-drogowa-pabianice-400.avif 400w, /assets/img/thumbs/laweta-z-autem-na-parkingu-pomoc-drogowa-pabianice-600.avif 600w" sizes="(max-width:699px) 100vw, 50vw"><source type="image/webp" srcset="/assets/img/thumbs/laweta-z-autem-na-parkingu-pomoc-drogowa-pabianice-400.webp 400w, /assets/img/thumbs/laweta-z-autem-na-parkingu-pomoc-drogowa-pabianice-600.webp 600w" sizes="(max-width:699px) 100vw, 50vw"><img src="/assets/img/thumbs/laweta-z-autem-na-parkingu-pomoc-drogowa-pabianice.jpg" alt="Laweta z samochodem na parkingu — Pomoc Drogowa Łukasz Rogowski" width="600" height="800" loading="lazy" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="8" data-full="/assets/img/laweta-z-malym-autem-na-poboczu-pabianice.jpg" aria-label="Powiększ zdjęcie: Laweta z małym autem na poboczu drogi — okolice Pabianic">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/laweta-z-malym-autem-na-poboczu-pabianice-400.avif 400w, /assets/img/thumbs/laweta-z-malym-autem-na-poboczu-pabianice-600.avif 600w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/laweta-z-malym-autem-na-poboczu-pabianice-400.webp 400w, /assets/img/thumbs/laweta-z-malym-autem-na-poboczu-pabianice-600.webp 600w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/laweta-z-malym-autem-na-poboczu-pabianice.jpg" alt="Laweta z małym autem na poboczu drogi — okolice Pabianic" width="600" height="800" loading="lazy" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="9" data-full="/assets/img/awaryjna-wymiana-kola-na-miejscu-pabianice.jpg" aria-label="Powiększ zdjęcie: Awaryjna wymiana koła na miejscu — mobilny serwis Pabianice">
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/awaryjna-wymiana-kola-na-miejscu-pabianice-400.avif 400w, /assets/img/thumbs/awaryjna-wymiana-kola-na-miejscu-pabianice-800.avif 800w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/awaryjna-wymiana-kola-na-miejscu-pabianice-400.webp 400w, /assets/img/thumbs/awaryjna-wymiana-kola-na-miejscu-pabianice-800.webp 800w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/awaryjna-wymiana-kola-na-miejscu-pabianice.jpg" alt="Awaryjna wymiana koła na miejscu — mobilny serwis Pabianice" width="800" height="450" loading="lazy" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="10" data-full="/assets/img/laweta-z-przyczepa-transport-ulica-pabianice.jpg" aria-label="Powiększ zdjęcie: Laweta z przyczepą w transporcie ulicą — Pabianice" hidden>
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/laweta-z-przyczepa-transport-ulica-pabianice-400.avif 400w, /assets/img/thumbs/laweta-z-przyczepa-transport-ulica-pabianice-800.avif 800w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/laweta-z-przyczepa-transport-ulica-pabianice-400.webp 400w, /assets/img/thumbs/laweta-z-przyczepa-transport-ulica-pabianice-800.webp 800w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/laweta-z-przyczepa-transport-ulica-pabianice.jpg" alt="Laweta z przyczepą w transporcie ulicą — Pabianice" width="800" height="450" loading="lazy" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="11" data-full="/assets/img/czerwone-auto-na-lawecie-osiedle-pabianice.jpg" aria-label="Powiększ zdjęcie: Czerwone auto na lawecie na osiedlu — holowanie Pabianice" hidden>
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/czerwone-auto-na-lawecie-osiedle-pabianice-400.avif 400w, /assets/img/thumbs/czerwone-auto-na-lawecie-osiedle-pabianice-600.avif 600w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/czerwone-auto-na-lawecie-osiedle-pabianice-400.webp 400w, /assets/img/thumbs/czerwone-auto-na-lawecie-osiedle-pabianice-600.webp 600w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/czerwone-auto-na-lawecie-osiedle-pabianice.jpg" alt="Czerwone auto na lawecie na osiedlu — holowanie Pabianice" width="600" height="800" loading="lazy" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="12" data-full="/assets/img/laweta-transport-auta-droga-ekspresowa-pabianice.jpg" aria-label="Powiększ zdjęcie: Laweta transportuje auto drogą ekspresową — pomoc drogowa Pabianice" hidden>
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/laweta-transport-auta-droga-ekspresowa-pabianice-400.avif 400w, /assets/img/thumbs/laweta-transport-auta-droga-ekspresowa-pabianice-600.avif 600w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/laweta-transport-auta-droga-ekspresowa-pabianice-400.webp 400w, /assets/img/thumbs/laweta-transport-auta-droga-ekspresowa-pabianice-600.webp 600w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/laweta-transport-auta-droga-ekspresowa-pabianice.jpg" alt="Laweta transportuje auto drogą ekspresową — pomoc drogowa Pabianice" width="600" height="800" loading="lazy" decoding="async"></picture>
      </button>
      <button type="button" class="gal-cell" data-shot="13" data-full="/assets/img/mobilny-serwis-samochodowy-van-pabianice.jpg" aria-label="Powiększ zdjęcie: Van mobilnego serwisu samochodowego Pomoc Drogowa Pabianice" hidden>
        <picture style="display:contents"><source type="image/avif" srcset="/assets/img/thumbs/mobilny-serwis-samochodowy-van-pabianice-400.avif 400w, /assets/img/thumbs/mobilny-serwis-samochodowy-van-pabianice-800.avif 800w" sizes="(max-width:699px) 50vw, 25vw"><source type="image/webp" srcset="/assets/img/thumbs/mobilny-serwis-samochodowy-van-pabianice-400.webp 400w, /assets/img/thumbs/mobilny-serwis-samochodowy-van-pabianice-800.webp 800w" sizes="(max-width:699px) 50vw, 25vw"><img src="/assets/img/thumbs/mobilny-serwis-samochodowy-van-pabianice.jpg" alt="Van mobilnego serwisu samochodowego Pomoc Drogowa Pabianice" width="800" height="409" loading="lazy" decoding="async"></picture>
      </button>
<!-- GALLERY:END -->
    </div>
    <div style="display:flex;justify-content:center;margin-top:32px" data-more-wrap>
      <button type="button" data-more class="hov-brand hov-lift-flat press" style="all:unset;cursor:pointer;border:2px solid #f5c518;color:#f5c518;font-weight:800;font-size:17px;padding:14px 28px;transition:background-color .2s,color .2s,transform .2s cubic-bezier(.2,.7,.2,1)">Zobacz więcej zdjęć (<span data-more-count>4</span>)</button>
    </div>
  </section>

  <!-- LIGHTBOX -->
  <div class="lb" data-lightbox role="dialog" aria-modal="true" aria-label="Podgląd zdjęcia" tabindex="-1">
    <img data-lbimg src="" alt="" style="display:none">
    <button type="button" data-lbprev aria-label="Poprzednie" class="hov-brand" style="all:unset;cursor:pointer;position:absolute;left:16px;top:50%;margin-top:-28px;width:56px;height:56px;background:#111;border:2px solid #f5c518;color:#f5c518;font-size:30px;display:flex;align-items:center;justify-content:center;transition:background-color .2s,color .2s">‹</button>
    <button type="button" data-lbnext aria-label="Następne" class="hov-brand" style="all:unset;cursor:pointer;position:absolute;right:16px;top:50%;margin-top:-28px;width:56px;height:56px;background:#111;border:2px solid #f5c518;color:#f5c518;font-size:30px;display:flex;align-items:center;justify-content:center;transition:background-color .2s,color .2s">›</button>
    <button type="button" data-lbclose aria-label="Zamknij" class="hov-border" style="all:unset;cursor:pointer;position:absolute;right:16px;top:16px;width:48px;height:48px;background:#111;border:2px solid #555;color:#fff;font-size:26px;display:flex;align-items:center;justify-content:center;transition:border-color .2s">×</button>
    <div data-lbpos style="position:absolute;left:50%;bottom:20px;transform:translateX(-50%);background:#111;color:#ccc;font-size:14px;font-weight:700;padding:6px 12px"></div>
  </div>
<?php
include __DIR__ . '/partials/cta.php';
include __DIR__ . '/partials/footer.php';
