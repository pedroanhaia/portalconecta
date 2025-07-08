<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FriendsFixture
 */
class FriendsFixture extends TestFixture
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
                'user' => 1,
                'friend' => 1,
                'created' => '2025-02-24 16:14:30',
                'modified' => '2025-02-24 16:14:30',
                'role' => 1,
                'type' => 1,
            ],
        ];
        parent::init();
    }
}
