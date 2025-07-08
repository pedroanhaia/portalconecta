<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\GrupoUsersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\GrupoUsersTable Test Case
 */
class GrupoUsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\GrupoUsersTable
     */
    protected $GrupoUsers;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.GrupoUsers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('GrupoUsers') ? [] : ['className' => GrupoUsersTable::class];
        $this->GrupoUsers = $this->getTableLocator()->get('GrupoUsers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->GrupoUsers);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\GrupoUsersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
