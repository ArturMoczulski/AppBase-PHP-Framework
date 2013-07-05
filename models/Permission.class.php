<?php

namespace Models;

class Permission extends \Core\Model {

  public function check() {
    return $this->access == 1;
  }

  /**
   * @throws PermissionUndefined
   */
  public static function CheckByNameAndModel($sACOName, \Core\Model $oModel) {

    $oARO = new \Models\ARO();
    $oARO = $oARO->findByRequestObject($oModel);

    return self::CheckByNameAndARO($sACOName, $oARO);
  }

  public static function CheckByNameAndARO($sACOName, $oARO) {
    
    $oACO = new \Models\ACO();
    $oACO = $oACO->findBy("name", $sACOName, true);

    if( $oACO && $oARO ) {

      return self::CheckByACOAndARO($oACO, $oARO);
    
    } else {
      if( $oACO )
        return $oACO->default_access;
      else 
        return true;      
    }

  }

  public static function CheckByACOAndARO($oACO, $oARO) {
    
      $oPermission = new self();
      $oPermission = $oPermission->findByACOAndARO($oACO, $oARO);

      if( $oPermission ) 
        return $oPermission->check();
      else 
        return $oACO->default_access == 1;

  }

  public function findByACOAndARO($oACO, $oARO, $iDepth = 1) {
    
    $oFilter = new \Core\DataFilter();

    $oFilter->addConstraint(
      new \Core\DataFilterConstraint(
        "aco_id", 
        \Core\DataFilterConstraint::EQUAL, 
        $oACO->id));

    $oFilter->addConstraint(
      new \Core\DataFilterConstraint("aro_id", 
      \Core\DataFilterConstraint::EQUAL, 
      $oARO->id,
      \Core\DataFilterConstraint::_AND));

    return $this->find($oFilter, true, $iDepth);

  }

  protected $aBelongsTo = array("ACO","ARO");

  protected $aValidation = array(
    'access' => array('NotEmpty') );

}
