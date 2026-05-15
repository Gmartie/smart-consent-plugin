/**
 * Gestiona el botón flotante dactilar y el banner de consentimiento (tarjeta centrada).
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
    banner.classList.add('scp-visible');
}

function closeBanner(banner) {
    if (banner) banner.classList.remove('scp-visible');
}

document.addEventListener('DOMContentLoaded', function () {

    var banner    = document.getElementById('scp-consent-banner');
    var trigger   = document.getElementById('scp-cookie-trigger');
    var acceptBtn = document.getElementById('accept-cookies');
    var rejectBtn = document.getElementById('reject-cookies');

    // Integrar botón en barra de herramientas si hay selector configurado
    if (smartSettings.toolbarSelector && smartSettings.toolbarSelector.trim() !== '') {
        var toolbarEl = document.querySelector(smartSettings.toolbarSelector.trim());
        if (toolbarEl && trigger) {
            trigger.classList.remove('scp-pos-left', 'scp-pos-right');
            trigger.style.position = 'static';
            trigger.style.bottom   = 'auto';
            trigger.style.left     = 'auto';
            trigger.style.right    = 'auto';
            toolbarEl.appendChild(trigger);
            if (smartSettings.debug) console.log('[SmartConsent] Botón integrado en:', smartSettings.toolbarSelector);
        } else if (smartSettings.debug) {
            console.warn('[SmartConsent] Selector de toolbar no encontrado:', smartSettings.toolbarSelector);
        }
    }

    // Banner siempre cerrado al cargar — solo se abre con el botón
    closeBanner(banner);

    // Toggle: clic en el botón abre/cierra el banner
    if (trigger) {
        trigger.addEventListener('click', function () {
            if (!banner) return;
            if (banner.classList.contains('scp-visible')) {
                closeBanner(banner);
            } else {
                openBanner(banner);
            }
        });
    }

    // Cerrar al hacer clic en el overlay (fuera de la tarjeta)
    if (banner) {
        banner.addEventListener('click', function (e) {
            if (e.target === banner) {
                closeBanner(banner);
            }
        });
    }

    // Guardar preferencias (aceptar)
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

            // Pequeño delay para que GTM procese el consent update antes de recibir eventos
            setTimeout(function () {
                flushEvents();
            }, 300);

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
