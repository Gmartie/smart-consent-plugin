/**
 * Smart Consent Plugin - Consent Core
 * Manages banner display, user choices, and server-side persistence via AJAX.
 */
(function (window, document) {
    'use strict';

    var ScpConsent = {

        config: window.scpData || {},

        init: function () {
            ScpIntegrations.init();

            if (this._getStoredConsent()) {
                this._applyStoredConsent();
                return;
            }

            this._showBanner();
        },

        _getStoredConsent: function () {
            var name = 'scp_consent=';
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var c = cookies[i].trim();
                if (c.indexOf(name) === 0) {
                    try {
                        return JSON.parse(decodeURIComponent(c.substring(name.length)));
                    } catch (e) {
                        return null;
                    }
                }
            }
            return null;
        },

        _applyStoredConsent: function () {
            var stored = this._getStoredConsent();
            if (stored && stored.accepted) {
                ScpEventQueue.emit('consent:accepted', stored);
            } else {
                ScpEventQueue.emit('consent:rejected', stored);
            }
        },

        _showBanner: function () {
            var banner = document.getElementById('scp-consent-banner');
            if (banner) {
                banner.style.display = 'block';
                banner.setAttribute('aria-hidden', 'false');
            }
        },

        _hideBanner: function () {
            var banner = document.getElementById('scp-consent-banner');
            if (banner) {
                banner.style.display = 'none';
                banner.setAttribute('aria-hidden', 'true');
            }
        },

        accept: function () {
            var consentData = {
                accepted: true,
                timestamp: new Date().toISOString(),
                categories: { analytics: true, marketing: true, preferences: true }
            };
            this._saveConsent(consentData);
            ScpEventQueue.emit('consent:accepted', consentData);
            this._hideBanner();
        },

        reject: function () {
            var consentData = {
                accepted: false,
                timestamp: new Date().toISOString(),
                categories: { analytics: false, marketing: false, preferences: false }
            };
            this._saveConsent(consentData);
            ScpEventQueue.emit('consent:rejected', consentData);
            this._hideBanner();
        },

        _saveConsent: function (consentData) {
            var self = this;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', self.config.ajaxUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    ScpEventQueue.emit('consent:saved', consentData);
                }
            };
            xhr.send(
                'action=scp_save_consent' +
                '&nonce=' + encodeURIComponent(self.config.nonce) +
                '&consent=' + encodeURIComponent(JSON.stringify(consentData))
            );
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        ScpConsent.init();

        var acceptBtn = document.getElementById('scp-accept-btn');
        var rejectBtn = document.getElementById('scp-reject-btn');

        if (acceptBtn) {
            acceptBtn.addEventListener('click', function () { ScpConsent.accept(); });
        }
        if (rejectBtn) {
            rejectBtn.addEventListener('click', function () { ScpConsent.reject(); });
        }
    });

    window.ScpConsent = ScpConsent;

})(window, document);
