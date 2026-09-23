/* Pomoc Drogowa Pabianice — strona główna: mapa obszaru (Leaflet, ładowana leniwie) + akordeon FAQ */
(function () {
  var TOWNS = [
    { name: 'Pabianice', lat: 51.6639859, lon: 19.3535024, hub: true },
    { name: 'Łódź', lat: 51.7728245, lon: 19.478486 },
    { name: 'Konstantynów Łódzki', lat: 51.7572134, lon: 19.3082412 },
    { name: 'Ksawerów', lat: 51.6986, lon: 19.3828 },
    { name: 'Rzgów', lat: 51.6627785, lon: 19.4908887 },
    { name: 'Dobroń', lat: 51.63382, lon: 19.24516 },
    { name: 'Łask', lat: 51.5929947, lon: 19.1334784 },
    { name: 'Zduńska Wola', lat: 51.5949116, lon: 18.9501087 },
    { name: 'Lutomiersk', lat: 51.7550121, lon: 19.2120994 }
  ];
  var map = null, markers = {}, current = 'Pabianice';
  var label = document.querySelector('[data-town-label]');

  function setChips() {
    document.querySelectorAll('[data-town]').forEach(function (b) {
      var on = b.getAttribute('data-town') === current;
      b.classList.toggle('is-on', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    if (label) label.textContent = current;
    Object.keys(markers).forEach(function (n) {
      var el = markers[n].getElement && markers[n].getElement();
      if (el) el.classList.toggle('is-active', n === current);
      var tip = markers[n].getTooltip && markers[n].getTooltip(); // etykieta tylko przy wybranej miejscowości
      var tel = tip && tip.getElement && tip.getElement();
      if (tel) tel.classList.toggle('is-hidden', n !== current);
    });
  }
  function selectTown(name) {
    var t = TOWNS.filter(function (x) { return x.name === name; })[0];
    if (!t) return;
    current = name; setChips();
    if (map) map.panTo([t.lat, t.lon], { animate: true });
  }
  function renderMap(el) {
    if (map || !window.L) return;
    map = L.map(el, { scrollWheelZoom: false, zoomControl: true, attributionControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
    var hub = TOWNS[0];
    L.circle([hub.lat, hub.lon], { radius: 25000, color: '#f5c518', weight: 2, dashArray: '6 6', fill: true, fillColor: '#f5c518', fillOpacity: .05 }).addTo(map);
    var bounds = [];
    TOWNS.forEach(function (t) {
      var size = t.hub ? 22 : 16;
      var m = L.marker([t.lat, t.lon], { icon: L.divIcon({ className: 'pd-pin' + (t.hub ? ' is-hub' : ''), html: '<i></i>', iconSize: [size, size] }), title: t.name, keyboard: true }).addTo(map);
      m.bindTooltip(t.name, { permanent: true, direction: 'top', offset: [0, -6], className: 'pd-label' });
      m.on('click', function () { selectTown(t.name); });
      markers[t.name] = m; bounds.push([t.lat, t.lon]);
    });
    map.fitBounds(bounds, { padding: [30, 30] });
    setChips();
    setTimeout(function () { try { map.invalidateSize(); } catch (e) {} }, 250);
  }
  function loadLeaflet(el) {
    if (window.L) { renderMap(el); return; }
    if (document.querySelector('script[data-leaflet-js]')) return;
    var link = document.createElement('link'); link.rel = 'stylesheet'; link.href = '/assets/vendor/leaflet/leaflet.css'; link.setAttribute('data-leaflet-css', ''); document.head.appendChild(link);
    var s = document.createElement('script'); s.src = '/assets/vendor/leaflet/leaflet.js'; s.setAttribute('data-leaflet-js', ''); s.onload = function () { renderMap(el); }; document.head.appendChild(s);
  }
  var el = document.querySelector('[data-leaflet]');
  if (el) {
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) { entries.forEach(function (en) { if (en.isIntersecting) { io.disconnect(); loadLeaflet(el); } }); }, { rootMargin: '600px 0px' });
      io.observe(el);
    } else loadLeaflet(el);
  }

  // ── Slider opinii ──
  var slider = document.querySelector('[data-slider]');
  if (slider) {
    var track = slider.querySelector('[data-track]'), nav = slider.querySelector('[data-slider-nav]'), dotsEl = slider.querySelector('[data-dots]');
    var prevB = slider.querySelector('[data-prev]'), nextB = slider.querySelector('[data-next]');
    var slides = track.querySelectorAll('[data-slide]'), timer = null;
    function perView() { var s = slides[0]; return s ? Math.max(1, Math.round(track.clientWidth / s.getBoundingClientRect().width)) : 1; }
    function pages() { return Math.max(1, Math.ceil(slides.length / perView())); }
    function page() { return Math.round(track.scrollLeft / track.clientWidth); }
    function goTo(pg) { var n = pages(); pg = (pg + n) % n; track.scrollTo({ left: pg * track.clientWidth, behavior: 'smooth' }); }
    function renderDots() {
      var n = pages(), cur = page();
      nav.hidden = n <= 1;
      dotsEl.innerHTML = '';
      for (var i = 0; i < n; i++) {
        var d = document.createElement('button'); d.type = 'button'; d.className = 'rev-dot' + (i === cur ? ' is-on' : '');
        d.setAttribute('role', 'tab'); d.setAttribute('aria-label', 'Strona ' + (i + 1) + ' z ' + n); d.setAttribute('aria-selected', i === cur ? 'true' : 'false');
        d.setAttribute('data-goto', i); dotsEl.appendChild(d);
      }
    }
    function restart() { if (timer) clearInterval(timer); timer = setInterval(function () { goTo(page() + 1); }, 7000); }
    slider.addEventListener('click', function (e) {
      var t = e.target.closest('[data-prev],[data-next],[data-goto]'); if (!t) return;
      if (t.hasAttribute('data-goto')) goTo(Number(t.getAttribute('data-goto'))); else goTo(page() + (t.hasAttribute('data-next') ? 1 : -1));
      restart();
    });
    var scrollT; track.addEventListener('scroll', function () { clearTimeout(scrollT); scrollT = setTimeout(renderDots, 80); }, { passive: true });
    slider.addEventListener('mouseenter', function () { if (timer) clearInterval(timer); timer = null; });
    slider.addEventListener('mouseleave', restart);
    slider.addEventListener('focusin', function () { if (timer) clearInterval(timer); timer = null; });
    window.addEventListener('resize', renderDots);
    renderDots(); restart();
  }

  document.addEventListener('click', function (e) {
    var t = e.target; if (!t || !t.closest) return;
    var chip = t.closest('[data-town]');
    if (chip) { selectTown(chip.getAttribute('data-town')); return; }
    var fb = t.closest('[data-faq]');
    if (fb) {
      var i = fb.getAttribute('data-faq'), wasOpen = fb.classList.contains('is-open');
      document.querySelectorAll('[data-faq]').forEach(function (b) { b.classList.remove('is-open'); b.setAttribute('aria-expanded', 'false'); });
      document.querySelectorAll('[data-faq-wrap]').forEach(function (w) { w.classList.remove('is-open'); });
      if (!wasOpen) {
        fb.classList.add('is-open'); fb.setAttribute('aria-expanded', 'true');
        var w = document.querySelector('[data-faq-wrap="' + i + '"]'); if (w) w.classList.add('is-open');
      }
    }
  });
})();
