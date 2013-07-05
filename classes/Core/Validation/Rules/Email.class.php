<?php

namespace Core\Validation\Rules;

class Email extends RegEx {

  public function getNotPassedMessage() {
    return \Utils\NounInflector::Beautify($this->getPropertyName()) . " is invalid.";
  }

  protected $sPattern = 
    '/^[_a-z0-9-\+]+(\.[_a-z0-9-\+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

}
