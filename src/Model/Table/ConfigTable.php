<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ConfigTable extends Table
{
	
	public function initialize(array $config): void
	{
		parent::initialize($config);

		$this->setTable('config');
	}

	public function createConfigIf(): void
	{
		$config = $this->find()
			->where(['id' => 1])
			->first();

		if (!$config) {
			$entity = $this->newEntity([
				'id' => 1,
				'path' => '' // ou valor padrão
			]);
			$this->save($entity);
		}
	}
}
