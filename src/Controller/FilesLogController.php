<?php
declare(strict_types=1);

namespace App\Controller;

class FilesLogController extends AppController
{
	public function index()
	{
		$filesLog = $this->paginate($this->FilesLog);

		$this->set(compact('filesLog'));
	}

	public function view($id = null)
	{
		$filesLog = $this->FilesLog->get($id, [
			'contain' => [],
		]);

		$this->set(compact('filesLog'));
	}

	public function add()
	{
		$filesLog = $this->FilesLog->newEmptyEntity();
		if ($this->request->is('post')) {
			$filesLog = $this->FilesLog->patchEntity($filesLog, $this->request->getData());
			if ($this->FilesLog->save($filesLog)) {
				$this->Flash->success(__('The files log has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The files log could not be saved. Please, try again.'));
		}
		$this->set(compact('filesLog'));
	}

	public function edit($id = null)
	{
		$filesLog = $this->FilesLog->get($id, [
			'contain' => [],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$filesLog = $this->FilesLog->patchEntity($filesLog, $this->request->getData());
			if ($this->FilesLog->save($filesLog)) {
				$this->Flash->success(__('The files log has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The files log could not be saved. Please, try again.'));
		}
		$this->set(compact('filesLog'));
	}

	public function delete($id = null)
	{
		$this->request->allowMethod(['post', 'delete']);
		$filesLog = $this->FilesLog->get($id);
		if ($this->FilesLog->delete($filesLog)) {
			$this->Flash->success(__('The files log has been deleted.'));
		} else {
			$this->Flash->error(__('The files log could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}
}
