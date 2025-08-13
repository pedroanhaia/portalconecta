<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Mailer\Mailer;
use Cake\Mailer\MailerAwareTrait;

class OrdersController extends AppController
{
	use MailerAwareTrait;

	public function initialize(): void
	{
		parent::initialize();
		$this->loadComponent('Notification');
	}

	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);

		$this->loadModel('Customers');
		$this->loadModel('OrdersLog');
		$this->loadModel('Comments');
		$this->loadModel('Config');
		$this->loadModel('FilesLog');
		$this->loadModel('Users');
		$this->loadModel('OrdersUsers');

		$userLogado = $this->Authentication->getIdentity();
		$currentAction = $this->request->getParam('action');

		if(!$userLogado->admin) {
			if(in_array($currentAction, ['add', 'edit', 'changeStatus', 'delete'])) { 
				$this->Flash->error(__('Você não tem permissão para acessar esta página.'));
				return $this->redirect(['action' => 'index']);
			}
		}
	}

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
			debug('Erro ao enviar email: ' . $e->getMessage());
			$this->log('Erro ao enviar email para ' . $to . ': ' . $e->getMessage(), 'error');
			return false;
		}
	}

	public function testeemail()
	{
		// Variáveis para teste - você pode modificar aqui
		$emailTeste = 'joaomario3224@gmail.com';
		$assunto = 'Teste de Email - CGL Conecta';
		
		// Dados do pedido fictício para teste
		$numeroPedido = '12345';
		$statusTeste = 'Em Projeto';
		
		// Mensagem de teste mais elaborada
		$mensagemCompleta = "
			<h2>Teste de Email - CGL Conecta</h2>
			<p>Este é um email de teste do sistema.</p>
			<hr>
			<p><strong>Dados do teste:</strong></p>
			<ul>
				<li>Pedido: #{$numeroPedido}</li>
				<li>Status: {$statusTeste}</li>
				<li>Data/Hora: " . date('d/m/Y H:i:s') . "</li>
			</ul>
			<p>Se você recebeu este email, a configuração está funcionando corretamente!</p>
		";

		// Tentar enviar o email
		$resultado = $this->sendEmail($emailTeste, $assunto, $mensagemCompleta);
		
		if ($resultado) {
		   	debug('Email de teste enviado com sucesso para: ' . $emailTeste);
		} else {
		   	debug('Erro ao enviar email de teste para: ' . $emailTeste);
		}

		// Retornar resposta JSON se for requisição AJAX
		if ($this->request->is('ajax')) {
			return $this->response->withType('application/json')->withStringBody(json_encode([
				'success' => $resultado,
				'message' => $resultado ? 'Email enviado com sucesso!' : 'Erro ao enviar email',
				'email' => $emailTeste
			]));
		}

		die;
	}

	public function index()
	{
		$userLogado = $this->Authentication->getIdentity();

		// Iniciar a query base
		$query = $this->Orders->find('all')
			->contain(['Customers'])
			->order(['number' => 'DESC']);

		// Aplicar filtro de cliente se não for admin
		if(!$userLogado->admin) {
			$query->where(['Orders.id_customer' => $userLogado->id_customer]);
		}

		// Verificar se há busca por ordem de compra
		$ordemCompra = $this->request->getQuery('ordem_compra');
		$isOrdemCompraSearch = !empty($ordemCompra);

		if ($isOrdemCompraSearch) {
			// Se há busca por ordem de compra, usar apenas esse filtro
			$query->where(['Orders.ordem_compra LIKE' => '%' . $ordemCompra . '%']);
			$dataInicio = 	null;
			$dataFim = 		null;
			$status = 		null;
			$clienteId = 		null;
		} else {
			// Filtros normais apenas se não houver busca por ordem de compra
			
			// Filtros de data - definir período padrão de 30 dias se não houver filtros
			$dataInicio = $this->request->getQuery('data_inicio');
			$dataFim = $this->request->getQuery('data_fim');
			
			// Se não há filtros de data, definir os últimos 30 dias
			if (empty($dataInicio) && empty($dataFim)) {
				$dataInicio = date('Y-m-d', strtotime('-30 days'));
				$dataFim = date('Y-m-d');
			}

			if (!empty($dataInicio)) {
				$dataInicioFormatted = date('Y-m-d 00:00:00', strtotime($dataInicio));
				$query->where(['Orders.created >=' => $dataInicioFormatted]);
			}

			if (!empty($dataFim)) {
				$dataFimFormatted = date('Y-m-d 23:59:59', strtotime($dataFim));
				$query->where(['Orders.created <=' => $dataFimFormatted]);
			}

			// Filtro por status
			$status = $this->request->getQuery('status');
			if (!empty($status) && $status !== 'todos') {
				$query->where(['Orders.status' => $status]);
			}

			// Filtro por cliente (apenas para admin)
			$clienteId = $this->request->getQuery('cliente');
			if (!empty($clienteId) && $clienteId !== 'todos' && $userLogado->admin) {
				$query->where(['Orders.id_customer' => $clienteId]);
			}
		}

		$orders = $query->toArray();

		// Preparar dados para os filtros
		$customers = [];
		if($userLogado->admin) {
			$customers = $this->Customers->find('all')
				->select(['id', 'name', 'cnpj'])
				->order(['name' => 'ASC'])
				->toArray();
		}

		// Status disponíveis
		$statusOptions = [
			'todos' => 'Todos os Status',
			C_PedidosStatusRecebido => 'Recebido',
			C_PedidosStatusEmProjeto => 'Em Projeto',
			C_PedidosStatusEmFabricação => 'Em Fabricação',
			C_PedidosStatusEmTransporte => 'Em Transporte',
			C_PedidosStatusEntregue => 'Entregue'
		];

		$this->set(compact('orders', 'customers', 'statusOptions', 'dataInicio', 'dataFim', 'status', 'clienteId', 'ordemCompra', 'isOrdemCompraSearch'));
	}

	public function add()
	{
		$order = $this->Orders->newEmptyEntity();

		if ($this->request->is('post')) {
			$requestData = $this->request->getData();
			
			// Extrair user_ids para processamento separado
			$userIds = [];
			if (!empty($requestData['user_ids'])) {
				$userIds = $requestData['user_ids'];
				unset($requestData['user_ids']); // Remove do array principal
			}
			
			$order = $this->Orders->patchEntity($order, $requestData);

			if ($this->Orders->save($order)) {
				// Salvar relacionamentos com usuários
				if (!empty($userIds)) {
					$this->saveOrderUsers($order->id, $userIds);
				}
				
				$this->Flash->success(__('Pedido cadastrado com sucesso.'));
				return $this->redirect(['action' => 'edit', $order->id]);
			}

			$this->Flash->error(__('Não foi possível cadastrar o pedido. Por favor, tente novamente.'));
		}

		$customers = $this->Customers->find('all')->all();

		$customersOpt = $customers->combine(
			'id',
			function ($row) {
				return "{$row->cnpj} - {$row->name}";
			}
		)->toArray();
		
		$this->set(compact('order', 'customersOpt'));
	}

	public function view($id = null)
	{
		$userLogado = $this->Authentication->getIdentity();
		$isAdmin = $userLogado->admin == 1;
		
		$order = $this->Orders->findById($id)
			->contain([
				'Customers' => ['fields' => ['name']],
				'OrdersLog',
				'OrdersLog.Users',
				'OrdersLog.Comments',
				'OrdersLog.Comments.Users'
			])
		->first();

		$newLog = [];
		$allComments = [];

		// Busca comentários sem status (comentários gerais)
		$comentariosSemStatus = $this->Comments->find()
			->where(['Comments.id_order' => $id, 'Comments.refcomment IS' => null])
			->contain(['Users'])
			->order(['Comments.created' => 'DESC'])
		->toArray();

		// Comentários sem status
		foreach ($comentariosSemStatus as $comentario) {
			$allComments[] = [
				'autor' => $comentario->user->name ?? 'Desconhecido',
				'comentario' => $comentario->message,
				'data' => (new \Cake\I18n\FrozenTime($comentario->created))->i18nFormat('dd/MM/yyyy - HH:mm'),
				'status' => null
			];
		}

		// Busca comentários com status (referenciando OrdersLog) e usa a data original do comentário
		$comentariosComStatus = $this->Comments->find()
			->where(['Comments.id_order' => $id, 'Comments.refcomment IS NOT' => null])
			->contain(['Users', 'OrdersLog'])
			->order(['Comments.created' => 'DESC'])
		->toArray();

		// Comentários com status - usando a data real do comentário
		foreach ($comentariosComStatus as $comentario) {
			$allComments[] = [
				'autor' => $comentario->user->name ?? 'Desconhecido',
				'comentario' => $comentario->message,
				'data' => (new \Cake\I18n\FrozenTime($comentario->created))->i18nFormat('dd/MM/yyyy - HH:mm'),
				'status' => $comentario->orders_log->new_status ?? null
			];
		}

		// Monta o histórico de logs (sem duplicar comentários)
		foreach ($order->orders_log as $entry) {
			$mensagens = [];
			foreach ($entry->comments as $c) {
				$mensagens[] = [
					'comentario' => $c->message,
					'autor' => $c->user->name ?? 'Desconhecido'
				];
			}
			$newLog[] = [
				'id' => $entry->id,
				'status' => $entry->new_status,
				'data' => (new \Cake\I18n\FrozenTime($entry->modified))->i18nFormat('dd/MM/yyyy - HH:mm'),
				'user' => [
					'name' => $entry->user->name ?? 'Desconhecido'
				],
				'mensagens' => $mensagens
			];
		}

		// Ordenar todos os comentários por data (mais recentes primeiro)
		usort($allComments, function($a, $b) {
			// Converter datas para timestamp para comparação precisa
			$timeA = strtotime(str_replace('/', '-', $a['data']));
			$timeB = strtotime(str_replace('/', '-', $b['data']));
			
			// Se uma das datas for inválida, colocar no final
			if ($timeA === false) $timeA = 0;
			if ($timeB === false) $timeB = 0;
			
			// Ordem decrescente (mais recentes primeiro)
			return $timeB - $timeA;
		});

		$customers = $this->Customers->find('all')->all();
		$config = $this->Config->get(1);

		$customersOpt = $customers->combine(
			'id',
			function ($row) {
				return "{$row->cnpj} - {$row->name}";
			}
		)->toArray();

		$this->set('order', $order);
		$this->set('newLog', $newLog);
		$this->set('config', $config);
		$this->set('customersOpt', $customersOpt);
		$this->set('allComments', $allComments);
		$this->set('isAdmin', $isAdmin);
	}

	public function edit($id = null)
	{
		$userLogado = $this->Authentication->getIdentity();
		$isAdmin = $userLogado->admin == 1;
		
		$order = $this->Orders->findById($id)
			->contain([
				'Customers' => ['fields' => ['name']],
				'OrdersLog',
				'OrdersLog.Users',
				'OrdersLog.Comments',
				'OrdersLog.Comments.Users',
				'Users' // Incluir usuários associados
			])
		->first();

		$newLog = [];
		$allComments = [];

		// Busca comentários sem status (comentários gerais)
		$comentariosSemStatus = $this->Comments->find()
			->where(['Comments.id_order' => $id, 'Comments.refcomment IS' => null])
			->contain(['Users'])
			->order(['Comments.created' => 'DESC'])
		->toArray();

		// Comentários sem status
		foreach ($comentariosSemStatus as $comentario) {
			$allComments[] = [
				'autor' => $comentario->user->name ?? 'Desconhecido',
				'comentario' => $comentario->message,
				'data' => (new \Cake\I18n\FrozenTime($comentario->created))->i18nFormat('dd/MM/yyyy - HH:mm'),
				'status' => null
			];
		}

		// Comentários sem status
		$comentariosComStatus = $this->Comments->find()
			->where(['Comments.id_order' => $id, 'Comments.refcomment IS NOT' => null])
			->contain(['Users', 'OrdersLog'])
			->order(['Comments.created' => 'DESC'])
		->toArray();

		// Comentários com status - usando a data real do comentário
		foreach ($comentariosComStatus as $comentario) {
			$allComments[] = [
				'autor' => $comentario->user->name ?? 'Desconhecido',
				'comentario' => $comentario->message,
				'data' => (new \Cake\I18n\FrozenTime($comentario->created))->i18nFormat('dd/MM/yyyy - HH:mm'),
				'status' => $comentario->orders_log->new_status ?? null
			];
		}

		// Monta o histórico de logs
		foreach ($order->orders_log as $entry) {
			$mensagens = [];
			foreach ($entry->comments as $c) {
				$mensagens[] = [
					'comentario' => $c->message,
					'autor' => $c->user->name ?? 'Desconhecido'
				];
			}
			$newLog[] = [
				'id' => $entry->id,
				'status' => $entry->new_status,
				'data' => (new \Cake\I18n\FrozenTime($entry->modified))->i18nFormat('dd/MM/yyyy - HH:mm'),
				'user' => [
					'name' => $entry->user->name ?? 'Desconhecido'
				],
				'mensagens' => $mensagens
			];
		}

		// Ordenar todos os comentários por data (mais recentes primeiro)
		usort($allComments, function($a, $b) {
			// Converter datas para timestamp para comparação precisa
			$timeA = strtotime(str_replace('/', '-', $a['data']));
			$timeB = strtotime(str_replace('/', '-', $b['data']));
			
			// Se uma das datas for inválida, colocar no final
			if ($timeA === false) $timeA = 0;
			if ($timeB === false) $timeB = 0;
			
			// Ordem decrescente (mais recentes primeiro)
			return $timeB - $timeA;
		});

		if ($this->request->is(['patch', 'post', 'put'])) {
			$requestData = $this->request->getData();
			
			// Extrair user_ids para processamento separado
			$userIds = [];
			if (!empty($requestData['user_ids'])) {
				$userIds = $requestData['user_ids'];
				unset($requestData['user_ids']); // Remove do array principal
			}
			
			$order = $this->Orders->patchEntity($order, $requestData);
			
			if ($this->Orders->save($order)) {
				// Atualizar relacionamentos com usuários
				if (isset($userIds)) {
					$this->updateOrderUsers($order->id, $userIds);
				}
				
				$this->Flash->success(__('Os dados foram salvos com sucesso.'));
				return $this->redirect(['action' => 'edit', $order->id]);
			}

			$this->Flash->error(__('Não foi possível cadastrar o pedido, tente novamente.'));
		}

		$customers = $this->Customers->find('all')->all();
		$config = $this->Config->get(1);

		$customersOpt = $customers->combine(
			'id',
			function ($row) {
				return "{$row->cnpj} - {$row->name}";
			}
		)->toArray();

		$filesLog = $this->FilesLog->find()
			->where(['id_order' => $id])
			->order(['FilesLog.created' => 'DESC'])
		->all();

		$this->set('order', $order);
		$this->set('newLog', $newLog);
		$this->set('allComments', $allComments);
		$this->set('config', $config);
		$this->set('customersOpt', $customersOpt);
		$this->set('isAdmin', $isAdmin);
		$this->set('filesLog', $filesLog);
	}

	public function changeStatus()
	{
		$this->request->allowMethod(['post']);

		$orderId = $this->request->getData('order_id');
		$newStatus = (int)$this->request->getData('new_status');
		$comentario = trim($this->request->getData('comment'));

		$order = $this->Orders->get($orderId, ['contain' => ['Customers']]);
		$oldStatus = $order->status;
		$order->status = $newStatus;

		if ($this->Orders->save($order)) {
			// Criar log de alteração
			$log = $this->OrdersLog->newEntity([
				'id_order' => $orderId,
				'id_user' => $this->Authentication->getIdentity()->id,
				'message' => $comentario ?: null,
				'modified' => date('Y-m-d H:i:s'),
				'old_status' => $oldStatus,
				'new_status' => $newStatus,
			]);

			if ($this->OrdersLog->save($log)) {
				// Salvar comentário, se houver
				if (!empty($comentario)) {
					$commentEntity = $this->Comments->newEntity([
						'id_order' => $orderId,
						'id_user' => $this->Authentication->getIdentity()->id,
						'message' => $comentario,
						'refcomment' => $log->id,
					]);
					$this->Comments->save($commentEntity);
				}

				// Enviar notificações para usuários associados
				$this->Notification->sendNotificationToOrderUsers($orderId, 'status', [
					'old_status' => $oldStatus,
					'new_status' => $newStatus,
					'comment' => $comentario,
					'user_name' => $this->Authentication->getIdentity()->name ?? 'Usuário'
				]);

				// Recarregar todos os logs com seus comentários
				$logs = $this->OrdersLog->find()
					->where(['id_order' => $orderId])
					->contain(['Users', 'Comments' => ['Users']])
					->orderAsc('OrdersLog.modified')
					->toArray();

				$newLog = [];
				foreach ($logs as $entry) {
					$mensagens = [];
					foreach ($entry->comments as $c) {
						$mensagens[] = [
							'comentario' => $c->message,
							'autor' => $c->user->name ?? 'Desconhecido',
						];
					}

					$newLog[] = [
						'id' => $entry->id,
						'status' => $entry->new_status,
						'data' => (new \Cake\I18n\FrozenTime($entry->modified))->i18nFormat('dd/MM/yyyy - HH:mm'),
						'data_formatada' => (new \Cake\I18n\FrozenTime($entry->modified))->i18nFormat('dd/MM/yyyy - HH:mm'),
						'user' => [
							'name' => $entry->user->name ?? 'Desconhecido'
						],
						'mensagens' => $mensagens
					];
				}

				// // Envio de e-mail para o cliente
				// $customerEmail = $order->customer->email ?? null;
				// if ($customerEmail) {
				// 	$statusText = PedidosStatus($newStatus);
				// 	$emailSubject = "Status do pedido #{$order->number} alterado";
				// 	$emailMessage = "
				// 		<h2>Atualização do Pedido</h2>
				// 		<p>O status do seu pedido foi alterado:</p>
				// 		<ul>
				// 			<li><strong>Pedido:</strong> #{$order->number}</li>
				// 			<li><strong>Novo Status:</strong> {$statusText}</li>
				// 			<li><strong>Data:</strong> " . date('d/m/Y H:i') . "</li>
				// 		</ul>
				// 		" . (!empty($comentario) ? "<p><strong>Observações:</strong> {$comentario}</p>" : "") . "
				// 	";
					
				// 	$this->sendEmail($customerEmail, $emailSubject, $emailMessage);
				// }

				return $this->response->withType('application/json')->withStringBody(json_encode([
					'success' => true,
					'new_log' => $newLog
				]));
			}
		}

		return $this->response->withType('application/json')->withStringBody(json_encode([
			'success' => false,
			'message' => 'Erro ao salvar o pedido.'
		]));
	}

	public function delete($id = null)
	{
		$this->request->allowMethod(['post', 'delete']);
		$order = $this->Orders->get($id);
	
		if ($this->Orders->delete($order)) {
			$this->Flash->success(__('The order has been deleted.'));
		} else {
			$this->Flash->error(__('The order could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * Salvar relacionamentos entre pedido e usuários
	 */
	private function saveOrderUsers($orderId, $userIds)
	{
		if (empty($userIds)) {
			return;
		}

		foreach ($userIds as $userId) {
			if (!empty($userId)) {
				$orderUser = $this->OrdersUsers->newEmptyEntity();
				$orderUser->id_order = $orderId;
				$orderUser->id_user = $userId;
				$this->OrdersUsers->save($orderUser);
			}
		}
	}

	/**
	 * Atualizar relacionamentos entre pedido e usuários
	 */
	private function updateOrderUsers($orderId, $userIds)
	{
		// Remover relacionamentos existentes
		$this->OrdersUsers->deleteAll(['id_order' => $orderId]);

		// Adicionar novos relacionamentos
		$this->saveOrderUsers($orderId, $userIds);
	}

	/**
	 * AJAX: Buscar usuários de um cliente específico
	 */
	public function getUsersByCustomer()
	{
		$this->request->allowMethod(['get']);
		
		$customerId = $this->request->getQuery('customer_id');
		
		if (empty($customerId)) {
			return $this->response->withType('application/json')->withStringBody(json_encode([
				'success' => false,
				'message' => 'ID do cliente não fornecido',
				'users' => []
			]));
		}

		$users = $this->Users->find()
			->where(['id_customer' => $customerId])
			->select(['id', 'name', 'email'])
			->toArray();

		return $this->response->withType('application/json')->withStringBody(json_encode([
			'success' => true,
			'users' => $users
		]));
	}
}
