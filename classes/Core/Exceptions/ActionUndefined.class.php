<?php

namespace Core\Exceptions;

class ActionUndefined extends \Exception {

  public function __construct($sActionName) {
    $this->message = "Action undefined.";
    $this->sActionName = $sActionName;
  }

  protected $sActionName = "";

}
