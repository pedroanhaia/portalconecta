<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Comments Controller
 *
 * @method \App\Model\Entity\Comment[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CommentsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event) {
        parent::beforeFilter($event);
        
        $this->loadModel('Orders');
        $this->loadModel('OrdersLog');
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $comments = $this->paginate($this->Comments);

        $this->set(compact('comments'));
    }

    /**
     * View method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $comment = $this->Comments->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('comment'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
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
                'refcomment' => $lastLogEntry ? $lastLogEntry->id : null,
            ]);
            if ($this->Comments->save($comment)) {
                return $this->response->withType('application/json')->withStringBody(json_encode([
                    'success' => true,
                    'data' => [
                        'comment' => $message,
                        'user_name' => $userName,
                        'status_text' => 'Comentário atual'
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

    /**
     * Edit method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $comment = $this->Comments->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            if ($this->Comments->save($comment)) {
                $this->Flash->success(__('The comment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }
        $this->set(compact('comment'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
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
