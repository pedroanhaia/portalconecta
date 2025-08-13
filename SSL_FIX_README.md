# 🔧 Solução para Erro SSL em Produção

## 📋 Problema
Erro em produção: `stream_socket_enable_crypto(): This stream does not support SSL/crypto`

## 🎯 Causa
O erro ocorre quando o sistema não consegue estabelecer uma conexão SSL/TLS segura com o servidor SMTP em ambiente HTTPS de produção.

## ✅ Soluções Implementadas

### 1. Configurações SMTP Duplas
Foram criadas duas configurações no `config/app.php`:

- **`default`**: Porta 587 com STARTTLS (desenvolvimento)
- **`ssl`**: Porta 465 com SSL direto (produção)

### 2. Sistema de Fallback
O código foi atualizado para:
1. Tentar primeiro a configuração SSL (porta 465)
2. Se falhar, usar TLS como backup (porta 587)
3. Registrar logs detalhados

### 3. Contexto SSL Robusto
Adicionadas configurações SSL avançadas:
```php
'context' => [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
        'crypto_method' => STREAM_CRYPTO_METHOD_SSLv23_CLIENT,
    ]
]
```

## 🚀 Arquivos Alterados

### `config/app.php`
- Adicionada configuração `ssl` (porta 465)
- Melhorada configuração `default` (porta 587)

### `src/Controller/Component/NotificationComponent.php`
- Sistema de fallback SSL → TLS
- Logs detalhados de erro

### `src/Controller/OrdersController.php`
- Sistema de fallback SSL → TLS  
- Debug melhorado

## 🧪 Scripts de Teste

### `bin/test_smtp_configs.php`
Testa ambas as configurações SMTP:
```bash
php bin/test_smtp_configs.php
```

### `bin/diagnose_ssl_issue.php`
Diagnóstico completo do ambiente SSL:
```bash
php bin/diagnose_ssl_issue.php
```

## 📊 Monitoramento

### Logs para Acompanhar:
- `logs/error.log` - Erros de email
- Sistema mostra qual configuração foi usada

### Mensagens de Log:
- `Email enviado com configuração TLS fallback` - Fallback funcionou
- `Erro ao enviar email (SSL):` - Configuração SSL falhou
- `Erro ao enviar email (TLS):` - Ambas falharam

## ⚙️ Configuração do Gmail

### Pré-requisitos:
1. **Verificação em 2 etapas** ativada
2. **Senha de app** gerada (não usar senha normal)
3. Conta autorizada para SMTP

### Como Gerar Senha de App:
1. Acesse: https://myaccount.google.com/security
2. Ative verificação em 2 etapas
3. Vá em "Senhas de app"
4. Gere senha para "Email"
5. Use essa senha no `config/app.php`

## 🔍 Troubleshooting

### Se o Erro Persistir:

1. **Execute o diagnóstico em produção:**
   ```bash
   php bin/diagnose_ssl_issue.php
   ```

2. **Verifique os logs:**
   ```bash
   tail -f logs/error.log
   ```

3. **Teste específico:**
   ```bash
   php bin/test_smtp_configs.php
   ```

### Possíveis Soluções Adicionais:

1. **Firewall/Proxy:** Verifique se as portas 587 e 465 estão abertas
2. **Provider de Hosting:** Alguns bloqueiam SMTP externo
3. **Configuração PHP:** Verifique se OpenSSL está habilitado

## 🎯 Verificação de Sucesso

### Sinais de que Funcionou:
- ✅ Emails sendo enviados normalmente
- ✅ Log: "Email enviado com configuração SSL/TLS"
- ✅ Sem erros de SSL nos logs

### Em Caso de Problemas:
1. Monitore `logs/error.log` 
2. Execute scripts de diagnóstico
3. Verifique credenciais Gmail
4. Teste conectividade de rede

## 📞 Suporte Técnico

Se o problema persistir, colete as seguintes informações:
- Output do `bin/diagnose_ssl_issue.php`
- Últimas linhas do `logs/error.log`
- Versão do PHP e extensões ativas
- Configuração do servidor web
