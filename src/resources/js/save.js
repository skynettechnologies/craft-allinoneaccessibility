
/**
 * save.js - Save Changes
 */
(function (AIOA) {
    'use strict';

    const { $ } = AIOA.util;

    function save(event) {
     //   console.log('AIOA Form:', AIOA.form);

        if (!AIOA.form || typeof AIOA.form.collect !== 'function') {
           // console.error('AIOA: Form collect function is not initialized.');
            return;
        }

        
        const params = AIOA.form.collect();

        //console.log('AIOA Collected Settings:', params);
        // Prevent Craft's normal form submission and page reload.
        if (event) {
            event.preventDefault();
        }

        //const params = AIOA.form.collect();

       if (params === null) {
            //console.warn('AIOA: Settings validation failed. Please check the highlighted fields.');
            return;
        }

        if (typeof params !== 'object' || Object.keys(params).length === 0) {
           // console.error('AIOA: Settings data is empty or invalid.');
            return;
        }

        // Include the active website domain.
        params.url = AIOA.state.activeDomain;

        const button = $('#settings-submit');

        if (!button) {
           // console.error('AIOA: Save button #settings-submit not found.');
            return;
        }

        //console.log('AIOA Save Payload:', JSON.stringify(params, null, 2));

        AIOA.ui.setBusy(button, true, 'Saving…');

        AIOA.api.post(AIOA.config.apiUpdateUrl, params)
            .then((response) => {
                //console.log('AIOA Save API Response:', response);

                if (!response || !response.Data || !response.msg) {
                    throw new Error(
                        response?.message ||
                        response?.error ||
                        'Invalid save response from API.'
                    );
                }

                AIOA.form.setDirty(false);

                AIOA.ui.toast(
                    'success',
                    'Settings saved successfully!',
                    response.msg
                );

                // Log the values actually returned by the API.
                //console.log('AIOA Saved Settings:', response.Data);
            })
            .catch((err) => {
               // console.error('AIOA Save Error:', err);

                AIOA.ui.toast(
                    'error',
                    'Something went wrong.',
                    err.message || 'Your settings were not saved.'
                );
            })
            .finally(() => {
                AIOA.ui.setBusy(button, false);
            });
    }

    const submitButton = $('#settings-submit');

    if (submitButton) {
        submitButton.addEventListener('click', save);
    } else {
       // console.warn('AIOA: #settings-submit button not found.');
    }

})(window.AIOA);
