<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * GrupoUsersFixture
 */
class GrupoUsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'id_user' => 1,
                'id_grupo' => 1,
                'created' => '2025-06-03 17:47:38',
                'modified' => '2025-06-03 17:47:38',
                'role' => 1,
                'admin' => 1,
            ],
        ];
        parent::init();
    }
}
