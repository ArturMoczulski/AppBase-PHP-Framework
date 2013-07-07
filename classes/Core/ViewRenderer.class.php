<?php

namespace Core;

class ViewRenderer extends Helpers\Helper {

  protected $aHelpers = array("Model","HTML");

  public function render(\Core\Controller\ControllerResponse $oResponse) {

    $sControllerName = \Utils\NounInflector::Underscore($oResponse->getControllerName());
    $sActionName = \Utils\NounInflector::Underscore($oResponse->getActionName());

    if( $sFlashMessage = $oResponse->getFlashMessage() ) 
      $sFlashMessage = $this->renderFlashMessage($sFlashMessage);

    $sValidationErrors = "";
    if( $aValidationErrors = $oResponse->getValidationErrors() )
      $sValidationErrors = $this->renderValidationErrors($aValidationErrors);

    $sActionOutput = $this->renderView(
      $sControllerName, 
      $sActionName, 
      $oResponse->getViewData(),
      $oResponse->getLayoutData());

    $sOutput = $this->renderRawLayout($oResponse->getLayoutData());

    $sOutput = $this->replaceTags($sOutput, array(
      'renderedAction' => $sActionOutput,
      'controllerName' => $sControllerName,
      'actionName' => $sActionName,
      'flashMessage' => $sFlashMessage,
      'validationErrors' => $sValidationErrors
    ) );

    return $sOutput;

  }

  protected function renderRawLayout($aLayoutData) {
    extract($aLayoutData);
    ob_start();
    include "views/layouts/generic.php";
    $sOutput = ob_get_contents();
    ob_end_clean();
    return $sOutput;
  }

  protected function renderView($sControllerName, $sActionName, $aViewData, $aLayoutData) {
    extract($aLayoutData);
    extract($aViewData);
    $sActionOutput = "";
    if( file_exists("views/".$sControllerName."/".$sActionName.".php") ) {
      ob_start();
      include "views/".$sControllerName."/".$sActionName.".php";
      $sActionOutput = ob_get_contents();
      ob_end_clean();
    }
    return $sActionOutput;
  }

  public function renderValidationErrors($aErrors) {

    $sErrors = "";

    foreach( $aErrors as $aPropertyErrors ) {
      if( !empty($aPropertyErrors) ) {
        // only show one error per property
        $oError = $aPropertyErrors[0];
        if( $oError->toString() )
          $sErrors .= $this->getHelper("HTML")->tag("li", $oError->toString());
      }
    }

    $sErrorsHeader = $sErrors ? $this->getHelper("HTML")->tag("h3", "The following errors occurred.") : "";
    $sErrors = $sErrors ? $this->getHelper("HTML")->tag("ul", $sErrors) : "";
    $sErrors = $sErrorsHeader . $sErrors;
    $sErrors = $sErrors ? $this->getHelper("HTML")->tag("div", $sErrors, null, "errors") : "";

    return $sErrors;
  }

  public function renderFlashMessage($sContent) {
    return $this->getHelper("HTML")->tag("p", $sContent, null, "flash");
  }

  public function replaceTags($sOutput, $aMapping) {
    foreach($aMapping as $sTagName => $sContent ) {
      $sOutput = $this->replaceTag($sTagName, $sContent, $sOutput);
    }
    return $sOutput;
  }

  public function replaceTag($sTagName, $sContent, $sOutput) {
    return str_replace('#{'.$sTagName.'}', $sContent, $sOutput);
  }

}

?>
