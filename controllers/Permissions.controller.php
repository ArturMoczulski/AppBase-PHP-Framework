<?php

namespace Controllers;

class Permissions extends \Core\Controller {

  protected $aModelsUsed = array("Models\\Permission","Models\\ACO","Models\\ARO");

  public function indexAction() {

    $aACOs = $this->oACO->find();
    $aAROs = $this->oARO->find();

    $aPermissionsAssoc = array();

    foreach( $aACOs as $oACO ) {

      $aPermissionsAssoc[$oACO->name]  = array();

      foreach( $aAROs as $oARO ) {

        $aPermissionsAssoc[$oACO->toString()][$oARO->toString()] = 
          $this->oPermission->CheckByACOAndARO($oACO, $oARO);

      }

    }

    $this->setViewData("aPermissions", $aPermissionsAssoc);
    $this->setViewData("aAROs", $aAROs);
    $this->setViewData("aACOs", $aACOs);

  }

}
