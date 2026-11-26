<?php
/**
 * AIOA Settings Layout
 *
 * @var array  $page
 * @var string $assetBaseUrl
 */

// Single dynamic icon path for all PHP partials and JavaScript.
$iconBase = rtrim($assetBaseUrl, '/') . '/images/icons/';

// Boot data for assets/js/core.js
$bootData = [
    'domain'       => $page['domain'] ?? '',
    'name'         => $page['name'] ?? '',
    'email'        => $page['email'] ?? '',
    'iconBase'     => $iconBase,
    'defaultColor' => AIOA_DEFAULT_COLOR,

    'routeUrl' => \craft\helpers\UrlHelper::actionUrl(
        'allinone-accessibility/route/route'
    ),

    'csrfTokenName'  => \Craft::$app->getConfig()->getGeneral()->csrfTokenName,
    'csrfTokenValue' => \Craft::$app->getRequest()->getCsrfToken(),

    'apiUpdateUrl' => AIOA_API_BASE . 'widget-setting-update-platform',
    'geoApiUrl'    => AIOA_GEO_API,
];

$styles = [];
$scripts = [];
?>
<style>
    #action-buttons
    {
        display: none !important;
    }
</style>
<div class="aioa-page">

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main>
        <?php require __DIR__ . '/partials/status.php'; ?>

        <div id="settings_block" hidden>
            <?php require __DIR__ . '/partials/upgrade-banner.php'; ?>
            <?php require __DIR__ . '/partials/paid-banner.php'; ?>
            <?php require __DIR__ . '/partials/site-strip.php'; ?>
            <?php require __DIR__ . '/partials/settings-form.php'; ?>
        </div>
    </main>

</div>

<!-- Toast notifications -->
<div class="aioa-toasts" id="toasts" aria-live="polite"></div>

<!-- AIOA Boot Data -->
<script id="aioa-boot" type="application/json"><?=
    json_encode(
        $bootData,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
?></script>

<!-- Optional manually loaded scripts -->
<?php foreach ($scripts as $script): ?>
    <script src="<?= e(asset('js/' . $script)) ?>" defer></script>
<?php endforeach; ?>
