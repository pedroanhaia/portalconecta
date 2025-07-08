<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SeasonsFixture
 */
class SeasonsFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'seasons_month';
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
                'dateIni' => '2025-02-24 16:18:11',
                'datefin' => '2025-02-24 16:18:11',
                'name' => 'Lorem ipsum dolor sit amet',
                'icon' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-02-24 16:18:11',
                'modified' => '2025-02-24 16:18:11',
                'role' => 1,
                'type' => 1,
            ],
        ];
        parent::init();
    }
}
