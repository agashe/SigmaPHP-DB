<?php

use SigmaPHP\DB\Tests\TestCases\DbTestCase;
use SigmaPHP\DB\Seeders\Seeder;

/**
 * Seeder Test
 */
class SeederTest extends DbTestCase
{    
    /**
     * @var Seeder $seeder
     */
    private $seeder;

    /**
     * SeederTest SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // create new seeder instance
        $this->seeder = new class($this->connectToDatabase()) extends Seeder {
            public function query() {
                return $this->queryBuilder;
            }
        };
    }

    /**
     * Test seeder can use query builder.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testSeederCanUseQueryBuilder()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test
                (name, email)
            VALUES
                ('user1', 'email1'),
                ('user2', 'email2'),
                ('user3', 'email3');
        ");

        $addTestData->execute();

        $this->assertEquals(
            'email2',
            $this->seeder
                ->query()
                ->table('test')
                ->where('name', '=', 'user2')
                ->get()['email']
        );
    }
}