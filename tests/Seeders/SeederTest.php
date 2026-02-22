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
        $addTestData = $this->connectToDatabase()->prepare(<<<TEXT
            INSERT INTO test
                (name, email)
            VALUES
                ('<p>Something complex</p>',
                '<div style="border:2px solid #ffa500;">'),
                ('([{email2}])', '<div style="font-size:22px;">↓</div>'),
                ('!@#$%^&*('
                , '<small>(Database / Calling APIs)</small>
                ');
        TEXT);

        $addTestData->execute();

        $this->assertEquals(
            '<div style="border:2px solid #ffa500;">',
            $this->seeder
                ->query()
                ->table('test')
                ->where('name', '=', '<p>Something complex</p>')
                ->get()['email']
        );
    }
}
