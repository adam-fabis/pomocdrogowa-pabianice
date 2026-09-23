/* Pomoc Drogowa Pabianice — kontakt: mapa siedziby (Leaflet, ładowana leniwie) */
(function () {
  var HQ = [51.6607527, 19.3441975], map = null;
  function renderMap(el) {
    if (map || !window.L) return;
    map = L.map(el, { scrollWheelZoom: false }).setView(HQ, 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
    L.circle(HQ, { radius: 600, color: '#f5c518', weight: 2, dashArray: '5 6', fill: false, opacity: .9 }).addTo(map);
    L.marker(HQ, { icon: L.divIcon({ className: 'pd-hq', html: '<i></i>', iconSize: [22, 22], iconAnchor: [11, 11] }), title: 'Pomoc Drogowa Łukasz Rogowski — Moniuszki 41' })
      .addTo(map).bindPopup('<b>Pomoc Drogowa Łukasz Rogowski</b><br>ul. Stanisława Moniuszki 41<br>95-200 Pabianice');
    setTimeout(function () { try { map.invalidateSize(); } catch (e) {} }, 250);
  }
  function loadLeaflet(el) {
    if (window.L) { renderMap(el); return; }
    if (document.querySelector('script[data-leaflet-js]')) return;
    var link = document.createElement('link'); link.rel = 'stylesheet'; link.href = '/assets/vendor/leaflet/leaflet.css'; link.setAttribute('data-leaflet-css', ''); document.head.appendChild(link);
    var s = document.createElement('script'); s.src = '/assets/vendor/leaflet/leaflet.js'; s.setAttribute('data-leaflet-js', ''); s.onload = function () { renderMap(el); }; document.head.appendChild(s);
  }
  var el = document.querySelector('[data-leaflet]');
  if (!el) return;
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) { entries.forEach(function (en) { if (en.isIntersecting) { io.disconnect(); loadLeaflet(el); } }); }, { rootMargin: '600px 0px' });
    io.observe(el);
  } else loadLeaflet(el);
})();
