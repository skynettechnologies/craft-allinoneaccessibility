<?php
/**
 * ---------------------------------------------------------------------------
 * All in One Accessibility - AJAX router
 * ---------------------------------------------------------------------------
 * The page calls this file with fetch():  POST route.php?action=<name>
 * Every response is JSON.
 *
 *   fetchWidgetSettings  Load settings ({"not_registered": true} for a new website; never registers)
 *   registerDomain       Register the free widget for a new website (once), with no_required_eu
 *   getDashboardLink     Dashboard sign-in link, manage_domain from check-website (paid plans)
 *   fetchScanDetails     Accessibility Score tab: scan results of a domain (paid plans)
 *
 * Platform integration: this file and index.php belong in the platform's admin
 * panel. Serve them to signed-in admins only (see docs/APP.md, Before going to production).
 */

require_once __DIR__ . '/app/SkynetWidget.php';

header('Content-Type: application/json; charset=utf-8');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? 'default';

// Platform integration point: stop here (401 / 403) when the request does not come from a signed-in admin

// Only POST requests are accepted
if ($requestMethod !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    echo json_encode(['error' => 'Method Not Allowed.']);
    exit;
}

switch ($action) {
    case 'fetchWidgetSettings':
        echo SkynetWidget::fetchWidgetSettings($_POST);
        break;

    case 'registerDomain':
        echo SkynetWidget::registerDomain($_POST);
        break;

    case 'getDashboardLink':
        echo SkynetWidget::getDashboardLink($_POST);
        break;

    case 'fetchScanDetails':
        echo SkynetWidget::getScanDetails($_POST);
        break;

    default:
        echo json_encode(['error' => 'Unknown action.']);
        break;
}
