<?php
declare(strict_types=1);

namespace App\Controller;

class OrdersController extends AppController
{
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);

		$this->loadModel('Customers');
	}

	public function index()
	{
		$orders = $this->Orders->find()->contain(['Customers'])->order(['number DESC'])->toArray();

		$this->set(compact('orders'));
	}

	public function view($id = null)
	{
		$order = $this->Orders->get($id, [
			'contain' => [],
		]);

		$this->set(compact('order'));
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
		$order = $this->Orders->get($id, [
			'contain' => [],
		]);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$order = $this->Orders->patchEntity($order, $this->request->getData());
			
			if ($this->Orders->save($order)) {
				$this->Flash->success(__('The order has been saved.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__('The order could not be saved. Please, try again.'));
		}

		$this->set(compact('order'));
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
