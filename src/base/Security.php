<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace reagordi\framework\base;

use Reagordi;

class Security
{
    /**
     * Экземпляр класса Security
     *
     * @var Security
     */
    protected static $obj = null;

    /**
     * Ключ шифрования сообщений
     *
     * @var string
     */
    private $key;

    /**
     * Ключ для обратимого шифрования
     *
     * @var string
     */
    private $encrupt_key;

    /**
     * Security constructor.
     */
    private function __construct()
    {
        $this->key = Reagordi::$app->getConfig()->get('components', 'request', 'cookieValidationKey');
        $this->key = $this->key ? $this->key : 'd539582c6899f1cd796c698d6d8d4d6db1870b62885926b36b15d684dbd9af38';

        $this->encrupt_key = Reagordi::$app->getConfig()->get('components', 'request', 'encruptKey');
        $this->encrupt_key = $this->encrupt_key ? $this->encrupt_key: sha1($this->key);
    }

    /**
     * Генерация пароля
     *
     * @param string $password Пароль
     * @return false|string|null
     */
    public function generatePasswordHash(string $password)
    {
        return password_hash(md5(sha1($this->key) . sha1($password)), PASSWORD_DEFAULT);
    }

    /**
     * Проверка пароля
     *
     * @param $password Пароль
     * @param $hash Хеш
     * @return bool
     */
    public function validatePassword($password, $hash)
    {
        return password_verify(md5(sha1($this->key) . sha1($password)), $hash);
    }

    /**
     * Шифрование сообщения с помощью ключа
     *
     * @param string $plaintext Исходный текст
     * @param string|null $encrupt_key Ключ шифрования
     * @return string
     */
    public function encryptByKey(string $plaintext, string|null $encrupt_key = null): string
    {
        if (is_null($encrupt_key)) $encrupt_key = $this->encrupt_key;
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $encrupt_key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $encrupt_key, $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
        return $ciphertext;
    }

    /**
     * Расшифровка сообщения с помощью ключа
     *
     * @param string $ciphertext Зашифрованный текст
     * @param string|null $encrupt_key Ключ расшифровки
     * @return string
     */
    public function decryptByKey(string $ciphertext, string|null $encrupt_key = null): string
    {
        if (is_null($encrupt_key)) $encrupt_key = $this->encrupt_key;
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $plaintext = @openssl_decrypt($ciphertext_raw, $cipher, $encrupt_key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $encrupt_key, $as_binary = true);
        if (@hash_equals($hmac, $calcmac)) {
            return $plaintext;
        }
        return '';
    }

    /**
     * Returns current instance of the Security.
     *
     * @return Security
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new Security();
        }
        return self::$obj;
    }
}
