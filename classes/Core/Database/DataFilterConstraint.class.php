<?php 
namespace Core\Database;

class DataFilterConstraint {

  const EQUAL = "=";
  const _AND = "AND";

  public function __construct($sPropertyName, $sOperator, $sValue, $sLogicalOperator = "") {
    $this->sValue = $sValue;
    $this->sPropertyName = $sPropertyName;

    if( !self::IsValidOperator($sOperator) )
      throw new Exceptions\DataFilterOperatorInvalid($sOperator); 
    else
     $this->sOperator = $sOperator; 

    if( $sLogicalOperator ) {
      if( !self::IsValidLogicalOperator($sLogicalOperator) )
        throw new Exceptions\DataFilterOperatorInvalid($sLogicalOperator); 
      else
        $this->sLogicalOperator = $sLogicalOperator; 
    }
  }

  public function getValue() { return $this->sValue; }
  public function getOperator() { return $this->sOperator; }
  public function getPropertyName() { return $this->sPropertyName; }
  public function getLogicalOperator() { return $this->sLogicalOperator; }

  public static function GetOperators() { return array(self::EQUAL); }
  public static function IsValidOperator($sOperator) { return array_search($sOperator, self::GetOperators()) !== false; }

  public static function GetLogicalOperators() { return array(self::_AND); }
  public static function IsValidLogicalOperator($sOperator) { return array_search($sOperator, self::GetLogicalOperators()) !== false; }

  public function buildSql() {

    return 
      $this->getPropertyName() . " " . 
      $this->getOperator() . " " . 
      Database::GetInstance()->quote($this->getValue()); 
  }

  protected $sValue;
  protected $sOperator;
  protected $sPropertyName;
  protected $sLogicalOperator;

}

?>
