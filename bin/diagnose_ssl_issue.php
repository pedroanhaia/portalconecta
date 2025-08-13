<?php
/**
 * Script específico para diagnosticar problemas SSL em produção
 * Execute: php bin/diagnose_ssl_issue.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

echo "=== DIAGNÓSTICO DO PROBLEMA SSL ===\n\n";

// Verificar suporte SSL/TLS no PHP
echo "1. VERIFICANDO SUPORTE SSL/TLS:\n";
echo "   OpenSSL habilitado: " . (extension_loaded('openssl') ? '✅ Sim' : '❌ Não') . "\n";
echo "   cURL habilitado: " . (extension_loaded('curl') ? '✅ Sim' : '❌ Não') . "\n";
echo "   Stream Socket SSL: " . (function_exists('stream_socket_enable_crypto') ? '✅ Sim' : '❌ Não') . "\n";

// Verificar versão SSL/TLS
$openssl_version = phpversion('openssl');
echo "   Versão OpenSSL: " . ($openssl_version ?: 'Não detectada') . "\n";

// Verificar constantes SSL
$ssl_constants = [
    'STREAM_CRYPTO_METHOD_SSLv23_CLIENT',
    'STREAM_CRYPTO_METHOD_TLS_CLIENT', 
    'STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT',
    'STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT',
    'STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT'
];

echo "\n2. CONSTANTES SSL DISPONÍVEIS:\n";
foreach ($ssl_constants as $constant) {
    echo "   {$constant}: " . (defined($constant) ? '✅' : '❌') . "\n";
}

// Testar conexão direta com Gmail SMTP
echo "\n3. TESTANDO CONEXÃO DIRETA COM GMAIL:\n";

// Teste porta 587 (TLS)
echo "   Testando porta 587 (STARTTLS):\n";
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

$socket587 = @stream_socket_client(
    'smtp.gmail.com:587', 
    $errno, 
    $errstr, 
    30, 
    STREAM_CLIENT_CONNECT,
    $context
);

if ($socket587) {
    echo "   ✅ Conexão TCP na porta 587 OK\n";
    
    // Tentar STARTTLS
    $response = fgets($socket587);
    echo "   Resposta inicial: " . trim($response) . "\n";
    
    fputs($socket587, "EHLO localhost\r\n");
    $response = fgets($socket587);
    echo "   Resposta EHLO: " . trim($response) . "\n";
    
    fputs($socket587, "STARTTLS\r\n");
    $response = fgets($socket587);
    echo "   Resposta STARTTLS: " . trim($response) . "\n";
    
    if (strpos($response, '220') === 0) {
        $crypto = stream_socket_enable_crypto($socket587, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        echo "   Criptografia TLS: " . ($crypto ? '✅ OK' : '❌ FALHOU') . "\n";
    }
    
    fclose($socket587);
} else {
    echo "   ❌ Falha na conexão porta 587: {$errstr} ({$errno})\n";
}

// Teste porta 465 (SSL direto)
echo "\n   Testando porta 465 (SSL direto):\n";
$socket465 = @stream_socket_client(
    'ssl://smtp.gmail.com:465', 
    $errno, 
    $errstr, 
    30, 
    STREAM_CLIENT_CONNECT,
    $context
);

if ($socket465) {
    echo "   ✅ Conexão SSL na porta 465 OK\n";
    $response = fgets($socket465);
    echo "   Resposta inicial: " . trim($response) . "\n";
    fclose($socket465);
} else {
    echo "   ❌ Falha na conexão porta 465: {$errstr} ({$errno})\n";
}

// Verificar configuração atual do sistema
echo "\n4. CONFIGURAÇÃO ATUAL:\n";
echo "   Protocolo: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'HTTPS ✅' : 'HTTP ⚠️') . "\n";
echo "   Host: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\n";
echo "   PHP Version: " . phpversion() . "\n";

// Sugestões baseadas nos resultados
echo "\n=== RECOMENDAÇÕES ===\n";
echo "Para resolver o erro 'stream_socket_enable_crypto(): This stream does not support SSL/crypto':\n\n";

echo "1. CONFIGURAÇÃO PREFERENCIAL (porta 465 - SSL direto):\n";
echo "   - Use a configuração 'ssl' criada no app.php\n";
echo "   - Porta 465 evita problemas com STARTTLS\n";
echo "   - SSL é estabelecido na conexão inicial\n\n";

echo "2. SE PORTA 465 NÃO FUNCIONAR:\n";
echo "   - Mantenha a configuração 'default' (porta 587)\n";
echo "   - Adicione mais opções de contexto SSL\n";
echo "   - Considere usar verify_peer=false em produção\n\n";

echo "3. IMPLEMENTAÇÃO COM FALLBACK:\n";
echo "   - O código foi atualizado para tentar SSL primeiro\n";
echo "   - Se falhar, usa TLS como backup\n";
echo "   - Ambos os métodos foram implementados\n\n";

echo "4. VERIFICAÇÕES ADICIONAIS:\n";
echo "   - Confirme que a senha do Gmail é uma 'senha de app'\n";
echo "   - Verifique se a conta tem verificação 2 fatores\n";
echo "   - Teste em ambiente local primeiro\n\n";

echo "=== PRÓXIMOS PASSOS ===\n";
echo "1. Faça deploy das alterações em produção\n";
echo "2. Teste envio de email via sistema\n";
echo "3. Monitore os logs em logs/error.log\n";
echo "4. Se ainda houver erro, execute este script em produção\n";
