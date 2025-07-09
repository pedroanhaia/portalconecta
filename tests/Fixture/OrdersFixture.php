<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * OrdersFixture
 */
class OrdersFixture extends TestFixture
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
                'number' => 1,
                'id_customer' => 1,
                'value' => 1,
                'status' => 1,
                'type' => 1,
                'role' => 1,
                'created' => '2025-07-08 18:26:57',
                'modified' => '2025-07-08 18:26:57',
            ],
        ];
        parent::init();
    }
}
