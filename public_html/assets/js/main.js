/* Pomoc Drogowa Pabianice — wspólny skrypt: menu mobilne */
(function () {
  var nav = document.querySelector('[data-nav]');
  if (!nav) return;
  var burger = nav.querySelector('[data-burger]');
  function setMenu(open) {
    nav.classList.toggle('menu-open', open);
    document.body.classList.toggle('menu-open', open);
    if (burger) burger.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  document.addEventListener('click', function (e) {
    var t = e.target;
    if (!t || !t.closest) return;
    if (t.closest('[data-burger]')) { setMenu(!nav.classList.contains('menu-open')); return; }
    if (t.closest('[data-menuclose]')) setMenu(false);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape' || !nav.classList.contains('menu-open')) return;
    setMenu(false);
    if (burger) burger.focus();
  });
  var mq = window.matchMedia('(min-width:900px)');
  var onMq = function (ev) { if (ev.matches) setMenu(false); };
  if (mq.addEventListener) mq.addEventListener('change', onMq); else mq.addListener(onMq);
})();
