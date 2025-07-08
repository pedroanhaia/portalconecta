<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WeekliesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WeekliesTable Test Case
 */
class WeekliesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WeekliesTable
     */
    protected $Weeklies;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Weeklies',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Weeklies') ? [] : ['className' => WeekliesTable::class];
        $this->Weeklies = $this->getTableLocator()->get('Weeklies', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Weeklies);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\WeekliesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
