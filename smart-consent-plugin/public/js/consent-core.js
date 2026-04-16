window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}

// Leer consentimiento previo
function getConsent() {
  return document.cookie.includes('smart_consent=accepted');
}

// Estado inicial
window.userConsented = getConsent();

// Consent Mode por defecto bloqueado
gtag('consent', 'default', {
  ad_storage: 'denied',
  analytics_storage: 'denied',
  ad_user_data: 'denied',
  ad_personalization: 'denied'
});

// Debug
if (smartSettings.debug) {
  console.log("Smart Consent activo");
  console.log("Configuración:", smartSettings);
}

// FIX #9: Incluir nonce en la petición AJAX
function saveConsent(consent) {
  fetch(smartSettings.ajax_url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `action=save_consent&consent=${consent}&nonce=${smartSettings.nonce}`
  });
}

// garantizando que gtag esté listo antes de enviar los eventos encolados.
function loadGoogle(onReady) {
  if (smartSettings.analytics && smartSettings.ga_id) {

    let script = document.createElement('script');
    script.src = `https://www.googletagmanager.com/gtag/js?id=${smartSettings.ga_id}`;
    script.async = true;

    script.onload = function() {
      gtag('js', new Date());
      gtag('config', smartSettings.ga_id);

      if (smartSettings.debug) {
        console.log("Google Analytics cargado");
      }

      if (typeof onReady === 'function') {
        onReady(); // flushEvents se ejecuta aquí, tras la carga de GA
      }
    };

    document.head.appendChild(script);
  }
}

// DOM listo
document.addEventListener('DOMContentLoaded', function() {

  const acceptBtn = document.getElementById('accept-cookies');
  const rejectBtn = document.getElementById('reject-cookies');

  const banner = document.getElementById('scp-consent-banner');

  if (window.userConsented) {
    if (banner) banner.style.display = 'none';

    gtag('consent', 'update', {
      ad_storage: 'granted',
      analytics_storage: 'granted',
      ad_user_data: 'granted',
      ad_personalization: 'granted'
    });

    loadGoogle(function() {
      flushEvents();
    });

  } else {
    // Mostrar banner si no hay consentimiento previo
    if (banner) banner.style.display = 'block';
  }

  // Botón aceptar
  if (acceptBtn) {
    acceptBtn.addEventListener('click', function() {

      window.userConsented = true;

      saveConsent('accepted');

      gtag('consent', 'update', {
        ad_storage: 'granted',
        analytics_storage: 'granted',
        ad_user_data: 'granted',
        ad_personalization: 'granted'
      });

      loadGoogle(function() {
        flushEvents();
      });

      if (banner) banner.style.display = 'none';
    });
  }

  // Botón rechazar
  if (rejectBtn) {
    rejectBtn.addEventListener('click', function() {

      saveConsent('rejected');

      if (banner) banner.style.display = 'none';
    });
  }

});