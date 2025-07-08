<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersLogsFixture
 */
class UsersLogsFixture extends TestFixture
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
                'idUser' => 1,
                'action' => 'Lorem ipsum dolor sit amet',
                'obs' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-02-24 16:13:57',
                'modified' => '2025-02-24 16:13:57',
                'exp' => 1,
                'dateIni' => '2025-02-24 16:13:57',
                'dateFin' => '2025-02-24 16:13:57',
                'type' => 1,
                'role' => 1,
                'location' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
