<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class OrdersUsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('orders_users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Orders', [
            'foreignKey' => 'id_order',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'id_user',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->integer('id_order')
            ->requirePresence('id_order', 'create')
            ->notEmptyString('id_order');

        $validator
            ->integer('id_user')
            ->requirePresence('id_user', 'create')
            ->notEmptyString('id_user');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['id_order'], 'Orders'), ['errorField' => 'id_order']);
        $rules->add($rules->existsIn(['id_user'], 'Users'), ['errorField' => 'id_user']);

        // Evitar duplicatas
        $rules->add($rules->isUnique(['id_order', 'id_user']), [
            'message' => 'Este usuário já está associado a este pedido.'
        ]);

        return $rules;
    }
}
