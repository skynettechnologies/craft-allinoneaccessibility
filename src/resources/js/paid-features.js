
/**
 * ---------------------------------------------------------------------------
 * paid-features.js - Paid Features tab
 * ---------------------------------------------------------------------------
 *
 * API fields:
 *   statement_link
 *   report_problem_link
 *   widget_leave_page
 *   is_sound_disabled
 *   is_mobile_position
 *   is_widget_custom_position_mobile
 *   widget_position_mobile
 *
 * Mobile exact position:
 *   widget_position_left_mobile
 *   widget_position_right_mobile
 *   widget_position_horizontal_center_offset_mobile
 *   widget_position_top_mobile
 *   widget_position_bottom_mobile
 *
 * AIOA.paid.fill(result)
 * AIOA.paid.validate()
 * AIOA.paid.collect()
 */
function getRadioValue(name) {
    const input = document.querySelector(
        `input[name="${name}"]:checked, input[name="settings[${name}]"]:checked`
    );

    return input ? input.value : '';
}
(function (AIOA, document) {
    'use strict';

    const {
        $,
        checkedValue,
        checkRadio,
        positionValue,
        wholeNumber,
        wholeNumberField,
        requiredFields
    } = AIOA.util;

    /* =========================================================================
     * Configuration
     * ======================================================================= */

    const LINKS = [
        'settings-statement_link',
        'settings-report_problem_link'
    ];

    const LINK_NAMES = {
        'settings-statement_link': 'Accessibility statement link',
        'settings-report_problem_link': 'Report a problem link'
    };

    const SWITCHES = [
        'settings-widget_leave_page',
        'settings-is_sound_disabled'
    ];

    const OFFSET_MAX = 500;

    const isSet = (value) =>
        value !== null && value !== undefined && value !== '';

    const isOn = (value) => Number(value) === 1;

    const num = (value) => wholeNumber(value, 0);

    /* =========================================================================
     * Mobile field references
     * ======================================================================= */

    const mobileX = $('#settings-mobile_x_value');
    const mobileY = $('#settings-mobile_y_value');

    const mobilePositionSwitch = $('#settings-is_mobile_position');
    const customMobileSwitch = $('#settings-custom-position-mobile-switcher');

    const mobileFields = requiredFields(
        [mobileX, mobileY],
        $('#settings-mobile-pos-error'),
        'Enter a horizontal and a vertical position from 0 to 500 px.'
    );

    function mobileExact() {
        return !!(
            mobilePositionSwitch &&
            customMobileSwitch &&
            mobilePositionSwitch.checked &&
            customMobileSwitch.checked
        );
    }
/**
 * Validate mobile X/Y fields while typing.
 * Add/remove is-invalid on the .aioa-affix wrapper.
 */
function updateMobilePositionValidation() {
    if (!mobileExact()) {
        clearMobilePositionError();
        return;
    }

    const isValidPosition = (input) => {
        if (!input || input.value.trim() === '') {
            return false;
        }

        const value = Number(input.value);

        return (
            Number.isInteger(value) &&
            value >= 0 &&
            value <= OFFSET_MAX
        );
    };

    const invalidX = !isValidPosition(mobileX);
    const invalidY = !isValidPosition(mobileY);
    const hasError = invalidX || invalidY;

    [
        { input: mobileX, invalid: invalidX },
        { input: mobileY, invalid: invalidY }
    ].forEach(({ input, invalid }) => {
        if (!input) return;

        input.setAttribute('aria-invalid', String(invalid));

        const wrapper = input.closest('.aioa-affix');

        if (wrapper) {
            wrapper.classList.toggle('is-invalid', invalid);
        }
    });

    const error = $('#settings-mobile-pos-error');

    if (error) {
        error.hidden = !hasError;
        error.style.display = hasError ? 'block' : 'none';
        error.textContent = hasError
            ? 'Enter a horizontal and a vertical position from 0 to 500 px.'
            : '';
    }
}

/**
 * Clear mobile position validation errors.
 */
function clearMobilePositionError() {
    [mobileX, mobileY].forEach((input) => {
        if (!input) return;

        input.setAttribute('aria-invalid', 'false');

        const wrapper = input.closest('.aioa-affix');

        if (wrapper) {
            wrapper.classList.remove('is-invalid');
        }
    });

    const error = $('#settings-mobile-pos-error');

    if (error) {
        error.hidden = true;
        error.style.display = 'none';
        error.textContent = '';
    }

    mobileFields.reset();
}
    /* =========================================================================
     * Links
     * ======================================================================= */

    /**
     * Add https:// when the user enters a domain without a scheme.
     */
    function withScheme(value) {
        const text = String(value || '').trim();

        if (
            text &&
            !/^[a-z][a-z0-9+.-]*:/i.test(text) &&
            /^[^\s/]+\.[^\s/]+/.test(text)
        ) {
            return 'https://' + text;
        }

        return text;
    }

    /**
     * Validate an empty value or a valid HTTP/HTTPS URL.
     */
    function isValidLink(value) {
        if (!value) {
            return true;
        }

        try {
            const url = new URL(value);

            return (
                (url.protocol === 'https:' || url.protocol === 'http:') &&
                url.hostname.includes('.')
            );
        } catch (e) {
            return false;
        }
    }

    /**
     * Show or clear link validation messages.
     */
    function setLinkError(name, message) {
        const error = $('#' + name + '-error');
        const input = $('#' + name);

        const fieldName = name.replace(/^settings-/, '');

        const wrapper =
            $('[data-for="' + name + '"]') ||
            $('[data-for="' + fieldName + '"]');

        if (error) {
            error.textContent = message || '';
            error.hidden = !message;
        }

        if (input) {
            input.setAttribute(
                'aria-invalid',
                message ? 'true' : 'false'
            );
        }

        if (wrapper) {
            wrapper.classList.toggle('is-invalid', !!message);
        }
    }

    /**
     * Validate a link field.
     */
    function checkLink(name) {
        const input = $('#' + name);

        if (!input) {
            console.warn('[AIOA] Link input not found:', name);
            return true;
        }

        input.value = withScheme(input.value);

        const valid = isValidLink(input.value);

        setLinkError(
            name,
            valid
                ? ''
                : 'Enter a full link that starts with https://, for example ' +
                  input.placeholder
        );

        return valid;
    }

    /* =========================================================================
     * Mobile position
     * ======================================================================= */

    /**
     * Show/hide mobile position controls.
     */
    function syncMobile() {
        const mobileBody = $('#settings-mobile-position-body');
        const mobilePreset = $('#settings-mobile-preset');
        const mobileCustom = $('#settings-mobile-custom');

        const enabled = !!(
            mobilePositionSwitch &&
            mobilePositionSwitch.checked
        );

        const custom = !!(
            customMobileSwitch &&
            customMobileSwitch.checked
        );

        if (mobileBody) {
            mobileBody.hidden = !enabled;
        }

        if (mobilePreset) {
            mobilePreset.hidden = custom;
        }

        if (mobileCustom) {
            mobileCustom.hidden = !custom;
        }

        if (!mobileExact()) {
    clearMobilePositionError();
}
    }

    /**
     * Update the mobile preview according to the selected position.
     */
/**
 * Update mobile widget preview according to selected position.
 */
function updateMobilePreview() {
    const input = document.querySelector(
        'input[name="settings[position_mobile]"]:checked, ' +
        'input[name="position_mobile"]:checked'
    );

    const preview = $('#settings-mini-widget-mobile');

    if (!input || !preview) {
        console.warn('[AIOA] Mobile preview position not found.', {
            selectedInput: input,
            preview: preview
        });
        return;
    }

    const gx = input.dataset.gx;
    const gy = input.dataset.gy;

    if (gx === undefined || gy === undefined) {
        console.warn('[AIOA] Mobile position data-gx/data-gy missing.');
        return;
    }

    preview.style.setProperty('--gx', gx);
    preview.style.setProperty('--gy', gy);

    //console.log('[AIOA] Mobile preview updated:', gx, gy);
}

    /**
     * Set an exact mobile position axis.
     */
    function setAxis(axis, direction, value) {
        const directionInput = $('#settings-mobile_' + axis + '_dir');
        const valueInput = $('#settings-mobile_' + axis + '_value');

        const safeValue = Math.min(
            Math.max(num(value), 0),
            OFFSET_MAX
        );

        if (directionInput) {
            directionInput.value = direction;
        }

        if (valueInput) {
            valueInput.value = safeValue;
        }
    }

    /**
     * Read one mobile position axis.
     */
    function readAxis(axis) {
        const directionInput = $('#settings-mobile_' + axis + '_dir');
        const valueInput = $('#settings-mobile_' + axis + '_value');

        return {
            direction: directionInput ? directionInput.value : '',
            value: valueInput ? num(valueInput.value) : 0
        };
    }

    /* =========================================================================
     * Fill settings
     * ======================================================================= */

    function fill(result) {
        const r = result || {};

        mobileFields.reset();

        /*
         * Links
         *
         * HTML IDs:
         * settings-statement_link
         * settings-report_problem_link
         *
         * API keys:
         * statement_link
         * report_problem_link
         */
        LINKS.forEach((name) => {
            const input = $('#' + name);
            const fieldName = name.replace(/^settings-/, '');

            if (input) {
                input.value = r[fieldName] || '';
            }

            setLinkError(name, '');
        });

        /*
         * Paid feature switches.
         */
        SWITCHES.forEach((id) => {
            const input = $('#' + id);
            const fieldName = id.replace(/^settings-/, '');

            if (input) {
                input.checked = isOn(r[fieldName]);
            } else {
                console.warn('[AIOA] Switch not found:', id);
            }
        });

        /*
         * Mobile switches.
         */
        if (mobilePositionSwitch) {
            mobilePositionSwitch.checked = isOn(r.is_mobile_position);
        }

        if (customMobileSwitch) {
            customMobileSwitch.checked = isOn(
                r.is_widget_custom_position_mobile
            );
        }

        /*
         * Mobile preset position.
         */
       const mobileRadioName = document.querySelector(
    'input[name="settings[position_mobile]"]'
)
    ? 'settings[position_mobile]'
    : 'position_mobile';

const mobilePosition = positionValue(r.widget_position_mobile) || 'bottom_right';

document.querySelectorAll(
    'input[name="settings[position_mobile]"], input[name="position_mobile"]'
).forEach((radio) => {
    radio.checked = radio.value === mobilePosition;
});

updateMobilePreview();



        /*
         * Horizontal position:
         * Center first, then left or right.
         */
        if (isSet(r.widget_position_horizontal_center_offset_mobile)) {
            setAxis(
                'x',
                'center',
                r.widget_position_horizontal_center_offset_mobile
            );
        } else if (
            num(r.widget_position_left_mobile) > 0 &&
            !(num(r.widget_position_right_mobile) > 0)
        ) {
            setAxis(
                'x',
                'left',
                r.widget_position_left_mobile
            );
        } else {
            setAxis(
                'x',
                'right',
                r.widget_position_right_mobile
            );
        }

        /*
         * Vertical position:
         * Bottom or top.
         */
        if (
            num(r.widget_position_top_mobile) > 0 &&
            !(num(r.widget_position_bottom_mobile) > 0)
        ) {
            setAxis(
                'y',
                'top',
                r.widget_position_top_mobile
            );
        } else {
            setAxis(
                'y',
                'bottom',
                r.widget_position_bottom_mobile
            );
        }

        syncMobile();
    }

    /* =========================================================================
     * Validation
     * ======================================================================= */

    function validate() {
        const problems = [];

        /*
         * Validate exact mobile position.
         */
      const emptyOffset = mobileExact()
    ? mobileFields.check()
    : null;

      if (mobileExact()) {
          updateMobilePositionValidation();
      }

        if (emptyOffset) {
            problems.push({
                field: emptyOffset,
                tab: 'paid',
                name: 'Widget position for mobile (Paid Features)',
                title: 'Mobile position missing',
                text: 'Enter the exact mobile position (0 - 500 px) on the Paid Features tab.'
            });
        }

        /*
         * Validate accessibility links.
         */
        LINKS.forEach((name) => {
            if (!checkLink(name)) {
                problems.push({
                    field: $('#' + name),
                    tab: 'paid',
                    name: LINK_NAMES[name] + ' (Paid Features)',
                    title: 'Invalid link',
                    text: 'Check the links on the Paid Features tab.'
                });
            }
        });

        return problems;
    }

    /* =========================================================================
     * Collect settings
     * ======================================================================= */

    function collect() {
        const x = readAxis('x');
        const y = readAxis('y');

        const offset = (axis, direction) => {
            return axis.direction === direction
                ? axis.value
                : '';
        };

        const getChecked = (id) => {
            const input = $('#' + id);
            return input && input.checked ? 1 : 0;
        };

        const statementLink = $('#settings-statement_link');
        const reportProblemLink = $('#settings-report_problem_link');

        return {
            statement_link: statementLink
                ? statementLink.value
                : '',

            report_problem_link: reportProblemLink
                ? reportProblemLink.value
                : '',

            widget_leave_page: getChecked('settings-widget_leave_page'),

            is_sound_disabled: getChecked('settings-is_sound_disabled'),

            is_mobile_position: getChecked('settings-is_mobile_position'),

            is_widget_custom_position_mobile: getChecked(
                'settings-custom-position-mobile-switcher'
            ),

           widget_position_mobile: getRadioValue('position_mobile'),

            widget_position_right_mobile: offset(x, 'right'),

            widget_position_left_mobile: offset(x, 'left'),

            widget_position_horizontal_center_offset_mobile: offset(
                x,
                'center'
            ),

            widget_position_bottom_mobile: offset(y, 'bottom'),

            widget_position_top_mobile: offset(y, 'top'),

            widget_position_vertical_center_offset_mobile: ''
        };
    }

    /* =========================================================================
     * Event listeners
     * Bind once, outside fill().
     * ======================================================================= */

    LINKS.forEach((name) => {
        const input = $('#' + name);

        if (!input) {
            console.warn('[AIOA] Paid Features link field not found:', name);
            return;
        }

        input.addEventListener('blur', () => {
            checkLink(name);
        });

        input.addEventListener('input', () => {
            setLinkError(name, '');
        });
    });

    if (mobilePositionSwitch) {
        mobilePositionSwitch.addEventListener('change', syncMobile);
    }

    if (customMobileSwitch) {
        customMobileSwitch.addEventListener('change', syncMobile);
    }

    /*
     * Update preview when a mobile position is selected.
     */
    // document
    //     .querySelectorAll('input[name="position_mobile"]')
    //     .forEach((radio) => {
    //         radio.addEventListener('change', updateMobilePreview);
    //     });
    document.querySelectorAll(
        'input[name="settings[position_mobile]"], input[name="position_mobile"]'
    ).forEach((radio) => {
        radio.addEventListener('change', updateMobilePreview);
    });
    /*
     * Restrict mobile offsets to whole numbers from 0 to 500.
     */
    [
        ['x', mobileX],
        ['y', mobileY]
    ].forEach(([axis, input]) => {
        if (!input) return;

        wholeNumberField(input, OFFSET_MAX);

        input.addEventListener('input', updateMobilePositionValidation);
        input.addEventListener('change', updateMobilePositionValidation);
        input.addEventListener('blur', updateMobilePositionValidation);
    });

    /* =========================================================================
     * Public API
     * ======================================================================= */

    AIOA.paid = {
        fill: fill,
        validate: validate,
        collect: collect
    };

})(window.AIOA, document);
