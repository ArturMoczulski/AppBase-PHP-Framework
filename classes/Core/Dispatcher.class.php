<?php

namespace Core;

class Dispatcher {

  public function dispatchFromRelativePath($sRelativePath) {

    $aDispatchInformation = $this->translateRelativePath($sRelativePath); 
    $sControllerName = $aDispatchInformation['controller'];
    $sActionName = $aDispatchInformation['action'];
    $aArguments = $aDispatchInformation['arguments'];

    return $this->dispatch($sControllerName, $sActionName, $aArguments);

  }
  
  /**
   * @throws Exceptions\ControllerUndefined, Exceptions\ActionUndefined
   */
  public function dispatch($sControllerName, $sActionName, $aArguments, $bInternalRequest = false) {

   $oResult = null;

   $sControllerClass = "\\Controllers\\".$sControllerName;
   $sControllerMethod = $sActionName . "Action";

   if( !class_exists($sControllerClass) )
      throw new Exceptions\ControllerUndefined($sControllerName);

    $oController = new $sControllerClass();

    if( !method_exists($oController, $sControllerMethod) ) {
      throw new Exceptions\ActionUndefined($sActionName);
    }

    $oController->callAction($sControllerMethod, $aArguments, $bInternalRequest);  

    $oResult = new ControllerResponse(
      $sControllerName, 
      $sActionName, 
      $oController->getViewData(), 
      $oController->getLayoutData(), 
      $oController->getFlash(),
      $oController->getValidationErrors()
    );

    return $oResult;
  }

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
