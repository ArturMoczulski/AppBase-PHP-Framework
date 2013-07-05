<?php

namespace Core\Validation\Rules;

abstract class Rule {

  public function __construct($sPropertyName = "") {
    $this->sPropertyName = $sPropertyName;
  }

  public function attemptValidation($mValue = null, $bInsert = true) {

    if( $this instanceof ModelRule ) {

      if( ($this->validateOnInsert() && $bInsert) ||
          ($this->validateOnUpdate() && !$bInsert) ) {

        return new \Core\Validation\RuleResult(
          $this->validate($mValue),
          $this->getNotPassedMessage());

      } else
        return null;

    } else {
      return new \Core\Validation\RuleResult(
        $this->validate($mValue),
        $this->getNotPassedMessage());
    }

  }

  public abstract function validate($mValue = null);
  protected abstract function getNotPassedMessage();

  public function setPropertyName($sPropertyName) { $this->sPropertyName = $sPropertyName; }
  public function getPropertyName() { return $this->sPropertyName; }

  protected $sPropertyName = "";

}
