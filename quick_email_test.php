<?php
require_once 'vendor/autoload.php';
require_once 'config/bootstrap.php';

use Cake\Mailer\Mailer;

echo "=== TESTE RÁPIDO DE EMAIL (CONFIGURAÇÃO LIMPA) ===\n";

try {
    echo "Testando configuração padrão simplificada...\n";
    $mailer = new Mailer('default');
    $result = $mailer
        ->setFrom(['joaomario3224@gmail.com' => 'Teste CGL'])
        ->setTo('joaomario3224@gmail.com')
        ->setSubject('Teste - CGL Conecta (Config Limpa)')
        ->setEmailFormat('html')
        ->deliver('<h2>Teste de Email</h2><p>Configuração limpa (sem contexto SSL) funcionando!</p><p>Data: ' . date('d/m/Y H:i:s') . '</p>');
    
    echo "✅ SUCESSO: Email enviado!\n";
    echo "A configuração limpa (sem contexto SSL) funcionou!\n";
    echo "Verifique sua caixa de entrada.\n";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "\nSe o erro persistir, pode ser:\n";
    echo "1. Problema com credenciais do Gmail\n";
    echo "2. Necessidade de senha de app (não senha normal)\n";
    echo "3. Firewall bloqueando porta 587\n";
    echo "4. Servidor não suporta STARTTLS adequadamente\n";
}
