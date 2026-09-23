<?php
if (!defined('PD_APP')) { define('PD_APP', true); } // bezpośrednio (Apache) lub przez dispatcher w index.php (nginx)
$page = 'kontakt';
$title = 'Kontakt 24/7: 517 574 330 | Pomoc Drogowa Pabianice';
$desc = 'Zadzwoń: +48 517 574 330, całą dobę. Pomoc Drogowa Łukasz Rogowski, ul. Stanisława Moniuszki 41, 95-200 Pabianice. Dojazd w Pabianicach, Łodzi i okolicy.';
$path = '/kontakt/';
$pageJs = 'assets/js/kontakt.js';
$noCta = true; // design strony kontakt nie ma sekcji CTA (numer jest w hero)
include __DIR__ . '/partials/config.php';
$ogImage = 'assets/img/hero/' . pd_slug('kontakt-hero') . '.jpg';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <!-- HERO -->
  <section id="top" style="position:relative;overflow:hidden;background:#141414;scroll-margin-top:var(--navh)">
    <?php echo pd_picture('hero/' . pd_slug('kontakt-hero'), 'Laweta Pomocy Drogowej', 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.35', '100vw', true); ?>
    <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(20,20,20,.6) 0%,rgba(20,20,20,.85) 100%)"></div>
    <div style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(48px,8vw,80px) 20px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:16px">
      <nav aria-label="Okruszki" style="font-size:14px;color:#aaa"><a href="/" style="color:#aaa">Strona główna</a> <span style="color:#666">/</span> <span style="color:#f5c518">Kontakt</span></nav>
      <h1 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(44px,7vw,80px);line-height:.95">Kontakt</h1>
      <p style="margin:0;max-width:560px;font-size:18px;line-height:1.55;color:#ddd">Najszybciej przez telefon — odbieramy całą dobę, 7 dni w tygodniu.</p>
      <a href="<?php echo $PHONE_HREF; ?>" class="hov-big press" style="display:flex;align-items:center;gap:16px;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(52px,9vw,110px);line-height:1;color:#fff;background:#111;padding:6px 32px 10px;border:5px solid #f5c518;margin-top:10px;transition:color .2s,transform .2s cubic-bezier(.2,.7,.2,1),box-shadow .2s"><?php echo $PHONE_SHORT; ?></a>
      <div style="display:flex;align-items:center;gap:10px;font-size:15px;font-weight:700;color:#ccc"><span class="dot"></span>Dyżur teraz — dzwoń śmiało, także w nocy</div>
    </div>
  </section>
  <div style="height:16px;background:repeating-linear-gradient(135deg,#f5c518 0 22px,#111 22px 44px)"></div>

  <!-- KARTY KONTAKTU -->
  <section aria-label="Dane kontaktowe" style="max-width:1200px;margin:0 auto;padding:clamp(40px,7vw,64px) 20px 0">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:3px">
      <a href="<?php echo $PHONE_HREF; ?>" class="hov-dark" style="background:#2a2a2a;padding:28px;display:flex;flex-direction:column;gap:10px;border-top:4px solid #e8342a;color:#f2f2f2;transition:background-color .2s,transform .2s"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">TELEFON 24/7</div><div style="font-family:'Barlow Condensed';font-weight:800;font-size:32px;color:#f5c518;line-height:1"><?php echo $PHONE; ?></div><div style="font-size:14px;color:#aaa">Kliknij, aby zadzwonić</div></a>
      <div style="background:#2a2a2a;padding:28px;display:flex;flex-direction:column;gap:10px;border-top:4px solid #f5c518"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">ADRES</div><div style="font-family:'Barlow Condensed';font-weight:800;font-size:28px;line-height:1.05"><?php echo $ADDR1; ?></div><div style="font-size:15px;color:#ccc"><?php echo $ADDR2; ?></div></div>
      <div style="background:#2a2a2a;padding:28px;display:flex;flex-direction:column;gap:10px;border-top:4px solid #f5c518"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">GODZINY</div><div style="font-family:'Barlow Condensed';font-weight:800;font-size:32px;color:#f5c518;line-height:1">Całą dobę</div><div style="font-size:15px;color:#ccc">Poniedziałek – Niedziela, również w święta</div></div>
      <div style="background:#2a2a2a;padding:28px;display:flex;flex-direction:column;gap:12px;border-top:4px solid #f5c518"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">ZNAJDŹ NAS</div><a href="<?php echo $GMAPS_PLACE; ?>" target="_blank" rel="noopener" style="font-weight:700;font-size:17px;text-decoration:underline">Wizytówka Google · 5.0 ★</a><a href="<?php echo $FB; ?>" target="_blank" rel="noopener" style="font-weight:700;font-size:17px;text-decoration:underline">Facebook</a></div>
    </div>
  </section>

  <!-- ZANIM ZADZWONISZ + MAPA -->
  <section aria-labelledby="zanim-h" style="max-width:1200px;margin:0 auto;padding:clamp(40px,7vw,64px) 20px clamp(52px,9vw,80px);display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:48px;align-items:start">
    <div style="display:flex;flex-direction:column;gap:18px">
      <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518">ZANIM ZADZWONISZ</div>
      <h2 id="zanim-h" style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(34px,6vw,48px);line-height:1">Co warto mieć pod ręką</h2>
      <p style="margin:0;font-size:16px;line-height:1.6;color:#bbb">Nie musisz mieć wszystkiego — ale te informacje przyspieszą dojazd.</p>
      <div style="display:flex;flex-direction:column;border-top:1px solid #444">
        <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid #444;align-items:flex-start"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:24px;color:#f5c518;width:32px;flex:none">01</div><div><div style="font-weight:700;font-size:17px">Gdzie stoisz</div><div style="font-size:15px;color:#aaa;margin-top:2px">Ulica, kilometr trasy lub punkt orientacyjny.</div></div></div>
        <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid #444;align-items:flex-start"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:24px;color:#f5c518;width:32px;flex:none">02</div><div><div style="font-weight:700;font-size:17px">Jakie to auto</div><div style="font-size:15px;color:#aaa;margin-top:2px">Marka, model, osobowe czy dostawcze.</div></div></div>
        <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid #444;align-items:flex-start"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:24px;color:#f5c518;width:32px;flex:none">03</div><div><div style="font-weight:700;font-size:17px">Co się stało</div><div style="font-size:15px;color:#aaa;margin-top:2px">Nie odpala, kapeć, kolizja — wystarczy krótko.</div></div></div>
        <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid #444;align-items:flex-start"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:24px;color:#f5c518;width:32px;flex:none">04</div><div><div style="font-weight:700;font-size:17px">Dokąd holować</div><div style="font-size:15px;color:#aaa;margin-top:2px">Jeśli potrzebna laweta — adres warsztatu lub domu.</div></div></div>
      </div>
      <div style="background:#2a2a2a;border-left:4px solid #e8342a;padding:16px 18px;font-size:15px;line-height:1.55;color:#ddd"><b>Stoisz na drodze?</b> Włącz światła awaryjne, ustaw trójkąt i poczekaj w bezpiecznym miejscu, najlepiej za barierą.</div>
    </div>
    <div style="display:flex;flex-direction:column">
      <div style="height:clamp(320px,45vw,480px);border:3px solid #333;overflow:hidden;background:#2a2a2a">
        <div data-leaflet role="region" aria-label="Mapa dojazdu" style="width:100%;height:100%"></div>
      </div>
      <div style="background:#111;padding:14px 18px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:15px"><span>Moniuszki 41, Pabianice</span><a href="https://www.google.com/maps/dir/?api=1&amp;destination=Stanis%C5%82awa%20Moniuszki%2041%2C%20Pabianice" target="_blank" rel="noopener" style="font-weight:800">Wyznacz trasę →</a></div>
      <div style="margin-top:24px;display:flex;flex-direction:column;gap:12px">
        <div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">OBSZAR DZIAŁANIA</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px"><span style="background:#f5c518;color:#111;padding:7px 12px;font-size:14px;font-weight:800">Pabianice</span><span style="border:1px solid #666;padding:6px 12px;font-size:14px">Łódź</span><span style="border:1px solid #666;padding:6px 12px;font-size:14px">Konstantynów Łódzki</span><span style="border:1px solid #666;padding:6px 12px;font-size:14px">Ksawerów</span><span style="border:1px solid #666;padding:6px 12px;font-size:14px">Rzgów</span><span style="border:1px solid #666;padding:6px 12px;font-size:14px">Dobroń</span><span style="border:1px solid #666;padding:6px 12px;font-size:14px">Łask</span><span style="border:1px solid #666;padding:6px 12px;font-size:14px">Zduńska Wola</span><span style="border:1px solid #666;padding:6px 12px;font-size:14px">Lutomiersk</span><span style="background:#111;color:#f5c518;font-weight:800;padding:6px 12px;font-size:14px">S8 · S14 · A1</span></div>
      </div>
    </div>
  </section>
<?php
include __DIR__ . '/partials/cta.php';
include __DIR__ . '/partials/footer.php';
