<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Log\Log;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use App\Service\UsersPlatformService;
use Cake\Routing\Router;

use function PHPUnit\Framework\isEmpty;


class UsersController extends AppController
{
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		$this->Authentication->addUnauthenticatedActions(['login', 'logout']);
		parent::beforeFilter($event);

		$userLogado = $this->Authentication->getIdentity();
		$currentAction = $this->request->getParam('action');

		if($userLogado) {
			if(!$userLogado->admin) {
				if(in_array($currentAction, ['delete'])) { 
					$this->Flash->error(__('Você não tem permissão para acessar esta página.'));
					return $this->redirect(['action' => 'index']);
				}
			}
			
			if($userLogado->type == C_UsersTypeAdmin) {
				if(!in_array($currentAction, ['edit', 'changepassword', 'darkmode', 'login', 'logout'])) { 
					$this->Flash->error(__('Você não tem permissão para acessar esta página.'));
					return $this->redirect(['controller' => 'Orders', 'action' => 'index']);
				}
			}
		}


		$this->loadModel('Customers');
	}

	public function index() {
		if($this->Authentication->getIdentity()->admin) {
			$usersAdmin = $this->Users->findByAdmin(1)->toArray();
			$usersCustomer = $this->Users->findByAdmin(0)
				->contain(['Customers' => ['fields' => ['name', 'cnpj']]])
				->order('Customers.name ASC')
			->toArray();
	
			$this->set('usersAdmin', $usersAdmin);
			$this->set('usersCustomer', $usersCustomer);
		} else {
			if($this->Authentication->getIdentity()->type == C_UsersTypeAdmin) {
				$usersCustomer = $this->Users->findByIdCustomer($this->Authentication->getIdentity()->id_customer)->toArray();
				$this->set('usersCustomer', $usersCustomer);
			} else {
				$this->Flash->error(__('Você não possui permissão para acessar esta página.'));
			}
		}

	}

	public function add() {
		$user = $this->Users->newEmptyEntity();
		$customers = $this->Customers->find('all')->all();

		$customersOpt = $customers->combine(
			'id',
			function ($row) {
				return "{$row->cnpj} - {$row->name}";
			}
		)->toArray();


		if ($this->request->is('post')) {
			$user = $this->Users->patchEntity($user, $this->request->getData());
			$user->darkmode = 0;
			if ($this->Users->save($user)) {
				$this->Flash->success(__('Usuário cadastrado com sucesso!'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('Não foi possível cadastrar o usuário, tente novamente.'));
		}
		
		$this->set('user', $user);
		$this->set('customersOpt', $customersOpt);
	}

	public function edit($id = null) {
		if(($this->Authentication->getIdentity()->admin != 1 && $this->Authentication->getIdentity()->type != C_UsersTypeAdmin) && $id != $this->Authentication->getIdentity()->id) {
			$id = $this->Authentication->getIdentity()->id;
		}
		
		if(empty($id)) $id = $this->Authentication->getIdentity()->id;
		$user = $this->Users->get($id);

		$customers = $this->Customers->find('all')->all();

		$customersOpt = $customers->combine(
			'id',
			function ($row) {
				return "{$row->cnpj} - {$row->name}";
			}
		)->toArray();


		if ($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->getData());

			if ($this->Users->save($user)) {
				$this->Flash->success(__('Dados salvos com sucesso!'));
				return $this->redirect(['controller' => 'Users', 'action' => 'index']);
			}

			$this->Flash->error(__('Não foi possível atualizar os dados, tente novamente.'));
		}

		$this->set('user', $user);
		$this->set('customersOpt', $customersOpt);
	}

	public function delete($id = null) {
		$user = $this->Users->get($id);
		if ($this->Users->delete($user)) {
			$this->Flash->success(__('O usuário foi excluído com sucesso.'));
		} else {
			$this->Flash->error(__('O usuário não pôde ser excluído. Por favor, tente novamente.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function login() {
		$this->Authorization->skipAuthorization();

		$this->viewBuilder()->setLayout("login");

		$result = $this->Authentication->getResult();

		// Se o usuário já está autenticado, redireciona imediatamente
		if ($result && $result->isValid()) {
			$redirect = $this->request->getQuery('redirect', '/orders/index');
			return $this->redirect($redirect);
		}
		
		if ($this->request->is('POST')) {
			$user = $this->Users->findByEmail($this->request->getData('email'))->first();

			$result = $this->Authentication->getResult();
			
			if ($result && $result->isValid()) {
				// Captura a URL original da query string
				$redirect = $this->request->getQuery('redirect', '/orders/index');

				return $this->redirect($redirect);
			}
			
			$this->Flash->error(__('Usuário ou senha inválidos!'));
		}
	}

	public function logout() {
		$this->Authentication->logout();
		return $this->redirect(['controller' => 'Users', 'action' => 'login']);
	}

	public function darkmode()
	{
		$this->request->allowMethod(['post']);

		$userData = $this->Authentication->getIdentity()->getOriginalData();
		$usuarioId = $userData->id; // Obtém o ID do usuário autenticado
		$darkMode = $this->request->getData('darkmode'); // Obtém o valor do campo darkmode enviado por AJAX

		$usuario = $this->Users->findById($usuarioId)->first();
		$usuario->dark_mode = $darkMode;
		$this->Users->save($usuario);
		return $this->jsonResponse(['Msg' => 'Alterado com sucesso'], 200);

		return $this->jsonResponse(['Msg' => "Salvo com sucesso"], 200);
	}
	
	public function verificaEmailExistente() 
	{
		$this->autoRender = false; // Desativa a renderização automática da visão

		if ($this->request->is(['ajax', 'post'])) {
			$email = $this->request->getData('email');
			$existe = $this->Users->find()->where(['email' => $email])->count();

			if ($existe == 0) return $this->jsonResponse(['Msg' => 'ok', 'bExiste' => true], 200);
			else return $this->jsonResponse(['Msg' => 'Já existe um usuário cadastrado com este nome de email!', 'bExiste' => false], 200);
		}
	}

	public function changepassword()
	{
		$user = $this->Users->get($this->Authentication->getIdentity()->getIdentifier());

		if ($this->request->is(['patch', 'post', 'put'])) {

			$data = $this->request->getData();

			// Verifica se a senha atual está correta
			$hasher = new DefaultPasswordHasher();

			if (!$hasher->check($data['old_password'], $user->password)) {
				$this->Flash->error(__('Senha anterior incorreta.'));
				return $this->redirect($this->referer());
			}

			// Validação da nova senha e confirmação
			if (empty($data['password1']) || $data['password1'] !== $data['password2']) {
				$this->Flash->error(__('As senhas não coincidem ou estão vazias.'));
				return $this->redirect($this->referer());
			}

			// Define a nova senha
			$user->password = $data['password1'];

			if ($this->Users->save($user)) {
				$this->Flash->success(__('Senha atualizada com sucesso.'));
				return $this->redirect(['action' => 'edit']);
			}

			$this->Flash->error(__('Não foi possível atualizar a senha. Tente novamente.'));
		}

		$this->set(compact('user'));
	}
}
