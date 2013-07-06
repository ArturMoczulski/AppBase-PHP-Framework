<?php

namespace Core\Database;

/**
 * \Core\Database\DataFilter
 *
 * Allows treating SQL query filters as sets of constraint objects.
 *
 * TODO: it might be beneficial to introduce support of the 
 * Reverse Polish Notation for logical operators
 */
class DataFilter {

  public function addConstraint(DataFilterConstraint $oConstraint) { 
    $this->aConstraints []= $oConstraint;  
  }

  /**
   * provides the "WHERE ..." SQL code to use in a query
   */
  public function buildSql() {
    $sSql = "";
    $bWhereClause = false;
    foreach( $this->aConstraints as $oConstraint) {

      if( !$bWhereClause ) {
        // add the where clause for the first constraint
        $sSql .= " WHERE ";
        $bWhereClause = true;
      } else {
        // add the logical operators for multiple constraints
        $sSql .= " " . $oConstraint->getLogicalOperator() . " ";
      }

      $sSql .= $oConstraint->buildSql();

    }
    return $sSql;
  }

  protected $aConstraints = array();

}

?>
