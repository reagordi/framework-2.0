<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

/**#@+
 * Constants for expressing human-readable data sizes in their respective number of bytes.
 *
 * @since 0.0.1
 */
define('KB_IN_BYTES', 1024 );
define('MB_IN_BYTES', 1024 * KB_IN_BYTES);
define('GB_IN_BYTES', 1024 * MB_IN_BYTES);
define('TB_IN_BYTES', 1024 * GB_IN_BYTES);
/**#@-*/

/**#@+
 * Constants for expressing human-readable intervals
 * in their respective number of seconds.
 *
 * Please note that these values are approximate and are provided for convenience.
 * For example, MONTH_IN_SECONDS wrongly assumes every month has 30 days and
 * YEAR_IN_SECONDS does not take leap years into account.
 *
 * If you need more accuracy please consider using the DateTime class (https://secure.php.net/manual/en/class.datetime.php).
 *
 * @since 1.0.0
 */
define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
define('DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
define('WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
define('MONTH_IN_SECONDS',  30 * DAY_IN_SECONDS    );
define('YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );
/**#@-*/

/**
 * Путь до приложений
 *
 * @var string
 */
defined('APP_DIR') or define('APP_DIR', ROOT_DIR . '/app');

/**
 * Путь до временных данных
 *
 * @var string
 */
defined('DATA_DIR') or define('DATA_DIR', ROOT_DIR . '/storage');

/**
 * Тип продукта
 *
 * @var string
 */
defined('REAGORDI_ENV') or define('REAGORDI_ENV', 'prod');

/**
 * Способ вывода ошибок
 *
 * Возможно:
 * * html
 * * json
 * * xml
 * * text
 *
 * @var string
 */
defined('REAGORDI_DEV_VIEW') or define('REAGORDI_DEV_VIEW', 'html');

/**
 * Логирование ошибок
 *
 * @var string
 */
defined('REAGORDI_DEBUG_LOG') or define('REAGORDI_DEBUG_LOG', false);

/**
 * Показ выполненных запросов
 *
 * @var string
 */
defined('R_DEBUG') or define('R_DEBUG', false);

/**
 * Права для директорий
 *
 * @var string
 */
defined('REAGORDI_DIR_PERMISSIONS') or define('REAGORDI_DIR_PERMISSIONS', 0755);

/**
 * Права для файлов
 *
 * @var string
 */
defined('REAGORDI_FILE_PERMISSIONS') or define('REAGORDI_FILE_PERMISSIONS', 0644);
