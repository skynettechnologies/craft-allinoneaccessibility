<?php
/**
 * ---------------------------------------------------------------------------
 * All in One Accessibility - view helpers
 * ---------------------------------------------------------------------------
 * Small functions used by index.php and the templates in views/.
 */

/**
 * Escape a value for safe HTML output.
 */
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Clean the query string sent by the platform.
 *
 * Platforms that cannot provide the admin details (for example headless
 * setups) may send raw placeholders such as "{email}". Those are treated as
 * missing values, so the website is registered with no-reply@<website>.
 */
function aioa_request(array $query): array
{
    return array_map(function ($value) {
        if (!is_string($value) || preg_match('/^\{.*\}$/', trim($value))) {
            return '';
        }
        return trim(str_replace(['{', '}'], '', $value));
    }, $query);
}

/**
 * URL of a file in /assets with a version stamp (file modified time), so
 * browsers always load the latest CSS / JS after an update.
 */
function asset(string $path): string
{
    $file = AIOA_ROOT . '/assets/' . $path;
    $version = is_file($file) ? filemtime($file) : 1;
    return 'assets/' . $path . '?v=' . $version;
}

/**
 * Element id slug: "top_left" => "top-left", "aioa-extra-small-icon" => "extra-small".
 */
function aioa_slug(string $value): string
{
    return str_replace(['aioa-', '-icon', '_'], ['', '', '-'], $value);
}
