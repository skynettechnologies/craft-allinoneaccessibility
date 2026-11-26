<?php
/**
 * Paid Features > widget behavior switches (sent as 1 / 0)
 *   widget_leave_page  Enable External Link Navigation On Widget
 *   is_sound_disabled  Disable Open Close sound on Widget
 */

// [field name, label]
$toggles = [
    ['widget_leave_page', 'Enable External Link Navigation On Widget'],
    ['is_sound_disabled', 'Disable Open Close sound on Widget'],
];
?>
<div class="aioa-pair">
  <?php foreach ($toggles as $toggle) : ?>
    <label class="aioa-card aioa-toggle-card" for="<?php echo e($toggle[0]); ?>">
      <input type="checkbox" role="switch" class="aioa-switch" id="<?php echo e($toggle[0]); ?>" name="<?php echo e($toggle[0]); ?>" value="1" />
      <span><?php echo e($toggle[1]); ?></span>
    </label>
  <?php endforeach; ?>
</div>
