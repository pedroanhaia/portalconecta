<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;

class NotificationComponent extends Component
{
	public function initialize(array $config): void
	{
		parent::initialize($config);
		$this->getController()->loadModel('Orders');
		$this->getController()->loadModel('Users'); 
		$this->getController()->loadModel('OrdersUsers');
	}

	/**
	 * Enviar notificações para usuários associados ao pedido
	 */
	public function sendNotificationToOrderUsers($orderId, $type, $data = [])
	{
		try {
			// Buscar o pedido com suas configurações
			$order = $this->getController()->Orders->find()
				->contain(['Customers', 'Users'])
				->where(['Orders.id' => $orderId])
				->first();

			if (!$order) {
				$this->log('Pedido não encontrado para notificação: ' . $orderId, 'error');
				return false;
			}

			// Verificar o tipo de notificação configurado
			$notificationType = $order->not_type ?? C_PedidosNotificacaoTodas;

			// Determinar se deve enviar notificação baseado no tipo
			$shouldNotify = false;
			switch ($notificationType) {
				case C_PedidosNotificacaoTodas:
					$shouldNotify = true;
					break;
				case C_PedidosNotificacaoArquivos:
					$shouldNotify = ($type === 'file');
					break;
				case C_PedidosNotificacaoComentarios:
					$shouldNotify = ($type === 'comment');
					break;
				case C_PedidosNotificacaoNenhuma:
					$shouldNotify = false;
					break;
				default:
					$shouldNotify = true; // Por padrão, enviar todas
			}

			if (!$shouldNotify) {
				return true; // Não é erro, apenas não deve notificar
			}

			// Buscar usuários associados ao pedido
			if (empty($order->users)) {
				$this->log('Nenhum usuário associado ao pedido ' . $orderId, 'info');
				return true;
			}

			$emailsSent = 0;
			foreach ($order->users as $user) {
				if (!empty($user->email)) {
					$subject = $this->buildEmailSubject($type, $order, $data);
					$message = $this->buildEmailMessage($type, $order, $user, $data);
					
					if ($this->sendEmail($user->email, $subject, $message)) {
						$emailsSent++;
					}
				}
			}

			$this->log("Notificações enviadas: {$emailsSent} de " . count($order->users) . " usuários", 'info');
			return $emailsSent > 0;

		} catch (\Exception $e) {
			$this->log('Erro ao enviar notificações: ' . $e->getMessage(), 'error');
			return false;
		}
	}

	/**
	 * Construir assunto do email
	 */
	private function buildEmailSubject($type, $order, $data)
	{
		$orderNumber = $order->number ?? $order->id;
		
		switch ($type) {
			case 'status':
				$newStatus = PedidosStatus($data['new_status'] ?? 0);
				return "CGL Conecta - Pedido #{$orderNumber} - Status: {$newStatus}";
				
			case 'comment':
				return "CGL Conecta - Pedido #{$orderNumber} - Novo Comentário";
				
			case 'file':
				return "CGL Conecta - Pedido #{$orderNumber} - Arquivos Atualizados";
				
			default:
				return "CGL Conecta - Pedido #{$orderNumber} - Atualização";
		}
	}

	/**
	 * Construir corpo do email
	 */
	private function buildEmailMessage($type, $order, $user, $data)
	{
		$orderNumber = $order->number ?? $order->id;
		$customerName = $order->customer->name ?? 'Cliente';
		$userName = $user->name ?? 'Usuário';
		
		$baseMessage = "
			<h2>CGL Conecta - Notificação de Pedido</h2>
			<p>Olá <strong>{$userName}</strong>,</p>
			<p>Há uma atualização no pedido <strong>#{$orderNumber}</strong> da empresa <strong>{$customerName}</strong>.</p>
			<hr>
		";

		switch ($type) {
			case 'status':
				$oldStatus = PedidosStatus($data['old_status'] ?? 0);
				$newStatus = PedidosStatus($data['new_status'] ?? 0);
				$changedBy = $data['user_name'] ?? 'Usuário';
				
				$message = $baseMessage . "
					<h3>Mudança de Status</h3>
					<p><strong>Status anterior:</strong> {$oldStatus}</p>
					<p><strong>Novo status:</strong> {$newStatus}</p>
					<p><strong>Alterado por:</strong> {$changedBy}</p>
				";
				
				if (!empty($data['comment'])) {
					$message .= "<p><strong>Comentário:</strong> " . htmlspecialchars($data['comment']) . "</p>";
				}
				break;
				
			case 'comment':
				$commentBy = $data['user_name'] ?? 'Usuário';
				$comment = htmlspecialchars($data['message'] ?? '');
				
				$message = $baseMessage . "
					<h3>Novo Comentário</h3>
					<p><strong>Comentário de:</strong> {$commentBy}</p>
					<div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0;'>
						{$comment}
					</div>
				";
				break;
				
			case 'file':
				$message = $baseMessage . "
					<h3>Arquivos Atualizados</h3>
					<p>Os arquivos do pedido foram atualizados. Acesse o sistema para visualizar as alterações.</p>
				";
				break;
				
			default:
				$message = $baseMessage . "
					<h3>Atualização</h3>
					<p>O pedido foi atualizado. Acesse o sistema para mais detalhes.</p>
				";
		}

		$message .= "
			<hr>
			<p>Para acessar o pedido, <a href='" . $this->buildOrderUrl($order->id) . "'>clique aqui</a>.</p>
			<p><small>Esta é uma mensagem automática do sistema CGL Conecta.</small></p>
		";

		return $message;
	}

	/**
	 * Construir URL do pedido
	 */
	private function buildOrderUrl($orderId)
	{
		// Você pode ajustar a URL base conforme sua configuração
		$url = Router::url(['controller' => 'Orders', 'action' => 'view', $orderId], true);
		return $url;
	}

	/**
	 * Enviar email
	 */
	private function sendEmail($to, $subject, $message)
	{
		try {
			$mailer = new Mailer('default');

			$mailer
				->setFrom(['joaomario3224@gmail.com' => 'CGL Conecta'])
				->setTo($to)
				->setSubject($subject)
				->setEmailFormat('html')
				->deliver($message);
			
			return true;
		} catch (\Exception $e) {
			$this->log('Erro ao enviar email para ' . $to . ': ' . $e->getMessage(), 'error');
			return false;
		}
	}
}
