/* =====================================================================
   Service Worker · Carreras BUAP (Fisioterapia + Marketing Digital) (PWA)
   - Guarda en caché el diseño, imágenes e íconos (funciona sin internet).
   - Páginas: primero intenta la red (datos frescos de la BD); si no hay
     conexión muestra la copia guardada o la página offline.html.
   ===================================================================== */
const VERSION = "carreras-v1";
const ESTATICOS = [
  "offline.html",
  "assets/css/style.css?v=1",
  "assets/js/app.js?v=1",
  "assets/img/logos/portal.png",
  "assets/img/logos/fisio-web.png",
  "assets/img/logos/mkt-web.png",
  "assets/img/fisio/fisio1-sm.jpg",
  "assets/img/fisio/fisio2.jpg",
  "assets/img/mkt/hero.svg",
  "assets/img/mkt/estrategia.svg",
  "icons/icon-192.png",
  "icons/icon-512.png",
  "icons/favicon-64.png",
  "manifest.webmanifest",
];
// Páginas públicas que se pueden guardar para verlas sin conexión
const PUBLICAS = ["", "index.php", "carrera.php", "plan.php", "campo.php"];

self.addEventListener("install", (ev) => {
  ev.waitUntil(caches.open(VERSION).then((c) => c.addAll(ESTATICOS)).then(() => self.skipWaiting()));
});

self.addEventListener("activate", (ev) => {
  ev.waitUntil(
    caches.keys()
      .then((claves) => Promise.all(claves.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (ev) => {
  const req = ev.request;
  if (req.method !== "GET") return;                       // formularios POST siempre van a la red
  const url = new URL(req.url);
  if (/\.(mp4|mp3)$/i.test(url.pathname)) return;         // video/audio: directo a la red

  // Navegación entre páginas: red primero
  if (req.mode === "navigate") {
    const pagina = url.pathname.split("/").pop();
    ev.respondWith(
      fetch(req)
        .then((resp) => {
          if (resp.ok && PUBLICAS.includes(pagina) && (!url.search || /^\?c=(fisioterapia|marketing)$/.test(url.search))) {
            const copia = resp.clone();
            caches.open(VERSION).then((c) => c.put(req, copia));
          }
          return resp;
        })
        .catch(() => caches.match(req).then((r) => r || caches.match("offline.html")))
    );
    return;
  }

  // Recursos (CSS, JS, imágenes, fuentes): caché y actualización en segundo plano
  const mismoOrigen = url.origin === self.location.origin;
  const esFuente = /fonts\.(googleapis|gstatic)\.com$/.test(url.hostname);
  if (!mismoOrigen && !esFuente) return;
  ev.respondWith(
    caches.match(req).then((guardado) => {
      const deRed = fetch(req)
        .then((resp) => {
          if (resp && (resp.ok || resp.type === "opaque")) {
            const copia = resp.clone();
            caches.open(VERSION).then((c) => c.put(req, copia));
          }
          return resp;
        })
        .catch(() => guardado);
      return guardado || deRed;
    })
  );
});
