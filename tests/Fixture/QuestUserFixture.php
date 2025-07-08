<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QuestUserFixture
 */
class QuestUserFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'quest_user';
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
                'idUser' => 1,
                'idQuest' => 1,
                'progress' => 1,
                'created' => '2025-02-24 16:15:40',
                'modified' => '2025-02-24 16:15:40',
                'type' => 1,
                'role' => 1,
            ],
        ];
        parent::init();
    }
}
