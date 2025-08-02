<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class OrdersTable extends Table
{
	public function initialize(array $config): void
	{
		parent::initialize($config);

		$this->setTable('orders');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Customers', [
			'foreignKey' => 'id_customer',
			'dependent' => true,
			'cascadeCallbacks' => true,
		]);

		$this->hasMany('OrdersLog', [
			'foreignKey' => 'id_order',
			'dependent' => true,
			'cascadeCallbacks' => true,
		]);

		$this->belongsToMany('Users', [
			'through' => 'OrdersUsers',
			'foreignKey' => 'id_order',
			'targetForeignKey' => 'id_user',
			'dependent' => true,
			'cascadeCallbacks' => true,
		]);
	}

	public function validationDefault(Validator $validator): Validator
	{
		$validator
			->integer('id')
			->allowEmptyString('id', null, 'create');

		$validator
			->integer('number')
			->allowEmptyString('number');

		$validator
			->integer('id_customer')
			->allowEmptyString('id_customer');

		$validator
			->numeric('value')
			->allowEmptyString('value');

		$validator
			->integer('status')
			->allowEmptyString('status');

		$validator
			->date('estimatetoready')
			->allowEmptyDate('estimatetoready');

		$validator
			->integer('type')
			->allowEmptyString('type');

		$validator
			->integer('role')
			->allowEmptyString('role');

		$validator
			->integer('not_type')
			->allowEmptyString('not_type');

		return $validator;
	}
}
