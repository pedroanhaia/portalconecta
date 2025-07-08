<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

class User extends Entity
{
	protected $_accessible = [
		'name' => true,
		'nickname' => true,
		'email' => true,
		'password' => true,
		'google_id' => true,
		'img' => true,
		'created' => true,
		'modified' => true,
		'role' => true,
		'dark_mode' => true,
	];

	protected $_hidden = [
		'password',
	];

	// Método mágico que intercepta a definição da senha e a criptografa
		protected function _setPassword(string $password): ?string
	{
		// Nada foi digitado?  deixa como está.
		if ($password === '') {
			return null;
		}

		// Já parece um hash bcrypt?  devolve inalterado.
		if (preg_match('/^\$2y\$/', $password)) {
			return $password;
		}

		// Caso contrário gera o hash normalmente.
		return (new DefaultPasswordHasher())->hash($password);
	}
}
