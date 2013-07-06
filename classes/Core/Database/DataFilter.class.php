<?php

namespace Core\Database;

class DataFilter {

  public function __construct() {
    $this->aConstraints = array();
  }

  public function addConstraint(DataFilterConstraint $oConstraint) { $this->aConstraints []= $oConstraint;  }

  public function buildSql() {
    $sSql = "";
    $bWhereClause = false;
    foreach( $this->aConstraints as $oConstraint) {

      if( !$bWhereClause ) {
        $sSql .= " WHERE ";
        $bWhereClause = true;
      } else {
        $sSql .= " " . $oConstraint->getLogicalOperator() . " ";
      }

      $sSql .= $oConstraint->buildSql();

    }
    return $sSql;
  }

  protected $aConstraints;

}

?>
