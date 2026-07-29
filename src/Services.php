<?php

namespace CloudDFe\IntegraBancosSDK;

class Services
{
    protected $is_production = false;
    protected $version = "1";
    protected $timeout = 60;
    protected $base_uri = "";
    protected $debug = false;

    const URI_PRODUCTION = "api.integrabancos.com.br/v";
    const URI_SANDBOX = "hom-api.integrabancos.com.br/v";

    /**
     * Construtor do cliente de serviços HTTP.
     *
     * @param bool $is_production Ambiente de produção quando true
     * @param string $version Versão da API
     * @param int $timeout Timeout em segundos para requisições
     */
    public function __construct($is_production = false, $version = "1", $timeout = 60, $debug = false)
    {
        $this->is_production = $is_production;
        $this->timeout = $timeout;
        $this->version = $version;
        $this->debug = $debug;

        $this->base_uri = $is_production ? self::URI_PRODUCTION : self::URI_SANDBOX;
    }

    /**
     * Executa uma requisição HTTP ao serviço.
     *
     * @param string $method Verbo HTTP (GET, POST, PUT, DELETE)
     * @param string $endpoint Endpoint (sem barra inicial ou com)
     * @param array $body Payload da requisição
     * @param array $headers Cabeçalhos HTTP adicionais
     * @return object Resposta decodificada em objeto
     * @throws \RuntimeException Em caso de erro na requisição ou resposta inválida
     */
    public function request($method, $endpoint, $body = [], $headers = [])
    {
        $oCurl = curl_init();
        $url = "{$this->base_uri}{$this->version}{$endpoint}";
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYHOST => $this->is_production ? 2 : 0,
            CURLOPT_SSL_VERIFYPEER => $this->is_production ? true : false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
        ];
        if (!empty($body)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        if (!empty($headers)) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        curl_setopt_array($oCurl, $options);

        $response = curl_exec($oCurl);
        $curlError = curl_error($oCurl);
        $httpCode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);

        curl_close($oCurl);

        if ($response === false) {
            $error = array(
                "message" => "Erro na Requisição cURL",
                "error" => $curlError,
                "http_code" => $httpCode
            );
            if ($this->debug) {
                $error["request"] = [
                    "method" => $method,
                    "url" => $url,
                    "body" => $body,
                    "headers" => $headers
                ];
            }
            throw new \RuntimeException(json_encode($error));
        }

        if (empty($response)) {
            $error = array(
                "message" => "Resposta Vazia",
                "http_code" => $httpCode
            );
            if ($this->debug) {
                $error["request"] = [
                    "method" => $method,
                    "url" => $url,
                    "body" => $body,
                    "headers" => $headers
                ];
            }
            throw new \RuntimeException(json_encode($error));
        }

        $decoded = json_decode($response);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = array(
                "message" => "Resposta JSON Inválida",
                "response" => $response,
                "http_code" => $httpCode,
                "json_error" => json_last_error_msg()
            );
            if ($this->debug) {
                $error["request"] = [
                    "method" => $method,
                    "url" => $url,
                    "body" => $body,
                    "headers" => $headers
                ];
            }
            throw new \RuntimeException(json_encode($error));
        }

        return $decoded;
    }
}