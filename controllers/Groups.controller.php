<?php

namespace Controllers;

class Groups extends \Core\Controller\Controller {

  public function viewAction($sId) {
    $oGroup = $this->oGroup->findBy("id",$sId,true);
    if( $oGroup ) {
      $this->setViewData('oGroup', $oGroup);
      $this->setViewData('oUserModel', new \Models\User());
    } else
      throw new \Core\Exceptions\ResourceNotSpecified();
  }

  public function indexAction() {
    $this->setViewData('oModel', $this->oGroup);
    $this->setViewData('aData', $this->oGroup->find());
  }

  public function deleteAction($sId) {
    $oGroup = $this->oGroup->findBy("id",$sId,true);
    if( $oGroup ) {
      $oGroup->delete();
      $this->setFlash("Group has been deleted.");
      $this->redirect("/groups");
    } else 
      throw new \Core\Exceptions\ResourceNotFound();
  }

  public function saveAction($iId = null) {

    $oGroup = $iId ? 
      $this->oGroup->findBy("id",$iId,true) :
      new \Models\Group();

    if( $this->isPostSent() ) {
    
      $oGroup->title = $this->getPostValue('sTitle');
      $oSaveResult = $oGroup->save();

      if( $oSaveResult->success() ) {
        $this->setFlash("Group saved.");
        $this->redirect("/groups");
      } else {
        $this->setValidationErrors($oSaveResult->getValidationErrors());
      }

    }

    $this->setViewData('bAdd', $oGroup->isNew());
    $this->setViewData('oGroup', $oGroup);

  }
}

?>
