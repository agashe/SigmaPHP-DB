<?php

use SebastianBergmann\CodeCoverage\Report\PHP;
use SigmaPHP\DB\TestCases\DbTestCase;

require('ExampleModel.php');

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
        $objectReflection = new ReflectionClass($object);
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
     * Test query method.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testQueryMethod()
    {
        $addTestData = $this->connectToDatabase()->prepare("
            INSERT INTO example_models
                (name, email)
            VALUES
                ('test1', 'test1@test.local'), 
                ('test2', 'test2@test.local'), 
                ('test3', 'test3@test.local'); 
        ");

        $addTestData->execute();

        $this->assertEquals(
            'test1',
            $this->model->query()
                ->where('name', '=', 'test1')
                ->get()['name']
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
}