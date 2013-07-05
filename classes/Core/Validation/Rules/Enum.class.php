<?php

namespace Core\Validation\Rules;

class Enum extends SimpleRule {

  public function configure($aConfig) {
    $this->aValues = $aConfig;
  }

  public function validate($mValue = null) {
    return in_array($mValue, $this->aValues);
  }

  public function getNotPassedMessage() {
    return \Utils\NounInflector::Beautify($this->getPropertyName()) . " is not valid.";
  }

}
