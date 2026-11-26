<?php
/**
 * Paid Features > custom links shown in the widget
 *   statement_link       custom accessibility statement page
 *   report_problem_link  custom "report a problem" page
 * Empty is allowed; otherwise a full http(s) link (assets/js/paid-features.js).
 */

// [field name, title, placeholder, icon (SVG inner markup, 24 x 24)]
$links = [
    ['statement_link', 'Enter custom accessibility statement link', 'https://www.yourwebsite.com/accessibility-statement',
        '<path d="M9 7h8M9 12h8M9 17h8" /><circle cx="5.5" cy="7" r=".6" fill="currentColor" /><circle cx="5.5" cy="12" r=".6" fill="currentColor" /><circle cx="5.5" cy="17" r=".6" fill="currentColor" />'],
    ['report_problem_link', 'Enter custom report a problem link', 'https://www.yourwebsite.com/report-a-problem',
        '<path d="M12 4l9 16H3z" /><path d="M12 10v4M12 17v.5" />'],
];
?>
<?php foreach ($links as $link) : ?>
  <section class="aioa-card" aria-labelledby="<?php echo e($link[0]); ?>-title">
    <h2 class="aioa-feature-title" id="<?php echo e($link[0]); ?>-title">
      <span class="aioa-feature-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $link[3]; ?></svg>
      </span>
      <label for="<?php echo e($link[0]); ?>"><?php echo e($link[1]); ?></label>
    </h2>
    <div class="aioa-affix aioa-link-input" data-for="<?php echo e($link[0]); ?>">
      <span class="aioa-affix-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1" /><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1" /></svg>
      </span>
      <input type="url" id="<?php echo e($link[0]); ?>" name="<?php echo e($link[0]); ?>" inputmode="url" autocomplete="off" spellcheck="false"
        placeholder="<?php echo e($link[2]); ?>" aria-describedby="<?php echo e($link[0]); ?>-error" />
    </div>
    <p class="aioa-error" id="<?php echo e($link[0]); ?>-error" hidden></p>
  </section>
<?php endforeach; ?>
