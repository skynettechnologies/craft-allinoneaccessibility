/**
 * ---------------------------------------------------------------------------
 * app.js - loads the widget settings and starts the page
 * ---------------------------------------------------------------------------
 * Loaded last. The website is the `url` the platform sent, otherwise this
 * page's own hostname (window.location.hostname).
 *
 * 1. The server route (action fetchWidgetSettings) loads its settings. It never
 *    registers anything.
 * 2. A website that is not registered yet answers {"not_registered": true}. Only then
 *    the page asks ipapi.co whether the admin is in the EU, and registers the
 *    free widget (action registerDomain -> add-user-domain) with:
 *      no_required_eu  0 = EU (ipapi.co in_eu: true)
 *                      1 = not EU (in_eu: false, or ipapi.co did not answer)
 *      name            admin name, or the website
 *      email           admin email, or no-reply@<website>
 * 3. Then the settings are shown.
 *
 * ipapi.co and registerDomain are each requested at most once per page. After a
 * website has been sent for registration, the page does not send it again for
 * 10 minutes, not on "Try again" and not after a reload (localStorage
 * "aioa-registered:<website>"); it only loads the settings again.
 */
(function (AIOA, window) {
  'use strict';

  const { $ } = AIOA.util;
  const state = AIOA.state;

  const REGISTERED_KEY = 'aioa-registered:';
  const REGISTER_AGAIN_AFTER = 10 * 60 * 1000; // ms: no second registration of a website before this
  const GEO_TIMEOUT = 8000;                    // ms to wait for ipapi.co

  let registerSent = false; // registerDomain was requested on this page
  let euRequest = null;     // the one ipapi.co request of this page

  /** Show the error view; Try again only helps when there is a website to load */
function showError(message, title) {
    const errorTitle = $('#settings-error-title');
    const errorMessage = $('#settings-error-message');
    const retryButton = $('#settings-retry-btn');

    if (errorTitle) {
        errorTitle.textContent = title || 'We couldn\'t load your widget settings';
    }

    if (errorMessage) {
        errorMessage.textContent = message;
    }

    if (retryButton) {
        retryButton.hidden = !state.activeDomain;
    }

    AIOA.ui.setView('error_block');
}
  /** Route answer -> result, or an error (err.pending: the website is still being set up) */
  function checkResult(result) {
    if (!result || typeof result !== 'object') {
      throw new Error('Unexpected response from the server.');
    }
    if (result.error) {
      const err = new Error(result.error);
      err.pending = result.pending === true;
      throw err;
    }
    return result;
  }

  /** Load state.activeDomain, registering it first when it is not registered yet */
function loadSettings() {
    if (!state.activeDomain) {
        showError(
            'No valid website domain was found. Open this page on your website domain or on localhost (not an IP address), or check the website domain your platform sends.',
            'A valid domain is required'
        );
        return;
    }

    AIOA.ui.setView('settings-load');

    AIOA.api.route('fetchWidgetSettings', {
        url: state.activeDomain
    })
        .then((result) => {
            //console.log('Fetch widget settings response:', result);
            return checkResult(result);
        })
        .then((result) => (
            result.not_registered ? registerWebsite() : result
        ))
        .then(applyResult)
        .catch((err) => {
            console.error('AIOA loading error:', err);

            if (err.pending) {
                showError(err.message, 'Your widget is being set up');
            } else {
                showError(
                    err instanceof TypeError
                        ? 'Please check your connection and try again.'
                        : (err.message || 'Please try again.')
                );
            }
        });
}

  /* ---------- Registration (at most once) ---------- */

  function registeredRecently() {
    const sentAt = Number(AIOA.storage.get(REGISTERED_KEY + state.activeDomain)) || 0;
    return sentAt > 0 && Date.now() - sentAt < REGISTER_AGAIN_AFTER;
  }

  /** Register the free widget for state.activeDomain and resolve with its settings */
  function registerWebsite() {
    if (registerSent || registeredRecently()) {
      const err = new Error('Your website was just registered, and its settings are not ready yet. Please try again in a moment.');
      err.pending = true;
      return Promise.reject(err);
    }
    registerSent = true;
    AIOA.storage.set(REGISTERED_KEY + state.activeDomain, String(Date.now()));

    return noRequiredEu()
      .then((noRequiredEuValue) => AIOA.api.route('registerDomain', {
        url: state.activeDomain,
        username: AIOA.boot.name,
        email: AIOA.boot.email,
        no_required_eu: noRequiredEuValue
      }))
      .then(checkResult);
  }

  /**
   * Is the admin in the EU? ipapi.co, at most once per page.
   * Resolves with no_required_eu: 0 when in_eu is true, otherwise 1 (also when ipapi.co fails).
   */
  function noRequiredEu() {
    if (!euRequest) {
      const controller = new AbortController();
      const timer = window.setTimeout(() => controller.abort(), GEO_TIMEOUT);
      euRequest = window.fetch(AIOA.config.geoApiUrl, { credentials: 'omit', signal: controller.signal })
        .then((res) => res.json())
        .then((data) => (data && data.in_eu === true ? 0 : 1))
        .catch(() => 1)
        .finally(() => window.clearTimeout(timer));
    }
    return euRequest;
  }

  /* ---------- Settings ---------- */

  /** Show the saved settings, the plan (notice, tab order and locks) and the site line */
  function applyResult(result) {
    // console.log('1. Applying widget settings:', result);

    AIOA.storage.remove(REGISTERED_KEY + state.activeDomain);

    state.customPosition = Number(result.is_widget_custom_position) === 1 ? 1 : 0;
    state.customSize = Number(result.is_widget_custom_size) === 1 ? 1 : 0;

    AIOA.form.fill(AIOA.form.settingsFrom(result));
   // console.log('2. Form settings filled');

    AIOA.paid.fill(result);
    //console.log('3. Paid settings filled');

    AIOA.plan.render(result.is_free_widget);
    //console.log('4. Plan rendered');

    AIOA.form.setDirty(false);

    AIOA.ui.setView('settings-settings_block');
    //console.log('5. Settings view displayed');

    if (state.isPaid) {
        AIOA.dashboard.load();
    }
}

const retryButton = $('#settings-retry-btn');

if (retryButton) {
    retryButton.addEventListener('click', loadSettings);
}

  /* ---------- Start ---------- */

  loadSettings();
})(window.AIOA, window);
