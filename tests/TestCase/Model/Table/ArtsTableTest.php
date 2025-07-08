<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArtsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArtsTable Test Case
 */
class ArtsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ArtsTable
     */
    protected $Arts;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Arts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Arts') ? [] : ['className' => ArtsTable::class];
        $this->Arts = $this->getTableLocator()->get('Arts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Arts);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ArtsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
