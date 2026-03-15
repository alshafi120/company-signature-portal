<?php
/**
 * Language Handler
 */

/**
 * Get current language
 */
function getCurrentLang(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

/**
 * Load language file
 */
function loadLang(string $lang): array
{
    $file = __DIR__ . '/../lang/' . $lang . '.php';
    if (file_exists($file)) {
        return require $file;
    }
    return require __DIR__ . '/../lang/ar.php';
}

/**
 * Get translation
 */
function __($key): string
{
    global $lang;
    return $lang[$key] ?? $key;
}

/**
 * Get text direction
 */
function getDirection(): string
{
    global $lang;
    return $lang['dir'] ?? 'rtl';
}

// Initialize language
$currentLang = getCurrentLang();
$lang = loadLang($currentLang);
