const CACHE_NAME = 'mitihub-school-shell-v1';
const RUNTIME = 'mitihub-runtime-v1';
const PRECACHE_URLS = [
  '/',
  '/assets/css/style.css',
  '/assets/css/school.css',
  '/assets/js/school.js',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS)).then(self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  const currentCaches = [CACHE_NAME, RUNTIME];
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.map((k)=>{ if(!currentCaches.includes(k)) return caches.delete(k); }))).then(()=> self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);
  // Only same-origin
  if (url.origin !== location.origin) return;

  if (PRECACHE_URLS.includes(url.pathname)) {
    // cache-first for app shell
    event.respondWith(
      caches.match(req).then((cached)=> cached || fetch(req))
    );
    return;
  }

  if (req.method === 'GET') {
    // stale-while-revalidate for GET
    event.respondWith(
      caches.open(RUNTIME).then(async (cache)=>{
        const cached = await cache.match(req);
        const fetchPromise = fetch(req).then((resp)=>{ cache.put(req, resp.clone()); return resp; }).catch(()=>undefined);
        return cached || fetchPromise || new Response('Offline', {status: 503});
      })
    );
  }
});
