<?php
/**
 * TESTE DO SISTEMA COM FALLBACK cURL
 */

if (!defined('ROOT')) define('ROOT', dirname(__DIR__));
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once 'vendor/autoload.php';
require_once 'src/Utility/PHPMailer.php';
require_once 'src/Utility/CurlMailer.php';
require_once 'src/Utility/EmailHelper.php';

use App\Utility\EmailHelper;
use App\Utility\CurlMailer;

echo "=== TESTE SISTEMA COM FALLBACK cURL ===\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n\n";

// Verificar extensões
echo "1. VERIFICAÇÃO DE EXTENSÕES:\n";
echo "- OpenSSL: " . (extension_loaded('openssl') ? "✅ OK" : "❌ FALTA") . "\n";
echo "- cURL: " . (extension_loaded('curl') ? "✅ OK" : "❌ FALTA") . "\n";
echo "- Sockets: " . (extension_loaded('sockets') ? "✅ OK" : "❌ FALTA") . "\n";
echo "- SSL transport: " . (in_array('ssl', stream_get_transports()) ? "✅ OK" : "❌ FALTA") . "\n\n";

// Teste direto com cURL
echo "2. TESTE DIRETO COM cURL:\n";
if (extension_loaded('curl')) {
    try {
        $curlMailer = new CurlMailer(true);
        $resultado = $curlMailer->send(
            'teste@cglinox.com.br',
            'Teste cURL Direto - ' . date('H:i:s'),
            '<h2>Teste cURL</h2><p>Email enviado usando cURL SMTP.</p>',
            true
        );
        echo "Resultado cURL direto: " . ($resultado ? "✅ SUCESSO" : "❌ FALHOU") . "\n\n";
    } catch (Exception $e) {
        echo "❌ Erro cURL direto: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "❌ cURL não disponível\n\n";
}

// Teste com EmailHelper (detecção automática)
echo "3. TESTE COM EMAILHELPER (AUTO-DETECÇÃO):\n";
try {
    $resultado2 = EmailHelper::sendQuick(
        'teste@cglinox.com.br',
        'Teste EmailHelper Auto - ' . date('H:i:s'),
        '<h2>Auto-Detecção</h2><p>Sistema escolheu automaticamente o melhor método.</p>',
        true,
        true // debug ativo
    );
    echo "\nResultado EmailHelper: " . ($resultado2 ? "✅ SUCESSO" : "❌ FALHOU") . "\n\n";
} catch (Exception $e) {
    echo "❌ Erro EmailHelper: " . $e->getMessage() . "\n\n";
}

// Teste específico para produção
echo "4. SIMULAÇÃO AMBIENTE PRODUÇÃO:\n";
echo "Forçando uso de cURL (como se SSL não estivesse disponível)\n";

if (class_exists('App\\Utility\\CurlMailer')) {
    $prodMailer = new CurlMailer(false); // sem debug para produção
    $resultadoProd = $prodMailer->send(
        'pedroanhaia@gmail.com',
        'Sistema Produção - CGL Conecta',
        '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: #28a745; color: white; padding: 20px; text-align: center;">
                <h2>Sistema Funcionando em Produção</h2>
            </div>
            <div style="padding: 20px; background: #f8f9fa;">
                <p>O sistema de email está funcionando corretamente usando fallback cURL.</p>
                <ul>
                    <li>✅ Compatível com servidores sem extensão sockets</li>
                    <li>✅ Funciona com SSL/TLS</li>
                    <li>✅ Autenticação SMTP funcionando</li>
                    <li>✅ Templates HTML suportados</li>
                </ul>
            </div>
        </div>',
        true
    );
    
    echo "Resultado produção: " . ($resultadoProd ? "✅ PRONTO PARA PRODUÇÃO" : "❌ REQUER AJUSTES") . "\n";
    
    if ($resultadoProd) {
        echo "\n🎉 SISTEMA TOTALMENTE COMPATÍVEL!\n";
        echo "Pode ser usado em qualquer servidor, com ou sem extensão sockets.\n";
    }
} else {
    echo "❌ Classe CurlMailer não encontrada\n";
}

echo "\n=== INSTRUÇÕES PARA HABILITAR SSL NO PHP ===\n";
echo "Se quiser habilitar SSL completo (não é obrigatório, cURL funciona):\n\n";

echo "1. Windows (XAMPP/WAMP):\n";
echo "   - Abra: C:\\xampp\\php\\php.ini\n";
echo "   - Descomente: extension=openssl\n";
echo "   - Descomente: extension=sockets\n";
echo "   - Reinicie Apache\n\n";

echo "2. Linux (Ubuntu/CentOS):\n";
echo "   sudo apt-get install php-openssl php-sockets\n";
echo "   ou: sudo yum install php-openssl php-sockets\n\n";

echo "3. Docker:\n";
echo "   RUN docker-php-ext-install sockets\n";
echo "   RUN docker-php-ext-install openssl\n\n";

echo "=== FIM DO TESTE ===\n";
