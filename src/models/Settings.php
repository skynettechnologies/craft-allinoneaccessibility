<?php

namespace skynettechnologies\craftAllinoneaccessibility\models;
use craft\base\Model;
use Craft;

class Settings extends Model
{
  public $license_key = "";
  public $color = "#600b96";
  public $position = "bottom_right";
  public $icon_type = "aioa-icon-type-1";
  public $icon_size = "aioa-medium-icon";

  public function rules()
  {
      return [
        // these attributes are required
        [['color', 'position', 'icon_type', 'icon_size'], 'required', 'message' => 'Please complete all required fields']
      ];
  }
  
}


