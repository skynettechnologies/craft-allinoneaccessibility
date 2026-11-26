<?php

namespace skynettechnologies\craftallinoneaccessibility\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class AdminSettingsAsset extends AssetBundle
{
    public function init(): void
    {
        // Plugin resources directory
        $this->sourcePath = '@skynettechnologies/craftallinoneaccessibility/resources';

        // CSS files
        $this->css = [
         
            'css/tokens.css',
            'css/base.css',
            'css/components.css',
            'css/banners.css',
            'css/settings.css',
            'css/tabs.css',
            'css/paid.css',
            'css/score.css',
            'css/responsive.css',
        ];

        // JavaScript files in dependency order
        $this->js = [
            'js/aiojquery.js',

            'js/core.js',
            'js/ui.js',
            'js/theme.js',
            'js/tabs.js',
            'js/plan.js',
            'js/paid-features.js',
            'js/score.js',
            'js/settings-form.js',
            'js/save.js',
            'js/dashboard.js',
            'js/app.js',
            'js/theme-init.js',
        ];

        // Load JavaScript after HTML parsing
        $this->jsOptions = [
            'defer' => true,
        ];

        // Craft Control Panel dependencies
        $this->depends = [
            CpAsset::class,
        ];

        parent::init();
    }
}
