<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

$str_error_message = '';

// Проверка что сайт находится в корне
/*if ( PHP_SAPI !== 'cli' ) {
    if ( str_replace( '\\', '/', $_SERVER['DOCUMENT_ROOT'] ) != str_replace( '\\', '/', ROOT_DIR ) ) {
        $str_error_message .= '<p><b>$_SERVER[\'DOCUMENT_ROOT\']</b> variable must be set to the document root directory under which the current script is executing.</p>'."\n";
    }
}*/

// Проверка параметра short_open_tag
if (!ini_get('short_open_tag')) {
    $str_error_message .= '<p><b>short_open_tag</b> value must be turned on in you <b>php.ini</b> or <b>.htaccess</b> file.</p>' . "\n";
}

// socket
if (!function_exists('fsockopen')) {
    $str_error_message .= '<p><b>Socket functions</b> extension not installed</p>' . "\n";
}

// Regex functions
if (!function_exists('preg_match')) {
    $str_error_message .= '<p><b>Regex functions</b> extension not installed</p>' . "\n";
}

// Zlib
if (!extension_loaded('zlib') || !function_exists('gzcompress')) {
    $str_error_message .= '<p><b>Zlib</b> extension not installed</p>' . "\n";
}

// GD lib
if (!function_exists('imagecreate')) {
    $str_error_message .= '<p><b>GD lib</b> extension not installed</p>' . "\n";
}

// Free type
if (!function_exists('imagettftext')) {
    $str_error_message .= '<p><b>Free Type</b> extension not installed</p>' . "\n";
}

// openSSL
if (!function_exists('openssl_encrypt')) {
    $str_error_message .= '<p><b>openSSL</b> extension not installed</p>' . "\n";
}

// Hash
if (!function_exists('hash')) {
    $str_error_message .= '<p><b>Hash</b> extension not installed</p>' . "\n";
}

// XML
if (!function_exists('xml_parser_create')) {
    $str_error_message .= '<p><b>XML</b> extension not installed</p>' . "\n";
}

// JSON
if (!function_exists('json_encode')) {
    $str_error_message .= '<p><b>JSON</b> extension not installed</p>' . "\n";
}

// cURL
if (!function_exists('curl_init')) {
    $str_error_message .= '<p><b>cURL</b> extension not installed</p>' . "\n";
}

// POD
if (!class_exists('mysqli')) {
    $str_error_message .= '<p><b>MySQLi</b> extension not installed</p>' . "\n";
}

// PDO
if (!class_exists('PDO')) {
    $str_error_message .= '<p><b>PDO</b> extension not installed</p>' . "\n";
}

// Минимальные требования PHP
if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    $str_error_message .= '<p>Your host needs to use<b> PHP ';
    $str_error_message .= '7.2.0';
    $str_error_message .= '</b> or higher to run this version of Reagordi!</p>' . "\n";
}

// Если есть ошшибки, то выводим
if (strlen($str_error_message) > 0) {
    if (PHP_SAPI === 'cli') {
        echo strip_tags($str_error_message);
        die('Please modify the server\'s configuration or contact administrator of your hosting.' . "\n");
    }
    header('HTTP/1.1 500 Internal Server Error');
    echo '<!DOCTYPE html><html lang="en"><head><meta http-equiv="x-ua-compatible" content="ie=edge" /><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title>Error server</title><style type="text/css">body{margin:0;padding:0;direction:ltr;font-size:18px;font-family:-apple-system,BlinkMacSystemFont,Roboto,Open Sans,Helvetica Neue,sans-serif;line-height:1.154;font-weight:400;-webkit-font-smoothing:subpixel-antialiased;-moz-osx-font-smoothing:auto}p{color:#ff0000}</style></head><body>' . str_replace("\n", '', $str_error_message) . '<p>Please modify the server\'s configuration or contact administrator of your hosting.</p></body></html>';
    unset($str_error_message);
    die();
}
unset($str_error_message);
