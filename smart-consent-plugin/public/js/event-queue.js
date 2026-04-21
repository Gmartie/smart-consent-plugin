/**
 * Cola de eventos para acumular interacciones del usuario
 * mientras no ha dado consentimiento.
 *
 * Cuando el usuario acepta y GTM ya tiene permiso para disparar GA4,
 * flushEvents() envía todos los eventos acumulados de golpe.
 *
 * Si rechaza, la cola se descarta limpiamente (no se envía nada).
 */
window.consentEventQueue = [];

function trackEvent(name, data) {
    if (!window.userConsented) {
        // Sin consentimiento: guardar en cola
        consentEventQueue.push({ name, data });
        if (smartSettings.debug) console.log('[SmartConsent] Evento encolado:', name, data);
    } else {
        // Con consentimiento: enviar directamente al dataLayer para GTM
        dataLayer.push({
            event: name,
            ...data
        });
        if (smartSettings.debug) console.log('[SmartConsent] Evento enviado:', name, data);
    }
}

function flushEvents() {
    consentEventQueue.forEach(function(e) {
        dataLayer.push({
            event: e.name,
            ...e.data
        });
    });
    if (smartSettings.debug && consentEventQueue.length > 0) {
        console.log('[SmartConsent] Cola vaciada:', consentEventQueue.length, 'eventos enviados.');
    }
    consentEventQueue = [];
}
