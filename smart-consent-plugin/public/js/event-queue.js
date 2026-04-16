// La cola se mantiene solo en memoria durante la sesión actual.
// Si el usuario no acepta, los eventos se descartan limpiamente.
window.consentEventQueue = [];

function trackEvent(name, data) {

  if (!window.userConsented) {
    consentEventQueue.push({name, data});
  } else {
    gtag('event', name, data);
  }
}

// Vaciar cola (se llama tras loadGoogle, cuando GA ya está listo)
function flushEvents() {
  consentEventQueue.forEach(function(e) {
    gtag('event', e.name, e.data);
  });

  consentEventQueue = [];
}