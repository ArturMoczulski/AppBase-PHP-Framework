<?php

namespace Models;

class ARO extends \Core\Model\Model {

  public function findByRequestObject(\Core\Model\Model $oModel, $iDepth = 1) {

    $oFilter = new \Core\Database\DataFilter();

    $oFilter->addConstraint(
      new \Core\Database\DataFilterConstraint(
        "object_id", 
        \Core\Database\DataFilterConstraint::EQUAL, 
        $oModel->id));

    $oFilter->addConstraint(
      new \Core\Database\DataFilterConstraint(
        "table_name", 
        \Core\Database\DataFilterConstraint::EQUAL, 
        $oModel->getTableName(),
        \Core\Database\DataFilterConstraint::_AND));

    return $this->find($oFilter, true, $iDepth);

  }

  public function findRequestObject(\Models\ARO $oARO, $iDepth = 1) {

    $sModelName = \Utils\NounInflector::Camelize(\Utils\NounInflector::Singularize($oARO->table_name));
    $sModelClass = "\\Models\\".$sModelName;

    $oModel = new $sModelClass();
    return $oModel->findBy("id", $oARO->object_id, true);
  
  }

  public function getTableName() { return "aro"; }

  public function toString() { 
    return $this->findRequestObject($this)->toString(); 
  }

  protected $aHasMany = array("Permission");

  protected $aValidation = array(
    'object_id' => array('NotEmpty'),
    'table_name' => array('NotEmpty') );

}
