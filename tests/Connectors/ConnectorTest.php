<?php 

use PHPUnit\Framework\TestCase;

use SigmaPHP\DB\Connectors\Connector;

/**
 * Connector Test
 */
class ConnectorTest extends TestCase
{
    /**
     * @var array $dbConfigs
     */
    private $dbConfigs;
    
    /**
     * @var Connector $connector
     */
    private $connector;

    /**
     * ConnectorTest SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        // add your database configs to phpunit.xml
        $this->dbConfigs = [
            'host' => $GLOBALS['DB_HOST'],
            'name' => $GLOBALS['DB_NAME'],
            'user' => $GLOBALS['DB_USER'],
            'pass' => $GLOBALS['DB_PASS'],
            'port' => $GLOBALS['DB_PORT']
        ];
        
        // create new connector
        $this->connector = new Connector();
    }

    /**
     * Test PDO connection is created successfully.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testPdoConnectionIsCreatedSuccessfully()
    {
        $this->assertInstanceOf(
            \PDO::class,
            $this->connector->connect(
                $this->dbConfigs
            )
        );
    }
}