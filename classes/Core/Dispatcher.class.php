<?php

namespace Core;

/**
 * \Core\Dispatcher
 * 
 * Dispatchers are responsible for invoking the correct 
 * controller's action logic for request being handled 
 * and provide means of retrieving their output for
 * further processing.
 */
class Dispatcher {

  /* 
   * expects a correctly formatted resource path:
   * [underscored_controller_name]/[camelcaseActionName]/[argument_1]/...
   * i.e., users/view/1 
   */
  public function dispatchFromRelativePath($sRelativePath) {

    // parsing the path
    $aDispatchInformation = $this->translateRelativePath($sRelativePath); 
    $sControllerName = $aDispatchInformation['controller'];
    $sActionName = $aDispatchInformation['action'];
    $aArguments = $aDispatchInformation['arguments'];

    // dispatching
    return $this->dispatch($sControllerName, $sActionName, $aArguments);

  }
  
  /**
   * Runs the correct controller's action logic and returns the response.
   *
   * @throws Exceptions\ControllerUndefined, Exceptions\ActionUndefined
   * @returns \Core\Controller\ControllerResponse
   */
  public function dispatch($sControllerName, $sActionName, $aArguments, $bInternalRequest = false) {

    $oResult = null;

    // preparing class and method names
    $sControllerClass = "\\Controllers\\".$sControllerName;
    $sControllerMethod = $sActionName . "Action";

    // creating the controller object
    if( !class_exists($sControllerClass) )
      throw new Exceptions\ControllerUndefined($sControllerName);

    $oController = new $sControllerClass();

    // invoking the action
    if( !method_exists($oController, $sControllerMethod) ) {
      throw new Exceptions\ActionUndefined($sActionName);
    }

    $oController->callAction($sControllerMethod, $aArguments, $bInternalRequest);  

    // returning the response
    $oResult = new \Core\Controller\ControllerResponse(
      $sControllerName, 
      $sActionName, 
      $oController->getViewData(), 
      $oController->getLayoutData(), 
      $oController->getFlash(),
      $oController->getValidationErrors()
    );

    return $oResult;
  }

  /*
   * parses the controller name, action name and arguments from
   * correctly formatted resource path
   */
  protected function translateRelativePath($sRelativePath) {
    $sRelativePath = ltrim($sRelativePath,"/");
    $aResult = array();
    $aPathParts = explode("/",$sRelativePath);

    if( count($aPathParts) >= 1 ) {
      $aResult['controller'] = \Utils\NounInflector::Camelize($aPathParts[0]);
      $aResult['action'] = (isset($aPathParts[1]) && $aPathParts[1]) ? 
        lcfirst(\Utils\NounInflector::Camelize($aPathParts[1])) : 
        "index";
      $aResult['arguments'] = count($aPathParts)>2 ? array_slice($aPathParts, 2) : array();
    }
    return $aResult;
  }

}

?>
