<?php

namespace Core;

class Router {

  public function __construct() {
    $this->loadRoutes();
  }

  public function route($sPath) {
    if( isset($this->aRoutes[$sPath]) )
      return $this->aRoutes[$sPath];
    else
      return $sPath;
  }

  protected function loadRoutes() {
    $this->aRoutes = $GLOBALS['Application']['Router']; 
  }

  protected $aRoutes;

}

?>
