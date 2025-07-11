<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class OrdersLogTable extends Table
{
	
	public function initialize(array $config): void
	{
		parent::initialize($config);

		$this->setTable('orders_log');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Users', [
			'foreignKey' => 'id_user',
			'dependent' => true,
			'cascadeCallbacks' => true,
		]);

		$this->hasMany('Comments', [
			'foreignKey' => 'refcomment',
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
			->integer('id_order')
			->allowEmptyString('id_order');

		$validator
			->scalar('message')
			->allowEmptyString('message');

		$validator
			->integer('type')
			->allowEmptyString('type');

		$validator
			->integer('role')
			->allowEmptyString('role');

		return $validator;
	}
}
