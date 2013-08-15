<?php

/**
 * @author Artur Moczulski <artur.moczulski@gmail.com>
 */

namespace Core\Database;

/**
 * \Core\Database\Database
 *
 * This is a singleton class which provides
 * interface to communicate with the database.
 * It can be easily DB abstracted, but currently
 * it assumes a MySQL database. This is pretty
 * much a proxy wrapper around PHP's PDO.
 *
 * NOTE: singleton is a class of which there is
 * at most one instance through out the application
 * at any given time
 */

class Database {
  
  /**
   * Private constructor is a part of singleton pattern
   * implementation
   */
  private function __construct() {}   

  /**
   * Use to access the singleton from any point of the
   * application; this method also provides singleton
   * pattern implementation
   *
   * example usage:
   *  \Core\Database\Database::GetInstance->connect();
   *
   * @return \Core\Database\Database
   */
  public static function GetInstance() {
    if( self::$oInstance == null )
      self::$oInstance = new self();

    return self::$oInstance;
  }

  /**
   * Establish the database connection
   */
  public function connect() {
    $sDsn = "mysql:".
        "dbname=".$GLOBALS['Application']['DB']['name'].";".
        "host=".$GLOBALS['Application']['DB']['host'].";".
        "port=".$GLOBALS['Application']['DB']['port'].";".
        "unix_socket=".$GLOBALS['Application']['DB']['sock'];

    $this->oConnection = new \PDO($sDsn, $GLOBALS['Application']['DB']['user'], $GLOBALS['Application']['DB']['pass']);
  }

  /**
   * Run a SQL query. Returns error information on
   * error.
   *
   * @todo this needs to be changed to use PDO's
   * :argument syntax?
   *
   * @param string $sQuery
   *
   * @return \PDOStatement|array
   */
  public function query($sQuery) {

    $mResult = $this->oConnection->query($sQuery);

    if( $mResult === false ) {

      return $this->oConnection->errorInfo();

    } else {

      return $mResult;

    }

  }

  /**
   * Sanitize the string.
   *
   * @param string $sValue
   *
   * @return string
   */
  public function quote($sValue) { 
    return $this->oConnection->quote($sValue); 
  }

  /**
   * Get id of the last insert.
   *
   * @return int
   */
  public function lastInsertId() { 
    return $this->oConnection->lastInsertId(); 
  }

  /**
   * @var \Core\Database\Database
   */
  private static $oInstance; 

  /**
   * @var \PDO
   */
  protected $oConnection;

}

?>
