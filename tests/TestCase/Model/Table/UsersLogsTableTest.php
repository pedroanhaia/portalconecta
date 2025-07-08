<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersLogsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersLogsTable Test Case
 */
class UsersLogsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersLogsTable
     */
    protected $UsersLogs;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.UsersLogs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('UsersLogs') ? [] : ['className' => UsersLogsTable::class];
        $this->UsersLogs = $this->getTableLocator()->get('UsersLogs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->UsersLogs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\UsersLogsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
