<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\QuestUserTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\QuestUserTable Test Case
 */
class QuestUserTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\QuestUserTable
     */
    protected $QuestUser;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.QuestUser',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('QuestUser') ? [] : ['className' => QuestUserTable::class];
        $this->QuestUser = $this->getTableLocator()->get('QuestUser', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->QuestUser);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\QuestUserTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
