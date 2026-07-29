<?php

include __DIR__ . '/../../bootstrap.php';

use IntegraBancos\SdkPHP\Boleto;

$config = [
    "access_token" => "ACCESS_TOKEN",
    "x_api_key" => "X-API-KEY",
    "secret_key" => "CRYPTO_KEY", // pegar no cadastro da softhouse
    "is_production" => false
];

// Dados da cobrança
$data = [
    "codigo_banco" => "364",
    "identificacao" => "7",
    "autenticacao" => [
        "client_id" => "Client_Id",
        "client_secret" => "Client_Secret"
    ],
    "data_vencimento" => "2026-12-31",
    "valor" => "5.00"
];

try {
    $charge = new Boleto($config);

    // Requisição para atualização da cobrança
    $response = $charge->alterarBoleto($data);

    // Visualização do retorno
    echo json_encode($response);
} catch (\Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["erro" => $e->getMessage()]);
}
