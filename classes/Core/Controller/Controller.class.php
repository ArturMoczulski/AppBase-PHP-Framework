<?php
namespace Core\Controller;

abstract class Controller {

  public function __construct() {

    \Core\Database\Database::GetInstance()->connect();

    foreach( $this->getModelsUsed() as $sModelClass ) {
      $this->loadModel($sModelClass);
    }

    $this->restoreSession();

  }

  public function invokeControllerAction($sControllerName, $sActionName, $aArguments = array()) {

    $oDispatcher = new \Core\Dispatcher();
    $oResponse = $oDispatcher->dispatch($sControllerName, $sActionName, $aArguments, true); 

    foreach( $oResponse->getViewData() as $sVariableName => $mValue )
      $this->setViewData($sVariableName, $mValue); 

    return $oResponse;

  }

  public function setViewData($sVariableName, $mValue ) {
    $this->aViewData[$sVariableName] = $mValue;
  }

  public function setLayoutData($sVariableName, $mValue) {
    $this->aLayoutData[$sVariableName] = $mValue;
  }

  public function getViewData($sVariableName = "") {
    return $sVariableName ? $this->aViewData[$sVariableName] : $this->aViewData;
  }

  public function getLayoutData($sVariableName = "") {
    return $sVariableName ? $this->aLayoutData[$sVariableName] : $this->aLayoutData;
  }

