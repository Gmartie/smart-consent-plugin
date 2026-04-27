/**
 * consent-core.js
 *
 * Gestiona el banner de consentimiento y actualiza Google Consent Mode v2.
 * Compatible con GTM y con GA4 directo (sin GTM).
 *
 * El dataLayer y gtag() ya están declarados en <head> por enqueue.php
 * (antes de que cargue GTM o GA4), así que aquí solo nos aseguramos
 * de no sobreescribirlos.
 */

window.dataLayer = window.dataLayer || [];
function gtag() { dataLayer.push(arguments); }

// Estado de consentimiento inicial (viene de PHP vía wp_localize_script)
window.userConsented = (smartSettings.consented === 'true');

if (smartSettings.debug) {
    const modo = smartSettings.useGTM === 'true'
        ? 'GTM (' + (smartSettings.gtmId || '') + ')'
        : smartSettings.useGA4 === 'true'
            ? 'GA4 directo (' + smartSettings.ga4Id + ')'
            : 'sin destino configurado';
    console.log('[SmartConsent] Iniciado. Modo:', modo, '| Consentimiento previo:', window.userConsented);
}

function saveConsent(consent) {
    fetch(smartSettings.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=save_consent&consent=${consent}&nonce=${smartSettings.nonce}`
    });
}

document.addEventListener('DOMContentLoaded', function () {

    const banner    = document.getElementById('scp-consent-banner');
    const acceptBtn = document.getElementById('accept-cookies');
    const rejectBtn = document.getElementById('reject-cookies');

    if (window.userConsented) {
        // Ya tenía consentimiento: ocultar banner.
        // El consent update ya se hizo en <head> por PHP, antes de cargar GTM/GA4.
        if (banner) banner.style.display = 'none';
        if (smartSettings.debug) {
            console.log('[SmartConsent] Usuario ya había aceptado. Consent Mode ya actualizado en <head>.');
        }
    } else {
        if (banner) banner.style.display = 'block';
    }

    if (acceptBtn) {
        acceptBtn.addEventListener('click', function () {
            window.userConsented = true;
            saveConsent('accepted');

            // Actualizar Consent Mode v2 → GTM disparará las etiquetas pendientes
            // o GA4 directo comenzará a registrar datos
            gtag('consent', 'update', {
                'ad_storage':         'granted',
                'analytics_storage':  'granted',
                'ad_user_data':       'granted',
                'ad_personalization': 'granted'
            });

            // Vaciar la cola de eventos acumulados mientras no había consentimiento
            flushEvents();

            if (banner) banner.style.display = 'none';

            if (smartSettings.debug) {
                console.log('[SmartConsent] Aceptado. Consent Mode actualizado a "granted".');
            }
        });
    }

    if (rejectBtn) {
        rejectBtn.addEventListener('click', function () {
            window.userConsented = false;
            saveConsent('rejected');
            if (banner) banner.style.display = 'none';
            if (smartSettings.debug) {
                console.log('[SmartConsent] Rechazado. GA4/GTM no recibirán datos.');
            }
        });
    }
});
