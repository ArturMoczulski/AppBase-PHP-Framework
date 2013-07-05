<?php

namespace Core\Exceptions;

class ResourceNotFound extends \Exception {

  public function __construct() {
    $this->message = "Resource not found."; 
  }

}
