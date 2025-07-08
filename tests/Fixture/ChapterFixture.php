<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ChapterFixture
 */
class ChapterFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'chapter';
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
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2025-02-24 16:13:14',
                'modified' => '2025-02-24 16:13:14',
                'type' => 1,
                'role' => 1,
                'icon' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
