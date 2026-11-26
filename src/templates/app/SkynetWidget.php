<?php
/**
 * ---------------------------------------------------------------------------
 * All in One Accessibility - API service
 * ---------------------------------------------------------------------------
 * Server side calls to the Skynet Technologies API. Used by route.php, which
 * the page calls with fetch(). Every public method returns data that
 * route.php sends back as JSON.
 *
 * Loading never registers: fetchWidgetSettings answers {"not_registered": true}
 * for a new website. The page then calls registerDomain once, with the EU flag
 * (no_required_eu, from ipapi.co in the browser) and the admin's name and email
 * from the platform, or the website and no-reply@<website>.
 *
 * Saving the widget settings is done by the browser directly
 * (assets/js/save.js -> widget-setting-update-platform).
 */

require_once __DIR__ . '/config.php';

class SkynetWidget
{
    // Widget position for mobile (Paid Features): missing from widget-settings, see withMobileFields()
    private const MOBILE_FIELDS = [
        'is_mobile_position',
        'is_widget_custom_position_mobile',
        'widget_position_mobile',
        'widget_position_top_mobile',
        'widget_position_bottom_mobile',
        'widget_position_left_mobile',
        'widget_position_right_mobile',
        'widget_position_vertical_center_offset_mobile',
        'widget_position_horizontal_center_offset_mobile',
    ];

    /* =======================================================================
     * Validation helpers
     * ===================================================================== */

    /**
     * Normalize user input such as "https://www.Example.com/shop" to "www.example.com".
     */
    public static function normalizeDomain(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('#^[a-z][a-z0-9+.-]*://#', '', $value); // scheme
        $value = preg_replace('#[/?\#].*$#', '', $value);              // path, query, hash
        $value = preg_replace('#:\d+$#', '', $value);                  // port
        return trim($value, '.');
    }

    /**
     * True for a website host: a domain name such as "example.com", or "localhost"
     * (platforms that run on a local machine). IP addresses such as "127.0.0.1" are not
     * accepted. Skynet keeps one registration per host, without the port.
     */
    public static function isValidDomain(string $domain): bool
    {
        return (bool) preg_match('/^(?:localhost|(?=.{3,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63})$/D', $domain);
    }

    /* =======================================================================
     * Actions (called from route.php)
     * ===================================================================== */

    /**
     * Load the widget settings of a domain. Never registers anything: a domain that
     * is not registered yet answers {"not_registered": true}, and the page then calls
     * registerDomain (once).
     *
     * @param array $req ['url' => domain]
     * @return string    JSON: settings | {"not_registered": true} | {"error": "..."}
     */
    public static function fetchWidgetSettings($req)
    {
        $url = self::normalizeDomain($req['url'] ?? '');

        if (!self::isValidDomain($url)) {
            return json_encode(['error' => 'A valid domain is required, for example example.com or localhost.']);
        }

        $data = self::getSettingsData($url);

        if ($data === false) {
            return json_encode(['error' => 'Unable to load widget settings. Please try again.']);
        }

        if ($data === null) {
            return json_encode(['not_registered' => true]);
        }

        return json_encode($data);
    }

    /**
     * Register the free widget for a domain (add-user-domain), then wait for its settings.
     * The page calls it only after fetchWidgetSettings answered not_registered, and at
     * most once per website (assets/js/app.js). widget-settings is checked again first,
     * so a website that is already registered is never registered twice, and nothing is
     * registered when the API can't be reached.
     *
     * @param array $req ['url' => domain, 'username' => admin name, 'email' => admin email,
     *                    'no_required_eu' => '0' EU (ipapi.co in_eu true) | '1' not EU (anything else)]
     * @return string    JSON: settings | {"error": "...", "pending": true} | {"error": "..."}
     */
    public static function registerDomain($req)
    {
        $url = self::normalizeDomain($req['url'] ?? '');

        if (!self::isValidDomain($url)) {
            return json_encode(['error' => 'A valid domain is required, for example example.com or localhost.']);
        }

        $data = self::getSettingsData($url);

        // API not reachable: never register a website that may already be registered
        if ($data === false) {
            return json_encode(['error' => 'Unable to load widget settings. Please try again.']);
        }

        if ($data === null) {
            $noRequiredEu = ($req['no_required_eu'] ?? '') === '0' ? '0' : '1';
            self::register($url, $req['username'] ?? '', $req['email'] ?? '', $noRequiredEu);
            $data = self::waitForSettings($url);
        }

        // Registered, but the settings did not appear yet: the page waits instead of registering again
        if ($data === null) {
            return json_encode([
                'error'   => 'Your website was registered, but its settings are not ready yet. Please try again in a moment.',
                'pending' => true,
            ]);
        }

        return json_encode($data);
    }

