<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'role' => 'Lorem ipsum dolor ',
                'created' => '2025-02-24 16:13:32',
                'modified' => '2025-02-24 16:13:32',
                'nickname' => 'Lorem ipsum dolor sit amet',
                'api_key' => 'Lorem ipsum dolor sit amet',
                'api_key_plain' => 'Lorem ipsum dolor sit amet',
                'totexp' => 1,
                'idCurrentPool' => 1,
                'avatar' => 1,
                'hearts' => 1,
                'money' => 1,
                'streak' => 1,
                'idRank' => 1,
                'type' => 1,
                'birth' => '2025-02-24',
            ],
        ];
        parent::init();
    }
}
