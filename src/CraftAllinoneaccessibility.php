<?php

namespace skynettechnologies\craftallinoneaccessibility;

use skynettechnologies\craftallinoneaccessibility\models\Settings;
use Craft;
use craft\base\Plugin;
use skynettechnologies\craftallinoneaccessibility\assetbundles\craftallinoneaccessibility\CraftAllinoneaccessibilityAsset;
use yii\base\Event;
use craft\web\View;
use yii;
use craft\base\Model;
use yii\web\Application;
use craft\helpers\UrlHelper;

class CraftAllinoneaccessibility extends Plugin
{
  public static $plugin;
  public string $schemaVersion = '2.0.0';
  public bool $hasCpSettings = true;
  public bool $hasCpSection = false;

  public function init()
  {
    parent::init();
    self::$plugin = $this;

    \Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'registerCustomJs']);
  }

  protected function createSettingsModel(): ?Model
  {
    return new Settings();
  }

  protected function settingsHtml(): string
  {
    \skynettechnologies\craftallinoneaccessibility\assetbundles\AdminSettingsAsset::register(Craft::$app->getView());
    $settingVal = $this->getSettings();

    $data['license_key'] = $settingVal['license_key'];
    $data['color'] = $settingVal['color'];
    $data['position'] = $settingVal['position'];
    $data['icon_type'] = $settingVal['icon_type'];
    $data['icon_size'] = $settingVal['icon_size'];
    $data['isvalid_key'] = $settingVal['isvalid_key'];
    $siteurl = Craft::$app->getSites()->currentSite->baseUrl;
    // $domain = parse_url($siteurl, PHP_URL_HOST);

    if ($siteurl && !preg_match('#^https?://#', $siteurl)) {
      $siteurl = 'http://' . $siteurl;
    }

    $domain = $_SERVER['HTTP_HOST'];
    $data['domain'] = $domain;

    return Craft::$app->view->renderTemplate(
      "allinone-accessibility/settings",
      $data
    );
  }

  public function registerCustomJs($event)
  {
    $app = $event->sender;
    $scriptId = 'aioa-adawidget';

    $license_key = "";
    $color_code = '#420083';
    $position = 'bottom_right';
    $icon_type = 'aioa-icon-type-1';
    $icon_size = 'aioa-medium-icon';

    $settings = CraftAllinoneaccessibility::getInstance()->getSettings();

    $config = [
      "license_key" => $settings->license_key,
      "color" => $settings->color,
      "position" => $settings->position,
      "icon_type" => $settings->icon_type,
      "icon_size" => $settings->icon_size,
    ];

    if ($settings !== '') {
      $license_key = isset($settings->license_key) ? $settings->license_key : "";
      $color_code = isset($settings->color) ? $settings->color : "#600b96";
      $position = isset($settings->position) ? $settings->position : "bottom_right";
      $icon_type = isset($settings->icon_type) ? $settings->icon_type : "aioa-icon-type-1";
      $icon_size = isset($settings->icon_size) ? $settings->icon_size : "aioa-medium-icon";
    }

    // Add JS in Front side
    

    $apiUrl = "https://ipapi.co/json/";

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($response)) {
        $visitorData = json_decode($response, true);
    } else {
        $visitorData = ['in_eu' => false];
    }

    /**
     * Normalize EU flag
     */
    $rawInEU = $visitorData['in_eu'] ?? false;
    $inEU = filter_var($rawInEU, FILTER_VALIDATE_BOOLEAN);
    $is_eu = $inEU ? 0 : 1; // EU = 0, Non-EU = 1

    /**
     * Decide script URL based on EU status
     */
    if ($is_eu === 0) {
        // EU script
        $customJsUrl = "https://eu.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js"
            . "?colorcode=" . urlencode($color_code)
            . "&token=" . urlencode($license_key)
            . "&position=" . urlencode($position);
    } else {
        // Non-EU script
        $customJsUrl = "https://www.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js"
            . "?colorcode=" . urlencode($color_code)
            . "&token=" . urlencode($license_key)
            . "&position=" . urlencode($position . "." . $icon_type . "." . $icon_size);
    }

    // $currentUrl = Craft::$app->getRequest()->getAbsoluteUrl();

    $request = \Craft::$app->getRequest(); // initialize the variable first

    if ($request instanceof \craft\web\Request) {
      $currentUrl = Craft::$app->getRequest()->getAbsoluteUrl();
    } else {
      $currentUrl = null; // or some fallback
    }

    $adminBaseUrl = UrlHelper::cpUrl();
    $isInAdminPanel = Craft::$app->getRequest()->getIsCpRequest();

    // Check if the current URL starts with the admin base URL
    $isAdminSection = strpos($currentUrl, $adminBaseUrl) === 0;

    if (!$isAdminSection && !$isInAdminPanel) {
      // Register the JavaScript file
      Craft::$app->getView()->registerJsFile($customJsUrl, ['position' => View::POS_END, 'id' => $scriptId, 'async' => true]);
    }
  }
}
