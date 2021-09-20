<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\base;

use Phroute\Phroute\RouteCollector;

class Router extends RouteCollector
{
    /**
     * Экземпляр класса Reagordi
     *
     * @var Router
     */
    private static $app;

    /**
     * Экземпляр класса
     *
     * @var RouteCollector
     */
    private static $collector;

    /**
     * Reagordi constructor.
     */
    private function __construct()
    {
        parent::__construct();
        self::$collector = new RouteCollector();
    }

    public static function getInstance()
    {
        if (self::$app === null) {
            self::$app = new Router();
        }
        return self::$app;
    }
}
