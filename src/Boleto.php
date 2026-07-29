<?php

namespace IntegraBancos\SdkPHP;

class Boleto extends Controller
{
    protected $x_api_key = "";
    protected $secret_key = "";

    /**
     * Construtor da classe Boleto
     *
     * @param array $config Configurações de inicialização
     * @throws \InvalidArgumentException Quando chaves obrigatórias não fornecidas
     */
    public function __construct(array $config = [], $debug = false)
    {
        parent::__construct($config, $debug);

        $x_api_key = isset($config["x_api_key"]) ? $config["x_api_key"] : null;
        $secret_key = isset($config["secret_key"]) ? $config["secret_key"] : null;

        if (!$x_api_key || !$secret_key) {
            $error = array(
                "message" => "x_api_key e secret_key são obrigatórios!",
                "config" => $config
            );
            throw new \InvalidArgumentException(json_encode($error));
        }

        $this->x_api_key = $x_api_key;
        $this->secret_key = $secret_key;
    }

    private function has_keys()
    {
        if (empty($this->x_api_key) || empty($this->secret_key)) {
            $error = array(
                "message" => "Os parametros [x_api_key e secret_key] são obrigatórias para esta operação.",
                "x_api_key" => $this->x_api_key,
                "secret_key" => $this->secret_key
            );
            throw new \RuntimeException(json_encode($error));
        }
        return true;
    }

    /**
     * Gera um novo boleto enviando os dados criptografados para o serviço.
     *
     * @param array $data Dados do boleto
     * @return object Resposta decodificada do serviço
     * @throws \RuntimeException Quando chaves não estão configuradas ou encriptação falha
     */
    public function gerarBoleto(array $data)
    {
        $this->has_keys();

        $doc = !empty($data["pagador"]["cnpj"]) ? $data["pagador"]["cnpj"] : "";
        if (empty($doc) && !empty($data["pagador"]["cpf"])) {
            $doc = $data["pagador"]["cpf"];
        }
        $valor = (float)(isset($data["pagamento"]["valor"]) ? $data["pagamento"]["valor"] : 0);
        $identificacao = isset($data["identificacao"]) ? $data["identificacao"] : "";
        $vencimento = isset($data["pagamento"]["data_vencimento"]) ? $data["pagamento"]["data_vencimento"] : "";

        if (empty($valor)) {
            $error = array(
                "message" => "pagamento.valor deve ser informado e maior que zero!",
                "config" => $data
            );
            throw new \InvalidArgumentException(json_encode($error));
        }

        $hash = hash("sha256", "{$identificacao}|{$doc}|{$valor}|{$vencimento}");
        $payload = json_encode($data);

        $encryptedPayload = Crypto::encrypt($payload, $this->secret_key);
        $body = [
            "hash" => $hash,
            "payload" => $encryptedPayload
        ];
        $headers = $this->getHeaders();
        $headers[] = "x-api-key: {$this->x_api_key}";
        return $this->services->request("POST", "/charge", $body, $headers);
    }

    /**
     * Consulta um boleto por identificação.
     *
     * @param array $data Dados de consulta (deve conter `identificacao`)
     * @return object Resposta decodificada do serviço
     * @throws \InvalidArgumentException Quando `identificacao` não informado
     */
    public function consultarBoleto(array $data)
    {
        $this->has_keys();

        $identificacao = isset($data["identificacao"]) ? $data["identificacao"] : "";
        if (empty($identificacao)) {
            $error = array(
                "message" => "O campo [identificacao] é obrigatório para consulta de boleto.",
                "data" => $data
            );
            throw new \InvalidArgumentException(json_encode($error));
        }
        $headers = $this->getHeaders();
        $headers[] = "x-api-key: {$this->x_api_key}";
        return $this->services->request("GET", "/charge/{$identificacao}", [], $headers);
    }

    /**
     * Altera um boleto existente.
     *
     * @param array $data Dados para alteração do boleto
     * @return object Resposta decodificada do serviço
     * @throws \RuntimeException Quando chaves não estão configuradas ou encriptação falha
     */
    public function alterarBoleto(array $data)
    {
        $this->has_keys();
        $payload = json_encode($data);
        $encryptedPayload = Crypto::encrypt($payload, $this->secret_key);
        $body = [
            "payload" => $encryptedPayload
        ];
        $headers = $this->getHeaders();
        $headers[] = "x-api-key: {$this->x_api_key}";
        return $this->services->request("PUT", "/charge", $body, $headers);
    }

    /**
     * Cancela um boleto.
     *
     * @param array $data Dados para cancelamento do boleto
     * @return object Resposta decodificada do serviço
     * @throws \RuntimeException Quando chaves não estão configuradas ou encriptação falha
     */
    public function cancelarBoleto(array $data)
    {
        $this->has_keys();
        $payload = json_encode($data);
        $encryptedPayload = Crypto::encrypt($payload, $this->secret_key);
        $body = [
            "payload" => $encryptedPayload
        ];
        $headers = $this->getHeaders();
        $headers[] = "x-api-key: {$this->x_api_key}";
        return $this->services->request("DELETE", "/charge", $body, $headers);
    }

    /**
     * Consulta um evento de boleto por identificação e id do evento.
     *
     * @param array $data Dados de consulta (deve conter `identificacao` e `evento_id`)
     * @return object Resposta decodificada do serviço
     * @throws \InvalidArgumentException Quando campos obrigatórios não informados
     */
    public function consultarEventoBoleto(array $data)
    {
        $this->has_keys();

        $identificacao = isset($data["identificacao"]) ? $data["identificacao"] : "";
        $evento_id = isset($data["evento_id"]) ? $data["evento_id"] : "";
        if (empty($identificacao) || empty($evento_id)) {
            $error = array(
                "message" => "Os campos [identificacao] e [evento_id] são obrigatórios para consulta de eventos de boleto.",
                "data" => $data
            );
            throw new \InvalidArgumentException(json_encode($error));
        }
        $headers = $this->getHeaders();
        $headers[] = "x-api-key: {$this->x_api_key}";
        return $this->services->request("GET", "/charge/{$identificacao}/{$evento_id}", [], $headers);
    }

}
