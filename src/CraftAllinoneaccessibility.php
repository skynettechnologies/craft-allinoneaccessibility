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

    public string $schemaVersion = "2.0.0";

    public bool $hasCpSettings = true;

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        Event::on(
            View::class,

            View::EVENT_END_BODY,

            function () {
                if (Craft::$app->getRequest()->getIsSiteRequest()) {
                    $this->registerCustomJs();
                }
            }
        );
    }

    public function afterInstall(): void
    {
        parent::afterInstall();
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        $view = Craft::$app->getView();

        $bundle = \skynettechnologies\craftallinoneaccessibility\assetbundles\AdminSettingsAsset::register(
            $view
        );

        $assetBaseUrl = $bundle->baseUrl;

        // Read parameters passed to the Craft settings page.
        $request = Craft::$app->getRequest();
            $user = Craft::$app->getUser()->getIdentity();

            $siteUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();
            $domain = parse_url($siteUrl, PHP_URL_HOST) ?: $request->getHostName();

            $page = [
                'domain' => $request->getQueryParam('url') ?: $domain,
                'name' => $request->getQueryParam('username') ?: ($user?->username ?? ''),
                'email' => $request->getQueryParam('email') ?: ($user?->email ?? ''),
                'theme' => $request->getQueryParam('theme') ?: '',
            ];

        $phpFile = __DIR__ . '/templates/setting.php';

        if (!file_exists($phpFile)) {
            return '';
        }

        ob_start();

        try {
            include $phpFile;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Generic CURL
     */

    private function callApi(
        string $url,
        $post = null,
        bool $json = false
         ): array {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CONNECTTIMEOUT => 3,

            CURLOPT_TIMEOUT => 5,

            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        if ($post !== null) {
            curl_setopt($ch, CURLOPT_POST, true);

            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                $json ? json_encode($post) : $post
            );

            if ($json) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                ]);
            }
        }

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response, true) ?: [];
    }

    /**
     * Detect EU
     */

    private function getNoRequiredEu(): int
    {
        $data = $this->callApi("https://ipapi.co/json/");

        if (isset($data["in_eu"])) {
            return $data["in_eu"] ? 0 : 1;
        }

        $ip =
            $_SERVER["HTTP_CF_CONNECTING_IP"] ??
            ($_SERVER["HTTP_X_FORWARDED_FOR"] ?? $_SERVER["REMOTE_ADDR"]);

        $ip = trim(explode(",", $ip)[0]);

        $data = $this->callApi("https://ipwho.is/" . urlencode($ip));

        return !empty($data["is_eu"]) ? 0 : 1;
    }

    /**
     * Load widget
     */

    private function registerCustomJs(): void
    {
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return;
        }

        $settings = $this->getSettings();

        $domain = Craft::$app->getRequest()->getHostName();

        $cache = Craft::$app->getCache();

        $cacheKey = "aioa_widget_" . md5($domain);

        $noRequiredEu = $cache->get($cacheKey);

        if ($noRequiredEu === false) {
            $response = $this->callApi(
                "https://ada.skynettechnologies.us/api/widget-settings",

                ["website_url" => $domain]
            );

            $noRequiredEu =
                $response["Data"]["no_required_eu"] ?? $this->getNoRequiredEu();

            $cache->set($cacheKey, $noRequiredEu, 86400);
        }

        if ($noRequiredEu == 0) {
            $script = sprintf(
                "https://eu.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js?colorcode=%s&token=%s&position=%s",

                urlencode($settings->color),

                urlencode($settings->license_key),

                urlencode($settings->position)
            );
        } else {
            $script = sprintf(
                "https://www.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js?colorcode=%s&token=%s&position=%s",

                urlencode($settings->color),

                urlencode($settings->license_key),

                urlencode(
                    $settings->position .
                        "." .
                        $settings->icon_type .
                        "." .
                        $settings->icon_size
                )
            );
        }

        Craft::$app->getView()->registerJsFile($script, [
            "id" => "aioa-adawidget",

            "position" => View::POS_END,

            "async" => true,
        ]);
    }
}
