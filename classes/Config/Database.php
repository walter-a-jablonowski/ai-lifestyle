<?php
namespace AILifestyle\Config;

use Symfony\Component\Yaml\Yaml;

class Database
{
  private static $instance = null;
  private $connection;

  private function __construct()
  {
    // Load configuration from YAML file
    $configPath = dirname( dirname( dirname( __FILE__ ) ) ) . '/config.yml';
    $config = Yaml::parseFile( $configPath );
    
    $host = $config['database']['host'];
    $dbname = $config['database']['dbname'];
    $username = $config['database']['username'];
    $password = $config['database']['password'];

    try
    {
      $this->connection = new \PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
          \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
          \PDO::ATTR_EMULATE_PREPARES => false
        ]
      );
    }
    catch( \PDOException $e )
    {
      throw new \Exception( "Database connection failed: " . $e->getMessage() );
    }
  }

  public static function getInstance() : self
  {
    if( self::$instance === null )
    {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function getConnection() : \PDO
  {
    return $this->connection;
  }
}
