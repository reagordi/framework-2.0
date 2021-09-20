<?php
/**
 * MediaLife Framework
 *
 * @package medialife
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\io;

class Directory
{
    /**
     * Создание директории
     *
     * @param string $directory Путь до директории
     * @return void
     */
    public static function createDirectory($directory)
    {
        if (is_dir($directory)) return;
        $directory = str_replace('\\', '/', $directory);
        $dirs = explode('/', $directory);
        $dir = '';
        foreach ($dirs as $dir_name) {
            if ($dir_name) $dir .= $dir_name . '/';
            if (!is_dir($dir)) mkdir($dir, REAGORDI_DIR_PERMISSIONS);
        }
    }

    /**
     * Удаление директорий
     *
     * @param string $directory Путь до директории
     */
    public static function deleteDirectory($directory)
    {
        if ($objs = @glob($directory . '/*')) {
            foreach ($objs as $obj) {
                is_dir($obj) ? self::deleteDirectory($obj) : @unlink($obj);
            }
        }
        @rmdir($directory);
    }

    /**
     * Определяет существует ли папка
     *
     * @param string $directory Путь до директории
     * @return bool
     */
    public static function isDirectoryExists($directory)
    {
        return is_dir($directory);
    }
}