    /**
     * Dashboard sign-in link of a paid plan: "manage_domain" from check-website.
     * The page loads it once the plan is known as paid, so the "Dashboard" button
     * opens it at once. Only the link is passed on; api_key and the rest stay here.
     * An unknown website gets an empty manage_domain.
     *
     * @param array $req ['url' => domain]
     * @return string    JSON: {"link": "https://..."} | {"error": "..."}
     */
    public static function getDashboardLink($req)
    {
        $url = self::normalizeDomain($req['url'] ?? '');
        if (!self::isValidDomain($url)) {
            return json_encode(['error' => 'A valid domain is required, for example example.com or localhost.']);
        }

        $res = self::post(AIOA_CHECK_WEBSITE_API, ['domain' => $url]);
        if ($res === null) {
            return json_encode(['error' => 'Unable to load the dashboard link right now.']);
        }

        $link = (string) ($res['manage_domain'] ?? '');
        if (!preg_match('#^https://#i', $link)) {
            return json_encode(['error' => 'No dashboard link was found for ' . $url . '.']);
        }

        return json_encode(['link' => $link]);
    }

    /**
     * Accessibility Score tab: the scan results of a domain, trimmed to what the score card shows.
     *
     * No finished results yet (the page shows "Scanning in progress"):
     *   not scanned yet  {"Data": [], "status": 404, "msg": "Domain details not found or package information is unavailable."}
     *   scan running     {"Data": {...partial...}, "status": 201, "msg": "Domain scan is currently in progress. ..."}
     *                    The partial Data has a running score but no summary, page counts or last scan.
     *
     * @param array $req ['url' => domain]
     * @return string    JSON: score summary | {"no_scan": true, "in_progress": bool, "message": "..."} | {"error": "..."}
     */
    public static function getScanDetails($req)
    {
        $url = self::normalizeDomain($req['url'] ?? '');
        if (!self::isValidDomain($url)) {
            return json_encode(['error' => 'Enter a valid domain, for example example.com or localhost']);
        }

        $res = self::post(AIOA_SCAN_API, ['lang_code' => AIOA_SCAN_LANG, 'website' => base64_encode($url)]);
        if ($res === null) {
            return json_encode(['error' => 'Unable to load the accessibility score right now. Please try again.']);
        }

        $data = $res['Data'] ?? null;
        $inProgress = (int) ($res['status'] ?? 0) === 201;
        if ($inProgress || empty($data) || !is_array($data)) {
            return json_encode(['no_scan' => true, 'in_progress' => $inProgress, 'message' => (string) ($res['msg'] ?? '')]);
        }

        return json_encode(self::scoreSummary($data));
    }

    /* =======================================================================
     * Internal
     * ===================================================================== */

    /**
     * Register the free widget for a domain (add-user-domain).
     *   name            the admin's name, or the website when the platform sent none
     *   email           the admin's email, or no-reply@<website> (without "www.") when the platform sent none
     *   no_required_eu  '0' the admin is in the EU, '1' not in the EU (ipapi.co in_eu, checked by the browser)
     *
     * @return array|null Raw API response
     */
    private static function register(string $url, string $name, string $email, string $noRequiredEu): ?array
    {
        $name = trim($name);
        $email = trim($email);
        $now = new DateTime('now', new DateTimeZone('UTC'));

        $postFields = [
            'name'            => $name !== '' ? $name : $url,
            'email'           => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'no-reply@' . preg_replace('/^www\./', '', $url),
            'company_name'    => '',
            'website'         => base64_encode($url),
            'package_type'    => 'free-widget',
            'start_date'      => $now->format('Y-m-d\TH:i:sO'),
            'end_date'        => '',
            'price'           => '',
            'discount_price'  => '0',
            'platform'        => AIOA_PLATFORM,
            'api_key'         => '',
            'is_trial_period' => '',
            'is_free_widget'  => '1',
            'no_required_eu'  => $noRequiredEu,
            'bill_address'    => '',
            'country'         => '',
            'state'           => '',
            'city'            => '',
            'post_code'       => '',
            'transaction_id'  => '',
            'subscr_id'       => '',
            'payment_source'  => '',
        ];

        return self::apiPost('add-user-domain', $postFields);
    }

    /**
     * Settings of a registered domain, null when the domain is not registered,
     * or false when the API could not be reached or did not answer with JSON.
     *
     * @return array|false|null
     */
    private static function getSettingsData(string $url)
    {
        $res = self::apiPost('widget-settings', ['website_url' => $url]);
        if ($res === null) {
            return false;
        }
        if (empty($res['Data']) || !is_array($res['Data'])) {
            return null;
        }
        return self::withMobileFields($url, $res['Data']);
    }

