

// dataLayer ya está declarado en <head> por enqueue.php (antes de GTM)
// Solo nos aseguramos de que no sobreescribe
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}

// Leer estado de consentimiento desde la variable inyectada por PHP
window.userConsented = (smartSettings.consented === 'true');

if (smartSettings.debug) {
    console.log('[SmartConsent] Iniciado. Consentimiento previo:', window.userConsented);
}

function saveConsent(consent) {
    fetch(smartSettings.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=save_consent&consent=${consent}&nonce=${smartSettings.nonce}`
    });
}

document.addEventListener('DOMContentLoaded', function() {

    const banner = document.getElementById('scp-consent-banner');
    const acceptBtn = document.getElementById('accept-cookies');
    const rejectBtn = document.getElementById('reject-cookies');

    if (window.userConsented) {
        // Ya tenía consentimiento: ocultar banner
        if (banner) banner.style.display = 'none';
        // El update de consentimiento ya se hizo en <head> por PHP,
        // así que GTM ya habrá disparado GA4.
        if (smartSettings.debug) console.log('[SmartConsent] Usuario ya había aceptado. GTM gestionará GA4.');

    } else {
        if (banner) banner.style.display = 'block';
    }

    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() {
            window.userConsented = true;
            saveConsent('accepted');

            // Notificamos a GTM que el usuario ha aceptado.
            // GTM disparará automáticamente todas las etiquetas pendientes
            // (GA4, Ads, etc.) que tienen como condición analytics_storage=granted.
            gtag('consent', 'update', {
                'ad_storage':         'granted',
                'analytics_storage':  'granted',
                'ad_user_data':       'granted',
                'ad_personalization': 'granted'
            });

            // Vaciar la cola de eventos acumulados mientras no había consentimiento
            flushEvents();

            if (banner) banner.style.display = 'none';

            if (smartSettings.debug) console.log('[SmartConsent] Aceptado. GTM ha recibido consent update.');
        });
    }

    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            window.userConsented = false;
            saveConsent('rejected');
            if (banner) banner.style.display = 'none';
            if (smartSettings.debug) console.log('[SmartConsent] Rechazado. GA4 no se disparará.');
        });
    }
});
