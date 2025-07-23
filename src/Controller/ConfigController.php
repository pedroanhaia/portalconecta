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

	public function seedData()
	{
		$userLogado = $this->Authentication->getIdentity();
		
		// Apenas admin pode acessar
		if (!$userLogado->admin) {
			$this->Flash->error(__('Você não tem permissão para acessar esta página.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->request->is('post')) {
			try {
				// Converter checkbox para boolean corretamente
				$preserveDataValue = $this->request->getData('preserve_data');
				$preserveData = !empty($preserveDataValue) && ($preserveDataValue === '1' || $preserveDataValue === 1 || $preserveDataValue === true);
				
				$totalOrders = (int)$this->request->getData('total_orders', 50);
				
				// Limitar a quantidade para evitar sobrecarga
				if ($totalOrders > 200) {
					$totalOrders = 200;
				}
				
				$seeder = new \App\Service\TestDataSeeder();
				$result = $seeder->generateTestData($preserveData, $totalOrders);
				
				if ($result['success']) {
					$this->Flash->success($result['message']);
					$this->set('result', $result);
				} else {
					$this->Flash->error($result['message']);
				}
				
			} catch (\Exception $e) {
				$this->Flash->error('Erro ao gerar dados: ' . $e->getMessage());
			}
		}

		// Contar registros existentes
		$this->loadModel('Customers');
		$this->loadModel('Orders');
		$this->loadModel('OrdersLog');
		$this->loadModel('Comments');
		$this->loadModel('Users');

		$currentStats = [
			'users' => $this->Users->find()->count(),
			'customers' => $this->Customers->find()->count(),
			'orders' => $this->Orders->find()->count(),
			'orders_log' => $this->OrdersLog->find()->count(),
			'comments' => $this->Comments->find()->count()
		];

		$this->set(compact('currentStats'));
	}

	public function createDirectories()
	{
		$userLogado = $this->Authentication->getIdentity();
		
		// Apenas admin pode acessar
		if (!$userLogado->admin) {
			$this->Flash->error(__('Você não tem permissão para acessar esta página.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->request->is('post')) {
			try {
				$seeder = new \App\Service\TestDataSeeder();
				$result = $seeder->createMissingOrderDirectories();
				
				if ($result['success']) {
					$message = sprintf(
						'%s - %d pastas criadas, %d já existiam, %d erros',
						$result['message'],
						$result['stats']['directories_created'],
						$result['stats']['directories_skipped'],
						$result['stats']['errors']
					);
					$this->Flash->success($message);
					$this->set('directoryResult', $result);
				} else {
					$this->Flash->error($result['message']);
				}
				
			} catch (\Exception $e) {
				$this->Flash->error('Erro ao criar pastas: ' . $e->getMessage());
			}
		}

		// Redirecionar de volta para seedData
		return $this->redirect(['action' => 'seedData']);
	}

}
