<?php
/**
 * Accessibility Score card: status, score ring and three figures.
 * Included twice by views/partials/accessibility-score.php:
 *   live   - filled by assets/js/score.js from route.php?action=fetchScanDetails ([data-field] elements)
 *   sample - fixed example values behind the free plan blur (no ids)
 *
 * @var bool $sample
 */

// Example values for the sample card
$values = $sample ? [
    'status' => 'Compliant', 'score' => 98,
    'violations-pct' => '86.36%', 'violations-count' => '38 / 44',
    'alt-pct' => '0%', 'alt-count' => '0 / 0',
    'pages' => '13', 'pages-count' => '13 / 13',
] : array_fill_keys(['status', 'score', 'violations-pct', 'violations-count', 'alt-pct', 'alt-count', 'pages', 'pages-count'], '');

// [value field, count field, label]
$stats = [
    ['violations-pct', 'violations-count', 'Violations Remediated'],
    ['alt-pct', 'alt-count', 'Alt Text Remediated'],
    ['pages', 'pages-count', 'Web Pages Scanned'],
];
?>
<section class="aioa-card aioa-score" data-level="compliant"
  <?php echo $sample ? '' : 'id="score-card" aria-labelledby="score-title" hidden'; ?>>
  <div class="aioa-score-head">
    <h2 <?php echo $sample ? '' : 'id="score-title"'; ?>>Accessibility Score</h2>
    <p class="aioa-score-status" data-field="status"><?php echo e($values['status']); ?></p>
  </div>

  <div class="aioa-score-body">
    <!-- Score ring: --p is the score in percent -->
    <div class="aioa-donut" style="--p:<?php echo (int) $values['score']; ?>" role="img" aria-label="Accessibility score <?php echo (int) $values['score']; ?>%">
      <svg viewBox="0 0 120 120" aria-hidden="true">
        <circle class="aioa-donut-track" cx="60" cy="60" r="52" />
        <circle class="aioa-donut-bar" cx="60" cy="60" r="52" pathLength="100" />
      </svg>
      <span class="aioa-donut-value" aria-hidden="true"><b data-field="score"><?php echo e($values['score']); ?></b><small>%</small></span>
    </div>

    <div class="aioa-score-stats">
      <?php foreach ($stats as $stat) : ?>
        <div class="aioa-stat">
          <p class="aioa-stat-value" data-field="<?php echo e($stat[0]); ?>"><?php echo e($values[$stat[0]]); ?></p>
          <p class="aioa-stat-label"><?php echo e($stat[2]); ?></p>
          <p class="aioa-stat-count" data-field="<?php echo e($stat[1]); ?>"><?php echo e($values[$stat[1]]); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!$sample) : ?>
    <!-- Last scan date and PDF report (when the API has one) -->
    <p class="aioa-score-foot">
      <span data-field="last-scan"></span>
      <a data-field="report" href="#" target="_blank" rel="noopener" hidden>Download PDF report<span class="visually-hidden"> (opens in a new tab)</span></a>
    </p>
  <?php endif; ?>
</section>
