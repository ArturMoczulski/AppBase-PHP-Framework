<?php

namespace Core\Exceptions;

class ModelUndefined extends \Exception {

  public function __construct($sModelName) {
    parent::__construct();
    $this->message = "Model $sModelName undefined."; 
  }

}
