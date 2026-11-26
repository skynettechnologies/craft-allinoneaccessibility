<?php
/**
 * Accessibility Score tab (assets/js/score.js).
 * Paid plan: the scan results load the first time the tab opens, and show as
 * loading, error, "scanning in progress" (no results yet) or the score card.
 * Free plan: a sample card, blurred, with the upgrade overlay.
 */
?>
<div class="aioa-lockable">

  <!-- Free plan: example card behind the blur -->
  <div class="aioa-lock-content" id="score-sample" aria-hidden="true" hidden>
    <?php $sample = true; require __DIR__ . '/score/card.php'; ?>
  </div>

  <!-- Paid plan -->
  <div id="score-live">
    <div class="aioa-loading aioa-card" id="score-loading" role="status" hidden>
      <span class="aioa-spinner" aria-hidden="true"></span>
      <span>Loading your accessibility score…</span>
    </div>

    <section class="aioa-card aioa-error-card" id="score-error" hidden>
      <h2>We couldn't load your accessibility score</h2>
      <p id="score-error-text">Please check your connection and try again.</p>
      <button type="button" class="aioa-btn aioa-btn-primary" id="score-retry">Try again</button>
    </section>

    <!-- No scan results yet (the scanner answered no_scan) -->
    <section class="aioa-card aioa-error-card" id="score-empty" hidden>
      <h2>Scanning in progress</h2>
      <p>Website scanning is in progress. It may take a few days, depending on the number of web pages and other factors. Once it is complete, the system will send a confirmation to your registered email.</p>
    </section>

    <?php $sample = false; require __DIR__ . '/score/card.php'; ?>
  </div>

  <?php $lockId = 'score-lock'; $lockMod = 'is-center'; require __DIR__ . '/lock.php'; ?>
</div>
