<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Order Entity
 *
 * @property int $id
 * @property int|null $number
 * @property int|null $id_customer
 * @property float|null $value
 * @property int|null $status
 * @property int|null $type
 * @property int|null $role
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class Order extends Entity
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
        'number' => true,
        'id_customer' => true,
        'status' => true,
        'ordem_compra' => true,
        'not_mode' => true,
        'type' => true,
        'role' => true,
        'created' => true,
        'modified' => true,
        'estimatetoready' => true,
        'coditem' => true,
        'descitem' => true,
    ];
}
