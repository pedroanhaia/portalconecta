<?php
/**
 * DEBUG DO PROBLEMA - Teste com debug ativo
 */

if (!defined('ROOT')) define('ROOT', dirname(__DIR__));
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once 'vendor/autoload.php';
require_once 'src/Utility/PHPMailer.php';
require_once 'src/Utility/EmailHelper.php';

use App\Utility\PHPMailer;
use App\Utility\EmailHelper;

echo "=== DEBUG DO PROBLEMA DE EMAIL ===\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n\n";

// Teste 1: Debug direto com a classe PHPMailer
echo "1. TESTE DIRETO COM DEBUG ATIVO:\n";
$mailer = new PHPMailer(true); // debug ativo

try {
    echo "Tentando enviar email...\n";
    
    $resultado = $mailer->send(
        'teste@cglinox.com.br',
        'Debug Test - ' . date('H:i:s'),
        '<h2>Debug Test</h2><p>Teste de debug para identificar o problema.</p>',
        true
    );
    
    echo "Resultado direto: " . ($resultado ? "✅ SUCESSO" : "❌ FALHOU") . "\n";
    
} catch (Exception $e) {
    echo "❌ EXCEÇÃO CAPTURADA: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    
    if ($e->getPrevious()) {
        echo "Exceção anterior: " . $e->getPrevious()->getMessage() . "\n";
    }
}

echo "\n2. TESTE COM EMAILHELPER E DEBUG:\n";

try {
    // Vou modificar temporariamente o EmailHelper para ter debug
    $resultado2 = EmailHelper::sendQuick(
        'teste@cglinox.com.br',
        'EmailHelper Debug - ' . date('H:i:s'),
        'Teste com EmailHelper para debug',
        true,
        true // este parâmetro deveria ser debug, mas vamos verificar
    );
    
    echo "Resultado EmailHelper: " . ($resultado2 ? "✅ SUCESSO" : "❌ FALHOU") . "\n";
    
} catch (Exception $e) {
    echo "❌ ERRO EmailHelper: " . $e->getMessage() . "\n";
}

echo "\n3. VERIFICANDO AUTOLOAD E CLASSES:\n";

if (class_exists('App\\Utility\\PHPMailer')) {
    echo "✅ Classe PHPMailer carregada\n";
} else {
    echo "❌ Classe PHPMailer NÃO carregada\n";
}

if (class_exists('App\\Utility\\EmailHelper')) {
    echo "✅ Classe EmailHelper carregada\n";
} else {
    echo "❌ Classe EmailHelper NÃO carregada\n";
}

echo "\n4. TESTE DE CONECTIVIDADE BÁSICA:\n";

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
        'disable_compression' => true
    ]
]);

$socket = @stream_socket_client(
    "ssl://mail-ssl.m9.network:465",
    $errno,
    $errstr,
    30,
    STREAM_CLIENT_CONNECT,
    $context
);

if ($socket) {
    echo "✅ Conectividade SSL OK\n";
    $response = fgets($socket, 512);
    echo "Resposta do servidor: " . trim($response) . "\n";
    fclose($socket);
} else {
    echo "❌ Falha na conectividade: $errstr ($errno)\n";
}

echo "\n=== FIM DO DEBUG ===\n";
