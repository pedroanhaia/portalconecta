<?php
/**
 * Script para testar diferentes configurações SMTP
 * Execute: php bin/test_smtp_configs.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;

echo "=== TESTE DE CONFIGURAÇÕES SMTP ===\n\n";

// Email de destino para teste
$emailTeste = 'joaomario3224@gmail.com';
$assunto = 'Teste SMTP - CGL Conecta';
$mensagem = "
<h2>🧪 Teste de Configuração SMTP</h2>
<p>Este email foi enviado para testar a configuração SMTP.</p>
<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>
<p><strong>Servidor:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</p>
<hr>
<p><small>Teste automático do sistema CGL Conecta</small></p>
";

// Configurações para testar
$configuracoes = [
    'default' => 'Configuração TLS (porta 587)',
    'ssl' => 'Configuração SSL (porta 465)'
];

foreach ($configuracoes as $config => $descricao) {
    echo "=== TESTANDO: {$descricao} ===\n";
    
    try {
        $mailer = new Mailer($config);
        $mailer
            ->setFrom(['joaomario3224@gmail.com' => 'CGL Conecta - Teste'])
            ->setTo($emailTeste)
            ->setSubject($assunto . " - {$descricao}")
            ->setEmailFormat('html');
        
        echo "Enviando email...\n";
        $resultado = $mailer->deliver($mensagem);
        
        if ($resultado) {
            echo "✅ SUCESSO: {$descricao} funcionou!\n";
            echo "   Email enviado para: {$emailTeste}\n\n";
        } else {
            echo "❌ FALHA: Não foi possível enviar com {$descricao}\n\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERRO com {$descricao}: " . $e->getMessage() . "\n";
        
        // Detalhes específicos do erro
        if (strpos($e->getMessage(), 'stream_socket_enable_crypto') !== false) {
            echo "   🔍 DIAGNÓSTICO: Problema com SSL/TLS\n";
            echo "   💡 SOLUÇÃO: Tente usar a outra configuração\n";
        } elseif (strpos($e->getMessage(), 'Authentication failed') !== false) {
            echo "   🔍 DIAGNÓSTICO: Problema de autenticação\n";
            echo "   💡 SOLUÇÃO: Verifique usuário/senha\n";
        } elseif (strpos($e->getMessage(), 'timeout') !== false) {
            echo "   🔍 DIAGNÓSTICO: Timeout de conexão\n";
            echo "   💡 SOLUÇÃO: Verifique conectividade\n";
        }
        echo "\n";
    }
    
    // Pausa entre testes
    sleep(2);
}

echo "=== CONFIGURAÇÕES DISPONÍVEIS ===\n";
echo "Para usar uma configuração específica no código:\n";
echo "\n1. TLS (porta 587): new Mailer('default');\n";
echo "2. SSL (porta 465): new Mailer('ssl');\n\n";

echo "=== RECOMENDAÇÕES ===\n";
echo "• Para produção HTTPS: use preferencialmente a configuração 'ssl'\n";
echo "• Para desenvolvimento local: use 'default'\n";
echo "• Sempre use senhas de app do Gmail (não a senha normal)\n";
echo "• Verifique se a conta tem verificação em 2 etapas ativada\n\n";

echo "=== COMO ALTERNAR NO CÓDIGO ===\n";
echo "No NotificationComponent.php e OrdersController.php:\n";
echo "Altere: new Mailer('default') para new Mailer('ssl')\n\n";

// Verificar qual configuração está sendo usada atualmente
echo "=== CONFIGURAÇÃO ATUAL NO CÓDIGO ===\n";
$notificationFile = dirname(__DIR__) . '/src/Controller/Component/NotificationComponent.php';
$ordersFile = dirname(__DIR__) . '/src/Controller/OrdersController.php';

if (file_exists($notificationFile)) {
    $content = file_get_contents($notificationFile);
    if (strpos($content, "new Mailer('ssl')") !== false) {
        echo "NotificationComponent: Usando configuração SSL ✅\n";
    } elseif (strpos($content, "new Mailer('default')") !== false) {
        echo "NotificationComponent: Usando configuração TLS ⚠️\n";
    } else {
        echo "NotificationComponent: Configuração não identificada ❓\n";
    }
}

if (file_exists($ordersFile)) {
    $content = file_get_contents($ordersFile);
    if (strpos($content, "new Mailer('ssl')") !== false) {
        echo "OrdersController: Usando configuração SSL ✅\n";
    } elseif (strpos($content, "new Mailer('default')") !== false) {
        echo "OrdersController: Usando configuração TLS ⚠️\n";
    } else {
        echo "OrdersController: Configuração não identificada ❓\n";
    }
}

echo "\n=== LOGS ===\n";
echo "Verifique os logs em: logs/error.log\n";
echo "Para mais detalhes sobre erros SMTP\n";
