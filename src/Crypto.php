<?php

namespace IntegraBancos\SdkPHP;

class Crypto
{
    private static $CIPHER = "aes-256-gcm";

    /**
     * Encripta um texto usando AES-256-GCM e retorna payload em base64.
     *
     * @param string $plaintext Texto a ser encriptado
     * @param string $key Chave de 32 caracteres
     * @return string Payload encriptado em base64
     * @throws \RuntimeException Quando chave inválida ou encriptação falhar
     */
    public static function encrypt($plaintext, $key)
    {
        if (strlen($key) !== 32) {
            $error = array(
                "message" => "O campo secret_key deve ter exatamente 32 caracteres, pegar no cadastro da softhouse.",
                "provided_key_length" => strlen($key)
            );
            throw new \RuntimeException(json_encode($error));
        }
        $ivLength = openssl_cipher_iv_length(self::$CIPHER);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::$CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        if ($ciphertext === false) {
            $error = array(
                "message" => "Encriptação Falhou.",
                "plaintext" => $plaintext,
                "key_length" => strlen($key)
            );
            throw new \RuntimeException(json_encode($error));
        }
        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Desencripta um payload em base64 gerado por `encrypt`.
     *
     * @param string $encryptedMsg Payload encriptado em base64
     * @param string $key Chave de 32 caracteres
     * @return string Texto desencriptado
     * @throws \RuntimeException Quando chave inválida, base64 inválido ou desencriptação falhar
     */
    public function decrypt($encryptedMsg, $key)
    {
        if (strlen($key) !== 32) {
            $error = array(
                "message" => "O campo secret_key deve ter exatamente 32 caracteres, pegar no cadastro da softhouse.",
                "provided_key_length" => strlen($key)
            );
            throw new \RuntimeException(json_encode($error));
        }
        $payload = base64_decode($encryptedMsg, true);
        if ($payload === false) {
            $error = array(
                "message" => "Codificação base64 Inválida.",
                "encryptedMsg" => $encryptedMsg
            );
            throw new \RuntimeException(json_encode($error));
        }
        $ivLength = openssl_cipher_iv_length(self::$CIPHER);
        $tagLength = 16;

        if (strlen($payload) < ($ivLength + $tagLength)) {
            $error = array(
                "message" => "Comprimento do payload inválido.",
                "payload_length" => strlen($payload)
            );
            throw new \RuntimeException(json_encode($error));
        }
        $iv = substr($payload, 0, $ivLength);
        $tag = substr($payload, $ivLength, $tagLength);
        $ciphertext = substr($payload, $ivLength + $tagLength);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::$CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            $error = array(
                "message" => "Decriptação Falhou. Chave Inválida ou dados adulterados."
            );
            throw new \RuntimeException(json_encode($error));
        }
        return $plaintext;
    }
}
