<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;

use Cake\View\JsonView;
use Cake\View\XmlView;
use Cake\Event\EventInterface;
use Authentication\AuthenticationService;
use Cake\Controller\Component\AuthComponent;

require_once (ROOT . DS . 'vendor' . DS  . 'KanbadaPackages' . DS . 'Utilities.php');

class AppController extends Controller
{
	public function initialize(): void
	{
		parent::initialize();

		$this->loadModel('Users');
		$this->loadComponent('Authorization.Authorization');
		$this->loadComponent('Authentication.Authentication');
		$this->loadComponent('Flash'); // <-- esta linha resolve o erro

		// Se quiser pular autorização em algumas actions, pode usar:
		
		$this->Authorization->skipAuthorization();
	}
    
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);

		// date_default_timezone_set('America/Sao_Paulo'); COLOQUEI ESSE AQUI SO PRA OLHAR SE FUNCIONA MAS NAO TESTEI

		if ($this->request->getParam('plugin') === 'DebugKit') {
			$this->Authorization->skipAuthorization();
		}

		$userLogado = $this->Authentication->getIdentity();

		if(!empty($userLogado)) {
			$userData = $this->Authentication->getIdentity()->getOriginalData();
			$userAtt = $this->Users->get($userData->id);

			$currentController = $this->request->getParam('controller');
			$currentAction = $this->request->getParam('action');
			$this->set('currentAction', $currentAction);
			$this->set('currentController', $currentController);

			$this->set('userDarkMode', $userAtt->dark_mode);
			$this->set('userId', $userData->id);
			$this->set('userRole', $userAtt->role);
			$this->set('userType', $userAtt->type);
			$this->set('userAdmin', $userAtt->admin);
			$this->set('userName', $userAtt->name);
		}
	}
	public function isAuthorized($user)	{
		return true;
	}

	public function viewClasses(): array {
		return [JsonView::class];
	}

	public function jsonResponse($responseData = [], $responseStatusCode = 200) {
		return $this->response
		->withType('json', ['application/json'])
		->withStatus($responseStatusCode)
		->withStringBody(json_encode($responseData));
	}
}