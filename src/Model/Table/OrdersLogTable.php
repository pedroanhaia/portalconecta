<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OrdersLog Model
 *
 * @method \App\Model\Entity\OrdersLog newEmptyEntity()
 * @method \App\Model\Entity\OrdersLog newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\OrdersLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdersLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdersLog findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\OrdersLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdersLog[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdersLog|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdersLog saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdersLog[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\OrdersLog[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\OrdersLog[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\OrdersLog[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdersLogTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('orders_log');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
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
