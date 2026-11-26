<?php
/**
 * Free plan overlay for a paid tab: centered card over the blurred tab content,
 * with the free trial text and the "Upgrade to Paid Plan" button.
 * Shown / hidden by AIOA.tabs.setPlan (assets/js/tabs.js).
 *
 * @var string $lockId   id prefix for the card text (aria-labelledby), e.g. "paid-lock"
 * @var string $lockMod  extra class: "" (card near the top) or "is-center" (short content)
 */
?>
<div class="aioa-lock <?php echo e($lockMod); ?>" hidden>
  <div class="aioa-lock-card" role="group" aria-labelledby="<?php echo e($lockId); ?>-text">
    <span class="aioa-lock-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V8a4 4 0 0 1 8 0v3" /></svg>
    </span>
    <p class="aioa-lock-text" id="<?php echo e($lockId); ?>-text">Try a 10-day free trial to view all 90+ features.</p>
    <a href="<?php echo e(AIOA_UPGRADE_URL); ?>" target="_blank" rel="noopener" class="aioa-btn aioa-btn-primary">
      Upgrade to Paid Plan
      <span class="visually-hidden">(opens in a new tab)</span>
    </a>
  </div>
</div>
