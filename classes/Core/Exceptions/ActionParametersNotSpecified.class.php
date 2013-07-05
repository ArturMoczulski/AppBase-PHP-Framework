<?php

namespace Core\Exceptions;

class ActionParametersNotSpecified extends \Exception {

  public function __construct() {
    $this->message = "Action parameters not specified."; 
  }

}
