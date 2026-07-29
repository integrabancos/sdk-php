<?php

include __DIR__ . '/../../bootstrap.php';

use IntegraBancos\SdkPHP\Emitente;

$config = [
    "access_token" => "ACCESS_TOKEN",
    "is_production" => false
];

// Dados do emitente
$data = [
    "nome" => "EMPRESA TESTE",
    "razao" => "EMPRESA TESTE",
    "cnpj" => "44354598000192",
    "cpf" => "12345678901",
    "telefone" => "46998895532",
    "email" => "empresa@teste.com",
    "rua" => "TESTE",
    "numero" => "1",
    "complemento" => "NENHUM",
    "bairro" => "TESTE",
    "nome_municipio" => "CIDADE TESTE",
    "codigo_municipio" => "5300108",
    "uf" => "PR",
    "cep" => "85000100"
];

try {
    $emitente = new Emitente($config);

    // Requisição para criação do emitente
    $response = $emitente->cria($data);

    // Obtenção do x-api-token
    $x_api_key = !empty($response->token) ? $response->token : null;

    // Visualização do retorno
    echo json_encode($response);
} catch (\Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["erro" => $e->getMessage()]);
}
