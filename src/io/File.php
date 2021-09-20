<?php
/**
 * MediaLife Framework
 *
 * @package medialife
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\io;

class File
{
    public static function hasFile($file, $algo = 'md5')
    {
        if (!self::isFileExists($file)) return null;
        return hash_file($algo, $file);
    }

    /**
     * Определяет существует ли папка
     *
     * @param string $file Путь до файла
     * @return bool
     */
    public static function isFileExists($file)
    {
        return is_file($file);
    }
}
