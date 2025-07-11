<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;


class Config extends Entity
{
	protected $_accessible = [
		'path' => true,
	];
}
