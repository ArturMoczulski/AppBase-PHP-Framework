<?php

namespace Core\Validation\Rules;

class RegEx extends SimpleRule {

  public function configure($aConfig) {
    if( isset($aConfig[0]) )
      $this->sPattern = $aConfig[0];
  }

  public function validate($mValue = "") {
    if( isset($this->sPattern) )
      return preg_match($this->sPattern, $mValue) === 1;
    else 
      return true;
  }

  public function getNotPassedMessage() {
    return \Utils\NounInflector::Beautify($this->getPropertyName()) . " does not match required pattern.";
  }

}
