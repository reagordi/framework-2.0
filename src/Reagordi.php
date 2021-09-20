<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

/**
 *
 * @method reagordi\framework\base\Router getRouter()
 * @method reagordi\framework\web\Response getResponse()
 * @method reagordi\framework\base\Config getConfig()
 * @method reagordi\framework\base\Security getSecurity()
 * @method reagordi\framework\base\Mailer getMailer()
 * @method reagordi\framework\web\Languages i18n()
 *
 * @see Reagordi
 */
class Reagordi
{
    /**
     * Версия движка
     *
     * @var string
     */
    const VERSION = '0.0.1-dev';

    /**
     * Экземпляр класса Reagordi
     *
     * @var Reagordi
     */
    public static $app;

    /**
     * Список загружаемых классов
     *
     * @var array
     */
    private $class_list;

    /**
     * Reagordi constructor.
     */
    private function __construct()
    {
        $this->class_list = [];

        $this->addCallback('framework', 'getResponse', 'web\Response', 'reagordi\framework');
        $this->addCallback('framework', 'getRouter', 'base\Router', 'reagordi\framework');
        $this->addCallback('framework', 'getConfig', 'base\Config', 'reagordi\framework');
        $this->addCallback('framework', 'getSecurity', 'base\Security', 'reagordi\framework');
        $this->addCallback('framework', 'getMailer', 'base\Mailer', 'reagordi\framework');
        $this->addCallback('framework', 'i18n', 'web\Languages', 'reagordi\framework');
    }

    /**
     * Запускается при вызове недоступных методов в контексте объект
     *
     * @param string $name Имя метода
     * @param array $arguments Аргументы
     * @return mixed
     * @throws Exception
     */
    public function __call(string $name, array $arguments)
    {
        if (!empty($this->class_list[$name])) {
            require_once $this->class_list[$name]['file'];
            return $this->class_list[$name]['class']::getInstance();
        }
        $mess = 'Method Reagirdi::$app->' . $name . '(';
        foreach ($arguments as $arg) {
            $v = is_array($arg) ? '[...]': $arg;
            $v = is_object($v) ? '{...}': $v;
            $mess .= gettype($arg) . ' "' . htmlentities($v) . '", ';
        }
        if (count($arguments) >= 1) {
            $mess = substr($mess, 0, -2);
        }
        $mess .= ') not found';
        throw new \Exception($mess);
    }

    /**
     * Добавление новых классов
     *
     * @param string $module Имя модуля
     * @param string $name_callback Имя вызываемого метода
     * @param string $class Подключаемый класс
     * @param string $namespace Пространство имен
     */
    public function addCallback(string $module, string $name_callback, string $class, string $namespace)
    {
        $module = str_replace(':', '/', $module);
        if (is_file(VENDOR_DIR . '/reagordi/' . $module . '/src/' . str_replace('\\', '/', $class) . '.php')) {
            $this->class_list[$name_callback] = [
                'file' => VENDOR_DIR . '/reagordi/' . $module . '/src/' . str_replace('\\', '/', $class) . '.php',
                'class' => $namespace . '\\' . $class
            ];
        } elseif (is_file(APP_DIR . '/modules/' . $module . '/src/' . str_replace('\\', '/', $class) . '.php')) {
            $this->class_list[$name_callback] = [
                'file' => APP_DIR . '/modules/' . $module . '/src/' . str_replace('\\', '/', $class) . '.php',
                'class' => $namespace . '\\' . $class
            ];
        }
    }

    /**
     * Returns current instance of the Reagordi.
     *
     * @return Reagordi
     */
    public static function getInstance()
    {
        if (self::$app === null) {
            self::$app = new Reagordi();
        }
        return self::$app;
    }
}
