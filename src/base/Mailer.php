<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\base;

use PHPMailer\PHPMailer\PHPMailer;
use Reagordi;

class Mailer
{
    /**
     * Экземпляр класса
     *
     * @var null
     */
    protected static $obj = null;

    /**
     * Экземпляр PHPMailer
     *
     * @var PHPMailer
     */
    private $mailer;

    /**
     * Ошибка при отправки
     *
     * @var string
     */
    private $error_info;

    /**
     * Mailer constructor.
     */
    private function __construct()
    {
        $this->mailer = null;
    }

    /**
     * Стартует создание письма
     *
     * @return $this
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function compose()
    {
        unset($this->mailer);
        $this->mailer = new PHPMailer();
        $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $this->mailer->Encoding = PHPMailer::ENCODING_BASE64;
        $this->mailer->XMailer = 'Reagordi Framework';
        $this->mailer->isHTML();
        $this->mailer->setFrom(Reagordi::$app->options->get('components', 'mailer', 'senderEmail'), str_replace('&amp;', '&', Reagordi::$app->options->get('components', 'mailer', 'senderName')));
        return $this;
    }

    /**
     * Кому письмо
     *
     * @param string $addres
     * @return $this
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function setTo(string $addres)
    {
        $this->mailer->addAddress($addres);
        return $this;
    }

    /**
     * Тема письма
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    /**
     * Обычный текст письма
     *
     * @param string $body
     * @return $this
     */
    public function setTextBody(string $body)
    {
        $this->mailer->Body = $body;
        return $this;
    }

    /**
     * HTML версия письма
     *
     * @param string $body
     * @return $this
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function setHtmlBody(string $body)
    {
        $this->mailer->msgHTML($body);
        return $this;
    }

    /**
     * Отправка письма
     *
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exceptionэ
     */
    public function send()
    {
        $status = $this->mailer->send();
        $this->error_info = $this->mailer->ErrorInfo;
        unset($this->mailer);
        return $status;
    }

    /**
     * Возврат ошибки
     *
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->error_info;
    }

    /**
     * Инициализация один раз класс
     *
     * @return Mailer
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new Mailer();
        }
        return self::$obj;
    }
}
