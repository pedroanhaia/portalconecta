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
			->scalar('ordem_compra')
			->maxLength('ordem_compra', 255)
			->allowEmptyString('ordem_compra');
		$validator
			->scalar('coditem')
			->maxLength('coditem', 255)
			->allowEmptyString('coditem');
			$validator
			->scalar('descitem')
			->maxLength('descitem', 255)
			->allowEmptyString('descitem');

		$validator
			->integer('id_customer')
			->allowEmptyString('id_customer');

		$validator
			->scalar('not_mode')
			->allowEmptyString('not_mode');

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
