<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FilesLogTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FilesLogTable Test Case
 */
class FilesLogTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FilesLogTable
     */
    protected $FilesLog;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.FilesLog',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('FilesLog') ? [] : ['className' => FilesLogTable::class];
        $this->FilesLog = $this->getTableLocator()->get('FilesLog', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->FilesLog);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\FilesLogTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
