<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WeekliesFixture
 */
class WeekliesFixture extends TestFixture
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
                'dateIni' => '2025-02-24 16:16:47',
                'datefin' => '2025-02-24 16:16:47',
                'created' => '2025-02-24 16:16:47',
                'modified' => '2025-02-24 16:16:47',
                'role' => 1,
                'type' => 1,
            ],
        ];
        parent::init();
    }
}
