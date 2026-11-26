<?php
/**
 * Loading and error states. Only one of #load, #error_block and
 * #settings_block is visible at a time (AIOA.ui.setView in assets/js/ui.js).
 */
?>
<!-- Loading -->
<div id="load" class="aioa-loading" role="status">
  <span class="aioa-spinner" aria-hidden="true"></span>
  <span>Loading your widget settings…</span>
</div>

<!-- Error (settings could not be loaded); title and text set by assets/js/app.js -->
<section id="error_block" class="aioa-card aioa-error-card" hidden>
  <h2 id="error-title">We couldn't load your widget settings</h2>
  <p id="error-message">Please check your connection and try again.</p>
  <button type="button" class="aioa-btn aioa-btn-primary" id="retry-btn">Try again</button>
</section>
