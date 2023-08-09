<?php

namespace skynettechnologies\craftallinoneaccessibility\controllers;

use \skynettechnologies\craftallinoneaccessibility\CraftAllinoneaccessibility;
use craft\web\Controller;

class ConfigController extends Controller
{
  protected array|bool|int $allowAnonymous = ["config"];
  
  public function actionConfig()
  {
    $settings = CraftAllinoneaccessibility::getInstance()->getSettings();
    
    $config = [
        "license_key" => $settings->license_key,
        "color" => $settings->color,
        "position" => $settings->position,
        "icon_type" => $settings->icon_type,
        "icon_size" => $settings->icon_size,
    ];

    return $this->asJson($config);
  }
}