    /**
     * Add the widget position for mobile (Paid Features) to the settings of a paid plan.
     * widget-settings leaves these fields out; the widget's own settings endpoint
     * (AIOA_WIDGET_SETTINGS_API) returns them. Fields widget-settings already sent win.
     * When the call fails the settings are returned as they are and the page uses its defaults.
     */
    private static function withMobileFields(string $url, array $data): array
    {
        // Same rule as the page: anything but is_free_widget = 1 is a paid plan
        if ((int) ($data['is_free_widget'] ?? 0) === 1) {
            return $data;
        }

        $res = self::post(AIOA_WIDGET_SETTINGS_API, ['website_url' => $url]);
        $widget = $res['Data'] ?? null;
        if (!is_array($widget)) {
            return $data;
        }

        foreach (self::MOBILE_FIELDS as $field) {
            if (!array_key_exists($field, $data) && array_key_exists($field, $widget)) {
                $data[$field] = $widget[$field];
            }
        }
        return $data;
    }

    /**
     * Poll for the settings of a domain that was just registered (the API needs a moment to sync).
     */
    private static function waitForSettings(string $url, int $attempts = 3): ?array
    {
        for ($i = 0; $i < $attempts; $i++) {
            sleep(1);
            $data = self::getSettingsData($url);
            if (is_array($data)) {
                return $data;
            }
        }
        return null;
    }

    /**
     * Score card values from the scanner's "Data":
     *   score       success_percentage, rounded, 0 - 99 (100 is shown as 99), labeled with AIOA_SCORE_LEVELS
     *   violations  fixed / total violations (summary)
     *   alt_text    fixed / total of the alt text checks (language_key contains "alt")
     *   pages       scanned / selected pages
     * A check's total is its fixed plus its still open (total_error) violations.
     */
    private static function scoreSummary(array $data): array
    {
        // Up to 99, never 100: a scanner score of 100 is shown as 99
        $score = max(0, min(99, (int) round((float) ($data['success_percentage'] ?? 0))));

        $level = AIOA_SCORE_LEVELS[count(AIOA_SCORE_LEVELS) - 1];
        foreach (AIOA_SCORE_LEVELS as $candidate) {
            if ($score >= $candidate[0]) {
                $level = $candidate;
                break;
            }
        }

        // Every check: fixed by the widget, or still waiting for a manual fix
        $groups = $data['acessibility_voilation'] ?? []; // API spelling
        $checks = array_merge($groups['voilation_remediated'] ?? [], $groups['require_manual_action'] ?? []);

        $fixed = 0;
        $total = 0;
        $altFixed = 0;
        $altTotal = 0;
        foreach ($checks as $check) {
            $checkFixed = (int) ($check['total_fixed'] ?? 0);
            $checkTotal = $checkFixed + (int) ($check['total_error'] ?? 0);
            $fixed += $checkFixed;
            $total += $checkTotal;
            if (preg_match('/(^|_)alt(_|$)/', (string) ($check['language_key'] ?? ''))) {
                $altFixed += $checkFixed;
                $altTotal += $checkTotal;
            }
        }

        // The summary wins when the API sends it
        if (isset($data['summary']['total_violations'], $data['summary']['total_fixed'])) {
            $fixed = (int) $data['summary']['total_fixed'];
            $total = (int) $data['summary']['total_violations'];
        }

        $reportUrl = (string) ($data['pdf_report_url'] ?? '');

        return [
            'domain'     => (string) ($data['domain'] ?? ''),
            'score'      => $score,
            'level'      => $level[1],
            'status'     => $level[2],
            'violations' => self::ratio($fixed, $total),
            'alt_text'   => self::ratio($altFixed, $altTotal),
            'pages'      => [
                'scanned' => (int) ($data['total_scan_pages'] ?? 0),
                'total'   => (int) ($data['total_selected_pages'] ?? $data['total_pages'] ?? 0),
            ],
            'last_scan'  => (string) ($data['last_scan'] ?? ''),
            'report_url' => preg_match('#^https://#i', $reportUrl) ? $reportUrl : '',
        ];
    }

    /**
     * ['fixed' => 38, 'total' => 44, 'percentage' => 86.36]
     */
    private static function ratio(int $fixed, int $total): array
    {
        return [
            'fixed'      => $fixed,
            'total'      => $total,
            'percentage' => $total > 0 ? round($fixed / $total * 100, 2) : 0,
        ];
    }

    /**
     * POST form fields to a Skynet API endpoint (relative to AIOA_API_BASE).
     */
    private static function apiPost(string $endpoint, array $fields): ?array
    {
        return self::post(AIOA_API_BASE . $endpoint, $fields);
    }

    /**
     * POST form fields to a URL and decode the JSON answer.
     *
     * @return array|null Decoded JSON response (also for 4xx answers with a JSON body), or null on a network / JSON error
     */
    private static function post(string $url, array $fields): ?array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_TIMEOUT        => AIOA_HTTP_TIMEOUT,
            // The API firewall rejects requests without a User-Agent (HTTP 406)
            CURLOPT_USERAGENT      => AIOA_USER_AGENT,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            // TLS certificates are verified (curl default). Keep it that way.
        ]);

        // No curl_close(): the handle is freed when this function returns, and
        // curl_close() is deprecated since PHP 8.5 (it has had no effect since 8.0).
        $response = curl_exec($curl);

        if ($response === false) {
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }
}
