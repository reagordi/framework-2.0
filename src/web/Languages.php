<?php
/**
 * MediaLife Framework
 *
 * @package medialife
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\web;

use Reagordi;

class Languages
{
    /**
     * Объект класса
     *
     * @var null|Languages
     */
    private static $obj = null;

    /**
     * Текущий язык
     *
     * @var null|string
     */
    private $current_lang = null;

    /**
     * Переводы
     *
     * @var array
     */
    private $messages = [];

    /**
     * Получение перевода
     *
     * @param string $message Переводимое сообщение
     * @param null|array $replace Массив что необходимо изменить
     * @return string
     */
    public function getMessage($message, $replace = null)
    {
        if (!isset($this->messages[$message])) {
            return $message;
        }
        $s = $this->messages[$message];
        if ($replace !== null && is_array($replace)) {
            foreach ($replace as $search => $repl) {
                $s = str_replace($search, $repl, $s);
            }
        }
        return $s;
    }

    /**
     * Подключение переводов статических стрниц
     *
     * @param string $path Полный путь до файла где переводится
     * @return bool
     */
    public function loadLanguageFile($path)
    {
        if ($this->current_lang === null) $this->current_lang = $this->getCurrentLang();
        $path = str_replace('\\', '/', $path);
        $file = str_replace(APP_DIR . '/pages/', '', $path);
        if (is_file(APP_DIR . '/languages/' . $this->current_lang . '/' . $file)) {
            $data = require_once APP_DIR . '/languages/' . $this->current_lang . '/' . $file;
            if ($data && is_array($data)) {
                $this->messages = array_merge($this->messages, $data);
                return true;
            }
        }
        return false;
    }

    /**
     * Подключение переводов темы
     *
     * @param string $path Полный путь до файла где переводится
     * @return bool
     */
    public function loadTemplateLangFile($path)
    {
        if ($this->current_lang === null) $this->current_lang = $this->getCurrentLang();
        $file = str_replace('\\', '/', $file);
        $file = str_replace(APP_DIR . '/templates/', '', $path);
        $file = explode('/', $file);
        $thema = $file[0];
        unset($file[0]);
        $file = implode('/', $file);
        if (is_file(APP_DIR . '/templates/' . $thema . '/languages/' . $this->current_lang . '/' . $file)) {
            $data = require_once APP_DIR . '/templates/' . $thema . '/languages/' . $this->current_lang . '/' . $file;
            if ($data && is_array($data)) {
                $this->messages = array_merge($this->messages, $data);
                return true;
            }
        }
        return false;
    }

    /**
     * Подключение модуля
     *
     * @param string $path Полный путь до файла где переводится
     * @return bool
     */
    public function loadModuleLangFile($path)
    {
        if ($this->current_lang === null) $this->current_lang = $this->getCurrentLang();
        $path = str_replace('\\', '/', $path);
        $file = str_replace(APP_DIR . '/modules/', '', $path);
        $file = explode('/', $file);
        if (!isset($file[0]) || !isset($file[1])) return false;
        $partner = $file[0];
        $model = $file[1];
        unset($file[0]);
        unset($file[1]);
        $file = implode('/', $file);
        if (is_file(APP_DIR . '/modules/' . $partner . '/' . $model . '/languages/' . $this->current_lang . '/' . $file)) {
            $data = require_once APP_DIR . '/modules/' . $partner . '/' . $model . '/languages/' . $this->current_lang . '/' . $file;
            if ($data && is_array($data)) {
                $this->messages = array_merge($this->messages, $data);
                return true;
            }
        }
        return false;
    }

    /**
     * Подключение переводов спомогательных компонентов
     *
     * @param string $path Полный путь до файла где переводится
     * @return bool
     */
    public function loadComponentLangFile($path)
    {
        if ($this->current_lang === null) $this->current_lang = $this->getCurrentLang();
        $path = str_replace('\\', '/', $path);
        $file = str_replace(APP_DIR . '/modules/', '', $path);
        $file = explode('/', $file);
        if (!isset($file[0]) || !isset($file[1])) return false;
        $partner = $file[0];
        $model = $file[1];
        unset($file[0]);
        unset($file[1]);
        $file = implode('/', $file);

        $file = str_replace('components/', '', $file);
        $component = explode('/', $file);
        $component = $component[0];
        $file = str_replace($component . '/', '', $file);

        if (is_file(APP_DIR . '/modules/' . $partner . '/' . $model . '/components/' . $component . '/languages/' . $this->current_lang . '/' . $file)) {
            $data = require_once APP_DIR . '/modules/' . $partner . '/' . $model . '/components/' . $component . '/languages/' . $this->current_lang . '/' . $file;
            if ($data && is_array($data)) {
                $this->messages = array_merge($this->messages, $data);
                return true;
            }
        }
        return false;
    }

    /**
     * Определения языка системы
     *
     * @return string
     */
    public function getCurrentLang()
    {
        if ($this->current_lang) return $this->current_lang;

        if (Reagordi::$app->getResponse()->getRequest()->get('lang')) {
            $this->current_lang = Reagordi::$app->getResponse()->getRequest()->get('lang');
        } elseif (Reagordi::$app->getResponse()->getCookie()->get(RG_COOKIE_LANG)) {
            $this->current_lang = Reagordi::$app->getResponse()->getCookie()->get(RG_COOKIE_LANG);
        } elseif (Reagordi::$app->getConfig()->get('language')) {
            $this->current_lang = Reagordi::$app->getConfig()->get('default_lang');
        } else {
            $this->current_lang = 'en';
        }

        defined('LANGUAGE_ID') or define('LANGUAGE_ID', $this->current_lang);

        return $this->current_lang;
    }

    /**
     * Returns current instance of the Languages.
     *
     * @return Languages
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new Languages();
        }
        return self::$obj;
    }
}
