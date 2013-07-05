<?php

namespace Core\Validation;

class SaveResult {

  public function __construct($aValidationErrors = array()) {
    $this->aValidationErrors = $aValidationErrors;
  }

  public function success() {
    return count($this->getValidationErrors()) == 0;
  }

  public function merge($oSaveResult) {
    if( $oSaveResult )
      $this->addValidationErrors($oSaveResult->getValidationErrors());
  }

  public function addValidationErrors($aValidationErrors) {
    $this->aValidationErrors = array_merge($this->aValidationErrors, $aValidationErrors);
  }

  public function getValidationErrors() { return $this->aValidationErrors; }

  protected $aValidationErrors = array();

}
