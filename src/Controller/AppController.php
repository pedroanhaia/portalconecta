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

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
		$this->loadComponent('Authorization.Authorization');

        $this->loadModel('Users');

		$this->Authorization->skipAuthorization();
    }
    
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);

		$userLogado = $this->Authentication->getIdentity();

		if(!empty($userLogado)) {
			$userData = $this->Authentication->getIdentity()->getOriginalData();
			$usuarioId = $userData->id; // Obtém o ID do usuário autenticado
			$userAtt = $this->Users->get($userData->id);

			$currentController = $this->request->getParam('controller');
			$currentAction = $this->request->getParam('action');
			$this->set('currentAction', $currentAction);
			$this->set('currentController', $currentController);
			
			$this->userObj = $this->Users->findById($usuarioId)->first();
			if(!empty($userAtt->img)) $userImg = $currentController == 'Quadros' ? $userAtt->img : '../' . $userAtt->img;
			else $userImg = "";
			$this->set('userImg', $userImg);
			$this->set('darkMode', $userAtt->dark_mode);
			$this->set('iduserLogado', $userData->id);
			$this->set('role', $userAtt->role);
			$this->set('username', $userAtt->nickname);
			$this->set('userNameCompleto', $userAtt->name);
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