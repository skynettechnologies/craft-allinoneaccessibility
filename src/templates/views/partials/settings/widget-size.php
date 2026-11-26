<?php
/**
 * Settings > Widget size
 * Size of the panel that opens when visitors click the widget icon:
 *   Regular Size (widget_size = 0) - narrower panel, 2 columns of feature tiles
 *   Oversize     (widget_size = 1) - wider panel, 3 columns of feature tiles
 * Each option shows a small outline of that open panel.
 */

// [value, id, label, css modifier, number of feature tiles in the outline]
$widgetSizes = [
    ['0', 'edit-widget-size-regularsize', 'Regular Size', 'is-regular', 6],
    ['1', 'edit-widget-size-oversize', 'Oversize', 'is-over', 9],
];
?>
<section class="aioa-card" aria-labelledby="widget-size-title">
  <h2 id="widget-size-title">Widget size</h2>
  <p class="aioa-desc">Size of the panel that opens on your website.</p>

  <fieldset class="aioa-fieldset">
    <legend class="visually-hidden">Select widget size</legend>
    <div class="aioa-size-options">
      <?php foreach ($widgetSizes as $size) : ?>
        <label class="aioa-choice aioa-size-choice" for="<?php echo e($size[1]); ?>">
          <!-- Outline of the open widget panel: header, statement bar, feature tiles -->
          <span class="aioa-panel <?php echo e($size[3]); ?>" aria-hidden="true">
            <span class="aioa-panel-head"></span>
            <span class="aioa-panel-grid"><?php echo str_repeat('<i></i>', $size[4]); ?></span>
          </span>
          <span class="aioa-choice-text">
            <input type="radio" id="<?php echo e($size[1]); ?>" name="widget_size" value="<?php echo e($size[0]); ?>" <?php echo $size[0] === '0' ? 'checked' : 'aria-describedby="widget-size-note"'; ?> />
            <?php echo e($size[2]); ?>
          </span>
        </label>
      <?php endforeach; ?>
    </div>
  </fieldset>
  <p class="aioa-hint" id="widget-size-note">Oversize only works on desktop view.</p>
</section>
