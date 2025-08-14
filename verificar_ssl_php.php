<?php
/**
 * VERIFICADOR DE EXTENSÕES PHP
 */

echo "=== VERIFICAÇÃO DE EXTENSÕES PHP ===\n";
echo "Versão do PHP: " . PHP_VERSION . "\n\n";

// Verifica extensões relacionadas ao SSL/TLS
$extensoes = [
    'openssl' => 'OpenSSL (necessária para SSL/TLS)',
    'curl' => 'cURL (útil para conexões HTTPS)',
    'sockets' => 'Sockets (necessária para stream_socket_client)',
    'mbstring' => 'Multibyte String (recomendada)',
    'json' => 'JSON (necessária)'
];

echo "EXTENSÕES VERIFICADAS:\n";
foreach ($extensoes as $ext => $descricao) {
    $status = extension_loaded($ext) ? "✅ HABILITADA" : "❌ DESABILITADA";
    echo "- $ext: $status ($descricao)\n";
}

echo "\nTRANSPORTES DE STREAM DISPONÍVEIS:\n";
$transportes = stream_get_transports();
foreach ($transportes as $transporte) {
    echo "- $transporte\n";
}

echo "\nWRAPPERS DE STREAM DISPONÍVEIS:\n";
$wrappers = stream_get_wrappers();
foreach ($wrappers as $wrapper) {
    echo "- $wrapper\n";
}

echo "\nVERIFICAÇÃO ESPECÍFICA SSL:\n";
if (in_array('ssl', stream_get_transports())) {
    echo "✅ Transporte SSL disponível\n";
} else {
    echo "❌ Transporte SSL NÃO disponível\n";
}

if (in_array('tls', stream_get_transports())) {
    echo "✅ Transporte TLS disponível\n";
} else {
    echo "❌ Transporte TLS NÃO disponível\n";
}

// Testa conexão SSL básica
echo "\nTESTE DE CONECTIVIDADE SSL:\n";
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

echo "Testando conexão SSL com Google...\n";
$fp = @stream_socket_client("ssl://www.google.com:443", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
if ($fp) {
    echo "✅ Conexão SSL com Google: SUCESSO\n";
    fclose($fp);
} else {
    echo "❌ Conexão SSL com Google: FALHOU ($errstr)\n";
}

echo "\nTeste TCP simples (sem SSL):\n";
$fp = @stream_socket_client("tcp://www.google.com:80", $errno, $errstr, 5);
if ($fp) {
    echo "✅ Conexão TCP: SUCESSO\n";
    fclose($fp);
} else {
    echo "❌ Conexão TCP: FALHOU ($errstr)\n";
}

echo "\nLOCALIZAÇÃO DO PHP.INI:\n";
echo "- Arquivo carregado: " . php_ini_loaded_file() . "\n";
echo "- Diretórios adicionais: " . php_ini_scanned_files() . "\n";

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
