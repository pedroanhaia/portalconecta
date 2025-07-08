<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ChapterTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ChapterTable Test Case
 */
class ChapterTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ChapterTable
     */
    protected $Chapter;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Chapter',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Chapter') ? [] : ['className' => ChapterTable::class];
        $this->Chapter = $this->getTableLocator()->get('Chapter', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Chapter);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ChapterTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
