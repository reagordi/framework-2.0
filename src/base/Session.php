<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\base;

use Reagordi;

class Session
{
    /**
     * проверяем наличие открытой сессии
     *
     * @var bool
     */
    private $is_active = false;

    /**
     * ID сессии
     *
     * @var string
     */
    public $sid;

    /**
     * Экземпляр класса Session
     *
     * @var Session
     */
    protected static $obj = null;

    /**
     * Session constructor.
     */
    private function __construct()
    {
        $this->open();
    }

    /**
     * открываем сессию
     *
     * @return bool
     */
    private function open()
    {
        if ($this->is_active === false) {
            $this->is_active = true;
            $sid = Reagordi::$app->getResponse()->getRequest()->get('sid');
            $params = session_get_cookie_params();
            if (DOMAIN) $params['domain'] = DOMAIN;
            if (Reagordi::$app->getConfig()->get('components', 'request', 'onlySSL')) $params['secure'] = true;
            session_set_cookie_params($params['lifetime'], "/", $params['domain'], $params['secure'], true);
            session_name(RG_COOKIE_SID);
            if ($sid) {
                session_id($sid);
            }
            $status = session_start();
            $this->sid = session_id();
        }
        return $this->sid;
    }

    /**
     * закрываем сессию
     */
    public function close()
    {
        if ($this->is_active === false) $this->open();
        return session_register_shutdown();
    }

    /**
     * уничтожаем все данные сессии
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->is_active === false) $this->open();
        return session_destroy();
    }

    /**
     * Сохранение данных в сессии
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public function set($key, $value)
    {
        if ($this->is_active === false) $this->open();
        $_SESSION[$key] = $value;
        return true;
    }

    /**
     * Сохранение данных в сессии
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public function __set($key, $value)
    {
        if ($this->is_active === false) $this->open();
        $_SESSION[$key] = $value;
        return true;
    }

    /**
     * Получение данных из сессии
     *
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        if ($this->is_active === false) $this->open();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }

    /**
     * Возврат всей сессии
     *
     * @return array
     */
    public function getAll()
    {
        if ($this->is_active === false) $this->open();
        return $_SESSION;
    }

    /**
     * Получение данных из сессии
     *
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        if ($this->is_active === false) $this->open();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }

    /**
     * Проверка наличия данных в сессии
     *
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        if ($this->is_active === false) $this->open();
        if (isset($_SESSION[$key])) {
            return true;
        }
        return false;
    }

    /**
     * Удаление значения из сессии
     *
     * @param $key
     * @return bool
     */
    public function remove($key)
    {
        if ($this->is_active === false) $this->open();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    /**
     * Удаление значения из сессии
     *
     * @param $key
     * @return bool
     */
    public function __unset($key)
    {
        if ($this->is_active === false) $this->open();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    /**
     * устанавливаем значение flash сообщения
     *
     * @param $key
     * @param $value
     */
    public function setFlash($key, $value)
    {
        if ($this->is_active === false) $this->open();
        $_SESSION['reagordi_flash'][$key] = $value;
        return true;
    }

    /**
     * проверяем наличие flash сообщения
     *
     * @param $key
     * @return bool
     */
    public function hasFlash($key)
    {
        if ($this->is_active === false) $this->open();
        if (isset($_SESSION['reagordi_flash'][$key])) {
            return true;
        }
        return false;
    }

    /**
     * получаем и отображаем flash сообщение
     *
     * @param $key
     * @return mixed|null
     */
    public function getFlash($key)
    {
        if ($this->is_active === false) $this->open();
        if (isset($_SESSION['reagordi_flash'][$key])) {
            $msg = $_SESSION['reagordi_flash'][$key];
            $this->removeFlash($key);
            return $msg;
        }
        return null;
    }

    /**
     * получаем и отображаем всех flash сообщений
     *
     * @param $key
     * @return mixed|null
     */
    public function getAllFlashes()
    {
        if ($this->is_active === false) $this->open();
        if (isset($_SESSION['reagordi_flash'])) {
            return $_SESSION['reagordi_flash'];
        }
        return array();
    }

    /**
     * Удаление flash сообщения
     *
     * @param $key
     * @return mixed|null
     */
    public function removeFlash($key)
    {
        if ($this->is_active === false) $this->open();
        if (isset($_SESSION['reagordi_flash'][$key])) {
            unset($_SESSION['reagordi_flash'][$key]);
            return true;
        }
        return false;
    }

    /**
     * Returns current instance of the Reagordi.
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new Session();
        }
        return self::$obj;
    }
}
