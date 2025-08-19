<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * User Entity
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $id_customer
 * @property string|null $email
 * @property string|null $password
 * @property int $dark_mode
 * @property int|null $type
 * @property int|null $role
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Quadro[] $quadros
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'id_customer' => true,
        'email' => true,
        'password' => true,
        'dark_mode' => true,
        'type' => true,
        'role' => true,
        'created' => true,
        'modified' => true,
        'quadros' => true,
        'admin' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password',
    ];

    // Método mágico que intercepta a definição da senha e a criptografa
    protected function _setPassword(string $password): ?string
	{
		// Nada foi digitado?  deixa como está.
		if ($password === '') {
			return null;
		}

		// Já parece um hash bcrypt?  devolve inalterado.
		if (preg_match('/^\$2y\$/', $password)) {
			return $password;
		}

		// Caso contrário gera o hash normalmente.
		return (new DefaultPasswordHasher())->hash($password);
	}
}
