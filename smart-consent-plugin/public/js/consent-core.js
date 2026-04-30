/**
 * Gestiona el botón flotante de galleta y el banner de consentimiento.
 * Compatible con GTM y GA4 directo.
 *
 * Política de visibilidad:
 *  - El botón de galleta es SIEMPRE visible.
 *  - El banner NUNCA se abre solo — solo al hacer clic en el botón.
 *  - Al aceptar o rechazar, el banner se cierra. El botón permanece.
 */

window.dataLayer = window.dataLayer || [];
function gtag() { dataLayer.push(arguments); }

window.userConsented = (smartSettings.consented === 'true');

if (smartSettings.debug) {
    var modo = smartSettings.useGTM === 'true'
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
        body: 'action=save_consent&consent=' + consent + '&nonce=' + smartSettings.nonce
    });
}

function openBanner(banner) {
    if (!banner) return;
    banner.style.animation = 'none';
    banner.offsetHeight;
    banner.style.animation = '';
    banner.style.display   = 'block';
}

function closeBanner(banner) {
    if (banner) banner.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {

    var banner    = document.getElementById('scp-consent-banner');
    var trigger   = document.getElementById('scp-cookie-trigger');
    var acceptBtn = document.getElementById('accept-cookies');
    var rejectBtn = document.getElementById('reject-cookies');

    // Banner siempre cerrado al cargar — solo se abre con el botón
    closeBanner(banner);

    // Toggle: clic en el botón abre/cierra el banner
    if (trigger) {
        trigger.addEventListener('click', function () {
            if (!banner) return;
            if (banner.style.display === 'block') {
                closeBanner(banner);
            } else {
                openBanner(banner);
            }
        });
    }

    // Aceptar
    if (acceptBtn) {
        acceptBtn.addEventListener('click', function () {
            window.userConsented = true;
            saveConsent('accepted');

            gtag('consent', 'update', {
                'ad_storage':         'granted',
                'analytics_storage':  'granted',
                'ad_user_data':       'granted',
                'ad_personalization': 'granted'
            });

            flushEvents();
            closeBanner(banner);

            if (smartSettings.debug) console.log('[SmartConsent] Aceptado. Consent Mode → granted.');
        });
    }

    // Rechazar
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function () {
            window.userConsented = false;
            saveConsent('rejected');
            closeBanner(banner);

            if (smartSettings.debug) console.log('[SmartConsent] Rechazado. GA4/GTM no recibirán datos.');
        });
    }
});
