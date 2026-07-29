# SDK de Integração para API Integra Bancos

Este SDK visa simplificar a integração do seu sistema com a nossa API, oferencendo classes com funções pré-definidas para acessar as rotas da API. Isso Elimina a necessidade de desenvolver uma aplicação para se comunicar diretamente com a nossa API, tornado o processo mais eficiente e direto.

*NOTA: usa apenas o cURL diretamente sem usar pacotes de terceiros.*

## Forma de instalação do SDK

```bash
composer require integrabancos/sdk-php
```
## Regras de Autenticação
Para autenticar o uso da API é necessario obter o **Token de Acesso** (Access Token) no qual vai permitir realizar operações desejadas.

### Para obtenção do token é preciso ter as seguintes **Credenciais**.
- **Client ID**: Fornecido pelo IntegraBancos após o cadastro na aplicação.
- **Client Secret**: Fornecido pelo IntegraBancos após o cadastro na aplicação.
- **Login**: Seu Email usado no cadastro da aplicação.
- **Senha**: Sua senha cadastrada na aplicação.

### Código Exemplo para obteção do **Token de Acesso**.
> Pode se utilizar qualquer uma das classes de operação no exemplo abaixo vamos utilizar a de **Emitente**.

```php
<?php

use IntegraBancos\SdkPHP\Auth;
use IntegraBancos\SdkPHP\Crypto;

$is_production = false;

// Se não for a primeira vez rodando o codigo e já possuir o refresh_token salvo
$refresh_token = "SEU_REFRESH_TOKEN";

// Chave para criptografar o token e salva-lo no banco de dados
// IMPORTANTE: A chave deve conter 32 caracteres ou 32 bytes
// OBS: Logo abaixo é ensinado como pode ser gerado essa crypto_key 
$crypto_key = getenv("CRYPTO_KEY");

/**
 * RECOMENDAÇÃO: obtenha os valores da função getenv()
 * OBS: A função getenv() lê direto do sistema/servidor e 
 * não lê arquivos .env físicos automaticamente.
*/
$credentials = [
    "client_id"     => getenv("INTEGRABANCOS_CLIENT_ID"),
    "client_secret" => getenv("INTEGRABANCOS_CLIENT_SECRET"),
    "login"         => getenv("INTEGRABANCOS_LOGIN"),
    "password"      => getenv("INTEGRABANCOS_SENHA")
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
    // Exemplo de visualização de erro
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["erro" => $e->getMessage()]);
}
```

## Boas Práticas e Segurança da Informação

A manipulação de dados financeiros, credenciais e tokens de autenticação exige um controle rígido de segurança no ambiente da aplicação integradora. Caso informações sensíveis ou tokens de acesso venham a vazar por falta de proteção adequada, agentes maliciosos poderão obter acesso não autorizado aos serviços, gerando cobranças ou Pix falsos e causando graves prejuízos financeiros aos seus clientes.

### Por que utilizamos a abordagem AES-256-GCM?

A classe `Crypto` incluída neste SDK utiliza o padrão de criptografia avançada **AES-256-GCM** via extensão OpenSSL do PHP. Essa abordagem foi definida para atender aos mais altos critérios de proteção do mercado devido às seguintes características:

* **Criptografia Autenticada:** Ao contrário de modos mais antigos (como CBC), o modo GCM (Galois/Counter Mode) fornece criptografia autenticada. Isso significa que, além de ocultar a informação (confidencialidade), o algoritmo gera uma tag de autenticação que impede que os dados criptografados sejam adulterados ou modificados de forma maliciosa em trânsito ou no banco de dados.
* **Segurança de Nível Militar (256 bits):** O uso de uma chave simétrica robusta com exatamente 32 bytes (256 bits) oferece resistência extrema contra ataques modernos de força bruta.
* **Vetor de Inicialização Único (IV):** Cada operação de criptografia gera um IV inteiramente aleatório. Isso garante que, mesmo se você criptografar o mesmo token repetidas vezes, o resultado final gerado em formato Base64 será sempre completamente diferente, eliminando padrões previsíveis.

### Estrutura Educacional para Armazenamento de Tokens

Para apoiar a implementação segura do seu sistema, recomenda-se salvar os dados retornados pela autenticação em uma tabela dedicada, aplicando a encriptação fornecida pela classe `Crypto` nos campos indicados:

| Campo         |   Tipo   | Formato do Dado | Descrição |
| :------------ | :------: | :-------------: | :-------- |
| access_token  |   text   |  Criptografado  | Token de acesso retornado pela API, encriptado via SDK. |
| refresh_token |   text   |  Criptografado  | Token de renovação de sessão, encriptado via SDK. |
| valid_at      | datetime |   Texto Limpo   | Data e hora calculada do vencimento com base no `expires_in`. |

> 💡 **Dica de Implementação:** Sempre transforme o valor inteiro de segundos recebido no campo `expires_in` em um formato de data/hora real (`Y-m-d H:i:s`) no momento em que salvar o registro. Isso tornará simples criar validações rotineiras no seu sistema para disparar o fluxo de renovação por `refresh_token` de maneira automatizada e preventiva antes da expiração acontecer.

## Realizando operações
Com o **Access Token** agora é possível operar ações na API. Porém para realizar ações de
emitente é necessario cadastrar um emitente, que pode ser por via [Painel](link) ou via API.

### Código exemplo de cadastro de emitente via API
```php
<?php

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
    $x_api_key = $response->x_api_key;

    // Visualização do retorno
    echo json_encode($response);
} catch (\Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["erro" => $e->getMessage()]);
}
```

> Seguindo esse passo a passo agora você pode encontrar todos os exemplos de métodos disponíveis na pasta **examples**
