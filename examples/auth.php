<?php

include __DIR__ . '/../../bootstrap.php';

use IntegraBancos\SdkPHP\Auth;

$is_production = false;

// Se não for a primeira vez rodando o codigo e já possuir o refresh_token salvo
$refresh_token = "SEU_REFRESH_TOKEN";

/**
 * RECOMENDAÇÃO: obtenha os valores da função getenv()
 * OBS: A função getenv() lê direto do sistema/servidor e
 * não lê arquivos .env físicos automaticamente.
 */
$credentials = [
    "client_id" => "CLIENT-ID",
    "client_secret" => "CLIENT-SERCRET",
    "username" => "seuemail@suaempresa.com.br",
    "password" => "12345678",
];

try {
    // Requisição de geração do Access e Refresh Token
    $response = Auth::getAccessToken($credentials, $refresh_token, $is_production);

    if (empty($response->access_token)) {
        // Retornar erro ao solicitar access_token
        throw new \Exception("Falha ao obter o access_token.");
    }

    $access_token = $response->access_token;
    $refresh_token = $response->refresh_token;
    $expires_in = $response->expires_in;

    // Salvar os dados no Banco de dados

    // Visualização dos Campos
    echo json_encode(array(
        "access_token" => $access_token,
        "refresh_token" => $refresh_token,
        "expires_in" => $expires_in
    ));
} catch (\Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["erro" => $e->getMessage()]);
}
