<?php
declare(strict_types=1);

namespace App\Controller;

class OrdersController extends AppController
{
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);

		$this->loadModel('Customers');
		$this->loadModel('OrdersLog');
		$this->loadModel('Comments');
		$this->loadModel('Config');

		$userLogado = $this->Authentication->getIdentity();
		$currentAction = $this->request->getParam('action');

		if(!$userLogado->admin) {
			if(in_array($currentAction, ['add', 'edit', 'changeStatus', 'delete'])) { 
				$this->Flash->error(__('Você não tem permissão para acessar esta página.'));
				return $this->redirect(['action' => 'index']);
			}
		}
	}

	public function index()
	{
		$userLogado = $this->Authentication->getIdentity();

		if($userLogado->admin) {
			$orders = $this->Orders->find('all', $this->paginate)
				->contain(['Customers'])
				->order(['number' => 'DESC'])
				->toArray();
		} else {
			$orders = $this->Orders->find('all', $this->paginate)
				->where(['id_customer' => $userLogado->id_customer])
				->order(['number' => 'DESC'])
				->toArray();
		}

		$this->set(compact('orders'));
	}

	public function view($id = null)
	{
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

		// Busca comentários sem status
		$comentariosSemStatus = $this->Comments->find()
			->where(['id_order' => $id, 'refcomment IS' => null])
			->contain(['Users'])
		->toArray();

		// Comentários sem status
		foreach ($comentariosSemStatus as $comentario) {
			$allComments[] = [
				'autor' => $comentario->user->name ?? 'Desconhecido',
				'comentario' => $comentario->message,
				'data' => (new \Cake\I18n\FrozenTime($comentario->created))->i18nFormat('dd/MM/yyyy - HH:mm'),
				'status' => C_PedidosStatusRecebido
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

		// Comentários dos logs (com status)
		foreach ($newLog as $logEntry) {
			if (!empty($logEntry['mensagens'])) {
				foreach ($logEntry['mensagens'] as $mensagem) {
					$allComments[] = [
						'autor' => $mensagem['autor'],
						'comentario' => $mensagem['comentario'],
						'data' => $logEntry['data'],
						'status' => $logEntry['status']
					];
				}
			}
		}

		// Inverte para mostrar os mais recentes primeiro
		$allComments = array_reverse($allComments);

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
	}

	public function edit($id = null)
	{
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

		// Busca comentários sem status
		$comentariosSemStatus = $this->Comments->find()
			->where(['id_order' => $id, 'refcomment IS' => null])
			->contain(['Users'])
		->toArray();

		// Comentários sem status
		foreach ($comentariosSemStatus as $comentario) {
			$allComments[] = [
				'autor' => $comentario->user->name ?? 'Desconhecido',
				'comentario' => $comentario->message,
				'data' => (new \Cake\I18n\FrozenTime($comentario->created))->i18nFormat('dd/MM/yyyy - HH:mm'),
				'status' => C_PedidosStatusRecebido
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

		// Comentários dos logs (com status)
		foreach ($newLog as $logEntry) {
			if (!empty($logEntry['mensagens'])) {
				foreach ($logEntry['mensagens'] as $mensagem) {
					$allComments[] = [
						'autor' => $mensagem['autor'],
						'comentario' => $mensagem['comentario'],
						'data' => $logEntry['data'],
						'status' => $logEntry['status']
					];
				}
			}
		}

		// Inverte para mostrar os mais recentes primeiro
		$allComments = array_reverse($allComments);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$order = $this->Orders->patchEntity($order, $this->request->getData());
			
			if ($this->Orders->save($order)) {
				$this->Flash->success(__('Os dados foram salvos com sucesso.'));
				return $this->redirect(['action' => 'index']);
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

		$this->set('order', $order);
		$this->set('newLog', $newLog);
		$this->set('allComments', $allComments);
		$this->set('config', $config);
		$this->set('customersOpt', $customersOpt);
	}

	public function changeStatus()
	{
		$this->request->allowMethod(['post']);

		$orderId = $this->request->getData('pedido_id');
		$newStatus = (int)$this->request->getData('status');
		$comentario = trim($this->request->getData('comentario'));

		$order = $this->Orders->get($orderId);
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
						'user' => [
							'name' => $entry->user->name ?? 'Desconhecido'
						],
						'mensagens' => $mensagens
					];
				}

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
}
