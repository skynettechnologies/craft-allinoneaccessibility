<?php
/**
 * Paid Features > Widget position for mobile
 * Same layout as Appearance Design > Widget position (views/partials/settings/position.php),
 * with phone-shaped glyphs and a "Mobile view" preview in the center cell.
 *   is_mobile_position                  switch: use a separate position on phones
 *   is_widget_custom_position_mobile    "Set an exact position in pixels": 0 = presets, 1 = offsets
 *   widget_position_mobile              preset (AIOA_POSITIONS in app/config.php)
 *   Exact position (0 - 500 px):
 *     horizontal  To the left / To the right / Center  -> widget_position_{left,right}_mobile,
 *                 widget_position_horizontal_center_offset_mobile (the widget puts the icon at left: 50% + offset)
 *     vertical    To the top / To the bottom           -> widget_position_{top,bottom}_mobile
 */
?>
<section class="aioa-card" aria-labelledby="mobile-position-title">
  <div class="aioa-switch-head">
    <input type="checkbox" role="switch" class="aioa-switch" id="is_mobile_position" name="is_mobile_position" value="1" aria-controls="mobile-position-body" />
    <h2 id="mobile-position-title"><label for="is_mobile_position">Widget position for mobile</label></h2>
  </div>

  <div id="mobile-position-body" hidden>

    <!-- Preset grid <-> exact offsets -->
    <label class="aioa-check">
      <input type="checkbox" id="custom-position-mobile-switcher" aria-controls="mobile-custom mobile-preset" />
      Set an exact position in pixels
    </label>

    <!-- Preset positions -->
    <fieldset class="aioa-fieldset widget-position" id="mobile-preset">
      <legend class="visually-hidden">Widget icon position on mobile</legend>
      <div class="aioa-pos-grid">
        <?php foreach (AIOA_POSITIONS as $pos) : ?>
          <?php if ($pos === null) : ?>
            <!-- Center cell: mini website with the icon at the selected mobile position -->
            <div class="aioa-pos-center" aria-hidden="true">
              <span class="aioa-mini-label">Mobile view</span>
              <span class="aioa-mini-widget" id="mini-widget-mobile" style="--gx:1;--gy:1"><img class="iconimg" src="<?php echo  e($iconBase); ?>/aioa-icon-type-1.svg" alt="" /></span>
            </div>
          <?php else : ?>
            <div class="aioa-pos-option">
              <input type="radio" class="aioa-radio" id="mobile-position-<?php echo e(aioa_slug($pos[0])); ?>" name="position_mobile" value="<?php echo e($pos[0]); ?>" data-gx="<?php echo $pos[2]; ?>" data-gy="<?php echo $pos[3]; ?>" <?php echo $pos[0] === 'bottom_right' ? 'checked' : ''; ?> />
              <label for="mobile-position-<?php echo e(aioa_slug($pos[0])); ?>" class="aioa-pos-label">
                <span class="aioa-glyph is-phone" style="--gx:<?php echo $pos[2]; ?>;--gy:<?php echo $pos[3]; ?>" aria-hidden="true"><i></i></span>
                <?php echo e($pos[1]); ?>
              </label>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <!-- Exact offsets -->
    <div class="aioa-subpanel" id="mobile-custom" hidden>
      <div class="aioa-offset-row">
        <label class="aioa-label" for="mobile_x_value">Horizontal</label>
        <div class="aioa-affix" data-for="mobile_x_value">
          <input type="number" min="0" max="500" step="1" inputmode="numeric" id="mobile_x_value" value="0" aria-describedby="mobile-range-hint mobile-pos-error" />
          <span class="aioa-affix-text" aria-hidden="true">px</span>
        </div>
        <select class="aioa-select" id="mobile_x_dir" aria-label="Horizontal direction (mobile)">
          <option value="left">To the left</option>
          <option value="right" selected>To the right</option>
          <option value="center">Center</option>
        </select>
      </div>
      <div class="aioa-offset-row">
        <label class="aioa-label" for="mobile_y_value">Vertical</label>
        <div class="aioa-affix" data-for="mobile_y_value">
          <input type="number" min="0" max="500" step="1" inputmode="numeric" id="mobile_y_value" value="0" aria-describedby="mobile-range-hint mobile-pos-error" />
          <span class="aioa-affix-text" aria-hidden="true">px</span>
        </div>
        <select class="aioa-select" id="mobile_y_dir" aria-label="Vertical direction (mobile)">
          <option value="top">To the top</option>
          <option value="bottom" selected>To the bottom</option>
        </select>
      </div>
      <p class="aioa-hint" id="mobile-range-hint">0 - 500px are permitted values</p>
      <p class="aioa-error" id="mobile-pos-error" hidden></p>
    </div>
  </div>
</section>
