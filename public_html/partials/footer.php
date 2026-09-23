<?php
if (!defined('PD_APP')) { http_response_code(403); exit; }
/** Stopka (design/Stopka.dc.html) + pływający telefon + skrypty + zamknięcie dokumentu. Wymaga: $page. Opcjonalnie: $pageJs. */
$footItems = ['home' => ['Strona główna', '/'], 'oferta' => ['Oferta', '/oferta/'], 'galeria' => ['Galeria', '/galeria/'], 'kontakt' => ['Kontakt', '/kontakt/']];
?>
<footer style="background:#111;border-top:6px solid #f5c518;font-family:'Barlow',system-ui,sans-serif;color:#f2f2f2">
  <div style="max-width:1200px;margin:0 auto;padding:56px 20px 48px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:40px">
    <div style="display:flex;flex-direction:column;gap:12px"><img src="/assets/img/logo.png" alt="Logo Pomoc Drogowa Łukasz Rogowski" width="96" height="96" loading="lazy" style="width:96px;height:96px;object-fit:contain"><div style="font-family:'Barlow Condensed';font-weight:800;font-size:22px;color:#f5c518;line-height:1">POMOC DROGOWA<br><span style="font-family:'Barlow';font-weight:600;font-size:12px;letter-spacing:3px;color:#e8342a">ŁUKASZ ROGOWSKI</span></div><div style="font-size:14px;color:#999;line-height:1.5">Całodobowa pomoc drogowa dla Pabianic i okolic.</div></div>
    <div style="display:flex;flex-direction:column;gap:10px;font-size:15px"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">KONTAKT</div><a href="<?php echo $PHONE_HREF; ?>" style="font-weight:800;font-size:22px"><?php echo $PHONE; ?></a><div style="color:#ccc;line-height:1.5"><?php echo $ADDR1; ?><br><?php echo $ADDR2; ?></div><a href="<?php echo $GMAPS_PLACE; ?>" target="_blank" rel="noopener" style="text-decoration:underline">Wizytówka Google →</a></div>
    <div style="display:flex;flex-direction:column;gap:10px;font-size:15px"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">GODZINY</div><div style="color:#f5c518;font-weight:800;font-size:22px">Czynne 24/7</div><div style="color:#ccc;line-height:1.5">Poniedziałek – Niedziela<br>całą dobę, również w święta</div></div>
    <div style="display:flex;flex-direction:column;gap:10px;font-size:15px"><div style="font-size:12px;letter-spacing:2px;font-weight:700;color:#999">NAWIGACJA</div>
      <?php foreach ($footItems as $k => [$label, $href]): ?>
      <a href="<?php echo $page === $k ? '#top' : $href; ?>" class="hov-white" style="color:#ccc;transition:color .2s"><?php echo $label; ?></a>
      <?php endforeach; ?>
      <a href="<?php echo $FB; ?>" target="_blank" rel="noopener" class="hov-white" style="color:#ccc;transition:color .2s">Facebook</a></div>
  </div>
  <div style="border-top:1px solid #2a2a2a"><div data-bottomrow><span>© <?php echo date('Y'); ?> Pomoc Drogowa Łukasz Rogowski · Pabianice</span><span>Projekt i wykonanie: <a href="https://pozycjonujewizytowke.pl/" target="_blank" rel="noopener" class="hov-text" style="color:#aaa;text-decoration:underline;transition:color .2s">pozycjonujewizytowke.pl</a></span></div></div>
  <a href="<?php echo $PHONE_HREF; ?>" data-fab class="press" aria-label="Zadzwoń 24h: <?php echo $PHONE; ?>"><span class="dot"></span><span class="fab-desk">24H · <?php echo $PHONE_SHORT; ?></span><span class="fab-mob">ZADZWOŃ 24H · <?php echo $PHONE_SHORT; ?></span></a>
</footer>
</div>
<?php $mainJs = __DIR__ . '/../assets/js/main.js'; ?>
<script src="/assets/js/main.js?v=<?php echo filemtime($mainJs); ?>" defer></script>
<?php if (!empty($pageJs)): ?>
<script src="/<?php echo $pageJs; ?>?v=<?php echo filemtime(__DIR__ . '/../' . $pageJs); ?>" defer></script>
<?php endif; ?>
</body>
</html>
