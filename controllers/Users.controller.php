<?php
namespace Controllers;

class Users extends \Core\Controller\Controller {

  protected $aModelsUsed = array("Models\\User", "Models\\Group");
  protected $aPublicActions = array("loginAction");

  public function saveAction($iId = null) {

    $oUser = $iId ?  
      $this->oUser->findBy("id", $iId, true) :
      new \Models\User();

    if( $this->isPostSent() ) {

      // Password is a controller level input, validation is being done outside of model
      if( !$this->getPostValue('sPassword') ) {
        $this->addValidationError(
          'password',
          new \Core\Validation\RuleResult(false, "Password is empty.")
        );
      }

      // Checking if there are no typos in the password
      if( $this->getPostValue('sPassword') != $this->getPostValue('sPasswordConfirm') ) {
        $this->addValidationError(
          'password',
          new \Core\Validation\RuleResult(false, "Passwords don't match."));
      }

      if( !$oUser )
        $oUser = new \Models\User(); 
      $oUser->group = $this->oGroup->findBy("id", $this->getPostValue("iGroupId"), true);
      $oUser->email = $this->getPostValue('sEmail');
      $oUser->password = $this->getPostValue('sPassword');
      
      // model validation needs to be done outside of save to
      // allow merging of model and controller level validation
      $this->addValidationErrors($oUser->validate());

      if( $this->isValid() ) {

        $oSaveResult = $oUser->save();

        if( $oSaveResult->success() ) {
          $this->setFlash("User added.");
          $this->redirect("/users");
        } else {
          $this->refresh();
        }

      } else {
        $this->refresh();
      }

    }

    $this->setViewData('oUser', $oUser);
    $this->setViewData('bAdd', $oUser->isNew());
    $this->setViewData('aGroups', $this->oGroup->find());

  }

  public function indexAction() {

    $this->setViewData('oModel', $this->oUser);
    $this->setViewData('aData', $this->oUser->find());

  }

  public function deleteAction($sId) {
    $oUser = $this->oUser->findBy("id",$sId,true);
    if( $oUser ) {
      $oUser->delete();
      $this->setFlash("User has been deleted.");
      $this->redirect("/users");
    } else 
      throw new \Core\Exceptions\ResourceNotFound();
  }

  public function loginAction() {

    if( $this->isPostSent() ) {
      $oUser = $this->oUser->findBy("email", $this->getPostValue("sEmail"), true);
      if( $oUser && $oUser->authenticate($this->getPostValue("sPassword")) ) {
        $this->setUserLogged($oUser);
        $this->redirect('/');
      } else {
        $this->addValidationError("email", new \Core\Validation\RuleResult(false, "Invalid credentials."));
        $this->refresh();
      }
    }

  }

  public function logoutAction() {
    if( $this->isUserLogged() ) $this->setUserLogged(null);
    $this->redirect('/');
  }

  public function switchAction() {

    $oUser = $this->oUser->findBy("id", $this->getPostValue("iUserId"), true);
    if( $oUser ) {
      $this->setUserLogged($oUser);
      $this->setFlash("Switched to a different user.");
    } else {
      $this->addValidationError("user", new \Core\Validation\RuleResult(false, "User does not exist."));
    }

    $this->redirect('/');

  }

  public function checkActionAccess($sActionName) {

    if( $sActionName == "switch" ) {
      // overriding usual permission checking to additional conditions
      return $this->allowUserSwitching();
    } else {
      return parent::checkActionAccess($sActionName);
    }

  }

}

?>
