<?php
/**
 * Settings > Icon type
 * The icon visitors click to open the widget (aioa-icon-type-1 ... AIOA_ICON_TYPE_COUNT),
 * shown in the chosen widget color. Icons are bundled in assets/images/icons.
 */
?>
<section class="aioa-card" aria-labelledby="icon-type-title">
  <h2 id="icon-type-title">Icon type</h2>
  <p class="aioa-desc">The icon visitors click to open the widget.</p>

  <fieldset class="aioa-fieldset">
    <legend class="visually-hidden">Select icon type</legend>
    <div class="aioa-icon-grid">
      <?php for ($i = 1; $i <= AIOA_ICON_TYPE_COUNT; $i++) : ?>
        <div class="aioa-icon-option">
          <input type="radio" class="aioa-radio" id="edit-type-<?php echo $i; ?>" name="aioa_icon_type" value="aioa-icon-type-<?php echo $i; ?>" <?php echo $i === 1 ? 'checked' : ''; ?> />
          <label for="edit-type-<?php echo $i; ?>" class="aioa-icon-label">
            <span class="aioa-icon"><img
    src="<?php echo e($iconBase); ?>aioa-icon-type-<?php echo $i; ?>.svg"
    alt=""
    width="32"
    height="32"
    loading="lazy"
/></span>
            <span class="visually-hidden">Type <?php echo $i; ?></span>
          </label>
        </div>
      <?php endfor; ?>
    </div>
  </fieldset>
</section>
