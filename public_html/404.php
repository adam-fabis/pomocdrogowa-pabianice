<?php
// Strona 404 — include z dispatchera w index.php (PD_APP już zdefiniowane) lub bezpośrednio.
if (!defined('PD_APP')) { define('PD_APP', true); }
http_response_code(404);
$page = '404';
$title = 'Nie znaleziono strony | Pomoc Drogowa Pabianice';
$desc = 'Strona nie istnieje. Zadzwoń: +48 517 574 330 — pomoc drogowa Pabianice 24/7.';
$path = '/';
$preloadHero = false;
$noCta = true;
include __DIR__ . '/partials/config.php';
$ogImage = 'assets/img/hero/' . pd_slug('home-hero') . '.jpg';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <section style="max-width:1200px;margin:0 auto;padding:clamp(64px,12vw,120px) 20px;display:flex;flex-direction:column;gap:18px;align-items:flex-start">
    <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518">BŁĄD 404</div>
    <h1 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(44px,7vw,80px);line-height:.95">Nie ma takiej strony</h1>
    <p style="margin:0;max-width:560px;font-size:18px;line-height:1.55;color:#ddd">Adres jest błędny albo strona została przeniesiona. Potrzebujesz pomocy na drodze? Zadzwoń — odbieramy całą dobę.</p>
    <a href="<?php echo $PHONE_HREF; ?>" class="hov-yellow press" style="background:#f5c518;color:#111;font-weight:800;font-size:20px;padding:16px 26px;transition:background-color .2s,transform .2s var(--E),box-shadow .2s">Zadzwoń: <?php echo $PHONE; ?></a>
    <a href="/" style="text-decoration:underline">← Strona główna</a>
  </section>
<?php
include __DIR__ . '/partials/cta.php';
include __DIR__ . '/partials/footer.php';
