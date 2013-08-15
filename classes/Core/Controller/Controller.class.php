<?php

/**
 * @author Artur Moczulski <artur.moczulski@gmail.com>
 */

namespace Core\Controller;

use \Core\Validation\RuleResult as RuleResult;
use \Utils\NounInflector as NounInflector;
use \Utils\Namespaces as Namespaces;

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
   * @param string $sControllerName
   * @param string $sActionName
   * @param array $aArguments
   *
   * @return \Core\Controller\ControllerResponse
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
   * Assigning data for further passing to the view
   * template.
   *
   * @param string $sVariableName Name of the variable which will
   * be made available in the view file.
   * @param mixed $mValue
   */
  public function setViewData($sVariableName, $mValue ) {
    $this->aViewData[$sVariableName] = $mValue;
  }

  /**
   * Assigning data for further passing to the layout
   * file.
   *
   * @param string $sVariableName Name of the variable which will
   * be made available in the layout file.
   * @param mixed $mValue
   */
  public function setLayoutData($sVariableName, $mValue) {
    $this->aLayoutData[$sVariableName] = $mValue;
  }

  /**
   * Get data which was previously assigned to be
   * passed to the view.
   *
   * @param string $sVariableName (optional) get data
   * from specific view variable; if not passed all
   * the view data will be returned in form of an
   * array
   *
   * @return mixed|array
   */
  public function getViewData($sVariableName = "") {
    return $sVariableName ? $this->aViewData[$sVariableName] : $this->aViewData;
  }

  /**
   * Get data which was previously assigned to be
   * passed to the layout.
   *
   * @param string $sVariableName (optional) get data
   * from specific layout variable; if not passed
   * all the layout data will be returned in form
   * of an array
   *
   * @return mixed|array
   */
  public function getLayoutData($sVariableName = "") {
    return $sVariableName ? $this->aLayoutData[$sVariableName] : $this->aLayoutData;
  }

  /**
   * NOTE: using this method will override whatever else validation errors
   * have been already added. To simply add more see addValidationErrors()
   * and addValidationError()
   *
   * @param array $aErrors An array of RuleResult
   */
  public function setValidationErrors($aErrors) { 
    $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] = $aErrors; 
  }

  /**
   * Add validation errors.
   *
   * @param array $aErrors An array of RuleResult
   */
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

  /**
   * Add a single validation error.
   *
   * @param string $sPropertyName
   * @param RuleResult $oError
   */
  public function addValidationError($sPropertyName, $oError) {

    if( !isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']))
      $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] = array();

    if( !isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'][$sPropertyName]) )
      $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'][$sPropertyName] = array();

    $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'][$sPropertyName] []= $oError;
  }

  /**
   * Get errors that have been assigned to display as the reesult
   * of the currently processed HTTP request.
   *
   * @return array Array of strings
   */
  protected function getCurrentValidationErrors() {
    return isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']) ?
        $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] : array();
  }

  /**
   * Get errors that have been triggered in by the last request.
   *
   * @return array Array of strings
   */
  public function getValidationErrors() { 
    return $this->aValidationErrors; 
  }

  /**
   * Were any validation errors triggered in currently
   * processed request?
   *
   * @return bool
   */
  public function isValid() { 
    return count($this->getCurrentValidationErrors()) == 0; 
  }

  /**
   * Get currently logged in user
   *
   * @return \Models\User
   */
  public function getLoggedUser() { 
    return isset($_SESSION[$GLOBALS['Application']['name']]["oUser"]) ? 
      $_SESSION[$GLOBALS['Application']['name']]["oUser"] : 
      null; 
  }

  /**
   * Checks if the current visitor is a logged in
   * user.
   *
   * @return bool
   */
  public function isUserLogged() { 
    return $this->getLoggedUser() != null; 
  }

  /**
   * Sets the currently logged in user. This can be used
   * not only to initially set the authentication 
   * context, but also change it on the fly.
   *
   * @param \Models\User $oUser
   */
  public function setUserLogged($oUser) { 
    $_SESSION[$GLOBALS['Application']['name']]['oUser'] = $oUser; 

    /**
     * handling user switching functionality; once you have logged in as a
     * superuser you can keep switching users despite their permission
     * limitations
     */
    if( $oUser && \Models\Permission::CheckByNameAndModel('users/switch', $oUser->group ) ) {
      $_SESSION[$GLOBALS['Application']['name']]['bAllowUserSwitching'] = true;
    } else if( $oUser == null ) {
      unset($_SESSION[$GLOBALS['Application']['name']]['bAllowUserSwitching']);
    }
  }

  /**
   * Check if the application allows switching the
   * user context.
   *
   * @return bool
   */
  public function allowUserSwitching() { 
    return 
      isset($_SESSION[$GLOBALS['Application']['name']]['bAllowUserSwitching']) ? 
      $_SESSION[$GLOBALS['Application']['name']]['bAllowUserSwitching'] : 
      false; 
  }

  /**
   * Bring up user's session
   */
  protected function restoreSession() {

    if( !session_id() ) {
      session_start();
    }

    $this->restoreFlash();
    $this->restoreValidationErrors();

  }

  /**
   * Get flash set up in the last request.
   *
   * @return string
   */
  public function getFlash() { 
    return $this->sFlashMessage; 
  }

  /**
   * Get flash set up in the currently processed request.
   *
   * @return string
   */  
  public function getCurrentFlash() { 
    return 
      isset($_SESSION[$GLOBALS['Application']['name']]['sFlashMessage']) ?
      $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] : 
      '';
  }

  /**
   * Set flash message to be displayed in the view.
   *
   * @param string $sMessage
   */
  public function setFlash($sMessage) { 
    $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] = $sMessage; 
  }

  /**
   * Method used internally restore flash set in the previous request.
   */
  protected function restoreFlash() { 

    $this->sFlashMessage = isset($_SESSION[$GLOBALS['Application']['name']]['sFlashMessage']) ? 
      $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] : 
      ''; 

    $this->clearFlash(); 
  }

  /**
   * Method used internally to restore validation errors
   * set in the previous request.
   */
  protected function restoreValidationErrors() {

    $this->aValidationErrors = isset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']) ? 
      $_SESSION[$GLOBALS['Application']['name']]['aValidationErrors'] : 
      array();

    $this->clearValidationErrors();
  }

  /**
   * Clear the flash message, so that it won't be
   * displayed again in the current response.
   */
  protected function clearFlash() {
    $_SESSION[$GLOBALS['Application']['name']]['sFlashMessage'] = null;
  }

  /**
   * Clear the validation errors, so that they won't
   * be displayed again the current response.
   */
  protected function clearValidationErrors() {
    unset($_SESSION[$GLOBALS['Application']['name']]['aValidationErrors']);
  }

  /**
   * Determines if current user can access requested resource.
   * This is both authentication and access control.
   *
   * @param string $sActionName
   *
   * @return bool
   */
  public function authenticate($sActionName) {

    if( $this->isUserLogged() ) {

      $sActionName = substr($sActionName, 0, strlen($sActionName) - strlen("Action"));
      if( !$this->checkActionAccess($sActionName) ) {

        /**
         * handle access denied
         */
        $this->addValidationError("access", 
          new RuleResult(false, 
            NounInflector::Underscore(Namespaces::Strip(get_called_class()))."/".
            $sActionName));

        $this->redirect("/access/denied");

      }

    } else {

      if( !$this->isPublicAction($sActionName) )
        $this->redirect('/login');

    }
  }

  /**
   * Checks user group's ACL permissions for the specified action.
   *
   * @param string $sActionName Action's name with the 'Action' suffix.
   *
   * @return bool
   */
  public function checkActionAccess($sActionName) {
    
    // figuring out the control object name
    $sACOName =
      NounInflector::Underscore(Namespaces::Strip(get_class($this))) .
      '/' .
      $sActionName;

    // checking user group permissions
    $oPermission = null;

    return \Models\Permission::CheckByNameAndModel($sACOName, $this->getLoggedUser()->group);

  }

  /**
   * Pre-action action-specific hook.
   * NOTE: if you want to override it in your controller,
   * make sure you run the parent's implementation as
   * the first instruction.
   *
   * @param string $sActionName
   */
  public function beforeAction($sActionName) {

    if( !\Controllers\AppInstallation::IsInstalled() &&
        !($this instanceof \Controllers\AppInstallation) &&
        !$this->internalRequest() ) {

      /**
       * The application is not installed correctly; force reinstall.
       * this is not enforced if we are dealing with an internal request
       * @see invokeControllerAction() 
       **/
      session_destroy();
      $this->redirect("/install");

    } else {

      if( !$this->internalRequest() ) {
        // check access only for non-internal requests
        $this->authenticate($sActionName);
      }
    }

    // handling per-group homepages
    if( (!$this->getRequestedPath() || $this->getRequestedPath() == '/') &&
        $this->getLoggedUser()->group->home_url ) {

      // preserving the flash
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
    $this->setLayoutData('sCurrentActionUrl', 
      NounInflector::Underscore(Namespaces::Strip(get_called_class())).'/'.substr($sActionName, 0, strlen($sActionName)-strlen('Action')));
    $this->setLayoutData('sRequestedPath', $this->getRequestedPath());
  }

  /**
   * Post-action action-specific hook.
   *
   * @param string $sActionName
   */
  public function afterAction($sActionName) {
  }

  /**
   * This is controller's action logic wrapper. Serves the
   * following purposes:
   * * ensures that beforeAction() and afterAction() hooks 
   * are being invoked,
   * * handles switching the internal request state; 
   * particularly, the flag has to get enabled for the 
   * internal request processing and then disabled
   * afterwards.
   *
   * @param string $sActionName
   * @param string $sRequestedPath
   * @param array $aArguments (optional)
   * @param bool $bInternalRequest (optional)
   *
   * @return \Core\Controller\ControllerResponse
   */
  public function callAction($sActionName, $sRequestedPath, $aArguments = array(), $bInternalRequest = false ) {

    $this->setRequestedPath($sRequestedPath);

    // handle switching the internal request state
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
   * Post data sent?
   *
   * @return bool
   */
  public function isPostSent() { 
    return !empty($_POST); 
  }

  /**
   * Get post data.
   *
   * @param string $sPropertyName
   */
  public function getPostValue($sPropertyName) { 
    return isset($_POST[$sPropertyName]) ? 
      htmlspecialchars($_POST[$sPropertyName]) : 
      null; 
  }

  /**
   * Set post data.
   *
   * @param string $sKey
   * @param string $sValue
   */
  public function setPostValue($sKey, $sValue) {
    $_POST[$sKey] = $sValue;
  }

  /**
   * Get controller's default model object.
   *
   * @return \Core\Model\Model
   */
  public function getModel() { 
    return $this->oModel; 
  }

  /**
   * Issue redirection.
   *
   * @param string $sUrl
   */
  public function redirect($sUrl) { 
    if( !$this->internalRequest() ) {
      header("Location: ".$sUrl);
      exit();
    }
  }

  /**
   * Setter for requested path property.
   *
   * @param string $sPath
   */
  protected function setRequestedPath($sPath) {
    $this->sRequestedPath = $sPath;
  }

  /**
   * Getter for request path property.
   *
   * @return string
   */
  public function getRequestedPath() {
    return $this->sRequestedPath;
  }

  /**
   * Issue refreshing the current action.
   * NOTE: useful for displaying validation errors as those
   * processing another request
   */
  public function refresh() {
    if( !$this->internalRequest() )
      $this->redirect("");
  }

  /**
   * Checks if the action is accessible for non-authenticated
   * users.
   *
   * @param string $sActionName
   *
   * @return bool
   */
  protected function isPublicAction($sActionName) {
    if( property_exists( $this, "aPublicActions" ) ) {
      return array_search($sActionName, $this->aPublicActions) !== false; 
    } else
      return false;
  }

  /**
   * Get an array of models used by the current controller.
   *
   * @return array Array of strings
   */
  protected function getModelsUsed() { 

    if( property_exists( $this, 'aModelsUsed' ) )

      return $this->aModelsUsed;

    else {

      $sModelClass = 
        "Models\\" . 
        NounInflector::Singularize(Namespaces::Strip(get_called_class()));

      return array($sModelClass);
    }
  }

  /**
   * Loads the model DAO for use in the controller;
   * the DAO will be available in the appropriate controller 
   * object property, i.e.:
   * for model class "User", the DAO will be available under
   * $this->oUser
   *
   * @param string $sModelClass
   */
  protected function loadModel($sModelClass) {

    if( !class_exists($sModelClass) )
      throw new Exceptions\ModelUndefined($sModelClass);

    $sPropertyName = 'o'.substr($sModelClass, strlen('Models\\'));
    $this->$sPropertyName = new $sModelClass();
  }

  /**
   * Checks if the current action was triggered by another
   * action.
   *
   * @return bool
   */
  protected function internalRequest() { 
    return $this->bInternalRequest; 
  }

  /**
   * Default model used by the controller.
   *
   * @var \Core\Models\Model
   */
  protected $oModel = null;

  /**
   * @var string $sFlashMessage
   */
  protected $sFlashMessage = null;

  /**
   * $var array $aValidationErrors
   */
  protected $aValidationErrors = array();

  /**
   * Data set to be passed to the view.
   *
   * @var array $aViewData
   */
  protected $aViewData = array();

  /**
   * Data set to be passed to the layout.
   *
   * @var array $aLayoutData
   */
  protected $aLayoutData = array();

  /**
   * Internal state indicates if the current
   * action has been triggered by a different 
   * action.
   *
   * @var bool
   */
  protected $bInternalRequest = false;

  /**
   * @var string
   */
  protected $sRequestedPath = "";

}

?>
