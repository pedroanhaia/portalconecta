<?php
/**
 * DIAGNÓSTICO COMPLETO DE EMAIL
 */

// Define constantes
if (!defined('ROOT')) define('ROOT', __DIR__);
require_once 'src/Utility/PHPMailer.php';

use App\Utility\PHPMailer;

echo "=== DIAGNÓSTICO COMPLETO DE EMAIL ===\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n\n";

$mailer = new PHPMailer(true);

// Teste 1: Verificar se consegue enviar email completo
echo "1. TESTE DE ENVIO COMPLETO COM DEBUG:\n";
echo "Destinatário: portal@cglinox.com.br (mesmo domínio)\n\n";

$resultado = $mailer->send(
    'portal@cglinox.com.br', // mesmo email do remetente para teste
    'Teste Diagnóstico - ' . date('H:i:s'),
    'Este é um teste de diagnóstico do sistema de email.',
    false // texto simples
);

echo "\nResultado do envio: " . ($resultado ? "✅ SUCESSO" : "❌ FALHOU") . "\n\n";

// Teste 2: Vamos testar com um email externo real
echo "2. TESTE COM EMAIL EXTERNO:\n";
echo "Digite um email válido para teste (ex: seu gmail): ";
$handle = fopen("php://stdin", "r");
$emailTeste = trim(fgets($handle));
fclose($handle);

if (!empty($emailTeste) && filter_var($emailTeste, FILTER_VALIDATE_EMAIL)) {
    echo "Enviando para: $emailTeste\n\n";
    
    $resultado2 = $mailer->send(
        $emailTeste,
        'Teste Sistema CGL Inox - ' . date('H:i:s'),
        '<h2>🎯 Teste de Email</h2>
         <p>Se você recebeu este email, o sistema está funcionando perfeitamente!</p>
         <p><strong>Informações do teste:</strong></p>
         <ul>
            <li>Data/Hora: ' . date('d/m/Y H:i:s') . '</li>
            <li>Sistema: CGL Inox Email PHP</li>
            <li>Servidor: mail-ssl.m9.network:465</li>
            <li>Remetente: portal@cglinox.com.br</li>
         </ul>
         <p>Por favor, confirme o recebimento respondendo este email.</p>',
        true
    );
    
    echo "\nResultado para $emailTeste: " . ($resultado2 ? "✅ ENVIADO" : "❌ FALHOU") . "\n";
    
    if ($resultado2) {
        echo "\n📧 EMAIL ENVIADO COM SUCESSO!\n";
        echo "Verifique sua caixa de entrada (e spam/lixeira).\n";
        echo "Se não receber em 5 minutos, pode ser problema de:\n";
        echo "- Filtro de spam\n";
        echo "- Configuração do servidor de destino\n";
        echo "- Reputação do IP do servidor\n";
    }
} else {
    echo "Email inválido ou não informado.\n";
}

echo "\n=== VERIFICAÇÕES ADICIONAIS ===\n";

// Teste 3: Verificar configuração do servidor
echo "3. TESTANDO DIFERENTES CONFIGURAÇÕES:\n\n";

// Teste com TLS em vez de SSL
echo "Testando com TLS (porta 587)...\n";
$mailerTLS = new PHPMailer(false);
$mailerTLS->setConfig([
    'host' => 'mail-ssl.m9.network',
    'port' => 587,
    'username' => 'portal@cglinox.com.br',
    'password' => 'AkfnGkFy'
]);

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

echo "Testando porta 587 (STARTTLS)...\n";
$socket587 = @stream_socket_client(
    "tcp://mail-ssl.m9.network:587",
    $errno,
    $errstr,
    10,
    STREAM_CLIENT_CONNECT,
    $context
);

if ($socket587) {
    echo "✅ Porta 587 acessível\n";
    fclose($socket587);
} else {
    echo "❌ Porta 587 não acessível: $errstr\n";
}

echo "\n4. TESTANDO DIFERENTES EMAILS DE DESTINO:\n";

// Testa alguns provedores comuns
$emailsComuns = [
    'gmail.com' => 'Gmail',
    'hotmail.com' => 'Hotmail',
    'outlook.com' => 'Outlook',
    'yahoo.com' => 'Yahoo'
];

foreach ($emailsComuns as $dominio => $nome) {
    $mx = [];
    if (getmxrr($dominio, $mx)) {
        echo "✅ $nome ($dominio) - MX: " . $mx[0] . "\n";
    } else {
        echo "❌ $nome ($dominio) - Sem MX\n";
    }
}

echo "\n=== SUGESTÕES PARA MELHORAR ENTREGABILIDADE ===\n";
echo "1. ✅ Usar SMTP autenticado (já implementado)\n";
echo "2. ⚠️  Configurar SPF no DNS do domínio cglinox.com.br\n";
echo "3. ⚠️  Configurar DKIM para assinatura digital\n";
echo "4. ⚠️  Verificar reputação do IP mail-ssl.m9.network\n";
echo "5. ✅ Usar remetente válido (portal@cglinox.com.br)\n";
echo "6. ⚠️  Testar com diferentes provedores de email\n";

echo "\n=== PRÓXIMOS PASSOS ===\n";
echo "1. Verifique spam/lixeira no email de destino\n";
echo "2. Teste com diferentes emails (Gmail, Hotmail, etc)\n";
echo "3. Aguarde até 10 minutos para entrega\n";
echo "4. Se necessário, configure SPF/DKIM no domínio\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
