<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\OrdersLogTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\OrdersLogTable Test Case
 */
class OrdersLogTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\OrdersLogTable
     */
    protected $OrdersLog;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.OrdersLog',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('OrdersLog') ? [] : ['className' => OrdersLogTable::class];
        $this->OrdersLog = $this->getTableLocator()->get('OrdersLog', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->OrdersLog);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\OrdersLogTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
