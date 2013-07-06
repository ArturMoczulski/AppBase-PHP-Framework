<?php

namespace Core;

/**
 * \Core\Router
 *
 * This class takes care of expanding expanding shortcut paths into
 * correctly formatted resource paths.
 *
 * @see config/route.php for details on setting up the shortcut paths
 */
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
    $this->aRoutes = $GLOBALS['Application']['routes']; 
  }

  protected $aRoutes;

}

?>
