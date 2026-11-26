
/**
 * theme-init.js
 * Always apply light theme before the page loads.
 */
(function () {
    'use strict';

    document.documentElement.setAttribute('data-theme', 'light');

    try {
        localStorage.setItem('aioa-theme', 'light');
    } catch (e) {
        // Local storage is unavailable.
    }
})();
