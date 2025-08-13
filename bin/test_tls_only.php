<?php
/**
 * Script para testar configurações TLS apenas (sem SSL)
 * Execute: php bin/test_tls_only.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use Cake\Mailer\Mailer;

echo "=== TESTE TLS APENAS (SEM SSL) ===\n\n";

// Email de destino para teste
$emailTeste = 'joaomario3224@gmail.com';
$assunto = 'Teste TLS - CGL Conecta';
$mensagem = "
<h2>🧪 Teste de Configuração TLS</h2>
<p>Este email foi enviado para testar apenas configurações TLS (porta 587).</p>
<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>
<p><strong>Método:</strong> STARTTLS na porta 587</p>
<hr>
<p><small>Teste automático do sistema CGL Conecta</small></p>
";

// Configurações TLS para testar
$configuracoes = [
    'default' => 'Configuração TLS padrão (timeout 60s)',
    'fallback' => 'Configuração TLS fallback (timeout 90s, TLSv1.2)'
];

$sucessos = 0;
$falhas = 0;

foreach ($configuracoes as $config => $descricao) {
    echo "=== TESTANDO: {$descricao} ===\n";
    
    try {
        $mailer = new Mailer($config);
        $mailer
            ->setFrom(['joaomario3224@gmail.com' => 'CGL Conecta - Teste TLS'])
            ->setTo($emailTeste)
            ->setSubject($assunto . " - {$descricao}")
            ->setEmailFormat('html');
        
        echo "Enviando email...\n";
        $resultado = $mailer->deliver($mensagem);
        
        if ($resultado) {
            echo "✅ SUCESSO: {$descricao} funcionou!\n";
            echo "   Email enviado para: {$emailTeste}\n\n";
            $sucessos++;
        } else {
            echo "❌ FALHA: Não foi possível enviar com {$descricao}\n\n";
            $falhas++;
        }
        
    } catch (Exception $e) {
        echo "❌ ERRO com {$descricao}: " . $e->getMessage() . "\n";
        
        // Análise específica do erro
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, 'stream_socket_enable_crypto') !== false) {
            echo "   🔍 DIAGNÓSTICO: Problema com habilitação de crypto\n";
            echo "   💡 SOLUÇÃO: O servidor pode não suportar TLS adequadamente\n";
        } elseif (strpos($errorMsg, 'Authentication failed') !== false) {
            echo "   🔍 DIAGNÓSTICO: Problema de autenticação Gmail\n";
            echo "   💡 SOLUÇÃO: Verifique se é senha de app (não senha normal)\n";
        } elseif (strpos($errorMsg, 'Connection refused') !== false) {
            echo "   🔍 DIAGNÓSTICO: Conexão recusada\n";
            echo "   💡 SOLUÇÃO: Firewall ou porta 587 bloqueada\n";
        } elseif (strpos($errorMsg, 'timeout') !== false) {
            echo "   🔍 DIAGNÓSTICO: Timeout de conexão\n";
            echo "   💡 SOLUÇÃO: Aumentar timeout ou verificar rede\n";
        }
        echo "\n";
        $falhas++;
    }
    
    // Pausa entre testes
    sleep(2);
}

echo "=== RESULTADO FINAL ===\n";
echo "✅ Sucessos: {$sucessos}\n";
echo "❌ Falhas: {$falhas}\n\n";

if ($sucessos > 0) {
    echo "🎉 CONFIGURAÇÃO FUNCIONANDO!\n";
    echo "Pelo menos uma configuração TLS está operacional.\n";
    echo "O sistema usará automaticamente a configuração que funcionar.\n\n";
} else {
    echo "⚠️ NENHUMA CONFIGURAÇÃO FUNCIONOU\n";
    echo "Possíveis causas:\n";
    echo "1. Extensão OpenSSL não habilitada corretamente\n";
    echo "2. Firewall bloqueando porta 587\n";
    echo "3. Credenciais Gmail incorretas\n";
    echo "4. Servidor não suporta STARTTLS\n\n";
}

echo "=== INFORMAÇÕES DO AMBIENTE ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "OpenSSL: " . (extension_loaded('openssl') ? '✅ Ativado' : '❌ Desativado') . "\n";
echo "Stream Crypto: " . (function_exists('stream_socket_enable_crypto') ? '✅ Disponível' : '❌ Indisponível') . "\n";

// Verificar constantes TLS
$tlsConstants = [
    'STREAM_CRYPTO_METHOD_TLS_CLIENT',
    'STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT'
];

echo "\nConstantes TLS:\n";
foreach ($tlsConstants as $constant) {
    echo "{$constant}: " . (defined($constant) ? '✅' : '❌') . "\n";
}

echo "\n=== CONFIGURAÇÃO PARA PRODUÇÃO ===\n";
echo "Para ambiente de produção sem suporte SSL nativo:\n";
echo "1. Use apenas configurações TLS (porta 587)\n";
echo "2. Evite porta 465 (requer SSL transport)\n";
echo "3. Configure timeout adequado (60-90 segundos)\n";
echo "4. Use verify_peer=false para contornar problemas de certificado\n";
echo "5. Teste sempre em ambiente similar à produção\n\n";

echo "=== PRÓXIMOS PASSOS ===\n";
if ($sucessos > 0) {
    echo "1. ✅ Configuração funcionando - pode fazer deploy\n";
    echo "2. 📊 Monitore logs de produção\n";
    echo "3. 🧪 Teste envio real via sistema\n";
} else {
    echo "1. 🔍 Investigue problemas de ambiente\n";
    echo "2. 📞 Contate administrador do servidor\n";
    echo "3. 🔧 Considere usar serviço SMTP alternativo\n";
}
echo "\nArquivos de log: logs/error.log\n";
