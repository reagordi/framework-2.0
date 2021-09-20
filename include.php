<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

use reagordi\framework\loader\Loader;

require_once __DIR__ . '/include/defined.php';

umask(~(REAGORDI_FILE_PERMISSIONS | REAGORDI_DIR_PERMISSIONS) & 0777);

ob_start();
ob_implicit_flush(false);

if (REAGORDI_ENV == 'dev' || REAGORDI_ENV == 'test') {
    error_reporting(E_ALL);
    ini_set('html_errors', true);
    ini_set('display_errors', true);
    ini_set('display_startup_errors', true);
}

if (REAGORDI_ENV == 'dev') {
    $whoops = new \Whoops\Run();
    if (REAGORDI_DEV_VIEW == 'html') $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    if (REAGORDI_DEV_VIEW == 'json') $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
    if (REAGORDI_DEV_VIEW == 'xml') $whoops->pushHandler(new \Whoops\Handler\XmlResponseHandler());
    if (REAGORDI_DEV_VIEW == 'text') $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
    $whoops->register();
    unset($whoops);
}

require_once __DIR__ . '/include/check.php';
require_once __DIR__ . '/src/Loader/Loader.php';
require_once __DIR__ . '/src/Reagordi.php';

\spl_autoload_register([Loader::class, 'autoLoad']);

Loader::registerNamespace('reagordi\\framework', __DIR__ . '/src');

if (REAGORDI_DEBUG_LOG === true) {
    ini_set('log_errors', true);
    \Reagordi\Framework\IO\Directory::createDirectory(DATA_DIR . '/logs/');
    error_log(DATA_DIR . '/logs/php_error.log');
}

require_once __DIR__ . '/include/functions.php';

$domain_cookie = explode(".", clean_url($_SERVER['HTTP_HOST']));
$domain_cookie_count = count($domain_cookie);
$domain_allow_count = -2;
if ($domain_cookie_count > 2) {
    if (in_array($domain_cookie[$domain_cookie_count - 2], array('com', 'net', 'org'))) $domain_allow_count = -3;
    if ($domain_cookie[$domain_cookie_count - 1] == 'ua') $domain_allow_count = -3;
    $domain_cookie = array_slice($domain_cookie, $domain_allow_count);
}
$domain_cookie = "." . implode(".", $domain_cookie);

/**
 * Префикс Cookie
 *
 * @var string
 */
defined('RG_COOKIE_PREF') or define('RG_COOKIE_PREF', '_rg_' . md5($domain_cookie));

/**
 * Название Cookie языка
 *
 * @var string
 */
defined('RG_COOKIE_LANG') or define('RG_COOKIE_LANG', 'rglang' . RG_COOKIE_PREF);

/**
 * Название Cookie сессии
 *
 * @var string
 */
defined('RG_COOKIE_SID') or define('RG_COOKIE_SID', 'rgsid' . RG_COOKIE_PREF);

Reagordi::getInstance();

if (Reagordi::$app->getConfig()->get('components', 'request', 'multiCookieDomain')) {
    if (ip2long($_SERVER['HTTP_HOST']) == -1 or ip2long($_SERVER['HTTP_HOST']) === false) define('DOMAIN', $domain_cookie);
    else define('DOMAIN', null);
} else define('DOMAIN', null);
