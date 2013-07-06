<?php
namespace Core\Database;

// Singleton
class Database {

  private function __construct() {}   

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

  // Proxy
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
