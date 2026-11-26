<?php
/**
 * Paid Features tab. Read, filled and checked by assets/js/paid-features.js and
 * saved together with the Appearance Design settings (paid plans only).
 * Free plan: the content is blurred and inert, with the
 * upgrade overlay on top (views/partials/lock.php).
 */
?>
<div class="aioa-lockable">
  <div class="aioa-lock-content aioa-stack">
    <?php require __DIR__ . '/paid/mobile-position.php'; ?>
    <?php require __DIR__ . '/paid/links.php'; ?>
    <?php require __DIR__ . '/paid/toggles.php'; ?>
  </div>
  <?php $lockId = 'paid-lock'; $lockMod = ''; require __DIR__ . '/lock.php'; ?>
</div>
