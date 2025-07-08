<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\QuestsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\QuestsTable Test Case
 */
class QuestsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\QuestsTable
     */
    protected $Quests;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Quests',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Quests') ? [] : ['className' => QuestsTable::class];
        $this->Quests = $this->getTableLocator()->get('Quests', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Quests);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\QuestsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
