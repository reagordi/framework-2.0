<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\web;

class ErrorHandler
{
    public function notAllowedAction()
    {
        return '405';
    }

    public function notFoundAction()
    {
        return '404';
    }

    public function BadRouteAction()
    {
        return '500';
    }
}
