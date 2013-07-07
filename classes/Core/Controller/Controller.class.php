<?php
namespace Core\Controller;

/**
 * \Core\Controller\Controller
 *
 * The base controller logic. Provides support for following tasks:
 * * handling sessions,
 * * resource access restriction,
 * * initiating connection to the database,
 * * initiating Database Access Objects,
 * * storing validation errors,
 * * storing flash messages,
 * * handling POST data,
 * * invoking actions with respect to beforeAction() and afterAction() hooks,
 * * ensuring the application is installed correctly by enforcing redirections,
 * * providing means for sending data to views and layouts.
 */
abstract class Controller {

  public function __construct() {

    \Core\Database\Database::GetInstance()->connect();

    // loading DAO
    foreach( $this->getModelsUsed() as $sModelClass ) {
      $this->loadModel($sModelClass);
    }

    $this->restoreSession();

  }

  /**
   * This method allows invoking controller's action from another controller.
   * It ensures that the action will be processed in the same manner as it
   * would be access directly by the request. That includes transferring
   * the view data from the invoked controller to the current one seamlessly.
   *
   * This is also referred to as an "internal request"
   *
   * @returns \Core\Controller\ControllerResponse
   */
  public function invokeControllerAction($sControllerName, $sActionName, $aArguments = array()) {

    $oDispatcher = new \Core\Dispatcher();
    $oResponse = $oDispatcher->dispatch(
      $sControllerName, 
      $sActionName, 
      $aArguments, 
      $this->getRequestedPath(),
      true); 

    foreach( $oResponse->getViewData() as $sVariableName => $mValue )
      $this->setViewData($sVariableName, $mValue); 

    return $oResponse;

  }

  /**
   * use to send data to the view
   */
  public function setViewData($sVariableName, $mValue ) {
    $this->aViewData[$sVariableName] = $mValue;
  }

  /**
   * use to send data to the layout
   */
  public function setLayoutData($sVariableName, $mValue) {
    $this->aLayoutData[$sVariableName] = $mValue;
  }

  public function getViewData($sVariableName = "") {
    return $sVariableName ? $this->aViewData[$sVariableName] : $this->aViewData;
  }

  public function getLayoutData($sVariableName = "") {
    return $sVariableName ? $this->aLayoutData[$sVariableName] : $this->aLayoutData;
  }

  /**
   * NOTE: using this method will override whatever else validation errors
   * have been already added. To simply add more see addValidationErrors()
   * and addValidationError()
   */
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

