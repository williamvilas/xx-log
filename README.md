# xx-log / LogFormatter

Uma biblioteca PHP para formatar logs em JSON estruturado compatível com Monolog / Laravel.

---

## 🛠️ Funcionalidades

- Formatação de registros individuais em JSON com campos padronizados (aplicação, ambiente, nível, mensagem, contexto e datetime).
- Processamento em lote (batch) de registros.
- Compatível com registros do tipo `Monolog\LogRecord` ou arrays simples.
- Fácil integração em projetos Laravel / Illuminate.

---

## 🚀 Instalação

Adicione à lista de dependências via Composer:

```bash
composer require vilasboas/log-formatter
```

## Configuration Laravel

### `config/app.php`

Add the service provider to the `providers` array:

```php
'providers' => [
    // Other providers...
    LogFormatter\Providers\LoggerServiceProvider::class,
],
```

## Configuration Lumen

### `config/app.php`

Add the service provider to the `providers` array:


### Template log

```json
{"application":"api_cte","environment":"local","level":"INFO","message":"Response via api","context":{"type":"response","requestId":"6d20bcc1-5122-4333-a792-f49de948824a","status":200,"headers":{"cache-control":["no-cache, private"],"date":["Thu, 25 Sep 2025 18:50:33 GMT"],"content-type":["application/json"]},"body":"{\"data\":{\"id\":480,\"cnpj\":\"000000000\",\"corporate_name\":\"NAME LTDA\",\"fantasy_name\":\"NAME FANTASY\",\"email_ergon\":null,\"credit_Products\":[{\"id\":2,\"product_name\":\"Capital de Giro\",\"description\":\"Solu\\u00e7\\u00e3o de cr\\u00e9dito que oferece recursos financeiros para apoiar as opera\\u00e7\\u00f5es di\\u00e1rias e o fluxo de caixa das empresas.\",\"created_at\":\"2024-11-18T21:56:43.000000Z\",\"updated_at\":\"2024-11-18T21:56:43.000000Z\",\"pivot\":{\"company_id\":480,\"credit_product_id\":2}},{\"id\":1,\"product_name\":\"P\\u00f3s Pago\",\"description\":\"Produto que permite ao cliente antecipar o valor e pagar pelas opera\\u00e7\\u00f5es realizadas em uma data posterior, com condi\\u00e7\\u00f5es flex\\u00edveis de pagamento.\",\"created_at\":\"2024-11-18T21:56:43.000000Z\",\"updated_at\":\"2024-11-18T21:56:43.000000Z\",\"pivot\":{\"company_id\":480,\"credit_product_id\":1}}],\"created_at\":\"2024-03-21 14:57:02\",\"updated_at\":\"2025-03-14 12:52:18\"}}"},"datetime":"2025-09-25T15:50:34-03:00"}
```
