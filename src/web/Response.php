<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\web;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\Exception\BadRouteException;
use reagordi\framework\web\CookieCollection;
use reagordi\framework\base\Request;
use reagordi\framework\base\Session;
use reagordi\framework\base\Server;
use Phroute\Phroute\Dispatcher;
use Reagordi;

class Response
{
    /**
     * Формат html
     *
     * @var string
     */
    const FORMAT_HTML = 'text/html';

    /**
     * Формат JSON
     *
     * @var string
     */
    const FORMAT_JSON = 'application/json';

    /**
     * Формат JavaScript
     *
     * @var string
     */
    const FORMAT_JSONP = 'application/javascript';

    /**
     * Формат RAW
     *
     * @var string
     */
    const FORMAT_RAW = 'pplication/octet-stream';

    /**
     * Формат XML
     *
     * @var string
     */
    const FORMAT_XML = 'application/xml';

    /**
     * Формат TEXT
     *
     * @var string
     */
    const FORMAT_TEXT = 'text/plain';

    /**
     * Экземпляр класса Reagordi
     *
     * @var Response
     */
    public static $app;

    /**
     * Кодировка сайта
     *
     * @var string
     */
    public $charset;

    /**
     * Формат ответа
     *
     * @var string
     */
    public $format;

    /**
     * @var int the HTTP status code to send with the response.
     */
    private $_statusCode = 200;
    private $_statusText = 'Ok';

    /**
     * Содержание ответа
     *
     * @var string
     */
    public $content = '';

    /**
     * @var array
     */
    private $_headers = [];

    /**
     * @var string the version of the HTTP protocol to use. If not set, it will be determined via `$_SERVER['SERVER_PROTOCOL']`,
     * or '1.1' if that is not available.
     */
    public $version;

    /**
     * Список кодов состояния HTTP и соответствующие тексты
     *
     * @var string[]
     */
    public static $httpStatuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * Reagordi constructor.
     */
    private function __construct()
    {
        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') {
            $this->version = '1.0';
        } else {
            $this->version = '1.1';
        }

        $charset = Reagordi::$app->getConfig()->get('components', 'response', 'charset');
        $this->charset = $charset ? $charset: 'utf-8';

