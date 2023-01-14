<?php 

use SigmaPHP\DB\TestCases\DbTestCase;
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
        $this->seeder = new Seeder(
            $this->connectToDatabase()
        );
    }
    
    /**
     * Test insert data into table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testInsertDataIntoTable()
    {
        $this->seeder->insert(
            'test',
            [
                ['name' => 'user1', 'email' => 'email1'],
                ['name' => 'user2', 'email' => 'email2'],
                ['name' => 'user3', 'email' => 'email3'],
            ]
        );

        $dataWasInserted = $this->connectToDatabase()->prepare("
            SELECT * FROM test;
        ");

        $dataWasInserted->execute();
        $this->assertEquals(3, count($dataWasInserted->fetchAll()));
    }

    /**
     * Test update data in table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdateDataInTable()
    {
        $this->seeder->execute("
            INSERT INTO test (name, email) VALUES ('user1', 'email1');
        ");

        $this->seeder->update(
            'test',
            ['email' => 'email1_updated'],
            ['name' => 'user1']
        );

        $dataWasUpdated = $this->connectToDatabase()->prepare("
            SELECT * FROM test WHERE name = 'user1';
        ");

        $dataWasUpdated->execute();

        $this->assertEquals('email1_updated', $dataWasUpdated
            ->fetch()['email']);
    }
    
    /**
     * Test update all data in table if no condition was set.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdateAllDataInTableIfNoConditionWasSet()
    {
        $this->seeder->execute("
            INSERT INTO test (name, email) VALUES
            ('user1', 'email1'),
            ('user2', 'email2'),
            ('user3', 'email3');
        ");

        $this->seeder->update(
            'test',
            ['email' => 'email_with_no_number']
        );

        $dataWasUpdated = $this->connectToDatabase()->prepare("
            SELECT * FROM test WHERE email = 'email_with_no_number';
        ");

        $dataWasUpdated->execute();
        $this->assertEquals(3, count($dataWasUpdated->fetchAll()));
    }

    /**
     * Test delete data from table.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDeleteDataFromTable()
    {
        $this->seeder->execute("
            INSERT INTO test (name, email) VALUES ('user1', 'email1');
        ");

        $this->seeder->delete(
            'test',
            ['name' => 'user1']
        );

        $dataWasDeleted = $this->connectToDatabase()->prepare("
            SELECT * FROM test WHERE name = 'user1';
        ");

        $dataWasDeleted->execute();
        $this->assertFalse($dataWasDeleted->fetch());
    }

    /**
     * Test delete all data from table if no condition was set.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDeleteAllDataFromTableIfNoConditionWasSet()
    {
        $this->seeder->execute("
            INSERT INTO test (name, email) VALUES
            ('user1', 'email1'),
            ('user2', 'email2'),
            ('user3', 'email3');
        ");

        $this->seeder->delete('test');

        $dataWasDeleted = $this->connectToDatabase()->prepare("
            SELECT * FROM test;
        ");

        $dataWasDeleted->execute();
        $this->assertEmpty($dataWasDeleted->fetchAll());
    }
}