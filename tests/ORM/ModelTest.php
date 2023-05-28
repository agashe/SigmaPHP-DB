<?php

use SigmaPHP\DB\TestCases\DbTestCase;

require('ExampleModel.php');
require('RelationExampleModel.php');

/**
 * Model Test
 */
class ModelTest extends DbTestCase
{
    /**
     * @var Model $model
     */
    private $model;

    /**
     * ModelTest SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        
        // create tests table
        $this->createTestTable('example_models');

        // create new example model instance
        $this->model = new ExampleModel(
            $this->connectToDatabase(),
            $this->dbConfigs['name']
        );
    }

    /**
     * ModelTest TearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // drop tests table
        $this->dropTestTable('example_models');
    }
    
    /**
     * Get value of private property.
     *
     * @param mixed $object
     * @param string $property
     * @return mixed
     */
    private function getPrivatePropertyValue($object, $property)
    {
        $objectReflection = new \ReflectionClass($object);
        $propertyReflection = $objectReflection->getProperty($property);
        $propertyReflection->setAccessible(true);
        
        return $propertyReflection->getValue($object);
    }
    
    /**
     * Test model create table name automatically.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testModelCreateTableNameAutomatically()
    {
        $this->assertEquals(
            'example_models', 
            $this->getPrivatePropertyValue($this->model, 'table')
        );
    }

    /**
     * Test model fetch all fields in table automatically.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testModelFetchAllFieldsInTableAutomatically()
    {
        $this->assertEquals(
            ['id', 'name', 'email', 'age'], 
            $this->getPrivatePropertyValue($this->model, 'fields')
        );
    }
    
    /**
     * Test model access fields dynamically.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testModelAccessFieldsDynamically()
    {
        $this->model->name = 'hello';

        $this->assertEquals(
            'hello', 
            $this->model->name
        );
    }
    
    /**
     * Test throws exception if field does not exists.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testThrowsExceptionIfFieldDoesNotExists()
    {
        $this->expectException(\Exception::class);
        $this->model->gender = 'hello';
    }

    /**
     * Test get table name method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testGetTableNameMethod()
    {
        $this->assertEquals(
            'example_models', 
            $this->model->getTableName()
        );
    }

    /**
     * Test create model method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testCreateMethod()
    {
        $tempModel =  $this->model->create([
            'name' => 'test1',
            'email' => 'test1@test.local',
            'age' => 15
        ]);

        $this->assertInstanceOf(ExampleModel::class, $tempModel);
        $this->assertEquals('1', $tempModel->id);
        $this->assertEquals('test1', $tempModel->name);
        $this->assertEquals('test1@test.local', $tempModel->email);
        $this->assertEquals(15, $tempModel->age);
    }

    /**
     * Test all method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testAllMethod()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO example_models
                (name, email, age)
            VALUES
                ('test1', 'test1@test.local', 13), 
                ('test2', 'test2@test.local', 14), 
                ('test3', 'test3@test.local', 15); 
        ");

        $addTestData->execute();

        $testModels = $this->model->all();

        $this->assertEquals(3, count($testModels));
        
        foreach ($testModels as $testModel) {
            $this->assertInstanceOf(ExampleModel::class, $testModel);
        }
        
        $this->assertEquals(14, $testModels[1]->age);
    }
    
    /**
     * Test count method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testCountMethod()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO example_models
                (name, email, age)
            VALUES
                ('test1', 'test1@test.local', 13), 
                ('test2', 'test2@test.local', 14), 
                ('test3', 'test3@test.local', 15); 
        ");

        $addTestData->execute();

        $this->assertEquals(3, $this->model->count());
    }
    
    /**
     * Test find method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testFindMethod()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO example_models
                (name, email, age)
            VALUES
                ('test1', 'test1@test.local', 13), 
                ('test2', 'test2@test.local', 14), 
                ('test3', 'test3@test.local', 15); 
        ");

        $addTestData->execute();

        $testModel = $this->model->find(3);

        $this->assertInstanceOf(ExampleModel::class, $testModel);      
        $this->assertEquals('test3@test.local', $testModel->email);
    }
    
    /**
     * Test find by method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testFindByMethod()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO example_models
                (name, email, age)
            VALUES
                ('test1', 'test1@test.local', 13), 
                ('test2', 'test2@test.local', 14), 
                ('test3', 'test3@test.local', 15); 
        ");

        $addTestData->execute();

        $testModel = $this->model->findBy('age', 13);

        $this->assertInstanceOf(ExampleModel::class, $testModel);
        $this->assertEquals('test1', $testModel->name);
    }

    /**
     * Test create new model.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testCreateNewModel()
    {
        $this->model->name = 'hello';
        $this->model->email = 'hello@world.com';
        $this->model->save();

        $dataWasSaved = $this->connectToDatabase()->prepare('
            SELECT * FROM example_models;
        ');

        $dataWasSaved->execute();
        
        $this->assertEquals(1, $dataWasSaved->fetch()['id']);
        $this->assertEquals(1, $this->model->id);
        $this->assertEquals(
            false,
            $this->getPrivatePropertyValue($this->model, 'isNew')
        );
    }

    /**
     * Test update model.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdateModel()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO example_models
                (name, email, age)
            VALUES
                ('test1', 'test1@test.local', 13); 
        ");

        $addTestData->execute();

        $testModel = $this->model->find(1);
        $testModel->name = 'hello';
        $testModel->email = 'hello@world.com';
        $testModel->age = 20;
        $testModel->save();

        $dataWasSaved = $this->connectToDatabase()->prepare('
            SELECT * FROM example_models;
        ');

        $dataWasSaved->execute();
        $testResult = $dataWasSaved->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('hello', $testResult['name']);
        $this->assertEquals('hello@world.com', $testResult['email']);
        $this->assertEquals(20, $testResult['age']);
        $this->assertEquals(
            false,
            $this->getPrivatePropertyValue($testModel, 'isNew')
        );
    }

    /**
     * Test delete model.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testDeleteModel()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO example_models
                (name, email, age)
            VALUES
                ('test1', 'test1@test.local', 13); 
        ");

        $addTestData->execute();

        $testModel = $this->model->find(1);
        $testModel->delete();

        $dataWasSaved = $this->connectToDatabase()->prepare('
            SELECT * FROM example_models;
        ');

        $dataWasSaved->execute();

        $this->assertFalse($dataWasSaved->fetch());
        $this->assertEquals(
            true,
            $this->getPrivatePropertyValue($testModel, 'isNew')
        );
    }
    
    /**
     * Test has relation method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testHasRelationMethod()
    {
        $testTable = $this->connectToDatabase()->prepare("
            CREATE TABLE IF NOT EXISTS test_relations (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                example_id INT(11) UNSIGNED DEFAULT 0,
                address VARCHAR(50) NOT NULL
            );
        ");

        $testTable->execute();

        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO example_models
                (name, email, age)
            VALUES
                ('test1', 'test1@test.local', 13),
                ('test2', 'test2@test.local', 14);
        ");

        $addTestData->execute();

        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO test_relations
                (example_id, address)
            VALUES
                (2, 'test address 1'),
                (1, 'test address 2'),
                (2, 'test address 3');
        ");

        $addTestData->execute();

        $testModel1 = $this->model->find(1); 
        $relatedModels = $testModel1->relationExamples();

        $this->assertEquals(1, count($relatedModels));
        $this->assertInstanceOf(
            RelationExampleModel::class,
            $relatedModels[0]
        );

        $testModel2 = $this->model->find(2); 
        $relatedModels = $testModel2->relationExamples();

        $this->assertEquals(2, count($relatedModels));
        $this->assertInstanceOf(
            RelationExampleModel::class,
            $relatedModels[0]
        );
        $this->assertInstanceOf(
            RelationExampleModel::class,
            $relatedModels[1]
        );

        $this->dropTestTable('test_relations');
    }
}