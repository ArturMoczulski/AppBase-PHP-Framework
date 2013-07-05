<?php

namespace Core\Exceptions;

class ControllerUndefined extends \Exception {

  public function __construct($sControllerName) {
    $this->sControllerName = $sControllerName;
    $this->message = "Controller \"$sControllerName\" undefined."; 
  }

  protected $sControllerName = "";

}
