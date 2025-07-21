<?php
declare(strict_types=1);

namespace App\Controller;

class OrdersLogController extends AppController
{
	public function index()
	{
		$ordersLog = $this->paginate($this->OrdersLog);

		$this->set(compact('ordersLog'));
	}

	public function view($id = null)
	{
		$ordersLog = $this->OrdersLog->get($id, [
			'contain' => [],
		]);

		$this->set(compact('ordersLog'));
	}

	public function add()
	{
		$ordersLog = $this->OrdersLog->newEmptyEntity();
		if ($this->request->is('post')) {
			$ordersLog = $this->OrdersLog->patchEntity($ordersLog, $this->request->getData());
			if ($this->OrdersLog->save($ordersLog)) {
				$this->Flash->success(__('The orders log has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The orders log could not be saved. Please, try again.'));
		}
		$this->set(compact('ordersLog'));
	}

	public function edit($id = null)
	{
		$ordersLog = $this->OrdersLog->get($id, [
			'contain' => [],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$ordersLog = $this->OrdersLog->patchEntity($ordersLog, $this->request->getData());
			if ($this->OrdersLog->save($ordersLog)) {
				$this->Flash->success(__('The orders log has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The orders log could not be saved. Please, try again.'));
		}
		$this->set(compact('ordersLog'));
	}

	public function delete($id = null)
	{
		$this->request->allowMethod(['post', 'delete']);
		$ordersLog = $this->OrdersLog->get($id);
		if ($this->OrdersLog->delete($ordersLog)) {
			$this->Flash->success(__('The orders log has been deleted.'));
		} else {
			$this->Flash->error(__('The orders log could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}
}
