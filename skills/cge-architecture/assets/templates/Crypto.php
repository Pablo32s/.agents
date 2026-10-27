<?php

namespace App\Core\Security;

/**
 * Servicio de cifrado y ofuscación de IDs y parámetros GET.
 * Previene ataques IDOR y exposición de PII en URLs.
 * Compatible con PHP 7.3.
 */
class Crypto
{
    private const CIPHER_METHOD = 'aes-128-cbc';

    /**
     * Obtiene la clave de cifrado desde el entorno.
     */
    private static function getKey(): string
    {
        $key = getenv('APP_KEY') ?: '';
        if ($key === '') {
            throw new \RuntimeException('APP_KEY no está configurada en el archivo .env.');
        }
        return substr(hash('sha256', $key, true), 0, 16);
    }

    /**
     * Cifra un ID numérico para exponerlo de forma segura en el frontend.
     */
    public static function encryptId(int $id): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_METHOD));
        $encrypted = openssl_encrypt((string)$id, self::CIPHER_METHOD, self::getKey(), 0, $iv);
        return rtrim(strtr(base64_encode($iv . $encrypted), '+/', '-_'), '=');
    }

    /**
     * Descifra un token a su ID numérico original. Retorna null si es inválido.
     */
    public static function decryptId(string $token): ?int
    {
        $data = base64_decode(strtr($token, '-_', '+/'));
        $ivLength = openssl_cipher_iv_length(self::CIPHER_METHOD);
        if (strlen($data) <= $ivLength) {
            return null;
        }

        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        $decrypted = openssl_decrypt($encrypted, self::CIPHER_METHOD, self::getKey(), 0, $iv);

        if ($decrypted === false || !ctype_digit($decrypted)) {
            return null;
        }

        return (int)$decrypted;
    }

    /**
     * Cifra un arreglo completo de parámetros GET en un único token URL-safe.
     * Ejemplo: ['documento' => '12345678', 'idAgente' => 45] -> "q=TOKEN_OFUSCADO"
     */
    public static function encryptQuery(array $params): string
    {
        $json = json_encode($params);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_METHOD));
        $encrypted = openssl_encrypt($json, self::CIPHER_METHOD, self::getKey(), 0, $iv);
        return rtrim(strtr(base64_encode($iv . $encrypted), '+/', '-_'), '=');
    }

    /**
     * Descifra el token de consulta GET a su arreglo original de parámetros.
     */
    public static function decryptQuery(string $token): array
    {
        $data = base64_decode(strtr($token, '-_', '+/'));
        $ivLength = openssl_cipher_iv_length(self::CIPHER_METHOD);
        if (strlen($data) <= $ivLength) {
            return [];
        }

        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        $decrypted = openssl_decrypt($encrypted, self::CIPHER_METHOD, self::getKey(), 0, $iv);

        if ($decrypted === false) {
            return [];
        }

        $decoded = json_decode($decrypted, true);
        return is_array($decoded) ? $decoded : [];
    }
}
