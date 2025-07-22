<?php
declare(strict_types=1);

namespace App\Controller;

class CommentsController extends AppController
{
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);
		
		$this->loadModel('Orders');
		$this->loadModel('OrdersLog');
	}

	public function add()
	{
		$this->request->allowMethod(['post']);
		
		if ($this->request->is('ajax')) {
			$orderId = $this->request->getData('order_id');
			$status = $this->request->getData('status');
			$message = trim($this->request->getData('comment'));
			
			if (empty($orderId) || empty($message)) {
				return $this->response->withType('application/json')->withStringBody(json_encode([
					'success' => false,
					'message' => 'Dados obrigatórios não fornecidos.'
				]));
			}
			
			// Verifica se a order existe
			$order = $this->Orders->find()->where(['id' => $orderId])->first();
			if (!$order) {
				return $this->response->withType('application/json')->withStringBody(json_encode([
					'success' => false,
					'message' => 'Pedido não encontrado.'
				]));
			}
			
			// Obtém o usuário autenticado obrigatoriamente
			$userId = null;
			$userName = 'Usuário';
			
			// Verifica se há usuário autenticado
			if ($this->Authentication && $this->Authentication->getIdentity()) {
				$userId = $this->Authentication->getIdentity()->id;
				$userName = $this->Authentication->getIdentity()->name ?? 'Usuário';
			} else {
				return $this->response->withType('application/json')->withStringBody(json_encode([
					'success' => false,
					'message' => 'Você precisa estar logado para comentar.'
				]));
			}
			
			// Busca o último registro de OrdersLog para o pedido
			$lastLogEntry = $this->OrdersLog->find()
				->where(['id_order' => $orderId])
				->order(['modified' => 'DESC', 'id' => 'DESC'])
				->first();

			// Cria o comentário referenciando o último log (se existir)
			$comment = $this->Comments->newEntity([
				'id_order' => $orderId,
				'id_user' => $userId,
				'message' => $message,
				'refcomment' => $lastLogEntry ? $lastLogEntry->id : C_PedidosStatusRecebido,
			]);
			if ($this->Comments->save($comment)) {
				return $this->response->withType('application/json')->withStringBody(json_encode([
					'success' => true,
					'data' => [
						'comentario' => $message,
						'autor' => $userName,
						'status' => $lastLogEntry ? $lastLogEntry->new_status : C_PedidosStatusRecebido,
					]
				]));
			} else {
				// Captura erros específicos de validação
				$errors = $comment->getErrors();
				return $this->response->withType('application/json')->withStringBody(json_encode([
					'success' => false,
					'message' => 'Erro ao salvar comentário: ' . json_encode($errors)
				]));
			}
		}
		
		// Método original para requests não-AJAX
		$comment = $this->Comments->newEmptyEntity();
		if ($this->request->is('post')) {
			$comment = $this->Comments->patchEntity($comment, $this->request->getData());
			if ($this->Comments->save($comment)) {
				$this->Flash->success(__('The comment has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The comment could not be saved. Please, try again.'));
		}
		
		$this->set(compact('comment'));
	}

	public function delete($id = null)
	{
		$this->request->allowMethod(['post', 'delete']);
		$comment = $this->Comments->get($id);
		if ($this->Comments->delete($comment)) {
			$this->Flash->success(__('The comment has been deleted.'));
		} else {
			$this->Flash->error(__('The comment could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}
}
