/**
 * Smart Consent Plugin - Integrations
 * Bridges consent events to third-party analytics and marketing tools.
 */
(function (window) {
    'use strict';

    var ScpIntegrations = {

        init: function () {
            if (!window.ScpEventQueue) {
                console.warn('[SCP Integrations] ScpEventQueue not found.');
                return;
            }

            ScpEventQueue.on('consent:accepted', function (data) {
                ScpIntegrations._enableGoogleAnalytics(data);
                ScpIntegrations._enableFacebookPixel(data);
                ScpIntegrations._enableHotjar(data);
            });

            ScpEventQueue.on('consent:rejected', function () {
                ScpIntegrations._disableTracking();
            });
        },

        _enableGoogleAnalytics: function (data) {
            if (typeof window.gtag === 'function') {
                window.gtag('consent', 'update', {
                    analytics_storage: 'granted',
                    ad_storage: data.categories && data.categories.marketing ? 'granted' : 'denied'
                });
                console.log('[SCP Integrations] Google Analytics consent updated.');
            }
        },

        _enableFacebookPixel: function () {
            if (typeof window.fbq === 'function') {
                window.fbq('consent', 'grant');
                console.log('[SCP Integrations] Facebook Pixel consent granted.');
            }
        },

        _enableHotjar: function () {
            if (typeof window.hj === 'function') {
                window.hj('optIn');
                console.log('[SCP Integrations] Hotjar opted in.');
            }
        },

        _disableTracking: function () {
            if (typeof window.gtag === 'function') {
                window.gtag('consent', 'update', {
                    analytics_storage: 'denied',
                    ad_storage: 'denied'
                });
            }
            if (typeof window.fbq === 'function') {
                window.fbq('consent', 'revoke');
            }
            console.log('[SCP Integrations] All tracking disabled.');
        }
    };

    window.ScpIntegrations = ScpIntegrations;

})(window);
