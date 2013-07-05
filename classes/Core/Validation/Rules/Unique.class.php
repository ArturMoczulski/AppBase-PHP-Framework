<?php

namespace Core\Validation\Rules;

class Unique extends ModelRule {

  public function __construct(\Core\Model $oModel = null, $sPropertyName = "") { 
    parent::__construct($oModel, $sPropertyName);
    $this->bOnUpdate = false;
  }

  public function validate($mArgument = null) {

    $sPropertyName = $this->getPropertyName();
    $bDuplicate = false;
    if( isset($this->oModel->$sPropertyName) ) {
      $bDuplicate = $this->oModel->findBy($sPropertyName, $this->oModel->$sPropertyName, true) != null;
    }
    return !$bDuplicate;

  }

  protected function getNotPassedMessage() {
    return \Utils\NounInflector::Beautify($this->getPropertyName()) . " is not unique.";
  }

}
