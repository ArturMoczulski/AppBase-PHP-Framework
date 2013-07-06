<?php
namespace Core\Database;

/**
 * \Core\Database\Database
 *
 * This is a singleton class which provides
 * interface to communicate with the database.
 * It can be easily DB abstracted, but currently
 * it assumes a MySQL database. This is pretty
 * much a proxy wrapper PHP's PDO.
 *
 * NOTE: singleton is a class of which there is
 * at most one instance through out the application
 * at any given time
 */
class Database {
  
  /**
   * private constructor is a part of singleton pattern
   * implementation
   */
  private function __construct() {}   

  /**
   * use to access the singleton from any point of the
   * application; this method also provides singleton
   * pattern implementation
   *
   * example usage:
   *  \Core\Database\Database::GetInstance->connect();
   */
  public static function GetInstance() {
    if( self::$oInstance == null )
      self::$oInstance = new self();

    return self::$oInstance;
  }

  public function connect() {
    $sDsn = "mysql:".
        "dbname=".$GLOBALS['Application']['DB']['name'].";".
        "host=".$GLOBALS['Application']['DB']['host'].";".
        "port=".$GLOBALS['Application']['DB']['port'].";".
        "unix_socket=".$GLOBALS['Application']['DB']['sock'];

    $this->oConnection = new \PDO($sDsn, $GLOBALS['Application']['DB']['user'], $GLOBALS['Application']['DB']['pass']);
  }

  public function query($sQuery) {
    $mResult = $this->oConnection->query($sQuery);
    if( $mResult === false )
      return $this->oConnection->errorInfo();
    else
      return $mResult;
  }

  public function quote($sValue) { return $this->oConnection->quote($sValue); }

  public function lastInsertId() { return $this->oConnection->lastInsertId(); }

  private static $oInstance; 
  protected $oConnection;

}

?>
