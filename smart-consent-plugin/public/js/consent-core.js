/**
 * Gestiona el botón flotante de galleta, el banner de consentimiento
 * y actualiza Google Consent Mode v2.
 * Compatible con GTM y con GA4 directo (sin GTM).
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
    const trigger   = document.getElementById('scp-cookie-trigger');
    const acceptBtn = document.getElementById('accept-cookies');
    const rejectBtn = document.getElementById('reject-cookies');

    // ── Mostrar/ocultar según estado de consentimiento ──────────────────
    if (window.userConsented) {
        // Ya había aceptado: ocultar todo silenciosamente.
        // El consent update ya se hizo en <head> por PHP.
        if (banner)  banner.style.display  = 'none';
        if (trigger) trigger.style.display = 'none';
        if (smartSettings.debug) {
            console.log('[SmartConsent] Usuario ya había aceptado. Consent Mode ya actualizado en <head>.');
        }
    } else {
        // Sin consentimiento previo: mostrar solo el botón flotante.
        if (banner)  banner.style.display  = 'none';
        if (trigger) trigger.style.display = 'flex';
    }

    // ── Abrir banner al hacer clic en el botón de galleta ───────────────
    if (trigger) {
        trigger.addEventListener('click', function () {
            if (banner) {
                banner.style.display = 'block';
                // Reiniciar la animación de entrada cada vez que se abre
                banner.style.animation = 'none';
                // eslint-disable-next-line no-unused-expressions
                banner.offsetHeight; // forzar reflow
                banner.style.animation = '';
            }
            trigger.classList.add('scp-trigger--hidden');
        });
    }

    // ── Aceptar ──────────────────────────────────────────────────────────
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

            if (banner)  banner.style.display = 'none';
            if (trigger) trigger.style.display = 'none'; // ya no necesita el botón

            if (smartSettings.debug) {
                console.log('[SmartConsent] Aceptado. Consent Mode actualizado a "granted".');
            }
        });
    }

    // ── Rechazar ─────────────────────────────────────────────────────────
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function () {
            window.userConsented = false;
            saveConsent('rejected');

            if (banner) banner.style.display = 'none';
            // Volver a mostrar el botón por si el usuario quiere cambiar de opinión
            if (trigger) {
                trigger.classList.remove('scp-trigger--hidden');
                trigger.style.display = 'flex';
            }

            if (smartSettings.debug) {
                console.log('[SmartConsent] Rechazado. GA4/GTM no recibirán datos.');
            }
        });
    }
});