  /**
   * allows accessing validation errors that have been set up by the current
   * action for the next request
   */
  protected function getCurrentValidationErrors() {
    return isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']) ?
        $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] : array();
  }

  /**
   * allows accessing errors set up by the last requested action
   */
  public function getValidationErrors() { 
    return $this->aValidationErrors; 
  }

  /**
   * checks if there were any validation errors in the CURRENT action
   */
  public function isValid() { return count($this->getCurrentValidationErrors()) == 0; }

  public function getLoggedUser() { 
    return isset($_SESSION[$GLOBALS['Application']['name']]["oUser"]) ? $_SESSION[$GLOBALS['Application']['name']]["oUser"] : null; 
  }

  public function isUserLogged() { return $this->getLoggedUser() != null; }

  public function setUserLogged($oUser) { 
    $_SESSION[$GLOBALS['Application']['name']]["oUser"] = $oUser; 

    /* handling user switching functionality; once you have logged in as a
     * superuser you can keep switching users despite their permission
     * limitations */
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

  public function getCurrentFlash() { 
    return isset($_SESSION[$GLOBALS['Application']['name']]['sFlashMessage']) ?
        $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] : "";
  }

  public function setFlash($sMessage) { $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] = $sMessage; }

  protected function restoreFlash() { 
     $this->sFlashMessage = isset($_SESSION[$GLOBALS['Application']['name']]['sFlashMessage']) ? $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] : ""; 
    $this->clearFlash(); 
  }

  protected function restoreValidationErrors() {
    $this->aValidationErrors = isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']) ? $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] : array();
    $this->clearValidationErrors();
  }

  protected function clearFlash() {
    $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] = null;
  }

  /**
   * clears errors set up in the current action
   */
  protected function clearValidationErrors() {
    unset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']);
  }

  /**
   * access control for both login-level access and ACL permissions
   */
  public function authenticate($sActionName) {

    if( $this->isUserLogged() ) {

      $sActionName = substr($sActionName, 0, strlen($sActionName) - strlen("Action"));
      if( !$this->checkActionAccess($sActionName) ) {

        $this->addValidationError("access", 
          new \Core\Validation\RuleResult(false, 
            \Utils\NounInflector::Underscore(\Utils\Namespaces::Strip(get_called_class()))."/".
            $sActionName));
        $this->redirect("/access/denied");

      }

    } else {
      if( !$this->isPublicAction($sActionName) )
        $this->redirect('/login');
    }
  }

  /**
   * checks user group's ACL permissions for the specified action;
   * expects camelcase action name without the "Action" suffix
   */
  public function checkActionAccess($sActionName) {
    
    // figuring out the control object name
    $sACOName =
      \Utils\NounInflector::Underscore(
        \Utils\Namespaces::Strip(get_class($this))) .
      '/' .
      $sActionName;

    // checking user group permissions
    $oPermission = null;
    return \Models\Permission::CheckByNameAndModel($sACOName, $this->getLoggedUser()->group);

  }

  public function beforeAction($sActionName) {

    if( !\Controllers\AppInstallation::IsInstalled() &&
        !($this instanceof \Controllers\AppInstallation) &&
        !$this->internalRequest() ) {

      /* the application is not installed correctly; force reinstall.
       * this is not enforced if we are dealing with an internal request
       * @see invokeControllerAction() */
      session_destroy();
      $this->redirect("/install");

    } else {

      if( !$this->internalRequest() ) {
        // check access only for non-internal requests
        $this->authenticate($sActionName);
      }
    }

    // handling per-group homepages
    if( (!$this->getRequestedPath() || $this->getRequestedPath() == "/") &&
        $this->getLoggedUser()->group->home_url ) {
      $this->setFlash($this->getFlash());
      $this->setValidationErrors($this->getValidationErrors());
      $this->redirect($this->getLoggedUser()->group->home_url);
    }

    // preparing data for user switching interface
    if( $this->allowUserSwitching() ) {
      $oUserModel = new \Models\User();
      $this->setLayoutData("aSwitchableUsers", $oUserModel->find(null, false, 0));
    }

    // other data for layout
    $this->setLayoutData('oLoggedUser', $this->getLoggedUser());
  }

  public function afterAction($sActionName) {
  }

  /**
   * This is controller's action logic wrapper. Servers the
   * following purposes:
   * * ensures that beforeAction() and afterAction() hooks 
   * are being invoked,
   * * handles switching the internal request state.
   */
  public function callAction($sActionName, $sRequestedPath, $aArguments = array(), $bInternalRequest = false ) {

    $this->setRequestedPath($sRequestedPath);

    // indicate if the request is internal
    $this->bInternalRequest = $bInternalRequest;

    $this->beforeAction($sActionName);

    // invoke the actual action method
    $oReflectSelf = new \ReflectionObject($this);
    $oReflectAction = $oReflectSelf->getMethod($sActionName);

    if( count($aArguments) < $oReflectAction->getNumberOfRequiredParameters() )
      throw new Exceptions\ActionParametersNotSpecified();

    $mResult = $oReflectAction->invokeArgs($this, $aArguments);

    $this->afterAction($sActionName);

    // reset the internal request state
    $this->bInternalRequest = false;

    return $mResult;

  }

  /**
   * use for checking if the form has been sent
   */
  public function isPostSent() { return !empty($_POST); }

  /**
   * use for retrieving data from the form
   */
  public function getPostValue($sPropertyName) { 
    return isset($_POST[$sPropertyName]) ? 
      htmlspecialchars($_POST[$sPropertyName]) : 
      null; 
  }

  public function setPostValue($sKey, $sValue) {
    $_POST[$sKey] = $sValue;
  }

  /**
   * access the main DAO
   */
  public function getModel() { return $this->oModel; }

  /**
   * use for redirects
   */
  public function redirect($sUrl) { 
    if( !$this->internalRequest() ) {
      header("Location: ".$sUrl);
      exit();
    }
  }

  protected function setRequestedPath($sPath) {
    $this->sRequestedPath = $sPath;
  }

  public function getRequestedPath() {
    return $this->sRequestedPath;
  }

  /**
   * use for refreshing the current action; especially
   * useful for displaying validation errors as those
   * processing another request
   */
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

  /**
   * loads a model DAO for use in the controller;
   * the DAO will be available in the appropriate controller 
   * object property, i.e.:
   * for model class "User", the DAO will be available under
   * $this->oUser
   */
  protected function loadModel($sModelClass) {

    if( !class_exists($sModelClass) )
      throw new Exceptions\ModelUndefined($sModelClass);

    $sPropertyName = "o".substr($sModelClass, strlen("Models\\"));
    $this->$sPropertyName = new $sModelClass();
  }

  /**
   * checks if the current action is triggered by an internal request
   */
  protected function internalRequest() { return $this->bInternalRequest; }

  protected $oModel = null;
  protected $sFlashMessage = null;
  protected $aValidationErrors = array();
  protected $aViewData = array();
  protected $aLayoutData = array();
  protected $bInternalRequest = false;
  protected $sRequestedPath = "";

}

?>
