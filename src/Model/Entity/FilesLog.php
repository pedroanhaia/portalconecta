<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FilesLog Entity
 *
 * @property int $id
 * @property int|null $id_file
 * @property string|null $message
 * @property int|null $type
 * @property int|null $role
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class FilesLog extends Entity
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
        'id_file' => true,
        'id_order' => true,
        'message' => true,
        'type' => true,
        'role' => true,
        'created' => true,
        'modified' => true,
    ];
}
