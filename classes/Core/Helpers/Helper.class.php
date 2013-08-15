<?php

/**
 * @author Artur Moczulski <artur.moczulski@gmail.com>
 */

namespace Core\Helpers;

/**
 * Objects providing logic for easing view generation,
 * especially HTML.
 *
 * This is a composite class.
 */
class Helper {

  /**
   * Array of helpers class names to be used.
   * This is a virtual property, whic means
   * that if it is defined in child classes
   * it will be used for initialization of
   * child helpers.
   *
   * @var array
   *
   * public $aHelpers = array(...)
   */

  /**
   * Get all helper objects.
   *
   * @return array
   */
  public function getHelpers() { 
    return $this->aHelpersInstances; 
  }

  /**
   * Get a specific helper by class name
   * 
   * @return \Core\Helpers\Helper
   */
  public function getHelper($sHelperName) {
    return isset($this->aHelpersInstances[$sHelperName]) ?
      $this->aHelpersInstances[$sHelperName] : null;
  }

  /**
   * Array of \Core\Helpers\Helper objects.
   *
   * @var array
   */
  protected $aHelpersInstances;

  /**
   * Initialize child helpers from $aHelpers
   * property.
   */
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
