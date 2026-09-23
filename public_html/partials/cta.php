<?php
if (!defined('PD_APP')) { http_response_code(403); exit; }
/** Zamyka <main>; sekcja "Potrzebujesz pomocy?" (design: ostatnia sekcja przed stopką). $noCta = true pomija sekcję. */
$noCta = $noCta ?? false;
?>
</main>
<?php if (!$noCta): ?>
  <section aria-labelledby="cta-h" style="position:relative;overflow:hidden;background:#141414">
    <?php echo pd_picture(pd_slug('cta-bg'), '', 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.25', '100vw'); ?>
    <div style="position:relative;max-width:1200px;margin:0 auto;padding:clamp(56px,10vw,88px) 20px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:16px">
      <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518">DOSTĘPNI 24 GODZINY NA DOBĘ</div>
      <h2 id="cta-h" style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(44px,6vw,72px);line-height:1">Potrzebujesz pomocy?</h2>
      <p style="margin:0;font-size:18px;color:#ccc;max-width:560px">Zadzwoń o każdej porze dnia i nocy — dojedziemy tam, gdzie nas potrzebujesz.</p>
      <a href="<?php echo $PHONE_HREF; ?>" class="hov-big press" style="font-family:'Barlow Condensed';font-weight:800;font-size:clamp(48px,8vw,88px);line-height:1;color:#fff;background:#111;padding:4px 28px 8px;border:5px solid #f5c518;margin-top:10px;transition:background-color .2s,color .2s,border-color .2s,transform .2s cubic-bezier(.2,.7,.2,1),box-shadow .2s"><?php echo $PHONE; ?></a>
    </div>
  </section>
<?php endif; ?>
