/**
 * ---------------------------------------------------------------------------
 * settings-form.js - the widget settings form
 * ---------------------------------------------------------------------------
 *   AIOA.form.settingsFrom(result)  API response -> settings with defaults
 *   AIOA.form.fill(settings)        put settings into the form
 *   AIOA.form.collect()             validate + read the form as API parameters
 *                                   (+ the Paid Features tab on a paid plan, see paid-features.js)
 *   AIOA.form.setDirty(bool)        "You have unsaved changes" indicator
 * Also handles the color picker, the preset / custom switches and the
 * live previews (icon images, mini website, --widget-color).
 */
(function (AIOA, document) {
  'use strict';

  const { $, $$, parseHex, checkedValue, checkRadio, positionValue, wholeNumber, wholeNumberField, requiredFields } = AIOA.util;
  const state = AIOA.state;
  const config = AIOA.config;
  // Do not show validation errors until the user clicks Save once.
let validationAttempted = false;
  /**
 * Find the selected radio value.
 * Supports both normal names and Craft names: settings[field].
 */
function getRadioValue(name) {
    const input = document.querySelector(
        `input[name="${name}"]:checked, input[name="settings[${name}]"]:checked`
    );

    return input ? input.value : '';
}

/**
 * Select a radio button by value.
 * Supports both normal names and Craft names: settings[field].
 */
function setRadioValue(name, value) {
    const radios = document.querySelectorAll(
        `input[name="${name}"], input[name="settings[${name}]"]`
    );

    radios.forEach((radio) => {
        radio.checked = radio.value === value;
    });
}

  // Exact position: both values are required while "Set an exact position in pixels" is on
/* =========================================================================
 * Desktop exact position fields
 * ======================================================================= */

const desktopX = $('#settings-custom_position_x_value');
const desktopY = $('#settings-custom_position_y_value');
const customPositionSwitch = $('#settings-custom-position-switcher');

const positionFields = requiredFields(
    [desktopX, desktopY],
    $('#settings-pos-error'),
    'Enter a horizontal and a vertical position from 0 to 500 px.'
);
/**
 * Validate desktop X/Y fields while the user is typing.
 * Clear aria-invalid when the entered value becomes valid.
 */
function updateDesktopPositionValidation() {
    // Show errors only after the first Save attempt.
    if (!validationAttempted) return;

    if (!desktopExact()) {
        clearDesktopPositionError();
        return;
    }

    const isValidPosition = (input) => {
        if (!input || input.value.trim() === '') {
            return false;
        }

        const value = Number(input.value);

        return Number.isInteger(value) &&
            value >= 0 &&
            value <= 500;
    };

    const invalidX = !isValidPosition(desktopX);
    const invalidY = !isValidPosition(desktopY);

    [
        { input: desktopX, invalid: invalidX },
        { input: desktopY, invalid: invalidY }
    ].forEach(({ input, invalid }) => {
        if (!input) return;

        input.setAttribute('aria-invalid', String(invalid));

        const wrapper = input.closest('.aioa-affix');

        if (wrapper) {
            wrapper.classList.toggle('is-invalid', invalid);
        }
    });

    const error = $('#settings-pos-error');
    const hasError = invalidX || invalidY;

    if (error) {
        error.hidden = !hasError;
        error.style.display = hasError ? 'block' : 'none';
        error.textContent = hasError
            ? 'Enter both horizontal and vertical positions from 0 to 500 px.'
            : '';
    }
}

/**
 * Clear desktop position validation errors.
 */
function clearDesktopPositionError() {
    [desktopX, desktopY].forEach(input => {
        if (!input) return;

        input.setAttribute('aria-invalid', 'false');

        const wrapper = input.closest('.aioa-affix');

        if (wrapper) {
            wrapper.classList.remove('is-invalid');
        }
    });

    const error = $('#settings-pos-error');

    if (error) {
        error.hidden = true;
        error.style.display = 'none';
        error.textContent = '';
    }

    positionFields.reset();
}
function desktopExact() {
    return !!(
        customPositionSwitch &&
        customPositionSwitch.checked
    );
}

  // Exact icon size: a whole number from 24 to 150 px while "Set an exact size in pixels" is on
  const SIZE_MIN = 24;
  const SIZE_MAX = 150;
  const sizeField = requiredFields(
    [$('#settings-widget_icon_size_custom')],
    $('#settings-size-error'),
    'Enter an exact icon size from ' + SIZE_MIN + ' to ' + SIZE_MAX + ' px.',
    (input) => {
      const size = wholeNumber(input.value, null);
      return size !== null && size >= SIZE_MIN && size <= SIZE_MAX;
    }
  );

  /* =========================================================================
   * Settings <-> form
   * ======================================================================= */

  /** Normalize a widget settings response; anything missing falls back to the defaults */
  function settingsFrom(result) {
    const defaults = config.defaults;
    return {
      widget_position: positionValue(result.widget_position) || defaults.widget_position,
      widget_color_code: result.widget_color_code || defaults.widget_color_code,
      widget_icon_type: result.widget_icon_type || defaults.widget_icon_type,
      widget_icon_size: result.widget_icon_size || defaults.widget_icon_size,
      widget_size:
      result.widget_size !== undefined &&
      result.widget_size !== null &&
      result.widget_size !== ''
          ? String(result.widget_size)
          : String(defaults.widget_size),
      widget_icon_size_custom: wholeNumber(result.widget_icon_size_custom, '') || '',
      widget_position_top: wholeNumber(result.widget_position_top, 0),
      widget_position_bottom: wholeNumber(result.widget_position_bottom, 0),
      widget_position_left: wholeNumber(result.widget_position_left, 0),
      widget_position_right: wholeNumber(result.widget_position_right, 0)
    };
  }

  /** Put settings into the form. state.customPosition / customSize must be set first. */
  function fill(settings) {
    positionFields.reset();
    sizeField.reset();
    $('#settings-custom-position-switcher').checked = state.customPosition === 1;
    $('#settings-custom-size-switcher').checked = state.customSize === 1;
    syncCustomPosition();
    syncCustomSize();

    setColor(settings.widget_color_code);

    setRadioValue('position', settings.widget_position);
    updateMiniWidget();

    setRadioValue('widget_size', settings.widget_size);
    setRadioValue('aioa_icon_type', settings.widget_icon_type);
    setRadioValue('aioa_icon_size', settings.widget_icon_size);
    updateIconImages(settings.widget_icon_type);

    $('#settings-widget_icon_size_custom').value = settings.widget_icon_size_custom;

    // Exact horizontal offset: right wins over left
    const xField = $('#settings-custom_position_x_value');
    const xDirection = $('#settings-custom_position_x_dir');
    if (settings.widget_position_right > 0) {
      xField.value = settings.widget_position_right;
      xDirection.value = 'cust-pos-to-the-right';
    } else if (settings.widget_position_left > 0) {
      xField.value = settings.widget_position_left;
      xDirection.value = 'cust-pos-to-the-left';
    } else {
      xField.value = 0;
      xDirection.value = 'cust-pos-to-the-right'; // default direction
    }
// Ensure empty position values default to 0
// if (desktopX && desktopX.value.trim() === '') {
//     desktopX.value = '0';
// }

// if (desktopY && desktopY.value.trim() === '') {
//     desktopY.value = '0';
// }
    // Exact vertical offset: bottom wins over top
    const yField = $('#settings-custom_position_y_value');
    const yDirection = $('#settings-custom_position_y_dir');
    if (settings.widget_position_bottom > 0) {
      yField.value = settings.widget_position_bottom;
      yDirection.value = 'cust-pos-to-the-lower';
    } else if (settings.widget_position_top > 0) {
      yField.value = settings.widget_position_top;
      yDirection.value = 'cust-pos-to-the-upper';
    } else {
      yField.value = 0;
      yDirection.value = 'cust-pos-to-the-lower'; // default direction
     
    }
   


// Update position validation
updateDesktopPositionValidation();
  }

  /**
   * Check every field before saving. Every invalid field is marked in red with its message
   * at the same time, not only the first one. Returns the problems in page order:
   * { field, tab, name, title, text } (title and text: the toast when it is the only problem).
   */

function validate() {
   validationAttempted = true;
    const problems = [];

    // Validate widget color
    const colorInput = $('#settings-colorcode');

    if (!parseHex(colorInput.value)) {
        setColorError(true);

        problems.push({
            field: colorInput,
            tab: 'appearance',
            name: 'Widget color',
            title: 'Invalid color',
            text: 'Enter a valid hex color code, for example FF5733.'
        });
    } else {
        setColorError(false);
    }

    // Validate exact desktop position only when enabled
  // Validate exact desktop position only when enabled
if (desktopExact()) {
    const isValidPosition = (input) => {
        if (!input || input.value.trim() === '') {
            return false;
        }

        const value = Number(input.value);

        return Number.isInteger(value) &&
            value >= 0 &&
            value <= 500;
    };

    const invalidX = !isValidPosition(desktopX);
    const invalidY = !isValidPosition(desktopY);

    [
        { input: desktopX, invalid: invalidX },
        { input: desktopY, invalid: invalidY }
    ].forEach(({ input, invalid }) => {
        if (!input) return;

        input.setAttribute('aria-invalid', String(invalid));

        const wrapper = input.closest('.aioa-affix');

        if (wrapper) {
            wrapper.classList.toggle('is-invalid', invalid);
        }
    });

    const positionError = $('#settings-pos-error');
    const hasError = invalidX || invalidY;

    if (positionError) {
        positionError.hidden = !hasError;
        positionError.style.display = hasError ? 'block' : 'none';
        positionError.textContent = hasError
            ? 'Enter both horizontal and vertical positions from 0 to 500 px.'
            : '';
    }

    if (invalidX || invalidY) {
        problems.push({
            field: invalidX ? desktopX : desktopY,
            tab: 'appearance',
            name: 'Widget position',
            title: 'Position missing',
            text: 'Enter the exact position (0 - 500 px), or turn off "Set an exact position in pixels".'
        });
    }
} else {
    clearDesktopPositionError();
}

  // Validate custom desktop icon size only when enabled.
const sizeInput = $('#settings-widget_icon_size_custom');
const customSizeSwitch = $('#settings-custom-size-switcher');

const isCustomSizeEnabled =
    customSizeSwitch && customSizeSwitch.checked;

const sizeValue = wholeNumber(sizeInput.value.trim(), null);

const badSize = isCustomSizeEnabled && (
    sizeInput.value.trim() === '' ||
    sizeValue === null ||
    sizeValue < SIZE_MIN ||
    sizeValue > SIZE_MAX
);

setCustomSizeError(badSize);

if (badSize) {
    problems.push({
        field: sizeInput,
        tab: 'appearance',
        name: 'Icon size for desktop',
        title: 'Invalid icon size',
        text: 'Enter an exact icon size from 24 to 150 px.'
    });
}

    // Validate Paid Features
    if (
        state.isPaid &&
        AIOA.paid &&
        typeof AIOA.paid.validate === 'function'
    ) {
        problems.push(...AIOA.paid.validate());
    }

    return problems;
}


  /** "A", "A and B", "A, B and C" */
  function joinNames(names) {
    return names.length > 1 ? names.slice(0, -1).join(', ') + ' and ' + names[names.length - 1] : names[0];
  }

  /** Point the admin at what needs a fix: the first problem's tab and field, and one toast for all of them */
  function report(problems) {
    const first = problems[0];
    AIOA.tabs.select(first.tab);
    first.field.focus();
    if (problems.length === 1) {
      AIOA.ui.toast('error', first.title, first.text);
    } else {
      AIOA.ui.toast('error', 'Check the fields marked in red', joinNames(problems.map((p) => p.name)) + ' need a fix before you can save.');
    }
  }

  /**
   * Validate the form and build the parameters for widget-setting-update-platform.
   * Shows what is wrong (every problem at once) and returns null when the form is invalid.
   */
function collect() {
    const problems = validate();

    if (problems.length) {
      

        report(problems);
        return null;
    }


    const hex = parseHex($('#settings-colorcode').value);

    // Whole pixels only (the fields never hold decimals, see wholeNumberField)
 const customSizeInput = $('#settings-widget_icon_size_custom');

const widget_icon_size_custom = state.customSize === 1
    ? String(wholeNumber(customSizeInput.value, ''))
    : '';
    // Exact offsets: only the chosen direction of each axis is sent
    // (empty only while the exact position is off: then 0, as before)
const custom_position_x = state.customPosition === 1
    ? wholeNumber($('#settings-custom_position_x_value').value, null)
    : null;

const custom_position_y = state.customPosition === 1
    ? wholeNumber($('#settings-custom_position_y_value').value, null)
    : null;
    const x_position_direction = $('#settings-custom_position_x_dir').value;
    const y_position_direction = $('#settings-custom_position_y_dir').value;

    let widget_position_right = null;
    let widget_position_left = null;
    let widget_position_top = null;
    let widget_position_bottom = null;

    if (x_position_direction === 'cust-pos-to-the-right') {
      widget_position_right = custom_position_x;
    } else if (x_position_direction === 'cust-pos-to-the-left') {
      widget_position_left = custom_position_x;
    }

    if (y_position_direction === 'cust-pos-to-the-lower') {
      widget_position_bottom = custom_position_y;
    } else if (y_position_direction === 'cust-pos-to-the-upper') {
      widget_position_top = custom_position_y;
    }

    const params = {
      u: state.activeDomain,
      widget_position: getRadioValue('position'),
      is_widget_custom_position: state.customPosition,
      is_widget_custom_size: state.customSize,
      widget_color_code: '#' + hex,

      widget_icon_type: getRadioValue('aioa_icon_type'),
      widget_icon_size: getRadioValue('aioa_icon_size'),
      widget_size: getRadioValue('widget_size'),
      widget_icon_size_custom: widget_icon_size_custom,
      widget_position_right: widget_position_right,
      widget_position_left: widget_position_left,
      widget_position_top: widget_position_top,
      widget_position_bottom: widget_position_bottom
    };

    // Paid Features: paid plans only (the tab holds the saved values by then, see app.js;
    // it was checked in validate)
    if (state.isPaid && AIOA.paid && typeof AIOA.paid.collect === 'function') {
    Object.assign(params, AIOA.paid.collect());
}
    return params;
  }

  /* =========================================================================
   * Previews and switches
   * ======================================================================= */

  /** Show the selected icon type in every preview image (.iconimg) */
/**
 * Update icon images in all icon size options.
 */
/**
 * Update icon images in all icon size options.
 */
function updateIconImages(iconType) {
    if (!iconType) {
        console.warn('Icon type is missing.');
        return;
    }

    const iconPath = config.iconBase + iconType + '.svg';

    console.log('Selected icon type:', iconType);
    console.log('Icon preview path:', iconPath);

    $$('.iconimg').forEach((img) => {
        img.src = iconPath;
    });
}
  /** Move the icon in the "Your website" preview to the selected position */
 /** Move the icon in the "Your website" preview to the selected position */
function updateMiniWidget() {
    const input = document.querySelector(
        'input[name="settings[position]"]:checked'
    );

    const preview = $('#settings-mini-widget');

    if (!input || !preview) {
        return;
    }

    preview.style.setProperty('--gx', input.dataset.gx);
    preview.style.setProperty('--gy', input.dataset.gy);
}

  /** Preset position grid <-> exact offsets */
function syncCustomPosition() {
    const on = desktopExact();

    state.customPosition = on ? 1 : 0;

    $('#settings-custom-position-controls').hidden = !on;
    $('#settings-position_html').hidden = on;

    if (!on) {
        clearDesktopPositionError();
    }
}

  function syncCustomSize() {
    const on = $('#settings-custom-size-switcher').checked;

    state.customSize = on ? 1 : 0;

    $('#settings-custom-size-controls').hidden = !on;
    $('#settings-select_icon_size').hidden = on;

    if (!on) {
        sizeField.reset();
        setCustomSizeError(false);
    }
}

  /* =========================================================================
   * Color picker
   * ======================================================================= */

function setColorError(show) {
    const error = $('#settings-colorcode-error');
    const input = $('#settings-colorcode');
    const wrapper = $('[data-for="colorcode"]');

    if (error) {
        error.hidden = !show;
        error.style.display = show ? 'block' : 'none';
    }

    if (input) {
        input.setAttribute('aria-invalid', show ? 'true' : 'false');
    }

    if (wrapper) {
        wrapper.classList.toggle('is-invalid', show);
    }
}

function updateCustomSizeValidation() {
    // Do not show errors before the first Save attempt.
    if (!validationAttempted) return;

    const input = $('#settings-widget_icon_size_custom');
    const customSizeSwitch = $('#settings-custom-size-switcher');

    if (!input || !customSizeSwitch) return;

    if (!customSizeSwitch.checked) {
        setCustomSizeError(false);
        return;
    }

    const value = input.value.trim();
    const size = wholeNumber(value, null);

    const invalid =
        value === '' ||
        size === null ||
        size < SIZE_MIN ||
        size > SIZE_MAX;

    setCustomSizeError(invalid);
}
function setCustomSizeError(show) {
    const error = $('#settings-size-error');
    const input = $('#settings-widget_icon_size_custom');
    const wrapper = $('[data-for="widget_icon_size_custom"]');

    if (error) {
        error.textContent = 'Enter an exact icon size from 24 to 150 px.';
        error.hidden = !show;
        error.style.display = show ? 'block' : 'none';
    }

    if (input) {
        input.setAttribute('aria-invalid', show ? 'true' : 'false');
    }

    if (wrapper) {
        wrapper.classList.toggle('is-invalid', show);
    }
}
  /**
   * Apply a color to the picker, the presets and the live preview (--widget-color).
   * @param {string} value   any hex format; invalid values fall back to the default color
   * @param {boolean} keepText leave the hex field as typed (while the admin is typing)
   */
  function setColor(value, keepText) {
    const hex = parseHex(value) || config.defaultColor;
    if (!keepText) {
      $('#settings-colorcode').value = hex;
    }
    $('#settings-colorpicker').value = '#' + hex.toLowerCase();
    document.documentElement.style.setProperty('--widget-color', '#' + hex);
    setColorError(false);
    $$('.aioa-preset').forEach((btn) => {
      btn.setAttribute('aria-pressed', String(btn.dataset.color === hex));
    });
  }

  /* =========================================================================
   * Save bar: unsaved changes indicator
   * ======================================================================= */

  function setDirty(dirty) {
    $('#settings-savebar').classList.toggle('is-dirty', dirty);
    $('#settings-save-state-text').textContent = dirty ? 'You have unsaved changes' : 'All changes saved';
  }

  /* =========================================================================
   * Event listeners
   * ======================================================================= */

  // Color picker, hex field, presets, reset
  $('#settings-colorpicker').addEventListener('input', (e) => setColor(e.target.value));

$('#settings-colorcode').addEventListener('input', (e) => {
    const clean = e.target.value
        .replace(/[^0-9a-f]/gi, '')
        .slice(0, 6)
        .toUpperCase();

    if (clean !== e.target.value) {
        e.target.value = clean;
    }

    if (clean.length === 6 && parseHex(clean)) {
        setColor(clean, true);
    }

    // After Save has been attempted, update the error while typing.
    if (validationAttempted) {
        setColorError(!parseHex(clean));
    }
});

$('#settings-colorcode').addEventListener('blur', (e) => {
    const hex = parseHex(e.target.value);

    if (hex) {
        setColor(hex);
    }

    // Do not show a new error on blur before Save is clicked.
    if (validationAttempted) {
        setColorError(!hex);
    }
});

  $$('.aioa-preset').forEach((btn) => {
    btn.addEventListener('click', () => {
      setColor(btn.dataset.color);
      setDirty(true);
    });
  });

  $('#settings-color-reset').addEventListener('click', () => {
    setColor(config.defaultColor);
    setDirty(true);
  });

  // Any change marks the form as unsaved; Enter must not submit the form
  $('#main-form').addEventListener('input', () => setDirty(true));
  $('#main-form').addEventListener('change', () => setDirty(true));
  $('#main-form').addEventListener('submit', (e) => e.preventDefault());

  // Preset / custom switches
$('#settings-custom-position-switcher').addEventListener('change', () => {
    syncCustomPosition();

    if (validationAttempted) {
        updateDesktopPositionValidation();
    }
});

$('#settings-custom-size-switcher').addEventListener('change', () => {
    syncCustomSize();

    if (validationAttempted) {
        updateCustomSizeValidation();
    }
});

  // Whole pixels only: exact offsets kept within 0 - 500 px while typing,
  // the exact icon size (24 - 150 px) is checked when saving
 // Desktop exact position: validate while typing
// Keep position values between 0 and 500.
[desktopX, desktopY].forEach((input) => {
    if (!input) return;

    wholeNumberField(input, 500);

    // After the first Save attempt, update validation while typing.
    input.addEventListener('input', updateDesktopPositionValidation);
    input.addEventListener('change', updateDesktopPositionValidation);
});
  //wholeNumberField($('#settings-widget_icon_size_custom'));
// Custom icon size: validate after Save, then update while typing.
const customSizeInput = $('#settings-widget_icon_size_custom');

wholeNumberField(customSizeInput);

customSizeInput.addEventListener('input', updateCustomSizeValidation);
customSizeInput.addEventListener('change', updateCustomSizeValidation);

  // Live previews
$$('input[name="settings[position]"]').forEach((radio) => {
    radio.addEventListener('change', updateMiniWidget);
});
/**
 * Update the selected icon type in every preview image.
 */
// function updateIconImages(iconType) {
//     if (!iconType) return;

//     $$('.iconimg').forEach((img) => {
//         img.src = config.iconBase + iconType + '.svg';
//     });
// }
$$(
    'input[name="aioa_icon_type"], input[name="settings[aioa_icon_type]"]'
).forEach((radio) => {
    radio.addEventListener('change', () => {
        if (radio.checked) {
            updateIconImages(radio.value);
            setDirty(true);
        }
    });
});
  AIOA.form = {
    settingsFrom: settingsFrom,
    fill: fill,
    collect: collect,
    setDirty: setDirty
  };
  
})(window.AIOA, document);
