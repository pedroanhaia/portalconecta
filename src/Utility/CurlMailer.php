<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * Alternative PHPMailer usando cURL em vez de stream sockets
 * Funciona mesmo quando a extensão sockets não está habilitada
 */
class CurlMailer
{
    private $host = 'mail-ssl.m9.network';
    private $port = 465;
    private $username = 'portal@cglinox.com.br';
    private $password = 'AkfnGkFy';
    private $from = 'portal@cglinox.com.br';
    private $fromName = 'CGL Inox';
    private $timeout = 30;
    private $debug = false;
    
    public function __construct($debug = false)
    {
        $this->debug = $debug;
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
     * Envio usando cURL SMTP
     */
    public function send($to, $subject, $message, $isHtml = true): bool
    {
        if (!extension_loaded('curl')) {
            $this->log('❌ Extensão cURL não disponível');
            return false;
        }
        
        try {
            $this->log("Enviando email via cURL SMTP");
            
            // Prepara os dados do email
            $headers = $this->buildHeaders($to, $subject, $isHtml);
            $body = $isHtml ? $message : strip_tags($message);
            $emailData = $headers . "\r\n" . $body;
            
            // Configura cURL
            $curl = curl_init();
            
            $url = "smtps://{$this->host}:{$this->port}";
            $this->log("Conectando via: $url");
            
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_USE_SSL => CURLUSESSL_ALL,
                CURLOPT_USERNAME => $this->username,
                CURLOPT_PASSWORD => $this->password,
                CURLOPT_MAIL_FROM => $this->from,
                CURLOPT_MAIL_RCPT => [$to],
                CURLOPT_READDATA => $emailData,
                CURLOPT_UPLOAD => true,
                CURLOPT_READFUNCTION => function($ch, $fd, $length) use ($emailData) {
                    static $position = 0;
                    $data = substr($emailData, $position, $length);
                    $position += strlen($data);
                    return $data;
                },
                CURLOPT_VERBOSE => $this->debug,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_RETURNTRANSFER => true
            ]);
            
            $result = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            
            curl_close($curl);
            
            if ($result === false || !empty($error)) {
                $this->log("❌ Erro cURL: $error");
                return false;
            }
            
            $this->log("✅ Email enviado via cURL com sucesso");
            return true;
            
        } catch (Exception $e) {
            $this->log("❌ Exceção: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Constrói headers do email
     */
    private function buildHeaders($to, $subject, $isHtml): string
    {
        $headers = [];
        $headers[] = "From: {$this->fromName} <{$this->from}>";
        $headers[] = "To: $to";
        $headers[] = "Subject: $subject";
        $headers[] = "Date: " . date('r');
        $headers[] = "Message-ID: <" . md5(uniqid('curl_')) . "@{$this->host}>";
        
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
