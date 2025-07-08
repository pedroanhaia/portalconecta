<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersWeeklyTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersWeeklyTable Test Case
 */
class UsersWeeklyTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersWeeklyTable
     */
    protected $UsersWeekly;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.UsersWeekly',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('UsersWeekly') ? [] : ['className' => UsersWeeklyTable::class];
        $this->UsersWeekly = $this->getTableLocator()->get('UsersWeekly', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->UsersWeekly);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\UsersWeeklyTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
