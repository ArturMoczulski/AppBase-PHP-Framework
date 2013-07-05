<?php

namespace Core\Validation;

class RuleResult {

  public function __construct($bPassed, $sMessage = "") {
    $this->bPassed = $bPassed;
    $this->sMessage = $sMessage;
  }

  public function getMessage() { return $this->sMessage; }
  public function setMessage($sMessage) { $this->sMessage = $sMessage; }
  public function passed() { return $this->bPassed; }

  public function toString() { return $this->getMessage(); }

  protected $bPassed = false;
  protected $sMessage = "";

}
