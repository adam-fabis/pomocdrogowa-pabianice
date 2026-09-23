/* Pomoc Drogowa Pabianice — galeria: "zobacz więcej" + lightbox */
(function () {
  var shots = Array.prototype.slice.call(document.querySelectorAll('[data-shot]'));
  var total = shots.length;
  var lb = document.querySelector('[data-lightbox]');
  if (!lb || !total) return;
  var img = lb.querySelector('[data-lbimg]'), pos = lb.querySelector('[data-lbpos]');
  var open = -1, trigger = null;
  function render() {
    var on = open >= 0;
    lb.classList.toggle('is-open', on);
    document.body.classList.toggle('lb-open', on);
    if (on) {
      var tile = shots[open].querySelector('img');
      img.setAttribute('src', shots[open].getAttribute('data-full'));
      img.setAttribute('alt', tile ? tile.getAttribute('alt') : 'Powiększone zdjęcie');
      img.style.display = 'block';
      pos.textContent = (open + 1) + ' / ' + total;
      // .lb jest visibility:hidden do końca klatki — focus() zadziała dopiero po przemalowaniu
      requestAnimationFrame(function () { requestAnimationFrame(function () { var c = lb.querySelector('[data-lbclose]'); if (c) c.focus(); else lb.focus(); }); });
    } else {
      img.style.display = 'none'; img.setAttribute('src', ''); img.setAttribute('alt', ''); pos.textContent = '';
      if (trigger) { trigger.focus(); trigger = null; }
    }
  }
  function step(d) { open = (open + d + total) % total; render(); }
  document.addEventListener('click', function (e) {
    var t = e.target; if (!t || !t.closest) return;
    if (t.closest('[data-more]')) {
      shots.forEach(function (s) { s.classList.remove('gal-more'); });
      var w = document.querySelector('[data-more-wrap]'); if (w) w.setAttribute('hidden', '');
      return;
    }
    if (t.closest('[data-lbclose]')) { open = -1; render(); return; }
    if (t.closest('[data-lbprev]')) { step(-1); return; }
    if (t.closest('[data-lbnext]')) { step(1); return; }
    var shot = t.closest('[data-shot]');
    if (shot) { trigger = shot; open = Number(shot.getAttribute('data-shot')); render(); return; }
    if (t === lb) { open = -1; render(); }
  });
  document.addEventListener('keydown', function (e) {
    if (open < 0) return;
    if (e.key === 'Tab') { // focus trap: Tab krąży po przyciskach lightboxa
      var f = lb.querySelectorAll('button'), first = f[0], last = f[f.length - 1];
      if (e.shiftKey && (document.activeElement === first || !lb.contains(document.activeElement))) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && (document.activeElement === last || !lb.contains(document.activeElement))) { e.preventDefault(); first.focus(); }
      return;
    }
    if (e.key === 'Escape') { open = -1; render(); }
    else if (e.key === 'ArrowLeft') step(-1);
    else if (e.key === 'ArrowRight') step(1);
  });
})();
