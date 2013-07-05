<?php

namespace Core\Validation\Rules;

class BelongsTo extends ModelRule {

  public function validate($mArgument = null) {

    $sPropertyName = $this->getPropertyName();
    return (isset($this->oModel->$sPropertyName) && $this->oModel->$sPropertyName );

  }

  public function getNotPassedMessage() {
    return \Utils\NounInflector::Beautify($this->getPropertyName()) . " does not exist.";
  }

}
