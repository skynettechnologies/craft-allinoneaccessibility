<?php

namespace skynettechnologies\craftallinoneaccessibility\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class AdminSettingsAsset extends AssetBundle
{
    public function init()
    {
        // Set the path where your resources are located
        $this->sourcePath = "@skynettechnologies/craftallinoneaccessibility/resources";

        // Define the CSS files to be included
        $this->css = [
            'css/aiostyle.css',
            'css/bootstraps.min.css',
        ];

        // Define the JavaScript files to be included
        $this->js = [
            'js/aiojquery.js',
        ];

        // Define the dependencies
        $this->depends = [
            CpAsset::class, // Ensures Craft's Control Panel styles and scripts are loaded
        ];

        parent::init();
    }
}
