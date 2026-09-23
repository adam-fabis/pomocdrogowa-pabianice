<?php
if (!defined('PD_APP')) { http_response_code(403); exit; }
/** Sticky nagłówek + menu mobilne (design/Naglowek.dc.html). Wymaga: $page. Otwiera <main>. */
$navItems = ['home' => ['Strona główna', '/'], 'oferta' => ['Oferta', '/oferta/'], 'galeria' => ['Galeria', '/galeria/'], 'kontakt' => ['Kontakt', '/kontakt/']];
$navHref = fn(string $k): string => $page === $k ? '#top' : $navItems[$k][1];
?>
<header data-nav style="position:sticky;top:0;z-index:40;display:block;background:#161616;border-bottom:3px solid #f5c518;font-family:'Barlow',system-ui,sans-serif;color:#f2f2f2">
  <div style="max-width:1200px;margin:0 auto;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px">
    <a href="<?php echo $page === 'home' ? '#top' : '/'; ?>" style="display:flex;align-items:center;gap:10px;flex:none"><img src="/assets/img/logo.png" alt="Logo Pomoc Drogowa Łukasz Rogowski" width="56" height="56" style="width:56px;height:56px;object-fit:contain"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:22px;line-height:.95;color:#f5c518">POMOC DROGOWA<br><span style="font-family:'Barlow';font-weight:600;font-size:11px;letter-spacing:3px;color:#e8342a">ŁUKASZ ROGOWSKI</span></div></a>
    <nav data-navlinks aria-label="Nawigacja główna">
      <?php foreach ($navItems as $k => [$label]): ?>
      <a href="<?php echo $navHref($k); ?>"<?php if ($page === $k) echo ' class="is-on" aria-current="page"'; ?>><?php echo $label; ?></a>
      <?php endforeach; ?>
    </nav>
    <a href="<?php echo $PHONE_HREF; ?>" data-navphone class="hov-red press"><span class="dot"></span><?php echo $PHONE; ?></a>
    <button data-burger aria-label="Menu" aria-expanded="false" aria-controls="mobile-menu"><span></span><span></span><span></span></button>
  </div>
  <div data-menu id="mobile-menu">
    <nav style="display:flex;flex-direction:column;padding:8px 20px" aria-label="Menu mobilne">
      <?php foreach ($navItems as $k => [$label]): ?>
      <a href="<?php echo $navHref($k); ?>" data-menuclose class="m-link<?php if ($page === $k) echo ' is-on'; ?>"><?php echo $label; ?><span style="color:#f5c518;font-size:24px">→</span></a>
      <?php endforeach; ?>
    </nav>
    <div style="padding:20px;display:flex;flex-direction:column;gap:10px">
      <div style="font-size:12px;letter-spacing:3px;font-weight:700;color:#999">CZYNNE 24/7 · PABIANICE I OKOLICE</div>
      <a href="<?php echo $PHONE_HREF; ?>" data-menuclose style="background:#e8342a;color:#fff;font-family:'Barlow Condensed';font-weight:800;font-size:34px;padding:14px 20px;display:flex;align-items:center;justify-content:center;gap:12px"><span class="dot"></span><?php echo $PHONE_SHORT; ?></a>
    </div>
  </div>
</header>
<main>
