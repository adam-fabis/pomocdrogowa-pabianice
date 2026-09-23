<?php
define('PD_APP', true); // strażnik: partials/*.php działają tylko przez include (podstrony: if (!defined) — mogą być require'owane stąd)
// Dispatcher: na nginx (staging) wszystkie nieistniejące ścieżki trafiają tu przez try_files;
// na Apache .htaccess mapuje ładne URL-e bezpośrednio, więc ten blok widzi tylko "/".
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($reqPath !== '/' && $reqPath !== '/index.php') {
    if (preg_match('#^/(oferta|galeria|kontakt)/$#', $reqPath, $m)) { require __DIR__ . "/{$m[1]}.php"; return; }
    if ($reqPath === '/robots.txt') { require __DIR__ . '/robots.php'; return; }
    if ($reqPath === '/sitemap.xml') { require __DIR__ . '/sitemap.php'; return; }
    if (preg_match('#^/(oferta|galeria|kontakt)(\.php)?$#', $reqPath, $m)) { header("Location: /{$m[1]}/", true, 301); return; }
    require __DIR__ . '/404.php'; return;
}
if ($reqPath === '/index.php') { header('Location: /', true, 301); return; }

$page = 'home';
$title = 'Pomoc drogowa Pabianice 24/7 – laweta i holowanie';
$desc = 'Całodobowa pomoc drogowa Pabianice i okolice: holowanie, laweta, naprawa na miejscu, odpalanie auta, wymiana koła, dowóz paliwa, auto zastępcze z OC sprawcy. Tel. +48 517 574 330.';
$path = '/';
$pageJs = 'assets/js/home.js';
$faqLd = [
  ['Czy pomoc drogowa działa całodobowo?', 'Tak. Dojeżdżamy 24 godziny na dobę, 7 dni w tygodniu — również w weekendy i święta. Wystarczy zadzwonić: +48 517 574 330.'],
  ['Jaki obszar obsługujecie?', 'Pabianice i okolice: Łódź, Konstantynów Łódzki, Ksawerów, Rzgów, Dobroń, Łask, Zduńska Wola i inne. Pomagamy też na trasach S8, S14 i A1.'],
  ['Jak szybko dojedziecie?', 'Czas dojazdu zależy od miejsca zdarzenia — podajemy go od razu w rozmowie. W Pabianicach i najbliższej okolicy zwykle to kilkadziesiąt minut.'],
  ['Ile kosztuje auto zastępcze?', 'Jeśli sprawcą kolizji był ktoś inny, auto zastępcze jest dla Ciebie bezpłatne — rozliczamy się bezpośrednio z ubezpieczycielem sprawcy.'],
  ['Czy da się naprawić auto na miejscu?', 'Często tak — wymiana koła, odpalenie auta czy dowóz paliwa odbywają się na miejscu. Gdy naprawa nie jest możliwa, holujemy lawetą.'],
  ['Auto po awarii jedzie — czy potrzebuję lawety?', 'Nie zawsze, ale jazda z usterką może ją pogłębić. Zadzwoń — ocenimy, czy wystarczy pomoc na miejscu.'],
  ['Co zrobić po kolizji?', 'Zadbaj o bezpieczeństwo, włącz światła awaryjne, ustaw trójkąt i zadzwoń do nas. Zajmiemy się pojazdem i pomożemy w formalnościach.'],
];
$towns = ['Pabianice', 'Łódź', 'Konstantynów Łódzki', 'Ksawerów', 'Rzgów', 'Dobroń', 'Łask', 'Zduńska Wola', 'Lutomiersk'];
$services = [ // [tytuł, opis, rola zdjęcia]
  ['Awaria samochodu', 'Usterka na drodze, parkingu lub posesji — naprawiamy na miejscu albo holujemy do warsztatu.', 'home-svc-1'],
  ['Kolizja lub wypadek', 'Zabezpieczamy i usuwamy pojazd, pomagamy w formalnościach.', 'home-svc-2'],
  ['Rozładowany akumulator', 'Auto nie odpala? Przyjedziemy z urządzeniem rozruchowym i uruchomimy silnik na miejscu.', 'home-svc-3'],
  ['Przebita opona', 'Awaryjna wymiana koła i serwis opon na miejscu — bez holowania do wulkanizacji.', 'home-svc-4'],
  ['Brak paliwa', 'Zabrakło paliwa w trasie? Przywieziemy tyle, żebyś spokojnie dojechał do stacji.', 'home-svc-5'],
  ['Holowanie i laweta', 'Bezpieczny transport lawetą do warsztatu lub domu.', 'home-svc-6'],
  ['Transport pojazdów', 'Przewóz aut osobowych i dostawczych, także na dalsze trasy.', 'home-svc-7'],
  ['Auto zastępcze z OC', 'Nie jesteś sprawcą? Auto zastępcze bezpłatnie, rozliczamy się z ubezpieczycielem.', 'home-svc-8'],
];
$reviews = [ // zweryfikowane z wizytówki Google (źródło: strona localo klienta); dopisuj kolejne na końcu
  ['Jacek A', 'Serdecznie polecam usługi Pana Łukasza. Przyjazd na miejsce zdarzenia ekspresowy, szybko i sprawnie. Bez nerwów, w dobrej atmosferze. Po zdarzeniu pomoc w załatwieniu koniecznych formalności. Jednym zdaniem — właściwy człowiek na właściwym miejscu. Uczciwy i bardzo pomocny. W razie potrzeby śmiało polecam Pana Łukasza i jego usługi. 10/10'],
  ['Jakub G', 'Korzystałem z usług tej firmy pomocy drogowej i jestem bardzo zadowolony! Profesjonalne podejście, szybki dojazd na miejsce i uprzejma obsługa sprawiły, że stresująca sytuacja stała się o wiele łatwiejsza do zniesienia. Panowie byli świetnie przygotowani, wszystko załatwili sprawnie i bez zbędnych komplikacji. Zdecydowanie polecam każdemu, kto szuka rzetelnej i uczciwej pomocy drogowej!'],
  ['Paweł K', 'Bardzo polecam tę pomoc drogową! Szybki czas reakcji — zjawili się na miejscu w niecałe 30 minut od zgłoszenia. Profesjonalne podejście, sympatyczny kierowca, który od razu wiedział, co robić. Pomogli mi uruchomić samochód i dali kilka cennych wskazówek na przyszłość. Cena była rozsądna i adekwatna do usługi. Zdecydowanie warto mieć ich numer.'],
  ['Sylwester Frąc', 'Szybka reakcja i pełen profesjonalizm. Auto odmówiło posłuszeństwa w centrum Pabianic, a pomoc była naprawdę ekspresowa. Wszystko sprawnie załatwione, bez zbędnego czekania. Polecam serdecznie, cena adekwatna do wykonanej usługi.'],
  ['Izabela Młynarczyk', 'Miałam awarię auta i skorzystałam z pomocy tej firmy. Pełen profesjonalizm — szybki dojazd, miła obsługa, wszystko sprawnie i bez stresu. Kierowca bardzo pomocny i życzliwy. Auto zostało bezpiecznie przewiezione pod wskazany adres. Serdecznie polecam, rzetelna i godna zaufania firma!'],
  ['Jowita Jędrzejek', 'Pan przyjechał chwilę po moim telefonie, niewiele później miałam sprawne auto. Serdecznie polecam!'],
  ['Dusia De.', 'Szybko, sprawnie i bez żadnych problemów. Motocykl odebrany i bezpiecznie dowieziony pod dom. Bardzo dobry kontakt, konkretna obsługa i rozsądna cena. Zdecydowanie polecam!'],
  ['Martyna M', 'Błyskawiczna pomoc, laweta była na miejscu w 20 minut, dodam że była to fachowa obsługa w stresującej dla mnie sytuacji. Pełen profesjonalizm. Cena taka, jak umówiliśmy się przez telefon, bez ukrytych opłat. Polecam w 100%'],
  ['Adrianna L', 'Szybkie wyciągnięcie auta zakopanego w błocie. Polecam!'],
  ['Natalia', 'Korzystałam z pomocy drogowej 24h w Pabianicach i jestem bardzo zadowolona. Szybki dojazd, bezpieczne holowanie i konkretna obsługa. W sytuacji awaryjnej można na nich liczyć.'],
];
include __DIR__ . '/partials/config.php';
$ogImage = 'assets/img/hero/' . pd_slug('home-hero') . '.jpg';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <!-- HERO -->
  <section id="top" style="position:relative;overflow:hidden;background:#141414;scroll-margin-top:var(--navh)">
    <?php echo pd_picture('hero/' . pd_slug('home-hero'), 'Laweta z samochodem na drodze ekspresowej', 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.5', '100vw', true); ?>
    <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(20,20,20,.55) 0%,rgba(20,20,20,.8) 100%)"></div>
    <div style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(52px,10vw,84px) 20px 48px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:18px">
      <div style="font-size:14px;letter-spacing:4px;font-weight:700;color:#f5c518">CZYNNE 24/7 · PABIANICE I OKOLICE</div>
      <h1 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(40px,6vw,68px);line-height:1;text-wrap:balance">Całodobowa pomoc drogowa<br>— Pabianice i okolice</h1>
      <p style="margin:0;max-width:640px;font-size:18px;line-height:1.55;color:#ddd;text-wrap:pretty">Złapałeś gumę, auto nie odpala albo potrzebujesz lawety? Dojeżdżamy o każdej porze dnia i nocy — wiele usterek usuwamy na miejscu.</p>
      <a href="<?php echo $PHONE_HREF; ?>" class="hov-big press" style="font-family:'Barlow Condensed';font-weight:800;font-size:clamp(56px,10vw,120px);line-height:1;color:#fff;background:#111;padding:4px 32px 8px;border:5px solid #f5c518;box-shadow:12px 12px 0 rgba(0,0,0,.6);margin:14px 0 6px;transition:background-color .2s,color .2s,border-color .2s,transform .2s cubic-bezier(.2,.7,.2,1),box-shadow .2s"><?php echo $PHONE_SHORT; ?></a>
      <a href="#zakres" style="color:#f5c518;font-weight:700;font-size:16px;text-decoration:underline">Zobacz zakres pomocy →</a>
    </div>
    <div style="position:relative;border-top:1px solid rgba(255,255,255,.15);background:rgba(17,17,17,.75)">
      <div data-stats style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
        <div style="padding:22px 24px;border-right:1px solid rgba(255,255,255,.12)"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:36px;color:#f5c518;line-height:1">24/7</div><div style="font-size:14px;color:#bbb;margin-top:4px">Kontakt o każdej porze, także w święta</div></div>
        <a href="<?php echo $GREVIEWS; ?>" target="_blank" rel="noopener" style="padding:22px 24px;border-right:1px solid rgba(255,255,255,.12);display:block"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:36px;color:#f5c518;line-height:1">5.0 ★</div><div style="font-size:14px;color:#bbb;margin-top:4px;text-decoration:underline">82 opinie Google</div></a>
        <div style="padding:22px 24px;border-right:1px solid rgba(255,255,255,.12)"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:36px;color:#f5c518;line-height:1">Na miejscu</div><div style="font-size:14px;color:#bbb;margin-top:4px">Mobilny serwis — często bez holowania</div></div>
        <div style="padding:22px 24px"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:36px;color:#f5c518;line-height:1">Auto zastępcze</div><div style="font-size:14px;color:#bbb;margin-top:4px">Bezpłatnie z OC sprawcy</div></div>
      </div>
    </div>
  </section>

  <div style="height:16px;background:repeating-linear-gradient(135deg,#f5c518 0 22px,#111 22px 44px)"></div>

  <!-- O FIRMIE -->
  <section id="o-firmie" style="max-width:1200px;margin:0 auto;padding:clamp(52px,9vw,80px) 20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:56px;align-items:center;scroll-margin-top:var(--navh)">
    <div style="display:flex;flex-direction:column;gap:18px">
      <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518">O FIRMIE</div>
      <h2 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(34px,7vw,52px);line-height:1">Pomoc Drogowa<br>Łukasz Rogowski</h2>
      <p style="margin:0;font-size:17px;line-height:1.6;color:#ccc;text-wrap:pretty">Zapewniamy profesjonalną pomoc drogową 24h na terenie Pabianic i okolic — holowanie i transport pojazdów oraz awaryjną pomoc mechaniczną na drodze.</p>
      <p style="margin:0;font-size:17px;line-height:1.6;color:#ccc;text-wrap:pretty">Dzięki mobilnemu serwisowi wiele usterek usuwamy na miejscu, bez konieczności holowania auta do warsztatu. Po kolizji pomagamy też w formalnościach.</p>
      <a href="<?php echo $PHONE_HREF; ?>" style="font-family:'Barlow Condensed';font-weight:800;font-size:36px;color:#f5c518">Zadzwoń: <?php echo $PHONE_SHORT; ?> →</a>
    </div>
    <?php echo pd_picture(pd_slug('home-about'), 'Żółta laweta Pomocy Drogowej Łukasz Rogowski', 'width:calc(100% - 12px);aspect-ratio:4/3;object-fit:cover;display:block;border:3px solid #333;box-shadow:12px 12px 0 #f5c518', '(max-width:760px) 100vw, 50vw'); ?>
  </section>

  <!-- OBSZAR DZIAŁANIA -->
  <section id="obszar" style="background:#262626;border-top:1px solid #333;border-bottom:1px solid #333;scroll-margin-top:var(--navh)">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(52px,9vw,80px) 20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:56px;align-items:start">
      <div style="display:flex;flex-direction:column;gap:18px">
        <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518">OBSZAR DZIAŁANIA</div>
        <h2 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(34px,7vw,52px);line-height:1">Dojeżdżamy tam,<br>gdzie nas potrzebujesz</h2>
        <p style="margin:0;font-size:17px;line-height:1.6;color:#ccc">Bazujemy w Pabianicach. Pomagamy kierowcom na drogach lokalnych i na głównych trasach regionu.</p>
        <div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999;margin-top:6px">REGULARNIE OBSŁUGUJEMY:</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          <?php foreach ($towns as $i => $t): ?>
          <button type="button" class="chip<?php if ($i === 0) echo ' is-on'; ?>" data-town="<?php echo $t; ?>" aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>"><?php echo $t; ?></button>
          <?php endforeach; ?>
        </div>
        <div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999;margin-top:6px">POMAGAMY TAKŻE NA TRASACH:</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px"><span style="background:#111;color:#f5c518;font-weight:800;padding:6px 12px;font-size:15px">S8</span><span style="background:#111;color:#f5c518;font-weight:800;padding:6px 12px;font-size:15px">S14</span><span style="background:#111;color:#f5c518;font-weight:800;padding:6px 12px;font-size:15px">A1</span><span style="background:#111;color:#f5c518;font-weight:800;padding:6px 12px;font-size:15px">DK71</span></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:0">
        <div style="position:relative;height:420px;border:3px solid #333;overflow:hidden;background:#2a2a2a">
          <div data-leaflet data-map-area role="region" aria-label="Mapa obszaru działania" style="width:100%;height:100%"></div>
        </div>
        <div style="background:#111;padding:14px 18px;font-size:15px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap"><span>Wybrana miejscowość: <b data-town-label style="color:#f5c518">Pabianice</b> — dojazd całodobowo</span><a href="<?php echo $PHONE_HREF; ?>" style="font-weight:800"><?php echo $PHONE_SHORT; ?></a></div>
      </div>
    </div>
  </section>

  <!-- ZAKRES POMOCY -->
  <section id="zakres" style="max-width:1200px;margin:0 auto;padding:clamp(52px,9vw,80px) 20px;scroll-margin-top:var(--navh)">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:24px;flex-wrap:wrap;margin-bottom:36px">
      <div><div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518;margin-bottom:14px">ZAKRES POMOCY</div>
      <h2 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(34px,7vw,52px);line-height:1">W jakich sytuacjach pomagamy?</h2></div>
      <a href="/oferta/" class="hov-brand hov-lift-flat" style="border:2px solid #f5c518;color:#f5c518;font-weight:800;padding:12px 20px;font-size:16px;transition:background-color .2s,color .2s,transform .2s">Pełna oferta →</a>
    </div>
    <div class="svc-grid">
      <?php foreach ($services as $i => [$t, $d, $role]): ?>
      <div style="background:#2a2a2a;display:flex;flex-direction:column;color:#f2f2f2;border-bottom:4px solid #f5c518">
        <div style="position:relative"><?php echo pd_picture(pd_slug($role), $t, 'width:100%;aspect-ratio:16/10;object-fit:cover;display:block', '(max-width:559px) 100vw, (max-width:999px) 50vw, 25vw'); ?><div style="position:absolute;left:0;bottom:0;background:#f5c518;color:#111;font-family:'Barlow Condensed';font-weight:800;font-size:22px;padding:4px 12px;line-height:1.1"><?php echo sprintf('%02d', $i + 1); ?></div></div>
        <div style="padding:20px 22px 24px;display:flex;flex-direction:column;gap:10px">
        <h3 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:26px;line-height:1.05"><?php echo $t; ?></h3>
        <div style="font-size:15px;color:#aaa;line-height:1.45"><?php echo $d; ?></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- AUTO ZASTĘPCZE -->
  <section aria-labelledby="oc-h" style="background:#f5c518;color:#111">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(52px,9vw,72px) 20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:48px;align-items:center">
      <?php echo pd_picture(pd_slug('home-oc'), 'Transport samochodu lawetą', 'width:100%;aspect-ratio:4/3;object-fit:cover;display:block;border:4px solid #111', '(max-width:760px) 100vw, 50vw'); ?>
      <div style="display:flex;flex-direction:column;gap:16px">
        <div style="font-size:13px;letter-spacing:3px;font-weight:800">BEZPŁATNIE · Z OC SPRAWCY</div>
        <h2 id="oc-h" style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(34px,7vw,52px);line-height:1">Auto zastępcze<br>z OC sprawcy</h2>
        <p style="margin:0;font-size:17px;line-height:1.6;text-wrap:pretty">Kolizja lub wypadek z winy innego kierowcy? Zapewniamy <b>bezpłatny samochód zastępczy</b> — nie ponosisz kosztów wynajmu, rozliczamy się bezpośrednio z ubezpieczycielem sprawcy.</p>
        <a href="<?php echo $PHONE_HREF; ?>" class="hov-white hov-lift press" style="align-self:flex-start;background:#111;color:#f5c518;font-weight:800;padding:14px 22px;font-size:17px;margin-top:6px;transition:background-color .2s,color .2s,border-color .2s,transform .2s cubic-bezier(.2,.7,.2,1),box-shadow .2s">Zapytaj o auto zastępcze</a>
      </div>
    </div>
  </section>

  <!-- OPINIE -->
  <section id="opinie" style="max-width:1200px;margin:0 auto;padding:clamp(52px,9vw,80px) 20px;scroll-margin-top:var(--navh)">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:20px;flex-wrap:wrap;margin-bottom:32px">
      <div><div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518;margin-bottom:14px">OPINIE KLIENTÓW</div><h2 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(34px,7vw,52px);line-height:1">Co mówią kierowcy</h2></div>
      <a href="<?php echo $GREVIEWS; ?>" target="_blank" rel="noopener" class="hov-brand hov-lift press" style="display:flex;align-items:center;gap:12px;border:2px solid #f5c518;padding:10px 16px;transition:background-color .2s,color .2s,border-color .2s,transform .2s cubic-bezier(.2,.7,.2,1),box-shadow .2s"><span style="font-family:'Barlow Condensed';font-weight:800;font-size:34px;line-height:1">5.0</span><span style="font-size:14px;line-height:1.3">★★★★★<br><span style="text-decoration:underline">82 opinie Google</span></span></a>
    </div>
    <div class="rev-slider" data-slider>
      <div class="rev-track" data-track aria-live="polite">
      <?php foreach ($reviews as $i => [$a, $q]): ?>
      <article class="rev-card" data-slide="<?php echo $i; ?>" style="background:#262626;border-top:4px solid #f5c518;padding:28px;display:flex;flex-direction:column;gap:16px">
        <div style="display:flex;align-items:center;gap:14px"><div style="width:44px;height:44px;background:#f5c518;color:#111;font-weight:800;font-size:20px;display:flex;align-items:center;justify-content:center;flex:none"><?php echo mb_substr($a, 0, 1); ?></div><div><div style="font-weight:700;font-size:17px"><?php echo $a; ?></div><div style="font-size:13px;color:#999">Google · <span style="color:#f5c518">★★★★★</span></div></div></div>
        <blockquote style="margin:0"><p style="margin:0;font-size:16px;line-height:1.6;color:#e6e6e6;text-wrap:pretty">„<?php echo $q; ?>”</p></blockquote>
      </article>
      <?php endforeach; ?>
      </div>
      <div class="rev-nav" data-slider-nav hidden>
        <button type="button" class="rev-btn hov-brand" data-prev aria-label="Poprzednie opinie">‹</button>
        <div class="rev-dots" data-dots role="tablist" aria-label="Strony opinii"></div>
        <button type="button" class="rev-btn hov-brand" data-next aria-label="Następne opinie">›</button>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" style="background:#262626;border-top:1px solid #333;scroll-margin-top:var(--navh)">
    <div style="max-width:900px;margin:0 auto;padding:clamp(52px,9vw,80px) 20px">
      <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518;margin-bottom:14px">FAQ</div>
      <h2 style="margin:0 0 28px;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(34px,7vw,52px);line-height:1">Najczęstsze pytania</h2>
      <?php foreach ($faqLd as $i => [$q, $a]): ?>
      <div style="border-top:1px solid #444">
        <button type="button" class="faq-btn<?php if ($i === 0) echo ' is-open'; ?>" data-faq="<?php echo $i; ?>" aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>" aria-controls="faq-a<?php echo $i; ?>"><?php echo $q; ?><span class="faq-icon" aria-hidden="true"><i class="h"></i><i class="v"></i></span></button>
        <div class="faq-wrap<?php if ($i === 0) echo ' is-open'; ?>" data-faq-wrap="<?php echo $i; ?>" id="faq-a<?php echo $i; ?>"><div style="overflow:hidden;min-height:0"><p><?php echo $a; ?></p></div></div>
      </div>
      <?php endforeach; ?>
      <div style="border-top:1px solid #444"></div>
    </div>
  </section>
<?php
include __DIR__ . '/partials/cta.php';
include __DIR__ . '/partials/footer.php';
