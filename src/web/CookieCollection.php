<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\web;

use Reagordi;

class CookieCollection
{
    /**
     * Экземпляр класса CookieCollection
     *
     * @var CookieCollection
     */
    protected static $obj = null;

    /**
     * Массив Cookie
     *
     * @var array
     */
    private $cookie;

    /**
     * CookieCollection constructor.
     */
    private function __construct()
    {
        $cookie = $_COOKIE;
        $this->cookie = [];
        if (isset($cookie[RG_COOKIE_SID])) $sid = $cookie[RG_COOKIE_SID];
        elseif (isset($this->cookie[RG_COOKIE_SID])) $sid = $this->cookie[RG_COOKIE_SID];
        else $sid = '';
        if (isset($cookie[RG_COOKIE_LANG])) $lang = $cookie[RG_COOKIE_LANG];
        elseif (isset($this->cookie[RG_COOKIE_LANG])) $lang = $this->cookie[RG_COOKIE_LANG];
        else $lang = '';
        foreach ($cookie as $name => $value) {
            $name = str_replace(RG_COOKIE_PREF, '', $name);
            $this->cookie[$name] = Reagordi::$app->getSecurity()->decryptByKey($value);
        }
        $this->cookie[str_replace(RG_COOKIE_PREF, '', RG_COOKIE_SID)] = $sid;
        $this->cookie[str_replace(RG_COOKIE_PREF, '', RG_COOKIE_LANG)] = $lang;
        unset($this->cookie[RG_COOKIE_SID], $this->cookie[RG_COOKIE_LANG]);
    }

    /**
     * Возвращает список всех Cookie
     *
     * @return array
     */
    public function getList()
    {
        return $this->cookie;
    }

    /**
     * Возвращает значение Cookie параметра
     *
     * @param string $name Имя параметра
     * @param null|mixed $default Значение по-умолчанию
     * @return mixed|null
     */
    public function getValue(string $name, $default = null)
    {
        if (isset($this->cookie[$name])) return $this->cookie[$name];
        return $default;
    }

    /**
     * Возвращает значение Cookie параметра
     *
     * @param string $name Имя параметра
     * @return mixed|null
     */
    public function get(string $name)
    {
        return $this->getValue($name);
    }

    /**
     * Возвращает значение Cookie параметра
     *
     * @param string $name Имя параметра
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->cookie[$name])) return $this->cookie[$name];
        return null;
    }

    /**
     * Проверяет существования Cookie параметра
     *
     * @param string $name Имя параметра
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->cookie[$name]) ? true : false;
    }

    /**
     * Добавление Cookie
     *
     * @param string $name Имя параметра
     * @param string $value Значение параметра
     * @param false $expires Время жизни
     * @return bool
     */
    public function add(string $name, string $value, $expires = false): bool
    {
        $this->cookie[$name] = $value;
        if ($expires) $expires = time() + ($expires * 86400);
        if (\Reagordi::$app->getConfig()->get('components', 'request', 'enableCookieValidation')) {
            $value = Reagordi::$app->getSecurity()->encryptByKey($value);
        }
        $_COOKIE[RG_COOKIE_PREF . $name] = $value;
        if (Reagordi::$app->getConfig()->get('components', 'request', 'onlySSL')) setcookie(RG_COOKIE_PREF . $name, $value, $expires, '/', DOMAIN, true, true);
        else setcookie(RG_COOKIE_PREF . $name, $value, $expires, '/', DOMAIN, null, true);
        return true;
    }

    /**
     * Удаление Cookie параметра
     *
     * @param string $name Имя параметра
     * @return bool
     */
    public function remove(string $name): bool
    {
        unset($_COOKIE[RG_COOKIE_PREF . $name], $this->cookie[$name]);
        $expires = time() - 86400;
        if (Reagordi::$app->getConfig()->get('components', 'request', 'onlySSL')) setcookie(RG_COOKIE_PREF . $name, '', $expires, '/', DOMAIN, true, true);
        else setcookie(RG_COOKIE_PREF . $name, '', $expires, '/', DOMAIN, null, true);
        return true;
    }

    /**
     * Удаление Cookie параметра
     *
     * @param string $name Имя параметра
     * @return bool
     */
    public function __unset($name): bool
    {
        unset($_COOKIE[$name], $this->cookie[$name]);
        $expires = time() - ($expires * 86400);
        if (Reagordi::$app->getConfig()->get('components', 'request', 'onlySSL')) setcookie($name, '', $expires, '/', DOMAIN, true, true);
        else setcookie($name, '', $expires, '/', DOMAIN, null, true);
        return true;
    }

    public function __toString()
    {
        return $this->cookie;
    }

    /**
     * Returns current instance of the CookieCollection.
     *
     * @return CookieCollection
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new CookieCollection();
        }
        return self::$obj;
    }
}
