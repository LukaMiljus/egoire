if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then(reg => console.log('Service Worker registrovan:', reg))
      .catch(err => console.log('Greška pri registraciji SW:', err));
  });
}
