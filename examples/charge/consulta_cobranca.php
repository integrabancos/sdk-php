<?php

include __DIR__ . '/../../bootstrap.php';

use IntegraBancos\SdkPHP\Boleto;

$config = [
    "access_token" => "ACCESS_TOKEN",
    "x_api_key" => "X-API-KEY",
    "is_production" => false
];

$data = [
    "identificacao" => "7"
];

try {
    $charge = new Boleto($config);

    // Requisição para consulta da cobrança
    $response = $charge->consultarBoleto($data);

    // Visualização do retorno
    echo json_encode($response);
} catch (\Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["erro" => $e->getMessage()]);
}
