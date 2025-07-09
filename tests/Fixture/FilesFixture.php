<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FilesFixture
 */
class FilesFixture extends TestFixture
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
                'patch' => 'Lorem ipsum dolor sit amet',
                'status' => 1,
                'type' => 1,
                'role' => 1,
                'created' => '2025-07-08 18:26:42',
                'modified' => '2025-07-08 18:26:42',
                'id_order' => 1,
            ],
        ];
        parent::init();
    }
}
