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
		$this->Authentication->addUnauthenticatedActions(['login']);
		parent::beforeFilter($event);
	}

	public function index() {
		if($this->Authentication->getIdentity()->admin != 1) {
			$this->Flash->error(__('Você não possui permissão para acessar esta página.'));
			return $this->redirect(['controller' => 'Orders', 'action' => 'index']);
		}
	}

	public function path()
	{
		$this->Config->createConfigIf(); // garante que id = 1 existe

		$config = $this->Config->get(1);

		if ($this->request->is(['post', 'put'])) {
			$config = $this->Config->patchEntity($config, $this->request->getData());

			if ($this->Config->save($config)) {
				$this->Flash->success('Configurações salvas.');
				return $this->redirect(['action' => 'edit']);
			}

			$this->Flash->error('Erro ao salvar.');
		}

		$this->set(compact('config'));
	}

}
