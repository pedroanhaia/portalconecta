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
	}

	public function index()
	{
		$orders = $this->Orders->find()->contain(['Customers'])->order(['number DESC'])->toArray();

		$this->set(compact('orders'));
	}

	public function view($id = null)
	{
		$order = $this->Orders->findById($id)
			->contain(['OrdersLog', 'OrdersLog.Users', 'OrdersLog.Comments', 'OrdersLog.Comments.Users'])
			->first();

		$newLog = [];

		if ($order && $order->orders_log) {
			foreach ($order->orders_log as $entry) {
				$mensagens = [];

				if ($entry->comments) {
					foreach ($entry->comments as $c) {
						$mensagens[] = [
							'comentario' => $c->message,
							'autor' => $c->user->name ?? 'Desconhecido'
						];
					}
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
		}

		$this->set(compact('order', 'newLog'));
	}

	public function add()
	{
		$order = $this->Orders->newEmptyEntity();

		if ($this->request->is('post')) {
			$order = $this->Orders->patchEntity($order, $this->request->getData());

			if ($this->Orders->save($order)) {
				$this->Flash->success(__('The order has been saved.'));
				return $this->redirect(['action' => 'index']);
			}
			
			$this->Flash->error(__('The order could not be saved. Please, try again.'));
		}

		$customers = $this->Customers->find('all')->all();

		$customersOpt = $customers->combine(
			'id',
			function ($row) {
				return "{$row->cnpj} - {$row->name}";
			}
		)->toArray();

		$this->set('order', $order);
		$this->set('customersOpt', $customersOpt);
	}

	public function edit($id = null)
	{
		$order = $this->Orders->findById($id)
			->contain(['OrdersLog', 'OrdersLog.Users', 'OrdersLog.Comments', 'OrdersLog.Comments.Users'])
		->first();

		$newLog = [];

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

		if ($this->request->is(['patch', 'post', 'put'])) {
			$order = $this->Orders->patchEntity($order, $this->request->getData());
			
			if ($this->Orders->save($order)) {
				$this->Flash->success(__('The order has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__('The order could not be saved. Please, try again.'));
		}

		$this->set('order', $order);
		$this->set('newLog', $newLog);
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
