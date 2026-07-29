<?php

include __DIR__ . '/../../bootstrap.php';

use IntegraBancos\SdkPHP\Emitente;

$config = [
    "access_token" => "ACCESS_TOKEN",
    "x_api_key" => "X-API-KEY",
    "is_production" => false
];

// Dados do emitente
$data = [
    "nome" => "EMPRESA TESTE",
    "razao" => "EMPRESA TESTE",
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
    $response = $emitente->atualiza($data);

    // Visualização do retorno
    echo json_encode($response);
} catch (\Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["erro" => $e->getMessage()]);
}
