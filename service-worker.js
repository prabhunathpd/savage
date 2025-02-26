const CACHE_NAME = "pwa-cache-v1";
const urlsToCache = [
  "/app",
  "/app/index.php",
  "/app/style.css",
  "/app/edit_profile.php",
  "/app/notification.php",
  "/app/post.php",
  "/app/search.php",
  "/app/user.php",
  "/app/assets/icon.png"
];

// Install Service Worker and Cache Files
elf.addEventListener('install', (event) => {
  event.waitUntil(
      caches.open(urlsToCache)
          .then((cache) => {
              return cache.addAll(urlsToCache);
          })
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
      caches.match(event.request)
          .then((response) => {
              return response || fetch(event.request);
          })
  );
});