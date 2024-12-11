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
    public $schemaVersion = '1.0.3';
    public $hasCpSettings = true;
    public $hasCpSection = false;

  public function init()
  {
    parent::init();
    self::$plugin = $this;

    \Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'registerCustomJs']);
  }

  protected function createSettingsModel()
  {
    return new Settings();
  }

  protected function settingsHtml()
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
    $domain = parse_url($siteurl, PHP_URL_HOST);
    
    $data['domain'] = $domain;
    if (Craft::$app->getUser()->getIdentity()) {
      $currentUser = Craft::$app->getUser()->getIdentity();
  
      // Get user details
     $data['username']= $currentUser->username; // User's username
     $data['email'] = $currentUser->email;       // User's email
      $id = $currentUser->id;             // User's ID

  } else {
      echo "No user is currently logged in.";
  }
    
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
      $color_code = '#600b96';
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
      $customJsUrl = "https://www.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js?colorcode=".$color_code."&token=".$license_key."&position=".$position.".".$icon_type.".".$icon_size." ";
      
      // Get the current URL path
      $currentUrl = Craft::$app->getRequest()->getAbsoluteUrl();
      $adminBaseUrl = UrlHelper::cpUrl();
      $isInAdminPanel = Craft::$app->getRequest()->getIsCpRequest();
      
      // Check if the current URL starts with the admin base URL
      $isAdminSection = strpos($currentUrl, $adminBaseUrl) === 0;
      
      if (!$isAdminSection && !$isInAdminPanel) {
        // Register the JavaScript file
        Craft::$app->getView()->registerJsFile($customJsUrl, ['position' => View::POS_END,'id' => $scriptId,'async'=>true]);
      }
  }
}
