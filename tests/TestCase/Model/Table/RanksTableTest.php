<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RanksTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RanksTable Test Case
 */
class RanksTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RanksTable
     */
    protected $Ranks;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Ranks',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Ranks') ? [] : ['className' => RanksTable::class];
        $this->Ranks = $this->getTableLocator()->get('Ranks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Ranks);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\RanksTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
