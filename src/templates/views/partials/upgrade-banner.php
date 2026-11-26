<?php
/**
 * Free plan: purple "Upgrade to Paid Plan" banner.
 * Shown when the registered website is on the free widget (is_free_widget = 1).
 */
?>
<section id="free_plan_upgrade_block" class="aioa-upgrade" aria-labelledby="upgrade-title" hidden>
  <!-- Large faded accessibility figure in the top right corner -->
  <svg class="aioa-upgrade-watermark" viewBox="0 0 69 69" fill="#fff" aria-hidden="true" focusable="false"><path d="<?php echo AIOA_LOGO_PATH; ?>" /></svg>

  <!-- Title, what the paid plan adds, call to action -->
  <div class="aioa-upgrade-head">
    <div class="aioa-upgrade-intro">
      <p class="aioa-upgrade-tag"><i aria-hidden="true"></i>Free plan active</p>
      <h2 id="upgrade-title">With 24 Features</h2>
      <p>Upgrade to unlock <strong>90+ Premium Accessibility Features</strong> and make your website even more inclusive.</p>
      <ul class="aioa-upgrade-points">
        <li>190+ Supported languages</li>
        <li>12 Accessibility Profiles</li>
      </ul>
    </div>
   <div class="aioa-upgrade-cta">
    <a
        href="<?php echo e(AIOA_UPGRADE_URL); ?>"
        data-upgrade-link
        target="_blank"
        rel="noopener"
        class="aioa-btn-white"
    >
        <span>Upgrade to Paid Plan</span>

        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>

        <span class="visually-hidden">
            (opens in a new tab)
        </span>
    </a>

    <small>10-day free trial &middot; Cancel anytime</small>
</div>

  <!-- Note -->
  <p class="aioa-upgrade-note">
    <b>NOTE:</b> Currently, All in One Accessibility is dedicated to enhancing accessibility specifically for websites and online stores.
  </p>
</section>
