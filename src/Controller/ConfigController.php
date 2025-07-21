<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Log\Log;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use App\Service\UsersPlatformService;
use Cake\Routing\Router;

use function PHPUnit\Framework\isEmpty;


class ConfigController extends AppController
{
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);

		$userLogado = $this->Authentication->getIdentity();
		$currentAction = $this->request->getParam('action');

		if(!$userLogado->admin) {
			if(in_array($currentAction, ['path'])) { 
				$this->Flash->error(__('Você não tem permissão para acessar esta página.'));
				return $this->redirect(['action' => 'index']);
			}
		}
	}

	public function index() {
	}

	public function path()
	{
		$this->Config->createConfigIf(); // garante que id = 1 existe

		$config = $this->Config->get(1);

		if ($this->request->is(['post', 'put'])) {
			$config = $this->Config->patchEntity($config, $this->request->getData());

			if ($this->Config->save($config)) {
				$this->Flash->success('Configurações salvas.');
				return $this->redirect(['action' => 'path']);
			}

			$this->Flash->error('Erro ao salvar.');
		}

		$this->set(compact('config'));
	}

}
