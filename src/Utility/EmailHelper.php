<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * Helper para facilitar o uso do PHPMailer
 */
class EmailHelper
{
    /**
     * Envio rápido de email
     */
    public static function sendQuick($to, $subject, $message, $isHtml = true, $debug = false): bool
    {
        $mailer = new PHPMailer($debug);
        return $mailer->send($to, $subject, $message, $isHtml);
    }
    
    /**
     * Envio com remetente personalizado
     */
    public static function sendFrom($from, $fromName, $to, $subject, $message, $isHtml = true, $debug = false): bool
    {
        $mailer = new PHPMailer($debug);
        $mailer->setFrom($from, $fromName);
        return $mailer->send($to, $subject, $message, $isHtml);
    }
    
    /**
     * Envio com múltiplos destinatários
     */
    public static function sendMultiple($recipients, $subject, $message, $isHtml = true, $debug = false): bool
    {
        $mailer = new PHPMailer($debug);
        
        $config = [
            'to' => $recipients,
            'subject' => $subject,
            'message' => $message,
            'html' => $isHtml
        ];
        
        return $mailer->sendAdvanced($config);
    }
    
    /**
     * Envio com anexos
     */
    public static function sendWithAttachments($to, $subject, $message, $attachments, $isHtml = true, $debug = false): bool
    {
        $mailer = new PHPMailer($debug);
        
        $config = [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'html' => $isHtml,
            'attachments' => $attachments
        ];
        
        return $mailer->sendAdvanced($config);
    }
    
    /**
     * Envio completo com todas as opções
     */
    public static function sendComplete($config, $debug = false): bool
    {
        $mailer = new PHPMailer($debug);
        
        // Configurações personalizadas do servidor
        if (isset($config['server'])) {
            $mailer->setConfig($config['server']);
        }
        
        // Remetente personalizado
        if (isset($config['from'])) {
            $mailer->setFrom($config['from'], $config['fromName'] ?? null);
        }
        
        return $mailer->sendAdvanced($config);
    }
    
    /**
     * Template de email de notificação
     */
    public static function sendNotification($to, $title, $content, $debug = false): bool
    {
        $template = self::getNotificationTemplate($title, $content);
        return self::sendQuick($to, $title, $template, true, $debug);
    }
    
    /**
     * Template HTML para notificações
     */
    private static function getNotificationTemplate($title, $content): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>$title</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { background: #f8f9fa; padding: 20px; border-left: 4px solid #007bff; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>$title</h2>
                </div>
                <div class='content'>
                    $content
                </div>
                <div class='footer'>
                    <p>Email enviado automaticamente pelo sistema CGL Inox</p>
                    <p>Data: " . date('d/m/Y H:i:s') . "</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
