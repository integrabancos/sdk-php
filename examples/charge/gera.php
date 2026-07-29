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
    "numero" => "5",
    "codigo_banco" => "364",
    "identificacao" => "7",
    "autenticacao" => [
        "client_id" => "Client_Id",
        "client_secret" => "Client_Secret"
    ],
    "pagamento" => [
        "valor" => "1",
        "data_vencimento" => "2026-05-30",
        "descricao" => "1"
    ],
    "pagador" => [
        "cpf" => "01234567890",
        "cnpj1" => "01234567890225",
        "nome" => "Cliente Teste",
        "endereco" => [
            "logradouro" => "Rodovia BR-277",
            "numero" => "592",
            "bairro" => "Parque Sao Paulo",
            "nome_municipio" => "Francisco Beltrao",
            "uf" => "SP",
            "cep" => "59200000"
        ]
    ]
];

try {
    $charge = new Boleto($config);

    // Requisição para criação da cobrança
    $response = $charge->gerarBoleto($data);

    // Visualização do retorno
    echo json_encode($response);
} catch (\Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["erro" => $e->getMessage()]);
}