  public function setValidationErrors($aErrors) { $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] = $aErrors; }

  public function addValidationErrors($aErrors) {
    
    if( !is_array($aErrors) ) return;

    if( !isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']) ) {
      $this->setValidationErrors($aErrors);
    } else {
      $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] = array_merge(
        $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'],
        $aErrors);
    }
  }

  public function addValidationError($sPropertyName, $oError) {

    if( !isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']))
      $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] = array();

    if( !isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'][$sPropertyName]) )
      $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'][$sPropertyName] = array();

    $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'][$sPropertyName] []= $oError;
  }

  protected function getCurrentValidationErrors() {
    return $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'];
  }

  public function getValidationErrors() { 
    return $this->aValidationErrors; 
  }

  public function isValid() { return count($this->getCurrentValidationErrors()) == 0; }

  public function getLoggedUser() { 
    return isset($_SESSION[$GLOBALS['Application']['name']]["oUser"]) ? $_SESSION[$GLOBALS['Application']['name']]["oUser"] : null; 
  }

  public function isUserLogged() { return $this->getLoggedUser() != null; }

  public function setUserLogged($oUser) { 
    $_SESSION[$GLOBALS['Application']['name']]["oUser"] = $oUser; 
    
    if( $oUser && \Models\Permission::CheckByNameAndModel("users/switch", $oUser->group ) ) {
      $_SESSION[$GLOBALS['Application']['name']]["bAllowUserSwitching"] = true;
    } else if( $oUser == null ) {
      unset($_SESSION[$GLOBALS['Application']['name']]["bAllowUserSwitching"]);
    }
  }

  public function allowUserSwitching() { 
    return isset($_SESSION[$GLOBALS['Application']['name']]["bAllowUserSwitching"]) ? 
      $_SESSION[$GLOBALS['Application']['name']]["bAllowUserSwitching"] : 
      false; 
  }

  protected function restoreSession() {
    if( !session_id() ) {
      session_start();
    }

    $this->restoreFlash();
    $this->restoreValidationErrors();
  }

  public function getFlash() { return $this->sFlashMessage; }

  public function setFlash($sMessage) { $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] = $sMessage; }

  protected function restoreFlash() { 
    $this->sFlashMessage = isset($_SESSION[$GLOBALS['Application']['name']]['sFlashMessage']) ? $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] : null; 
    $this->clearFlash(); 
  }

  protected function restoreValidationErrors() {
    $this->aValidationErrors = isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']) ? $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] : null;
    $this->clearValidationErrors();
  }

  protected function clearFlash() {
    $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] = null;
  }

  protected function clearValidationErrors() {
    unset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']);
  }

  public function authenticate($sActionName) {

    if( $this->isUserLogged() ) {

      $sActionName = substr($sActionName, 0, strlen($sActionName) - strlen("Action"));
      if( !$this->checkActionAccess($sActionName) ) {

        $this->redirect("/access/denied");

      }

    } else {
      if( !$this->isPublicAction($sActionName) )
        $this->redirect('/login');
    }
  }

  public function checkActionAccess($sActionName) {
    
    $sACOName =
      \Utils\NounInflector::Underscore(
        \Utils\Namespaces::Strip(get_class($this))) .
      '/' .
      $sActionName;

    // checking group permissions
    $oPermission = null;

    return \Models\Permission::CheckByNameAndModel($sACOName, $this->getLoggedUser()->group);

  }

  public function beforeAction($sActionName) {

    if( !\Controllers\AppInstallation::IsInstalled() &&
        !($this instanceof \Controllers\AppInstallation) &&
        !$this->internalRequest() ) {
      session_destroy();
      $this->redirect("/install");
    } else {

      if( !$this->internalRequest() ) {
        $this->authenticate($sActionName);
      }
    }

    if( $this->allowUserSwitching() ) {
      $oUserModel = new \Models\User();
      $this->setLayoutData("aSwitchableUsers", $oUserModel->find(null, false, 0));
    }

    $this->setLayoutData('oLoggedUser', $this->getLoggedUser());
  }

  public function afterAction($sActionName) {
  }

  public function callAction($sActionName, $aArguments = array(), $bInternalRequest = false ) {

    $this->bInternalRequest = $bInternalRequest;

    $this->beforeAction($sActionName);

    $oReflectSelf = new \ReflectionObject($this);
    $oReflectAction = $oReflectSelf->getMethod($sActionName);

    if( count($aArguments) < $oReflectAction->getNumberOfRequiredParameters() )
      throw new Exceptions\ActionParametersNotSpecified();

    $mResult = $oReflectAction->invokeArgs($this, $aArguments);

    $this->afterAction($sActionName);

    $this->bInternalRequest = false; // reset the internal request state

    return $mResult;

  }

  public function isPostSent() { return !empty($_POST); }

  public function getPostValue($sPropertyName) { 
    return isset($_POST[$sPropertyName]) ? 
      htmlspecialchars($_POST[$sPropertyName]) : 
      null; 
  }

  public function setPostValue($sKey, $sValue) {
    $_POST[$sKey] = $sValue;
  }

  public function getModel() { return $this->oModel; }

  public function redirect($sUrl) { 
    if( !$this->internalRequest() ) {
      header("Location: ".$sUrl);
      exit();
    }
  }

  public function refresh() {
    if( !$this->internalRequest() )
      $this->redirect("");
  }

  protected function isPublicAction($sActionName) {
    if( property_exists( $this, "aPublicActions" ) ) {
      return array_search($sActionName, $this->aPublicActions) !== false; 
    } else
      return false;
  }

  protected function getModelsUsed() { 
    if( property_exists( $this, 'aModelsUsed' ) )
      return $this->aModelsUsed;
    else {
      $sModelClass = 
        "Models\\" . 
        \Utils\NounInflector::Singularize(\Utils\Namespaces::Strip(get_called_class()));
      return array($sModelClass);
    }
  }

  protected function loadModel($sModelClass) {

    if( !class_exists($sModelClass) )
      throw new Exceptions\ModelUndefined($sModelClass);

    $sPropertyName = "o".substr($sModelClass, strlen("Models\\"));
    $this->$sPropertyName = new $sModelClass();
  }

  protected function internalRequest() { return $this->bInternalRequest; }

  protected $oModel = null;
  protected $sFlashMessage = null;
  protected $aValidationErrors = array();
  protected $aViewData = array();
  protected $aLayoutData = array();
  protected $bInternalRequest = false;

}

?>
