<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * Sistema de Envio de Email com PHP Puro
 * Funciona de forma similar ao sistema nativo do CakePHP
 */
class PHPMailer
{
    private $host = 'mail-ssl.m9.network';
    private $port = 465;
    private $username = 'portal@cglinox.com.br';
    private $password = 'AkfnGkFy';
    private $from = 'portal@cglinox.com.br';
    private $fromName = 'CGL Inox';
    private $timeout = 30;
    private $debug = false;
    
    private $socket;
    private $boundary;
    
    public function __construct($debug = false)
    {
        $this->debug = $debug;
        $this->boundary = md5(uniqid('boundary_'));
    }
    
    /**
     * Configura remetente
     */
    public function setFrom($email, $name = null): self
    {
        $this->from = $email;
        if ($name) {
            $this->fromName = $name;
        }
        return $this;
    }
    
    /**
     * Envio simples de email
     */
    public function send($to, $subject, $message, $isHtml = true): bool
    {
        try {
            // Conecta ao servidor SMTP
            if (!$this->connect()) {
                return false;
            }
            
            // Autentica
            if (!$this->authenticate()) {
                $this->disconnect();
                return false;
            }
            
            // Envia o email
            $result = $this->sendMessage($to, $subject, $message, $isHtml);
            
            // Desconecta
            $this->disconnect();
            
            return $result;
            
        } catch (Exception $e) {
            $this->log("Erro no envio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envio avançado com múltiplos destinatários e anexos
     */
    public function sendAdvanced($config): bool
    {
        $to = $config['to'] ?? null;
        $cc = $config['cc'] ?? [];
        $bcc = $config['bcc'] ?? [];
        $subject = $config['subject'] ?? 'Sem assunto';
        $message = $config['message'] ?? '';
        $isHtml = $config['html'] ?? true;
        $attachments = $config['attachments'] ?? [];
        
        try {
            if (!$this->connect()) {
                return false;
            }
            
            if (!$this->authenticate()) {
                $this->disconnect();
                return false;
            }
            
            $result = $this->sendAdvancedMessage($to, $cc, $bcc, $subject, $message, $isHtml, $attachments);
            $this->disconnect();
            
            return $result;
            
        } catch (Exception $e) {
            $this->log("Erro no envio avançado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Conecta ao servidor SMTP
     */
    private function connect(): bool
    {
        $this->log("Conectando a {$this->host}:{$this->port}");
        
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'disable_compression' => true
            ]
        ]);
        
        $this->socket = @stream_socket_client(
            "ssl://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        if (!$this->socket) {
            $this->log("Erro de conexão: $errstr ($errno)");
            return false;
        }
        
        $response = $this->getResponse();
        $this->log("Resposta do servidor: $response");
        
        return strpos($response, '220') === 0;
    }
    
    /**
     * Autentica no servidor
     */
    private function authenticate(): bool
    {
        // EHLO
        if (!$this->sendCommand("EHLO {$this->host}")) {
            return false;
        }
        
        // AUTH LOGIN
        if (!$this->sendCommand("AUTH LOGIN")) {
            return false;
        }
        
        // Username
        if (!$this->sendCommand(base64_encode($this->username))) {
            return false;
        }
        
        // Password
        if (!$this->sendCommand(base64_encode($this->password))) {
            return false;
        }
        
        $this->log("Autenticação realizada com sucesso");
        return true;
    }
    
    /**
     * Envia mensagem simples
     */
    private function sendMessage($to, $subject, $message, $isHtml): bool
    {
        // MAIL FROM
        if (!$this->sendCommand("MAIL FROM:<{$this->from}>")) {
            return false;
        }
        
        // RCPT TO
        if (!$this->sendCommand("RCPT TO:<$to>")) {
            return false;
        }
        
        // DATA
        if (!$this->sendCommand("DATA")) {
            return false;
        }
        
        // Headers e mensagem
        $headers = $this->buildHeaders($to, $subject, $isHtml);
        $body = $isHtml ? $message : strip_tags($message);
        
        $email = $headers . "\r\n" . $body . "\r\n.";
        
        if (!$this->sendCommand($email)) {
            return false;
        }
        
        $this->log("Email enviado com sucesso para: $to");
        return true;
    }
    
    /**
     * Envia mensagem avançada
     */
    private function sendAdvancedMessage($to, $cc, $bcc, $subject, $message, $isHtml, $attachments): bool
    {
        // MAIL FROM
        if (!$this->sendCommand("MAIL FROM:<{$this->from}>")) {
            return false;
        }
        
        // RCPT TO - destinatários principais
        $recipients = is_array($to) ? $to : [$to];
        foreach ($recipients as $recipient) {
            if (!$this->sendCommand("RCPT TO:<$recipient>")) {
                return false;
            }
        }
        
        // RCPT TO - CC
        foreach ($cc as $recipient) {
            if (!$this->sendCommand("RCPT TO:<$recipient>")) {
                return false;
            }
        }
        
        // RCPT TO - BCC
        foreach ($bcc as $recipient) {
            if (!$this->sendCommand("RCPT TO:<$recipient>")) {
                return false;
            }
        }
        
        // DATA
        if (!$this->sendCommand("DATA")) {
            return false;
        }
        
        // Monta email com anexos
        $email = $this->buildAdvancedEmail($to, $cc, $subject, $message, $isHtml, $attachments);
        
        if (!$this->sendCommand($email . "\r\n.")) {
            return false;
        }
        
        $this->log("Email avançado enviado com sucesso");
        return true;
    }
    
    /**
     * Constrói headers básicos
     */
    private function buildHeaders($to, $subject, $isHtml): string
    {
        $headers = [];
        $headers[] = "From: {$this->fromName} <{$this->from}>";
        $headers[] = "To: $to";
        $headers[] = "Subject: $subject";
        $headers[] = "Date: " . date('r');
        $headers[] = "Message-ID: <" . md5(uniqid('msg_')) . "@{$this->host}>";
        
        if ($isHtml) {
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
            $headers[] = "Content-Transfer-Encoding: 8bit";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }
        
        return implode("\r\n", $headers);
    }
    
    /**
     * Constrói email avançado com anexos
     */
    private function buildAdvancedEmail($to, $cc, $subject, $message, $isHtml, $attachments): string
    {
        $email = [];
        
        // Headers básicos
        $email[] = "From: {$this->fromName} <{$this->from}>";
        $email[] = "To: " . (is_array($to) ? implode(', ', $to) : $to);
        
        if (!empty($cc)) {
            $email[] = "Cc: " . implode(', ', $cc);
        }
        
        $email[] = "Subject: $subject";
        $email[] = "Date: " . date('r');
        $email[] = "Message-ID: <" . md5(uniqid('msg_')) . "@{$this->host}>";
        $email[] = "MIME-Version: 1.0";
        
        if (empty($attachments)) {
            // Email simples
            if ($isHtml) {
                $email[] = "Content-Type: text/html; charset=UTF-8";
            } else {
                $email[] = "Content-Type: text/plain; charset=UTF-8";
            }
            $email[] = "";
            $email[] = $message;
        } else {
            // Email com anexos
            $email[] = "Content-Type: multipart/mixed; boundary=\"{$this->boundary}\"";
            $email[] = "";
            
            // Corpo da mensagem
            $email[] = "--{$this->boundary}";
            if ($isHtml) {
                $email[] = "Content-Type: text/html; charset=UTF-8";
            } else {
                $email[] = "Content-Type: text/plain; charset=UTF-8";
            }
            $email[] = "";
            $email[] = $message;
            
            // Anexos
            foreach ($attachments as $attachment) {
                $email[] = "--{$this->boundary}";
                $email[] = "Content-Type: application/octet-stream";
                $email[] = "Content-Disposition: attachment; filename=\"" . basename($attachment) . "\"";
                $email[] = "Content-Transfer-Encoding: base64";
                $email[] = "";
                
                if (file_exists($attachment)) {
                    $email[] = chunk_split(base64_encode(file_get_contents($attachment)));
                }
            }
            
            $email[] = "--{$this->boundary}--";
        }
        
        return implode("\r\n", $email);
    }
    
    /**
     * Envia comando SMTP
     */
    private function sendCommand($command): bool
    {
        if (!$this->socket) {
            return false;
        }
        
        $this->log(">> $command");
        
        fwrite($this->socket, $command . "\r\n");
        $response = $this->getResponse();
        
        $this->log("<< $response");
        
        // Verifica códigos de sucesso SMTP
        $successCodes = ['220', '221', '235', '250', '334', '354'];
        $code = substr($response, 0, 3);
        
        return in_array($code, $successCodes);
    }
    
    /**
     * Obtém resposta do servidor
     */
    private function getResponse(): string
    {
        if (!$this->socket) {
            return '';
        }
        
        $response = '';
        while ($line = fgets($this->socket, 512)) {
            $response .= trim($line);
            // Para comando multi-linha SMTP
            if (strlen($line) > 3 && $line[3] == ' ') {
                break;
            } else {
                $response .= ' ';
            }
        }
        return trim($response);
    }
    
    /**
     * Desconecta do servidor
     */
    private function disconnect(): void
    {
        if ($this->socket) {
            $this->sendCommand("QUIT");
            fclose($this->socket);
            $this->socket = null;
            $this->log("Desconectado do servidor");
        }
    }
    
    /**
     * Log de debug
     */
    private function log($message): void
    {
        if ($this->debug) {
            echo "[" . date('H:i:s') . "] $message\n";
        }
    }
    
    /**
     * Configurações personalizadas
     */
    public function setConfig($config): self
    {
        if (isset($config['host'])) $this->host = $config['host'];
        if (isset($config['port'])) $this->port = $config['port'];
        if (isset($config['username'])) $this->username = $config['username'];
        if (isset($config['password'])) $this->password = $config['password'];
        if (isset($config['timeout'])) $this->timeout = $config['timeout'];
        
        return $this;
    }
}
