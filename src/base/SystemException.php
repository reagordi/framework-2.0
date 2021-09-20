<?php
/**
 * MediaLife Framework
 *
 * @package reagordi/framework
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\base;

use ErrorException;
use Throwable;

class SystemException extends ErrorException
{
    /**
     * SystemException constructor.
     * @param string $message
     * @param int $code
     * @param int $severity
     * @param null $filename
     * @param null $line
     * @param null $previous
     */
    public function __construct( $message = '', $code = 0, $severity = E_ERROR, $filename = null, $line = null, $previous = null )
    {
        $trace = debug_backtrace();
        $trace = isset($trace[1]) ? $trace[1]: $trace[0];
        if ( $filename === null ) $filename = $trace['file'];
        if ( $line === null ) $line = $trace['line'];
        parent::__construct( $message, $code, $severity, $filename, $line, $previous );
    }
}
