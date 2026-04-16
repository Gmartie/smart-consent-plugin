/**
 * Smart Consent Plugin - Event Queue
 * Queues and dispatches consent-related events to registered listeners.
 */
(function (window) {
    'use strict';

    var ScpEventQueue = {
        _queue: [],
        _listeners: {},

        on: function (eventName, callback) {
            if (!this._listeners[eventName]) {
                this._listeners[eventName] = [];
            }
            this._listeners[eventName].push(callback);
        },

        off: function (eventName, callback) {
            if (!this._listeners[eventName]) return;
            this._listeners[eventName] = this._listeners[eventName].filter(function (cb) {
                return cb !== callback;
            });
        },

        emit: function (eventName, data) {
            this._queue.push({ event: eventName, data: data, timestamp: Date.now() });
            this._dispatch(eventName, data);
        },

        _dispatch: function (eventName, data) {
            var callbacks = this._listeners[eventName] || [];
            callbacks.forEach(function (cb) {
                try {
                    cb(data);
                } catch (e) {
                    console.error('[SCP EventQueue] Error in listener for "' + eventName + '":', e);
                }
            });
        },

        getHistory: function () {
            return this._queue.slice();
        }
    };

    window.ScpEventQueue = ScpEventQueue;

})(window);
