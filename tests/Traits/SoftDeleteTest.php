<?php

use SigmaPHP\DB\TestCases\DbTestCase;

require('SoftDeleteExampleModel.php');

/**
 * SoftDelete Test
 */
class SoftDeleteTest extends DbTestCase
{
    /**
     * @var SoftDeleteExampleModel $model
     */
    private $model;

    /**
     * SoftDeleteTest SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // create tests table
        $this->createTestTable('test_soft_delete');

        // add soft delete field
        $addSoftDeleteField = $this->connectToDatabase()->prepare("
            ALTER TABLE test_soft_delete ADD deleted_at timestamp;
        ");

        $addSoftDeleteField->execute();

        // create new soft delete example model instance
        $this->model = new SoftDeleteExampleModel(
            $this->connectToDatabase(),
            $this->dbConfigs['name']
        );
    }

    /**
     * SoftDeleteTest TearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // drop tests table
        $this->dropTestTable('test_soft_delete');
    }

    /**
     * Test soft delete works with model.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testSoftDeleteWorksWithModel()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test_soft_delete
                (name, email, age)
            VALUES
                ('test1', 'test1@testing.com', 15);
        ");

        $addTestData->execute();

        $testModel = $this->model->find(1);
        $testModel->delete();

        $query = $this->connectToDatabase()->prepare("
            SELECT
                id, deleted_at
            FROM
                test_soft_delete
            WHERE
                id = 1
        ");

        $query->execute();
        $checkRow = $query->fetch();
        $this->assertNotEmpty($checkRow['deleted_at']);
    }

    /**
     * Test soft deleted models are not returned in the queries.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testSoftDeletedModelsAreNotReturnedInTheQueries()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test_soft_delete
                (name, email, age, deleted_at)
            VALUES
                ('test1', 'test1@testing.com', 15, NULL), 
                ('test2', 'test2@testing.com', 25, NOW()), 
                ('test3', 'test3@testing.com', 35, NULL);
        ");

        $addTestData->execute();

        $this->assertEquals(2, $this->model->count());
    }

    /**
     * Test soft deleted models cen be returned in the queries.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testSoftDeletedModelsCanBeReturnedInTheQueries()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test_soft_delete
                (name, email, age, deleted_at)
            VALUES
                ('test1', 'test1@testing.com', 15, NULL), 
                ('test2', 'test2@testing.com', 25, NOW()), 
                ('test3', 'test3@testing.com', 35, NULL);
        ");

        $addTestData->execute();

        $this->assertEquals(3, $this->model->withTrashed()->count());
    }

    /**
     * Test check if model is soft deleted.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testCheckIfModelIsSoftDeleted()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test_soft_delete
                (name, email, age, deleted_at)
            VALUES
                ('test1', 'test1@testing.com', 15, NULL), 
                ('test2', 'test2@testing.com', 25, NOW()),
                ('test3', 'test3@testing.com', 35, NOW());
        ");

        $addTestData->execute();

        $trashedModelsCount = 0;
        $testModels = $this->model->withTrashed()->all();
        // var_dump($testModels);
        foreach ($testModels as $testModel) {
            if ($testModel->isTrashed()) {
                $trashedModelsCount += 1;
            }
        }

        $this->assertEquals(2, $trashedModelsCount);
    }

    /**
     * Test soft deleted models cen be restored.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testSoftDeletedModelsCanBeRestored()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test_soft_delete
                (name, email, age, deleted_at)
            VALUES
                ('test1', 'test1@testing.com', 15, NOW());
        ");

        $addTestData->execute();

        $testModel = $this->model->withTrashed()->find(1);
        $testModel->restore();
        
        $query = $this->connectToDatabase()->prepare("
            SELECT
                id, deleted_at
            FROM
                test_soft_delete
            WHERE
                deleted_at IS NOT NULL
        ");

        $query->execute();
        $checkNoDeletedRows = $query->fetch();
        $this->assertEmpty($checkNoDeletedRows);
    }
}
