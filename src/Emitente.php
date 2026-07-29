<?php

namespace IntegraBancos\SdkPHP;

class Emitente extends Controller
{
    private $config;

    /**
     * Construtor da classe Boleto
     *
     * @param array $config Configurações de inicialização
     * @throws \InvalidArgumentException Quando chaves obrigatórias não fornecidas
     */
    public function __construct(array $config = [], $debug = false)
    {
        parent::__construct($config, $debug);

        $this->config = $config;
    }

    /**
     * Cria um emitente no serviço.
     *
     * @param array $body Dados do emitente
     * @return object Resposta do serviço
     * @throws \RuntimeException Em caso de erro do serviço
     */
    public function cria(array $body)
    {
        $headers = $this->getHeaders();
        return $this->services->request("POST", "/emitente", $body, $headers);
    }

    /**
     * Atualiza um emitente existente.
     *
     * @param array $body Dados de atualização do emitente
     * @return object Resposta do serviço
     * @throws \RuntimeException Em caso de erro do serviço
     */
    public function atualiza(array $body)
    {
        $x_api_key = isset($this->config["x_api_key"]) ? $this->config["x_api_key"] : null;

        if (!$x_api_key) {
            $error = array(
                "message" => "x_api_key é obrigatório!",
                "config" => $this->config
            );
            throw new \InvalidArgumentException(json_encode($error));
        }

        $headers = $this->getHeaders();
        $headers[] = "x-api-key: {$x_api_key}";
        return $this->services->request("PUT", "/emitente", $body, $headers);
    }

}
