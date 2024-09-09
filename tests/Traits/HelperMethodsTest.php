<?php 

use SigmaPHP\DB\Tests\TestCases\DbTestCase;
use SigmaPHP\DB\Traits\HelperMethods;

/**
 * HelperMethods Test
 */
class HelperMethodsTest extends DbTestCase
{
    /**
     * @var object $testTrait
     */
    private $testTrait;
    
    /**
     * HelperMethodsTest SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // create new instance of anonymous class that
        // implements HelperMethods Trait
        $this->testTrait = $this->createTestObject();
    }
    
    /**
     * Create new instance from test class.
     *
     * @return object
     */
    private function createTestObject()
    {
        return new class($this->connectToDatabase()) {
            use HelperMethods;

            private $dbConnection;
            
            public function __construct($dbConnection)
            {
                $this->dbConnection = $dbConnection;
            }
        };
    }

    /**
     * Test add quotes to string.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testAddQuotesToString()
    {
        $this->assertEquals("'test'", $this->testTrait->addQuotes('test'));
    }
    
    /**
     * Test no quotes are added to numeric values.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testNoQuotesAreAddedToNumericValues()
    {
        $this->assertEquals(10, $this->testTrait->addQuotes(10));
    }

    /**
     * Test no quotes are added to SQL functions.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testNoQuotesAreAddedToSqlFunctions()
    {
        $this->assertEquals('CONCAT(....)', 
            $this->testTrait->addQuotes('CONCAT(....)'));
        $this->assertEquals('TRIM(....)', 
            $this->testTrait->addQuotes('TRIM(....)'));
        $this->assertEquals('count(*)', 
            $this->testTrait->addQuotes('count(*)'));
        $this->assertEquals('Date("2023-3-3")', 
            $this->testTrait->addQuotes('Date("2023-3-3")'));
    }
    
    /**
     * Test no quotes are added to SQL constants.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testNoQuotesAreAddedToSqlConstants()
    {
        $this->assertEquals('CURRENT_TIMESTAMP', 
            $this->testTrait->addQuotes('CURRENT_TIMESTAMP')
        );
    }
    
    /**
     * Test no quotes are added to null value.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testNoQuotesAreAddedToNullValue()
    {
        $this->assertEquals('Null', 
            $this->testTrait->addQuotes('Null'));
        $this->assertEquals('null', 
            $this->testTrait->addQuotes('null'));
        $this->assertEquals('NULL', 
            $this->testTrait->addQuotes('NULL'));
        $this->assertEquals('NuLl', 
            $this->testTrait->addQuotes('NuLl'));
    }

    /**
     * Test concatenate tokens.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testConcatenateTokens()
    {
        $this->assertEquals(
            'test1,test2,test3',
            $this->testTrait->concatenateTokens(
                ['test1', 'test2', 'test3']
            )
        );
    }

    /**
     * Test concatenate tokens and add quotes.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testConcatenateTokensAndAddQuotes()
    {
        $this->assertEquals(
            "12,'test',NUll",
            $this->testTrait->concatenateTokens(
                ['12', 'test', 'NUll'], 
                true
            )
        );
    }
}