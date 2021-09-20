<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\loader;

class Loader
{
    /**
     * Может использоваться для предотвращения загрузки всех модулей, кроме system
     */
    const SAFE_MODE = false;

    protected static $safe_mode_modules = [];
    protected static $loaded_modules = [];
    protected static $semiloaded_modules = [];
    protected static $modules_holders = [];
    protected static $shareware_modules = [];

    /**
     * Пользовательские пути автоматической загрузки.
     * @var array [namespace => path]
     */
    protected static $namespaces = [];

    /**
     * Возвращается includeSharewareModule(), если модуль не найден
     */
    const MODULE_NOT_FOUND = 0;

    /**
     * Возвращается includeSharewareModule(), если модуль установлен
     */
    const MODULE_INSTALLED = 1;

    /**
     * Возвращается includeSharewareModule(), если модуль работает в демо-режиме
     */
    const MODULE_DEMO = 2;

    /**
     * Возвращается includeSharewareModule(), если пробный период истек
     */
    const MODULE_DEMO_EXPIRED = 3;

    protected static $autoload_classes = [];

    /**
     * @var bool Управление выбрасыванием исключения методом require Module
     */
    protected static $require_throw_exception = true;

    /** @deprecated */
    const ALPHA_LOWER = 'qwertyuioplkjhgfdsazxcvbnm';
    /** @deprecated */
    const ALPHA_UPPER = 'QWERTYUIOPLKJHGFDSAZXCVBNM';

    /**
     * Включает в себя модуль по его имени.
     *
     * @param string $module_name Имя включенного модуля
     * @return bool Возвращает true, если модуль был успешно включен, в противном случае возвращает false
     * @throws LoaderException
     */
    public static function includeModule($module_name)
    {
        if (!is_string($module_name) || $module_name == '') {
            throw new LoaderException('Empty module name');
        }

        if (strpos($module_name, ':') === false) {
            throw new LoaderException(sprintf('Module name \'%s\' is not correct', $module_name));
        }

        $data = explode(':', $module_name);

        if (preg_match('#[^a-zA-Z0-9._]#', $data[0])) {
            throw new LoaderException(sprintf('Partner name \'%s\' is not correct', $data[0]));
        }

        if (!isset($data[1]) || preg_match('#[^a-zA-Z0-9._]#', $data[1])) {
            throw new LoaderException(sprintf('Module name \'%s\' is not correct', $data[1]));
        }

        $data[0] = strtolower($data[0]);
        $data[1] = strtolower($data[1]);
        $module_name = $data[0] . '/' . $data[1];

        if (self::SAFE_MODE) {
            if (!isset(self::$safeModeModules[$module_name])) {
                return false;
            }
        }

        if (isset(self::$loaded_modules[$module_name])) {
            return self::$loaded_modules[$module_name];
        }

        if (isset(self::$semiloaded_modules[$module_name])) {
            trigger_error("Module '" . $module_name . "' is in loading progress", E_USER_WARNING);
        }

        /*$ar_installed_modules = ModuleManager::getInstalledModules();
        if ( !isset( $ar_installed_modules[$module_name] ) ) {
            return ( self::$loaded_modules[$module_name] = false );
        }*/

        $document_root = self::getDocumentRoot();

        $module_holder = APP_DIR;
        $path_to_include = $module_holder . '/modules/' . $module_name;
        if (!is_dir($path_to_include)) {
            return false;
            /*$module_holder = self::ML_HOLDER;
            $path_to_include = $document_root . '/' . $module_holder . '/modules/' . $module_name;
            if ( !is_dir( $path_to_include ) ) {
                return ( self::$loaded_modules[$module_name] = false );
            }*/
        }

        //register a PSR-4 base folder for the module
        if (strpos($module_name, '.') !== false) {
            //partner's module
            $base_name = str_replace('.', '\\', ucwords(str_replace('/', '\\', $module_name), '.'));
        } else {
            // medialife module
            $base_name = 'Reagordi\\' . ucfirst(str_replace('/', '\\', $module_name));
        }
        self::registerNamespace($base_name, $document_root . '/' . $module_holder . '/modules/' . $module_name . '/lib');

        self::$modules_holders[$module_name] = $module_holder;

        $res = true;
        if (is_file($path_to_include . '/include.php')) {
            //recursion control
            self::$semiloaded_modules[$module_name] = true;

            $res = self::includeModuleInternal($path_to_include . '/include.php');

            unset(self::$semiloaded_modules[$module_name]);
        }

        self::$loaded_modules[$module_name] = ($res !== false);

        if (self::$loaded_modules[$module_name] == false) {
            //unregister the namespace if "include" fails
            self::unregisterNamespace($base_name);
        } else {
            //ServiceLocator::getInstance()->registerByModuleSettings($module_name);
        }

        return self::$loaded_modules[$module_name];
    }

