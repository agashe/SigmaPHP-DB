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
}