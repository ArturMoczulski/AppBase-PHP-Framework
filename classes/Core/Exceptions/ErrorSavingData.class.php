<?php

namespace Core\Exceptions;

class ErrorSavingData extends \Exception {

  public function __construct($aErrorInfo) {
    $this->aErrorInfo = $aErrorInfo;
  }

  protected $aErrorInfo;

}
