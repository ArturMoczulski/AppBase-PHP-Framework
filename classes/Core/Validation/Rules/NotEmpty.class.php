<?php

namespace Core\Validation\Rules;

class NotEmpty extends SimpleRule {

  public function validate($mValue = null) {
    return !empty($mValue);
  }

  protected function getNotPassedMessage() {
    return \Utils\NounInflector::Beautify($this->getPropertyName()) . " is empty.";
  }

}
