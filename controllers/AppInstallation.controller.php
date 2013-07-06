<?php

namespace Controllers;

class AppInstallation extends \Core\Controller\Controller {

  public static function IsInstalled() {

    $oUserModel = new \Models\User();
    $oGroupModel = new \Models\Group();
    $oSuperuserGroup = $oGroupModel->findBy("title",\Models\Group::SUPERUSER,true,false);

    if( !$oSuperuserGroup ) throw new \Core\Exceptions\ApplicationInstallationError();

    // TODO: add finding by relations
    if( count($oUserModel->findBy("group_id", $oSuperuserGroup->id, false, false)) >= 1 ) 
      return true; 
    else 
      return false;

  }

  public function installAction() {

    if( self::IsInstalled() ) {
      // action not available if the app is already installed
      $this->redirect("/");
    } else {
      $this->invokeControllerAction("Users", "logout");
    }

    if( $this->isPostSent() ) {
      // the first user is always a super user
      $oGroupModel = new \Models\Group();
      $oSuperuserGroup = $oGroupModel->findBy("title",\Models\Group::SUPERUSER,true);
      $this->setPostValue('iGroupId', $oSuperuserGroup->id);
    }

    // performing the user add
    $this->invokeControllerAction("Users", "save", array(null));

    if( $this->isPostSent() ) {

      if( self::IsInstalled() ) {
        // auto login after installation
        $this->invokeControllerAction("Users", "login");
        $this->setFlash("Application installed successfully.");
        $this->redirect("/");
      } else
        $this->refresh();

    }

  }

  protected $aPublicActions = array("installAction");
  protected $aModelsUsed = array('Models\User','Models\Group');

}

?>
