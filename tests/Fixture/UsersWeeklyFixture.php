<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersWeeklyFixture
 */
class UsersWeeklyFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'users_weekly';
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
                'idWeekly' => 1,
                'idUser' => 1,
                'idRank' => 1,
                'position' => 1,
                'exp' => 1,
                'created' => '2025-02-24 16:17:14',
                'modified' => '2025-02-24 16:17:14',
                'role' => 1,
                'type' => 1,
            ],
        ];
        parent::init();
    }
}