    /**
     * Включает модуль по его имени, выдает исключение в случае сбоя
     *
     * @param string $module_name Имя модуля
     * @return bool
     * @throws LoaderException
     */
    public static function requireModule($module_name)
    {
        $included = self::includeModule($module_name);
        if (!$included && self::$require_throw_exception) {
            throw new LoaderException("Required module `{$module_name}` was not found");
        }
        return $included;
    }

    private static function includeModuleInternal($path)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        return require_once $path;
    }

    /**
     * Включает в себя условно-бесплатный модуль по своему названию.
     * Модуль должен инициализировать константу <module name>_DEMO = в include.php чтобы определить демо-режим.
     * include.php должен вернуть false, чтобы определить истечение пробного периода.
     * Константы используются потому, что их легко запутать.
     *
     * @param string $module_name Имя включенного модуля
     * @return int Следующие константы: Loader::MODULE_NOT_FOUND, Loader::MODULE_INSTALLED, Loader::MODULE_DEMO, Loader::MODULE_DEMO_EXPIRED
     */
    public static function includeSharewareModule($module_name)
    {
        if (isset(self::$shareware_modules[$module_name])) {
            return self::$shareware_modules[$module_name];
        }

        $module = str_replace('.', '_', $module_name);
        $module = str_replace('-', '_', $module_name);
        $module = str_replace(':', '_', $module_name);

        if (self::includeModule($module_name)) {
            if (defined(mb_strtoupper($module) . '_DEMO') && constant(mb_strtoupper($module) . '_DEMO') == 'Y') {
                self::$shareware_modules[$module_name] = self::MODULE_DEMO;
            } else {
                self::$shareware_modules[$module_name] = self::MODULE_INSTALLED;
            }
            return self::$shareware_modules[$module_name];
        }

        if (defined(mb_strtoupper($module) . '_DEMO') && constant(mb_strtoupper($module) . '_DEMO') == 'Y') {
            return (self::$shareware_modules[$module_name] = self::MODULE_DEMO_EXPIRED);
        }

        return (self::$shareware_modules[$module_name] = self::MODULE_NOT_FOUND);
    }

    public static function clearModuleCache($module_name)
    {
        if (!is_string($module_name) || $module_name == '') {
            throw new LoaderException('Empty module name');
        }

        if ($module_name !== 'reagordi:framework') {
            unset(self::$loadedModules[$module_name]);
            unset(self::$modulesHolders[$module_name]);
        }

        unset(self::$shareware_modules[$module_name]);
    }

    /**
     * Returns document root
     *
     * @return string Document root
     */
    public static function getDocumentRoot()
    {
        static $document_root = null;
        if ($document_root === null) {
            $document_root = rtrim(ROOT_DIR, '/\\');
        }
        return $document_root;
    }

    /**
     * Регистрирует классы для автоматической загрузки.
     * Все часто используемые классы должны быть зарегистрированы для автоматической загрузки (производительности).
     * Нет необходимости регистрировать редко используемые классы. Их можно найти и загрузить динамически.
     *
     * @param string $module_name Имя модуля. Может быть null, если классы не являются частью какого - либо модуля
     * @param array $classes Массив классов с именами классов в качестве ключей и путями в качестве значений.
     * @throws LoaderException
     */
    public static function registerAutoLoadClasses($module_name, array $classes)
    {
        if (empty($classes)) {
            return;
        }
        if (($module_name !== null) && empty($module_name)) {
            throw new LoaderException(sprintf("Module name '%s' is not correct", $module_name));
        }

        foreach ($classes as $class => $file) {
            $class = ltrim($class, "\\");
            $class = strtolower($class);

            self::$autoload_classes[$class] = [
                'module' => $module_name,
                'file' => $file,
            ];
        }
    }

    /**
     * Rрегистрирует пространства имен с пользовательскими путями.
     * e.g. ('Medialife\System\Dev', '/home/medialife/web/site/medialife/modules/main/dev/lib')
     *
     * @param string $namespace Префикс пространства имен.
     * @param string $path Абсолютный путь.
     */
    public static function registerNamespace($namespace, $path)
    {
        $namespace = trim($namespace, "\\") . "\\";
        $namespace = strtolower($namespace);

        $path = rtrim($path, "/\\");
        $depth = substr_count(rtrim($namespace, "\\"), "\\");

        self::$namespaces[$namespace] = [
            'path' => $path,
            'depth' => $depth,
        ];
    }

    /**
     * Отменяет регистрацию пространства имен.
     * @param string $namespace
     */
    public static function unregisterNamespace($namespace)
    {
        $namespace = trim($namespace, "\\") . "\\";
        $namespace = strtolower($namespace);

        unset(self::$namespaces[$namespace]);
    }

    /**
     * Регистрирует дополнительный обработчик автоматической загрузки.
     * @param callable $handler
     */
    public static function registerHandler(callable $handler)
    {
        \spl_autoload_register($handler);
    }

    /**
     * Совместимый с PSR-4 автопогрузчик.
     * https://www.php-fig.org/psr/psr-4/
     *
     * @param $className
     */
    public static function autoLoad($className)
    {
        defined('ROOT_DIR') or define('ROOT_DIR', str_replace('\\', '/', dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
        defined('APP_DIR') or define('APP_DIR', ROOT_DIR . '/app');

        // fix web env
        $className = ltrim($className, "\\");

        $classLower = strtolower($className);

        static $documentRoot = null;
        if ($documentRoot === null) {
            $documentRoot = self::getDocumentRoot();
        }
        //optimization via direct paths
        if (isset(self::$autoload_classes[$classLower])) {
            $pathInfo = self::$autoload_classes[$classLower];
            if ($pathInfo["module"] != "" && $pathInfo["module"] != 'reagordi:framework') {
                $module = str_replace(':', '/', $pathInfo["module"]);
                //$holder = (isset(self::$modules_holders[$module])? self::$modules_holders[$module] : self::ML_HOLDER);
                if (is_file(APP_DIR . "/modules/" . $module . "/" . $pathInfo["file"]))
                    require_once(APP_DIR . "/modules/" . $module . "/" . $pathInfo["file"]);
            } elseif (is_file($documentRoot . $pathInfo["file"])) {
                require_once $documentRoot . $pathInfo["file"];
            } elseif (is_file($pathInfo["file"])) {
                require_once $pathInfo["file"];
            }
            return;
        }

        if (preg_match("#[^\\\\/a-zA-Z0-9_]#", $className)) {
            return;
        }

        $tryFiles = [[
            "real" => $className,
            "lower" => $classLower,
        ]];

        if (substr($classLower, -5) == "table") {
            // old *Table stored in reserved files
            $tryFiles[] = [
                "real" => substr($className, 0, -5),
                "lower" => substr($classLower, 0, -5),
            ];
        }

        foreach ($tryFiles as $classInfo) {
            $classParts = explode("\\", $classInfo["lower"]);

            //remove class name
            array_pop($classParts);

            while (!empty($classParts)) {
                //go from the end
                $namespace = implode("\\", $classParts) . "\\";

                if (isset(self::$namespaces[$namespace])) {
                    //found
                    $depth = self::$namespaces[$namespace]["depth"];
                    $path = self::$namespaces[$namespace]["path"];

                    $fileParts = explode("\\", $classInfo["real"]);

                    for ($i = 0; $i <= $depth; $i++) {
                        array_shift($fileParts);
                    }

                    $classPath = implode("/", $fileParts);

                    $classPathLower = strtolower($classPath);

                    // final path lower case
                    $filePath = $path . '/' . $classPathLower . ".php";

                    if (file_exists($filePath)) {
                        require_once($filePath);
                        break 2;
                    }

                    // final path original case
                    $filePath = $path . '/' . $classPath . ".php";

                    if (file_exists($filePath)) {
                        require_once($filePath);
                        break 2;
                    }
                }

                //try the shorter namespace
                array_pop($classParts);
            }
        }
    }

    /**
     * @param $className
     *
     * @throws LoaderException
     */
    public static function requireClass($className)
    {
        $file = ltrim($className, "\\");    // fix web env
        $file = strtolower($file);

        if (preg_match("#[^\\\\/a-zA-Z0-9_]#", $file))
            return;

        $tryFiles = [$file];

        if (substr($file, -5) == "table") {
            // old *Table stored in reserved files
            $tryFiles[] = substr($file, 0, -5);
        }

        foreach ($tryFiles as $file) {
            $file = str_replace('\\', '/', $file);
            $arFile = explode("/", $file);

            if ($arFile[0] === "reagordi") {
                array_shift($arFile);

                if (empty($arFile)) {
                    break;
                }

                $module = array_shift($arFile);
                if ($module == null || empty($arFile)) {
                    break;
                }
            } else {
                $module1 = array_shift($arFile);
                $module2 = array_shift($arFile);

                if ($module1 == null || $module2 == null || empty($arFile)) {
                    break;
                }

                $module = $module1 . "." . $module2;
            }

            if (!self::includeModule($module)) {
                throw new LoaderException(sprintf(
                    "There is no `%s` class, module `%s` is unavailable", $className, $module
                ));
            }
        }

        self::autoLoad($className);
    }

    /**
     * Проверяет, существует ли файл в каталогах /app или /medialife
     *
     * @param string $path Путь к файлу относительно /app/ или /medialife/
     * @param string|null $root Корень документа сервера, по умолчанию self::getDocumentRoot()
     * @return string|bool Возвращает комбинированный путь или false, если файл не существует в обоих dirs
     */
    public static function getLocal($path, $root = null)
    {
        if (file_exists(APP_DIR . $path)) {
            return APP_DIR . $path;
        }
        return false;
    }

    /**
     * Проверяет, существует ли файл в личном каталоге.
     * Если $_SERVER["RG_PERSONAL_ROOT"] не установлен, то личный каталог равен to /medialife/
     *
     * @param string $path File path relative to personal directory
     * @return string|bool Returns combined path or false if the file does not exist
     */
    public static function getPersonal($path)
    {
        $root = self::getDocumentRoot();
        $personal = (isset($_SERVER["RG_PERSONAL_ROOT"]) ? $_SERVER["RG_PERSONAL_ROOT"] : "");

        if ($personal <> '' && file_exists($root . $personal . "/" . $path)) {
            return $root . $personal . "/" . $path;
        }

        return self::getLocal($path, $root);
    }

    /**
     * Изменения требуют поведения модуля
     *
     * @param bool $requireThrowException
     */
    public static function setRequireThrowException($requireThrowException)
    {
        self::$requireThrowException = (bool)$requireThrowException;
    }
}
