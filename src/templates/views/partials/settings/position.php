<?php
/**
 * Settings > Widget position
 * A 3 x 3 grid of preset positions (AIOA_POSITIONS in app/config.php). Each
 * option has a small screen glyph with a dot where the icon goes; the center
 * cell is a mini website whose icon moves to the selected position
 * (updateMiniWidget in assets/js/settings-form.js, using data-gx / data-gy).
 * With "Set an exact position" checked, exact offsets in pixels are used instead.
 */
?>
<section class="aioa-card" aria-labelledby="position-title">
  <h2 id="position-title">Widget position</h2>
  <p class="aioa-desc">Where the accessibility icon appears on your website.</p>

  <!-- Preset grid <-> exact offsets -->
  <label class="aioa-check">
    <input type="checkbox" id="custom-position-switcher" aria-controls="custom-position-controls position_html" />
    
    Set an exact position in pixels
  </label>

  <!-- Preset positions -->
  <fieldset class="aioa-fieldset widget-position" id="position_html">
    <legend class="visually-hidden">Widget icon position</legend>
    <div class="aioa-pos-grid">
      <?php foreach (AIOA_POSITIONS as $pos) : ?>
        <?php if ($pos === null) : ?>
          <!-- Center cell: mini website with the icon at the selected position -->
          <div class="aioa-pos-center" aria-hidden="true">
            <span class="aioa-mini-label">Your website</span>
            <span class="aioa-mini-widget" id="mini-widget" style="--gx:1;--gy:1"><img class="iconimg" src="<?php echo e($iconBase); ?>aioa-icon-type-1.svg" alt="" /></span>
          </div>
        <?php else : ?>
          <div class="aioa-pos-option">
            <input type="radio" class="aioa-radio" id="edit-position-<?php echo e(aioa_slug($pos[0])); ?>" name="position" value="<?php echo e($pos[0]); ?>" data-gx="<?php echo $pos[2]; ?>" data-gy="<?php echo $pos[3]; ?>" <?php echo $pos[0] === 'bottom_right' ? 'checked' : ''; ?> />
            <label for="edit-position-<?php echo e(aioa_slug($pos[0])); ?>" class="aioa-pos-label">
              <span class="aioa-glyph" style="--gx:<?php echo $pos[2]; ?>;--gy:<?php echo $pos[3]; ?>" aria-hidden="true"><i></i></span>
              <?php echo e($pos[1]); ?>
            </label>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </fieldset>

  <!-- Exact offsets (0 - 500 px) -->
  <div class="aioa-subpanel custom-position-controls" id="custom-position-controls" hidden>
    <div class="aioa-offset-row">
      <label class="aioa-label" for="custom_position_x_value">Horizontal</label>
      <div class="aioa-affix" data-for="custom_position_x_value">
        <input type="number" min="0" max="500" step="1" inputmode="numeric" id="custom_position_x_value" value="0" aria-describedby="pos-range-hint pos-error" />
        <span class="aioa-affix-text" aria-hidden="true">px</span>
      </div>
      <select class="aioa-select" id="custom_position_x_dir" aria-label="Horizontal direction">
        <option selected value="cust-pos-to-the-right">To the right</option>
        <option value="cust-pos-to-the-left">To the left</option>
      </select>
    </div>
    <div class="aioa-offset-row">
      <label class="aioa-label" for="custom_position_y_value">Vertical</label>
      <div class="aioa-affix" data-for="custom_position_y_value">
        <input type="number" min="0" max="500" step="1" inputmode="numeric" id="custom_position_y_value" value="0" aria-describedby="pos-range-hint pos-error" />
        <span class="aioa-affix-text" aria-hidden="true">px</span>
      </div>
      <select class="aioa-select" id="custom_position_y_dir" aria-label="Vertical direction">
        <option selected value="cust-pos-to-the-lower">To the bottom</option>
        <option value="cust-pos-to-the-upper">To the top</option>
      </select>
    </div>
    <p class="aioa-hint" id="pos-range-hint">0 - 500px are permitted values</p>
    <p class="aioa-error" id="pos-error" hidden></p>
  </div>
</section>
