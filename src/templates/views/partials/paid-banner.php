<?php
/**
 * Paid plan notice with the "Dashboard" button.
 * Shown instead of the upgrade notice when is_free_widget = 0. The button
 * opens the dashboard with the sign-in link (manage_domain from check-website)
 * that assets/js/dashboard.js loads as soon as the plan is known to be paid.
 */
?>
<section id="paid_plan_block" class="aioa-notice" aria-labelledby="paid-title" hidden>
  <div class="aioa-notice-body">
    <p class="aioa-tag is-success">Paid plan active</p>
    <h2 id="paid-title">All pro features are unlocked</h2>
    <p>Manage features, accessibility reports and widget analytics from your dashboard.</p>
  </div>
  <div class="aioa-notice-action">
    <button type="button" class="aioa-btn aioa-btn-primary" data-aioa-dashboard>
      Dashboard
      <span class="visually-hidden">(opens in a new tab)</span>
    </button>
  </div>
</section>
