<?php
/**
 * Tabs + widget settings form. Each Appearance Design section lives in
 * views/partials/settings/, the paid tabs in paid-features.php and accessibility-score.php.
 * The form is read and filled by assets/js/settings-form.js (+ paid-features.js)
 * and saved by assets/js/save.js. The save bar shows on Appearance Design, and on Paid Features
 * on a paid plan (assets/js/tabs.js).
 */
?>
<?php require __DIR__ . '/tabs.php'; ?>

<form id="widget-form" name="widget-form" class="aioa-form" novalidate>

  <!-- Appearance Design -->
  <div class="aioa-tabpanel" role="tabpanel" id="panel-appearance" aria-labelledby="tab-appearance" tabindex="0">
    <div class="aioa-columns">
      <?php require __DIR__ . '/settings/color.php'; ?>
      <?php require __DIR__ . '/settings/widget-size.php'; ?>
    </div>

    <?php require __DIR__ . '/settings/position.php'; ?>
    <?php require __DIR__ . '/settings/icon-type.php'; ?>
    <?php require __DIR__ . '/settings/icon-size.php'; ?>
  </div>

  <!-- Paid Features -->
  <div class="aioa-tabpanel" role="tabpanel" id="panel-paid" aria-labelledby="tab-paid" tabindex="0" hidden>
    <?php require __DIR__ . '/paid-features.php'; ?>
  </div>

  <!-- Accessibility Score -->
  <div class="aioa-tabpanel" role="tabpanel" id="panel-score" aria-labelledby="tab-score" tabindex="0" hidden>
    <?php require __DIR__ . '/accessibility-score.php'; ?>
  </div>

  <!-- Save bar (Appearance Design, and Paid Features on a paid plan); stays at the bottom of the screen while scrolling -->
  <div id="save-area">
    <?php require __DIR__ . '/settings/save-bar.php'; ?>
  </div>

</form>
