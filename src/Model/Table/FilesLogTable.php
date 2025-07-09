<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FilesLog Model
 *
 * @method \App\Model\Entity\FilesLog newEmptyEntity()
 * @method \App\Model\Entity\FilesLog newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FilesLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FilesLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\FilesLog findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FilesLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FilesLog[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FilesLog|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FilesLog saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FilesLog[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FilesLog[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FilesLog[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FilesLog[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FilesLogTable extends Table
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

        $this->setTable('files_log');
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
            ->integer('id_file')
            ->allowEmptyFile('id_file');

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
