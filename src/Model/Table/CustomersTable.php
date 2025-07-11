<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CustomersTable extends Table
{
	public function initialize(array $config): void
	{
		parent::initialize($config);

		$this->setTable('customers');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');
	}

	public function validationDefault(Validator $validator): Validator
	{
		$validator
			->integer('id')
			->allowEmptyString('id', null, 'create');

		$validator
			->scalar('cnpj')
			->maxLength('cnpj', 255)
			->allowEmptyString('cnpj');

		$validator
			->scalar('name')
			->maxLength('name', 255)
			->allowEmptyString('name');

		$validator
			->scalar('address')
			->allowEmptyString('address');

		return $validator;
	}
}
