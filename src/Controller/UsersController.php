<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Log\Log;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use App\Service\UsersPlatformService;
use Cake\Routing\Router;

use function PHPUnit\Framework\isEmpty;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		$this->Authentication->addUnauthenticatedActions(['login', 'add', 'register', 'verificaEmailExistente']);
		parent::beforeFilter($event);
	}

	public function index() {
		if($this->Authentication->getIdentity()->role != 1) {
			$this->Flash->error(__('Você não possui permissão para acessar esta página.'));
			return $this->redirect(['controller' => 'Orders', 'action' => 'index']);
		}

		$users = $this->paginate($this->Users);

		$this->set(compact('users'));
	}

	public function add() {
		$user = $this->Users->newEmptyEntity();
		if ($this->request->is('post')) {
			$user = $this->Users->patchEntity($user, $this->request->getData());
			if ($this->Users->save($user)) {
				$this->Flash->success(__('Usuário cadastrado com sucesso!'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('Não foi possível cadastrar o usuário, tente novamente.'));
		}
		$this->set(compact('user'));
	}

	public function register()
	{
		$this->viewBuilder()->setLayout('login');

		$user = $this->Users->newEmptyEntity();

		if ($this->request->is('post')) {
			/* 1.  Verificação de nickname duplicado ----------------------- */
			$email = $this->request->getData('email');
			if ($this->Users->exists(['email' => $email])) {
				$this->Flash->error(__('Já existe um usuário com este email.'));
				return $this->redirect(['action' => 'register']);
			}

			$user = $this->Users->patchEntity($user, $this->request->getData());

			if ($this->Users->save($user)) {
				$this->Flash->success(__('Usuário cadastrado com sucesso!'));
				return $this->redirect(['action' => 'download']);
			}

			$this->Flash->error(__('Não foi possível cadastrar o usuário, tente novamente.'));
		}

		$this->set(compact('user'));
	}

	public function edit($id = null) {
		if($this->Authentication->getIdentity()->role != 1 && isset($id)) {
			$this->Flash->error(__('Você não possui permissão para acessar esta página.'));
			return $this->redirect(['controller' => 'Orders', 'action' => 'index']);
		}
		
		$user = $this->Users->get($this->Authentication->getIdentity()->id, [
			'contain' => [],
		]);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$data = $this->request->getData();

			/** @var \Laminas\Diactoros\UploadedFile|null $img */
			$img = $data['img_edit'] ?? null;

			$user = $this->Users->patchEntity($user, $data);

			if ($img && $img->getError() === UPLOAD_ERR_OK) {
				if ($user->img && file_exists(WWW_ROOT . $user->img)) {
					@unlink(WWW_ROOT . $user->img);
				}

				$nome = 'img\\users_img\\' . $user->id . '-' . date('YmdHis') . '.png';
				$img->moveTo(WWW_ROOT . $nome);

				$user->img = $nome;
			}

			if ($this->Users->save($user)) {
				$this->Flash->success(__('Dados salvos com sucesso!'));
				return $this->redirect(['controller' => 'Quadros', 'action' => 'index']);
			}

			$this->Flash->error(__('Não foi possível atualizar os dados, tente novamente.'));
		}

		$this->set(compact('user'));
	}

	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$user = $this->Users->get($id);
		if ($this->Users->delete($user)) {
			$this->Flash->success(__('The user has been deleted.'));
		} else {
			$this->Flash->error(__('The user could not be deleted. Please, try again.'));
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

			debug($this->request->getData());

			$user = $this->Users->findByEmail($this->request->getData('email'))->first();

			debug($user);
			debug($result);
			die;

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
		// $this->viewBuilder()->setLayout('quadros');
		$user = $this->Users->get($this->Authentication->getIdentity()->getIdentifier());


		if ($this->request->is(['patch', 'post', 'put'])) {

			$data = $this->request->getData();

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
