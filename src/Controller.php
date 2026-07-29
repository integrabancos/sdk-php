<?php

namespace IntegraBancos\SdkPHP;

class Controller
{
    protected $access_token = "";
    protected $services = null;

    /**
     * Construtor do controller base.
     *
     * @param array $config Configurações (deve conter `access_token` e opcional `is_production`)
     * @throws \InvalidArgumentException Quando `access_token` não informado
     */
    public function __construct(array $config = [], $debug = false)
    {
        $access_token = isset($config["access_token"]) ? $config["access_token"] : null;
        $is_production = isset($config["is_production"]) ? $config["is_production"] : false;

        if (!$access_token) {
            $error = array(
                "message" => "Access token é obrigatório para inicializar o controller.",
                "config" => $config
            );
            throw new \InvalidArgumentException(json_encode($error));
        }

        $this->access_token = $access_token;
        $this->services = new Services($is_production, "1", 60, $debug);
    }

    protected function getHeaders()
    {
        $headers = [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Bearer {$this->access_token}",
        ];
        return $headers;
    }

    /**
     * Solicita um token de acesso ao serviço OAuth.
     *
     * @param array $credentials Credenciais do cliente (`client_id`, `client_secret`, `username`, `password`)
     * @param string|null $refresh_token Token de refresh opcional
     * @param bool $is_production Define ambiente de produção
     * @return object Resposta do serviço de autenticação
     * @throws \InvalidArgumentException Quando credenciais incompletas
     */
    public static function getAccessToken(array $credentials, $refresh_token = null, $is_production = false, $debug = false)
    {
        $client_id = isset($credentials["client_id"]) ? $credentials["client_id"] : null;
        $client_secret = isset($credentials["client_secret"]) ? $credentials["client_secret"] : null;
        $username = isset($credentials["username"]) ? $credentials["username"] : null;
        $password = isset($credentials["password"]) ? $credentials["password"] : null;

        $valid_credentials = !empty($client_id) && !empty($client_secret) &&
            ((!empty($username) && !empty($password)) || !empty($refresh_token));

        if (!$valid_credentials) {
            $error = array(
                "message" => "Credenciais incompletas para obtenção de token.",
                "credentials" => $credentials
            );
            throw new \InvalidArgumentException(json_encode($error));
        }

        $grant_type = !empty($refresh_token) ? "refresh_token" : "password";

        $payload = [
            "grant_type" => $grant_type,
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "scope" => "",
        ];

        if (!empty($refresh_token)) {
            $payload["refresh_token"] = $refresh_token;
        } else {
            $payload["username"] = $username;
            $payload["password"] = $password;
        }

        $services = new Services($is_production, "1", 60, $debug);
        $headers = [
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        return $services->request("POST", "/oauth/token", $payload, $headers);
    }
}
