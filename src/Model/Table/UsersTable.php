<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
	
	public function initialize(array $config): void
	{
		parent::initialize($config);

		$this->setTable('users');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');
		
		$this->belongsTo('Customers', [
			'foreignKey' => 'id_customer',
			'dependent' => true,
			'cascadeCallbacks' => true,
		]);
	}

	public function validationDefault(Validator $validator): Validator
	{
		$validator
			->scalar('name')
			->maxLength('name', 255)
			->allowEmptyString('name');

		$validator
			->scalar('nickname')
			->maxLength('nickname', 20)
			->allowEmptyString('nickname');

		$validator
			->email('email')
			->allowEmptyString('email');

		$validator
			->scalar('password')
			->maxLength('password', 255)
			->allowEmptyString('password');

		$validator
			->scalar('google_id')
			->maxLength('google_id', 255)
			->allowEmptyString('google_id');

		$validator
			->integer('role')
			->allowEmptyString('role');

		return $validator;
	}

	public function buildRules(RulesChecker $rules): RulesChecker
	{
		$rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

		return $rules;
	}
}