        $format = Reagordi::$app->getConfig()->get('components', 'response', 'format');
        $this->format = $format ? $format: self::FORMAT_HTML;
    }

    /**
     * Возвращает экземпляр класса Request
     *
     * @return reagordi\framework\base\Request
     */
    public function getRequest()
    {
        return Request::getInstance();
    }

    /**
     * Возвращает экземпляр класса Session
     *
     * @return reagordi\framework\base\Session
     */
    public function getSession()
    {
        return Session::getInstance();
    }

    /**
     * @return \reagordi\framework\web\CookieCollection
     */
    public function getCookie()
    {
        return CookieCollection::getInstance();
    }
    
    /**
     * Sets the response status code.
     * This method will set the corresponding status text if `$text` is null.
     * @param int $value the status code
     * @param string $text the status text. If not set, it will be set automatically based on the status code.
     * @return $this the response object itself
     * @throws InvalidArgumentException if the status code is invalid.
     */
    public function setStatusCode($value, $text = null)
    {
        if ($value === null) {
            $value = 200;
        }
        $this->_statusCode = (int)$value;
        if ($value < 100 || $value >= 600) {
            throw new \Exception('The HTTP status code is invalid: ' . $value);
        }
        if ($text === null) {
            $this->_statusText = isset(static::$httpStatuses[$this->_statusCode]) ? static::$httpStatuses[$this->_statusCode] : '';
        } else {
            $this->_statusText = $text;
        }

        return $this;
    }

    /**
     * Задание загаловка
     *
     * @param string $key Задание ключа
     * @param string $value Задание значения
     */
    public function setHeader(string $value)
    {
        if (in_array($value)) {
            $this->_headers[array_search($value)] = $value;
        } else {
            $this->_headers[] = $value;
        }
    }

    /**
     * Отправка ответа пользователю
     */
    public function sendContent()
    {
        // Установка внутренней кодировки в UTF-8
        !function_exists('mb_internal_encoding') or mb_internal_encoding($this->charset);
        header('Content-type: ' . $this->format . '; charset=' . $this->charset);
        foreach ($this->_headers as $header) {
            header($header);
        }
        $dispatcher = new Dispatcher(Reagordi::$app->getRouter()->getData());

        try {
            header('HTTP/' . $this->version . ' ' . $this->_statusCode . ' ' . $this->_statusText);
            $response = $dispatcher->dispatch(
                Reagordi::$app->getResponse()->getServer()->getRequestMethod(),
                parse_url(Reagordi::$app->getResponse()->getServer()->getRequestUri(), PHP_URL_PATH)
            );
            $this->generateResponse($response);
        } catch (HttpMethodNotAllowedException $exception) {
            header('HTTP/' . $this->version . ' 405 Method not allowed');
            if (
                self::FORMAT_JSON == $this->format ||
                self::FORMAT_JSONP == $this->format ||
                self::FORMAT_XML == $this->format
            ) {
                $this->generateResponse(array(
                    'status' => false,
                    'error' => [
                        'code' => 405,
                        'msg' => 'Method not allowed'
                    ]
                ));
            } else {
                $class_name = Reagordi::$app->getConfig()->get('components', 'errorHandler', 'class');
                $class_name = $class_name ? $class_name: 'reagordi\framework\web\ErrorHandler';

                $action = Reagordi::$app->getConfig()->get('components', 'errorHandler', 'HttpMethodNotAllowed');
                $action = $action ? $action: 'notAllowedAction';

                $obj = new $class_name();
                $this->generateResponse($obj->$action());
            }
        } catch (HttpRouteNotFoundException $exception) {
            header('HTTP/' . $this->version . ' 404 Not Found');
            if (
                self::FORMAT_JSON == $this->format ||
                self::FORMAT_JSONP == $this->format ||
                self::FORMAT_XML == $this->format
            ) {
                $this->generateResponse(array(
                    'status' => false,
                    'error' => [
                        'code' => 404,
                        'msg' => 'Invalid endpoint'
                    ]
                ));
            } else {
                $class_name = Reagordi::$app->getConfig()->get('components', 'errorHandler', 'class');
                $class_name = $class_name ? $class_name: 'reagordi\framework\web\ErrorHandler';

                $action = Reagordi::$app->getConfig()->get('components', 'errorHandler', 'HttpRouteNotFound');
                $action = $action ? $action: 'notFoundAction';

                $obj = new $class_name();
                $this->generateResponse($obj->$action());
            }
        } catch (BadRouteException $exception) {
            header('HTTP/' . $this->version . ' 500 Bad route');
            if (
                self::FORMAT_JSON == $this->format ||
                self::FORMAT_JSONP == $this->format ||
                self::FORMAT_XML == $this->format
            ) {
                $this->generateResponse(array(
                    'status' => false,
                    'error' => [
                        'code' => 500,
                        'msg' => 'Bad route'
                    ]
                ));
            } else {
                $class_name = Reagordi::$app->getConfig()->get('components', 'errorHandler', 'class');
                $class_name = $class_name ? $class_name: 'reagordi\framework\web\ErrorHandler';

                $action = Reagordi::$app->getConfig()->get('components', 'errorHandler', 'BadRoute');
                $action = $action ? $action: 'BadRouteAction';

                $obj = new $class_name();
                $this->generateResponse($obj->$action());
            }
        }
    }

    /**
     * Вывод ответа
     *
     * @param string|array $response Ответ
     */
    private function generateResponse(string|array $response)
    {
        if (
            self::FORMAT_JSON == $this->format ||
            self::FORMAT_JSONP == $this->format ||
            self::FORMAT_XML == $this->format &&
            is_array($response)
        ) {
            header('Access-Control-Allow-Origin: *');
            if (Reagordi::$app->getResponse()->getServer()->getRequestMethod() === 'OPTIONS') {
                header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
                header('Access-Control-Allow-Headers: Authorization, Content-Type');
                header('Access-Control-Max-Age: 1728000');
                header('Content-Length: 0');
                die();
            }
            echo json_encode($response);
        } else {
            echo $response;
        }
    }

    /**
     * Возвращяет экземпляр класса Server
     *
     * @return Server|null
     */
    public function getServer()
    {
        return Server::getInstance();
    }

    /**
     * Returns current instance of the Reagordi.
     *
     * @return Response
     */
    public static function getInstance()
    {
        if (self::$app === null) {
            self::$app = new Response();
        }
        return self::$app;
    }
}
