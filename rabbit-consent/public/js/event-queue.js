/**
 * event-queue.js
 *
 * Cola de eventos para acumular interacciones del usuario
 * mientras no ha dado consentimiento.
 *
 * Cuando el usuario acepta, flushEvents() envía todos los eventos
 * acumulados al dataLayer (GTM) o directamente a GA4 vía gtag().
 *
 * Si rechaza, la cola se descarta limpiamente (no se envía nada).
 */
window.consentEventQueue = [];

function trackEvent(name, data) {
    if (!window.userConsented) {
        // Sin consentimiento: guardar en cola
        consentEventQueue.push({ name, data });
        if (smartSettings.debug) {
            console.log('[SmartConsent] Evento encolado:', name, data);
        }
    } else {
        _sendEvent(name, data);
    }
}

/**
 * Envía un evento al destino correcto según la configuración:
 *  - GTM activo  → dataLayer.push (GTM gestiona GA4)
 *  - GA4 directo → gtag('event', ...) sin pasar por GTM
 */
function _sendEvent(name, data) {
    if (smartSettings.useGTM === 'true') {
        // GTM está activo: push al dataLayer, GTM se encarga del resto
        dataLayer.push({ event: name, ...data });
    } else if (smartSettings.useGA4 === 'true') {
        // Modo GA4 directo (sin GTM): usar gtag()
        gtag('event', name, data);
    }
    // Si no hay GTM ni GA4 configurados, no se envía nada
    if (smartSettings.debug) {
        const dest = smartSettings.useGTM === 'true' ? 'GTM/dataLayer' : 'GA4 directo';
        console.log('[SmartConsent] Evento enviado via', dest + ':', name, data);
    }
}

function flushEvents() {
    if (smartSettings.debug && consentEventQueue.length > 0) {
        console.log('[SmartConsent] Vaciando cola:', consentEventQueue.length, 'eventos.');
    }
    consentEventQueue.forEach(function (e) {
        _sendEvent(e.name, e.data);
    });
    consentEventQueue = [];
}
