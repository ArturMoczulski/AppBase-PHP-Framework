<?php
namespace Core\Helpers;

class Helper {

  public function getHelpers() { return $this->aHelpersInstances; }
  public function getHelper($sHelperName) {
    return isset($this->aHelpersInstances[$sHelperName]) ?
            $this->aHelpersInstances[$sHelperName] : null;
  }

  protected $aHelpersInstances;

  public function __construct() {
    $this->initHelpers();
  }

  protected function initHelpers() {
    if( !isset($this->aHelpers) ) return;
    $aHelpersInstances = array();
    foreach( $this->aHelpers as $sHelperClass ) {
      $sHelperClassName = "\\Core\\Helpers\\".$sHelperClass;
      $aHelpersInstances [$sHelperClass]= new $sHelperClassName();
    }
    $this->aHelpersInstances = $aHelpersInstances;
  }
}

?>
