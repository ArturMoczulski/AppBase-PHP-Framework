<?php 

/**
 * @author Artur Moczulski <artur.moczulski@gmail.com>
 */

namespace Core\Database;

/**
 * \Core\Database\DataFilterConstraint
 *
 * Allows treating SQL filter constraint as objects, allowing
 * later conjuction in \Core\Datbase\DataFilter objects.
 *
 * The most important role of the class is to provide user
 * input escaping logic which provides security against 
 * SQL injection attacks.
 *
 * Currently only supports the following comparison SQL operators:
 * *, =
 *
 * Currently only supports the following logical operators:
 * * AND
 *
 * TODO: providing support for full range of comparison and logical
 * operators
 */
class DataFilterConstraint {

  const EQUAL = "=";
  const _AND = "AND";

  /**
   * @param string $sPropertyName
   * @param string $sOperator
   * @param string $sValue
   * @param string $sLogicalOperator (optional)
   */
  public function __construct($sPropertyName, $sOperator, $sValue, $sLogicalOperator = "") {

    $this->sValue = $sValue;
    $this->sPropertyName = $sPropertyName;

    // validating operators input
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
  public static function IsValidOperator($sOperator) { 
    return array_search($sOperator, self::GetOperators()) !== false; 
  }

  public static function GetLogicalOperators() { return array(self::_AND); }
  public static function IsValidLogicalOperator($sOperator) { 
    return array_search($sOperator, self::GetLogicalOperators()) !== false; 
  }

  /**
   * Generates the SQL code for the constraint, i.e.
   * "id = 1"
   *
   * IMPORTANT: ensures the user input is being escaped correctly
   *
   * @return string
   */
  public function buildSql() {

    return 
      $this->getPropertyName() . " " . 
      $this->getOperator() . " " . 
      Database::GetInstance()->quote(strval($this->getValue())); 
  }

  /**
   * @var string
   */
  protected $sValue;

  /**
   * @var string
   */
  protected $sOperator;

  /**
   * @var string
   */
  protected $sPropertyName;

  /**
   * @var string
   */
  protected $sLogicalOperator;

}

?>
