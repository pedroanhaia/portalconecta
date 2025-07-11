<?php
declare(strict_types=1);

namespace App\Controller;

class CustomersController extends AppController
{
	public function index() {
		if($this->Authentication->getIdentity()->admin != 1) {
			$this->Flash->error(__('Você não possui permissão para acessar esta página.'));
			return $this->redirect(['controller' => 'Orders', 'action' => 'index']);
		}

		$customers = $this->Customers->find()->toArray();

		$this->set('customers', $customers);
	}

	public function add()
	{
		$customer = $this->Customers->newEmptyEntity();
		if ($this->request->is('post')) {
			$customer = $this->Customers->patchEntity($customer, $this->request->getData());

			if ($this->Customers->save($customer)) {
				$this->Flash->success(__('Os dados da empresa foram salvos com sucesso.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__('Não foi possível salvar os dados, tente novamente.'));
		}

		$this->set(compact('customer'));
	}

	public function edit($id = null)
	{
		$customer = $this->Customers->findById($id)->first();
		if ($this->request->is(['patch', 'post', 'put'])) {
			$customer = $this->Customers->patchEntity($customer, $this->request->getData());

			if ($this->Customers->save($customer)) {
				$this->Flash->success(__('Os dados da empresa foram salvos com sucesso.'));
				return $this->redirect(['action' => 'index']);
			}
			
			$this->Flash->error(__('Não foi possível salvar os dados, tente novamente.'));
		}

		$this->set(compact('customer'));
	}

   
	public function delete($id = null)
	{
		$this->request->allowMethod(['post', 'delete']);
		$customer = $this->Customers->get($id);
		if ($this->Customers->delete($customer)) {
			$this->Flash->success(__('The customer has been deleted.'));
		} else {
			$this->Flash->error(__('The customer could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}
}
