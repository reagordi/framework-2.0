<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\base;

class Request
{
    /**
     * GET параметры
     *
     * @var array
     */
    private $get = [];

    /**
     * Данные полученные POST-запросом
     *
     * @var array
     */
    private $post = [];

    /**
     * Данные полученные POST и GET запросом
     *
     * @var array
     */
    private $request = [];

    /**
     * Данные полученные в формате JSON
     *
     * @var array
     */
    private $json = [];

    /**
     * Информация о загружаемых файлах
     *
     * @var array
     */
    private $file = [];

    /**
     * Экземпляр класса Session
     *
     * @var Session
     */
    protected static $obj = null;

    /**
     * Request constructor.
     */
    private function __construct()
    {
        $this->get = $this->xss($_GET);
        $this->request = $this->xss($_REQUEST);
        $this->file = $_FILES;
        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json')
            $this->json = $this->xss(json_decode(file_get_contents('php://input'), true));
        else
            $this->post = $this->xss($_POST);
    }

    /**
     * Получение списка GOOKIE
     *
     * @return array
     */
    public function getCookieList()
    {
        return $this->cookie->getList();
    }

    /**
     * Получение списка GET и POST параметров
     *
     * @return array
     */
    public function getList()
    {
        return $this->request;
    }

    /**
     * Получение списка GET-параметров
     *
     * @return array
     */
    public function getQueryList()
    {
        return $this->get;
    }

    /**
     * Получение списка POST-параметров
     *
     * @return array
     */
    public function getPostList()
    {
        return $this->post;
    }

    /**
     * Получение списка JSON-параметров
     *
     * @return array
     */
    public function getJsonList()
    {
        return $this->json;
    }

    /**
     * Получение списка FILES-параметров
     *
     * @return array
     */
    public function getFileList()
    {
        return $this->file;
    }

    /**
     * Получение GOOKIE
     *
     * @param $param
     * @return mixed|null
     */
    public function getCookie($param)
    {
        if (isset($this->getCookieList()[$param])) return $this->getCookieList()[$param];
        return null;
    }

    /**
     * Получение GET и POST параметра
     *
     * @param $param
     * @return mixed|null
     */
    public function get($param)
    {
        if (isset($this->getList()[$param])) return $this->getList()[$param];
        return null;
    }

    /**
     * Получение GET-параметра
     *
     * @param $param
     * @return mixed|null
     */
    public function getQuery($param)
    {
        if (isset($this->getQueryList()[$param])) return $this->getQueryList()[$param];
        return null;
    }

    /**
     * Получение POST-параметра
     *
     * @param $param
     * @return mixed|null
     */
    public function getPost($param)
    {
        if (isset($this->getPostList()[$param])) return $this->getPostList()[$param];
        return null;
    }

    /**
     * Получение JSON-параметра
     *
     * @param $param
     * @return mixed|null
     */
    public function getJson($param)
    {
        if (isset($this->getJsonList()[$param])) return $this->getJsonList()[$param];
        return null;
    }

    /**
     * Получение информации о файле
     *
     * @param string $file_name
     * @return array|null
     */
    public function getFile($file_name)
    {
        if (isset($this->getFileList()[$file_name])) return $this->getFileList()[$file_name];
        return null;
    }

    /**
     * Получение метода запроса
     *
     * @return string
     */
    public function getRequestMethod()
    {
        Context::getCurrent()->getServer()->getRequestMethod();
    }

    /**
     * Проверка GET-запрос
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->getRequestMethod() == 'GET' ? true : false;
    }

    /**
     * Проверка POST-запрос
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->getRequestMethod() == 'POST' ? true : false;
    }

    /**
     * Проверка HTTPS-запроса
     *
     * @return bool
     */
    function isHttps()
    {
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
            || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
            || (isset($_SERVER['CF_VISITOR']) && $_SERVER['CF_VISITOR'] == '{"scheme":"https"}')
            || (isset($_SERVER['HTTP_CF_VISITOR']) && $_SERVER['HTTP_CF_VISITOR'] == '{"scheme":"https"}')
        ) return true;
        else return false;
    }

    /**
     * Проверка AJAX-запроса
     *
     * @return bool
     */
    public function isAjaxRequest()
    {
        return (isset($_SERVER['HTTP_REAGORDI_AJAX']) && $_SERVER['HTTP_REAGORDI_AJAX'] !== null) ||
        isset($_SERVER['HTTP_REAGORDI_AJAX']) === 'XMLHttpRequest' ? true : false;
    }

    /**
     * Валидация входных данных
     *
     * @access private
     * @param array $data входные данные
     * @return array
     */
    private function xss($data)
    {
        if (is_array($data)) {
            $escaped = array();
            foreach ($data as $key => $value) {
                $escaped[$key] = $this->xss($value);
            }
            return $escaped;
        }
        return trim(htmlspecialchars($data));
    }

    /**
     * Returns current instance of the Request.
     *
     * @return Request
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new Request();
        }
        return self::$obj;
    }
}
