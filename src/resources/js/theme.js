
/**
 * theme.js
 * Always use light theme.
 */
(function (AIOA, document) {
    'use strict';

    document.documentElement.setAttribute('data-theme', 'light');

    try {
        AIOA.storage.set('aioa-theme', 'light');
    } catch (e) {
        // Ignore storage errors.
    }

})(window.AIOA, document);
