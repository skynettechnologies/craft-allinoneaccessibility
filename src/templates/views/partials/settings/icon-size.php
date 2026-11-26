<?php
/**
 * Settings > Icon size for desktop
 * Preset sizes (AIOA_ICON_SIZES in app/config.php) or an exact size in pixels
 * when "Set an exact size" is checked. Each preview is drawn at its own size
 * (--d), from Big down to Extra small, and uses the selected icon type.
 */
?>
<section class="aioa-card" aria-labelledby="icon-size-title">
  <h2 id="icon-size-title">Icon size for desktop</h2>
  <p class="aioa-desc">Pick a preset size or set an exact size in pixels.</p>

  <!-- Preset sizes <-> exact size -->
  <label class="aioa-check">
    <input type="checkbox" id="custom-size-switcher" aria-controls="custom-size-controls select_icon_size" />
    Set an exact size in pixels
  </label>

  <!-- Preset sizes -->
  <fieldset class="aioa-fieldset widget-icon" id="select_icon_size">
    <legend class="visually-hidden">Select icon size for desktop</legend>
    <div class="aioa-size-row">
      <?php foreach (AIOA_ICON_SIZES as $size) : ?>
        <div class="aioa-icon-option">
          <input type="radio" class="aioa-radio" id="edit-size-<?php echo e(aioa_slug($size[0])); ?>" name="aioa_icon_size" value="<?php echo e($size[0]); ?>" <?php echo $size[0] === 'aioa-medium-icon' ? 'checked' : ''; ?> />
          <label for="edit-size-<?php echo e(aioa_slug($size[0])); ?>" class="aioa-icon-label">
            <span class="aioa-icon" style="--d:<?php echo (int) $size[2]; ?>px"><img
    src="<?php echo e($iconBase); ?>aioa-icon-type-1.svg"
    class="iconimg"
    alt=""
/></span>

 <!-- <span class="aioa-icon" style="--d:<?php echo (int) $size[2]; ?>px"><img  src="<?php echo e($iconBase); ?>aioa-icon-type-1.svg" class="iconimg" alt="" /></span> -->
            <span class="aioa-icon-caption"><?php echo e($size[1]); ?></span>
          </label>
        </div>
      <?php endforeach; ?>
    </div>
  </fieldset>

  <!-- Exact size (24 - 150 px) -->


   <!-- Exact size (24 - 150 px) -->
  <div class="aioa-subpanel custom-size-controls" id="custom-size-controls" hidden>
    <label class="aioa-label" for="widget_icon_size_custom">Exact icon size</label>
    <div class="aioa-affix aioa-size-input" data-for="widget_icon_size_custom">
      <input type="number" id="widget_icon_size_custom" name="widget_icon_size_custom" min="24" max="150" step="1" inputmode="numeric" value="" aria-describedby="size-range-hint size-error" />
      <span class="aioa-affix-text" aria-hidden="true">px</span>
    </div>
    <p class="aioa-hint" id="size-range-hint">24 - 150px are permitted values</p>
    <!-- <p class="aioa-error" id="size-error" hidden></p> -->
    <p class="aioa-error" id="size-error" hidden>
    Enter an exact icon size from 24 to 150 px.
</p>
  </div>
</section>
