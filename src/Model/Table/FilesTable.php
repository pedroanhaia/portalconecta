<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Files Model
 *
 * @method \App\Model\Entity\File newEmptyEntity()
 * @method \App\Model\Entity\File newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\File[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\File get($primaryKey, $options = [])
 * @method \App\Model\Entity\File findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\File patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\File[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\File|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\File saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FilesTable extends Table
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

		$this->setTable('files');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');
	}

	/**
	 * Busca categorias distintas para um pedido específico
	 *
	 * @param int $orderId ID do pedido
	 * @return array Lista de categorias encontradas com nome e label
	 */
	public function getCategoriesForOrder($orderId)
	{
		$categories = $this->find()
			->select(['category'])
			->where([
				'id_order' => $orderId,
				'category IS NOT' => null,
				'category !=' => ''
			])
			->group(['category'])
			->orderAsc('category')
			->toArray();

		// Transformar os resultados para incluir name e label
		$result = [];
		foreach ($categories as $category) {
			$categoryName = $category->category;
			$result[] = (object)[
				'name' => $categoryName,
				'label' => strtolower($categoryName)
			];
		}

		return $result;
	}

	/**
	 * Busca arquivos de uma categoria específica para um pedido
	 *
	 * @param int $orderId ID do pedido
	 * @param string $category Categoria dos arquivos
	 * @return \Cake\ORM\Query
	 */
	public function findByOrderAndCategory($orderId, $category)
	{
		return $this->find()
			->where([
				'id_order' => $orderId,
				'category' => $category
			]);
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
			->scalar('name')
			->maxLength('name', 255)
			->allowEmptyString('name');

		$validator
			->scalar('type')
			->maxLength('type', 255)
			->allowEmptyString('type');

		$validator
			->scalar('patch')
			->maxLength('patch', 255)
			->allowEmptyString('patch');

		$validator
			->integer('status')
			->allowEmptyString('status');

		$validator
			->integer('role')
			->allowEmptyString('role');

		$validator
			->integer('id_order')
			->allowEmptyString('id_order');

		return $validator;
	}
}
