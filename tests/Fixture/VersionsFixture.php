<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * VersionsFixture
 */
class VersionsFixture extends TestFixture
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
                'name' => 'Lorem ipsum dolor sit amet',
                'obs' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-03-13 10:10:37',
                'modified' => '2025-03-13 10:10:37',
                'type' => 1,
                'role' => 1,
            ],
        ];
        parent::init();
    }
}
