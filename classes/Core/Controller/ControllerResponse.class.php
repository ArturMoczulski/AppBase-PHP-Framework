<?php
namespace Core\Controller;

/**
 * \Core\Controller\ControllerResponse
 *
 * This is a wrapper class to gather response information
 * from the controller.
 */
class ControllerResponse {

  public function __construct($sControllerName, $sActionName, $aViewData = array(), $aLayoutData = array(), $sFlashMessage = "", $aValidationErrors =array()) {
    $this->sControllerName = $sControllerName;
    $this->sActionName = $sActionName;
    $this->aViewData = $aViewData;
    $this->aLayoutData = $aLayoutData;
    $this->sFlashMessage = $sFlashMessage;
    $this->aValidationErrors = $aValidationErrors;
  }

  public function getControllerName() { return $this->sControllerName; }
  public function getActionName() { return $this->sActionName; }
  public function getViewData() { return $this->aViewData; }
  public function getLayoutData() { return $this->aLayoutData; }
  public function getFlashMessage() { return $this->sFlashMessage; }
  public function getValidationErrors() { return $this->aValidationErrors; }

  protected $sControllerName = "";
  protected $sActionName = "";
  protected $aViewData = array();
  protected $aLayoutData = array();
  protected $sFlashMessage = '';
  protected $aValidationErrors = array();

}

