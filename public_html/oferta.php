<?php
if (!defined('PD_APP')) { define('PD_APP', true); } // bezpośrednio (Apache) lub przez dispatcher w index.php (nginx)
$page = 'oferta';
$title = 'Oferta: laweta, holowanie, naprawa | Pomoc Drogowa Pabianice';
$desc = 'Holowanie i laweta, naprawa na miejscu, awaryjne odpalanie, wymiana koła, dowóz paliwa, pomoc po kolizji, transport pojazdów i auto zastępcze z OC sprawcy. Pabianice, Łódź i okolice, 24/7.';
$path = '/oferta/';
$services = [ // [id, tytuł, opis, punkty, rola zdjęcia]
  ['holowanie', 'Holowanie i laweta', 'Niesprawne auto przewozimy lawetą do warsztatu, domu lub na wskazany parking. Pojazd jest zabezpieczony na całą drogę.', ['Samochody osobowe i dostawcze', 'Auta po awarii, kolizji i wypadku', 'Trasy lokalne i dalsze'], 'oferta-holowanie'],
  ['naprawa', 'Naprawa na miejscu', 'Mobilny serwis przyjeżdża do Ciebie. Wiele usterek usuwamy od ręki — bez holowania i czekania na warsztat.', ['Diagnoza usterki na miejscu', 'Drobne naprawy mechaniczne i elektryczne', 'Holowanie, gdy naprawa na miejscu nie jest możliwa'], 'oferta-naprawa'],
  ['akumulator', 'Awaryjne odpalanie', 'Rozładowany akumulator to najczęstszy powód, dla którego auto nie odpala — szczególnie zimą. Przyjedziemy i uruchomimy silnik.', ['Rozruch z profesjonalnego urządzenia', 'Sprawdzenie akumulatora i ładowania', 'Parking, garaż, pobocze — dojedziemy'], 'oferta-akumulator'],
  ['opony', 'Wymiana koła i serwis opon', 'Przebita opona w trasie? Założymy koło zapasowe lub dojazdowe na miejscu, żebyś mógł bezpiecznie ruszyć dalej.', ['Awaryjna wymiana koła', 'Pomoc przy uszkodzonej feldze lub śrubach', 'Transport do wulkanizacji, jeśli potrzeba'], 'oferta-opony'],
  ['paliwo', 'Dowóz paliwa', 'Zabrakło paliwa na trasie lub w mieście? Przywieziemy tyle, żebyś spokojnie dojechał do najbliższej stacji.', ['Benzyna i olej napędowy', 'Dojazd na drogi lokalne i ekspresowe', 'O każdej porze dnia i nocy'], 'oferta-paliwo'],
  ['kolizja', 'Pomoc po kolizji i wypadku', 'Zabezpieczamy miejsce zdarzenia, usuwamy uszkodzony pojazd i pomagamy przejść przez formalności.', ['Usunięcie i transport uszkodzonego auta', 'Pomoc w formalnościach po zdarzeniu', 'Auto zastępcze z OC sprawcy'], 'oferta-kolizja'],
  ['transport', 'Transport pojazdów', 'Przewozimy nie tylko auta po awarii. Kupiłeś samochód w innym mieście albo chcesz przewieźć quada? Zajmiemy się tym.', ['Samochody kupione lub sprzedane', 'Quady i inne pojazdy', 'Trasy po całej Polsce'], 'oferta-transport'],
  ['oc', 'Auto zastępcze z OC sprawcy', 'Jeśli kolizję spowodował ktoś inny, masz prawo do auta zastępczego na czas naprawy. Załatwimy to za Ciebie.', ['Bezpłatnie dla poszkodowanego', 'Rozliczenie bezpośrednio z ubezpieczycielem', 'Pomoc w zgłoszeniu szkody'], 'oferta-oc'],
];
include __DIR__ . '/partials/config.php';
$ogImage = 'assets/img/hero/' . pd_slug('oferta-hero') . '.jpg';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <!-- HERO -->
  <section id="top" style="position:relative;overflow:hidden;background:#141414;scroll-margin-top:var(--navh)">
    <?php echo pd_picture('hero/' . pd_slug('oferta-hero'), 'Auto na lawecie', 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.4', '100vw', true); ?>
    <div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(20,20,20,.92) 0%,rgba(20,20,20,.6) 60%,rgba(20,20,20,.35) 100%)"></div>
    <div style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(48px,8vw,80px) 20px;display:flex;flex-direction:column;gap:18px;align-items:flex-start">
      <nav aria-label="Okruszki" style="font-size:14px;color:#aaa"><a href="/" style="color:#aaa">Strona główna</a> <span style="color:#666">/</span> <span style="color:#f5c518">Oferta</span></nav>
      <h1 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(44px,7vw,80px);line-height:.95;text-wrap:balance">Oferta pomocy drogowej</h1>
      <p style="margin:0;max-width:600px;font-size:18px;line-height:1.55;color:#ddd;text-wrap:pretty">Od holowania lawetą po naprawę na poboczu. Działamy całą dobę w Pabianicach, Łodzi i okolicy — zadzwoń, powiemy od razu, jak pomożemy.</p>
      <a href="<?php echo $PHONE_HREF; ?>" class="hov-yellow press" style="background:#f5c518;color:#111;font-weight:800;font-size:20px;padding:16px 26px;margin-top:6px;transition:background-color .2s,transform .2s cubic-bezier(.2,.7,.2,1),box-shadow .2s">Zadzwoń: <?php echo $PHONE; ?></a>
    </div>
  </section>
  <div style="height:16px;background:repeating-linear-gradient(135deg,#f5c518 0 22px,#111 22px 44px)"></div>

  <!-- PRZEJDŹ DO -->
  <section aria-label="Spis usług" style="background:#262626;border-bottom:1px solid #333">
    <div style="max-width:1200px;margin:0 auto;padding:28px 20px;display:flex;flex-wrap:wrap;gap:8px;align-items:center">
      <span style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999;margin-right:8px">PRZEJDŹ DO:</span>
      <?php foreach ($services as $i => [$id, $t]): ?>
      <a href="#<?php echo $id; ?>" class="hov-brand" style="border:1px solid #555;color:#eee;padding:7px 12px;font-size:14px;font-weight:600;transition:background-color .2s,color .2s,border-color .2s"><?php echo sprintf('%02d', $i + 1); ?> <?php echo $t; ?></a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- USŁUGI -->
  <div style="max-width:1200px;margin:0 auto;padding:clamp(40px,7vw,72px) 20px;display:flex;flex-direction:column;gap:clamp(48px,8vw,88px)">
    <?php foreach ($services as $i => [$id, $t, $d, $b, $role]): ?>
    <article id="<?php echo $id; ?>" class="svc-row<?php if ($i % 2) echo ' is-rev'; ?>">
      <div style="flex:1 1 0;min-width:0;position:relative">
        <?php echo pd_picture(pd_slug($role), $t, 'width:100%;aspect-ratio:4/3;object-fit:cover;display:block;border:3px solid #333', '(max-width:819px) 100vw, 50vw'); ?>
        <div style="position:absolute;left:0;top:0;background:#f5c518;color:#111;font-family:'Barlow Condensed';font-weight:800;font-size:28px;padding:6px 14px;line-height:1"><?php echo sprintf('%02d', $i + 1); ?></div>
      </div>
      <div style="flex:1 1 0;min-width:0;display:flex;flex-direction:column;gap:16px">
        <h2 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(32px,5vw,46px);line-height:1"><?php echo $t; ?></h2>
        <p style="margin:0;font-size:17px;line-height:1.6;color:#ccc;text-wrap:pretty"><?php echo $d; ?></p>
        <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px">
          <?php foreach ($b as $x): ?>
          <li style="display:flex;gap:12px;align-items:flex-start;font-size:16px;line-height:1.45"><span style="width:10px;height:10px;background:#f5c518;flex:none;margin-top:6px"></span><?php echo $x; ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?php echo $PHONE_HREF; ?>" class="hov-brand hov-lift-flat press" style="align-self:flex-start;border:2px solid #f5c518;color:#f5c518;font-weight:800;padding:12px 20px;font-size:16px;margin-top:6px;transition:background-color .2s,color .2s,transform .2s cubic-bezier(.2,.7,.2,1)">Potrzebuję tej pomocy →</a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <!-- JAK TO WYGLĄDA -->
  <section aria-labelledby="jak-h" style="background:#262626;border-top:1px solid #333;border-bottom:1px solid #333">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(52px,9vw,80px) 20px">
      <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518;margin-bottom:14px">JAK TO WYGLĄDA</div>
      <h2 id="jak-h" style="margin:0 0 36px;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(34px,7vw,52px);line-height:1">Od telefonu do rozwiązania</h2>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:3px">
        <div style="background:#1f1f1f;padding:28px;display:flex;flex-direction:column;gap:12px;border-top:4px solid #f5c518"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:56px;line-height:1;color:#f5c518">1</div><h3 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:26px">Dzwonisz</h3><div style="font-size:15px;color:#aaa;line-height:1.5">Mówisz, gdzie stoisz i co się stało. Odbieramy o każdej porze.</div></div>
        <div style="background:#1f1f1f;padding:28px;display:flex;flex-direction:column;gap:12px;border-top:4px solid #f5c518"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:56px;line-height:1;color:#f5c518">2</div><h3 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:26px">Ustalamy szczegóły</h3><div style="font-size:15px;color:#aaa;line-height:1.5">Podajemy czas dojazdu i omawiamy, czego się spodziewać.</div></div>
        <div style="background:#1f1f1f;padding:28px;display:flex;flex-direction:column;gap:12px;border-top:4px solid #f5c518"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:56px;line-height:1;color:#f5c518">3</div><h3 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:26px">Przyjeżdżamy</h3><div style="font-size:15px;color:#aaa;line-height:1.5">Naprawiamy na miejscu albo bezpiecznie holujemy tam, gdzie wskażesz.</div></div>
      </div>
    </div>
  </section>
<?php
include __DIR__ . '/partials/cta.php';
include __DIR__ . '/partials/footer.php';
