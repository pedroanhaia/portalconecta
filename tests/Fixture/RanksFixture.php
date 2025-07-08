<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RanksFixture
 */
class RanksFixture extends TestFixture
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
                'icon' => 'Lorem ipsum dolor sit amet',
                'level' => 1,
                'created' => '2025-02-24 16:16:19',
                'modified' => '2025-02-24 16:16:19',
                'type' => 1,
                'role' => 1,
            ],
        ];
        parent::init();
    }
}
