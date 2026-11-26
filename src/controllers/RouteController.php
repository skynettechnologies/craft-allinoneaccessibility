<?php

namespace skynettechnologies\craftallinoneaccessibility\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class RouteController extends Controller
{
    protected array|bool|int $allowAnonymous = false;

   public function actionRoute(): Response
{
    $request = Craft::$app->getRequest();

    $action = $request->getBodyParam('aioaAction');
    $postData = $request->getBodyParams();

    $widgetFile = dirname(__DIR__) . '/templates/app/SkynetWidget.php';

    if (!file_exists($widgetFile)) {
        return $this->asJson([
            'error' => 'SkynetWidget file not found.',
            'path' => $widgetFile,
        ]);
    }

    require_once $widgetFile;

    if (!class_exists('\\SkynetWidget')) {
        return $this->asJson([
            'error' => 'SkynetWidget class not found.',
        ]);
    }

    try {
        switch ($action) {
            case 'fetchWidgetSettings':
                $result = \SkynetWidget::fetchWidgetSettings($postData);
                break;

            case 'registerDomain':
                $result = \SkynetWidget::registerDomain($postData);
                break;

            case 'getDashboardLink':
                $result = \SkynetWidget::getDashboardLink($postData);
                break;

            case 'fetchScanDetails':
                $result = \SkynetWidget::getScanDetails($postData);
                break;

            default:
                return $this->asJson([
                    'error' => 'Invalid AIOA action: ' . $action,
                ]);
        }

        $decoded = is_string($result) ? json_decode($result, true) : $result;

        if (!is_array($decoded)) {
            return $this->asJson([
                'error' => 'Invalid response from SkynetWidget.',
            ]);
        }

        return $this->asJson($decoded);

    } catch (\Throwable $e) {
        Craft::error(
            'AIOA route error: ' . $e->getMessage(),
            __METHOD__
        );

        return $this->asJson([
            'error' => 'Unable to process the request.',
        ]);
    }
}
}
