<?php
/**
 * MediaLife Framework
 *
 * @package medialife
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\base;

use reagordi\framework\web\Response;

class Config
{
    /**
     * Массив параметров
     *
     * @var array|mixed
     */
    protected $options = [];

    /**
     * Экземпляр класса Config
     *
     * @var Config
     */
    protected static $obj = null;

    protected function __construct()
    {
        if (is_file(APP_DIR . '/config/config.php'))
            $this->options = require_once APP_DIR . '/config/config.php';
        $this->defaultConfig();
    }

    /**
     * Получение значения с конфигурационных настроек
     *
     * @return array|mixed|null
     */
    public function get()
    {
        $args = func_get_args();
        $data = $this->options;
        foreach ($args as $arg) {
            if (isset($data[$arg])) {
                $data = $data[$arg];
            } else {
                $data = null;
                break;
            }
        }
        return $data;
    }

    /**
     * Настройки системы по-умолчанию
     *
     * @return void
     */
    private function defaultConfig()
    {
        //$this->options['components'] = !empty($this->options['components']) ? $this->options['components']: [];
        //$this->options['components']['response'] = !empty($this->options['components']['response']) ? $this->options['components']['response']: [];
        //$this->options['components']['response']['format'] = isset($this->options['components']['response']['format']) ? 'text/html': $this->options['components']['response']['format'];
        //$this->options['components']['response']['charset'] = isset($this->options['components']['response']['charset']) ? 'utf-8': $this->options['components']['response']['charset'];
        //$this->options['components']['request']['multiCookieDomain'] = isset($this->options['components']['request']['multiCookieDomain']) ? false: $this->options['components']['request']['multiCookieDomain'];
    }

    /**
     * Returns current instance of the Reagordi.
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new Config();
        }
        return self::$obj;
    }
}
