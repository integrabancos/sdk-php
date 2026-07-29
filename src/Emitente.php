<?php

namespace IntegraBancos\SdkPHP;

class Emitente extends Controller
{

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
        $headers = $this->getHeaders();
        return $this->services->request("PUT", "/emitente", $body, $headers);
    }

}
