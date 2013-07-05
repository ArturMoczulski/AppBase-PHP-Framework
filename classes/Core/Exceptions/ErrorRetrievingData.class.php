<?php

namespace Core\Exceptions;

class ErrorRetrievingData extends \Exception {

  public function __construct($sQuery, $aErrorInfo) {
    if( is_array($aErrorInfo) ) {
      $this->sCode = isset($aErrorInfo[0]) ? $aErrorInfo[0] : "";
      $this->sMessage = isset($aErrorInfo[2]) ? $aErrorInfo[2] : "";
    }

    $this->sQuery = $sQuery;
  }

  protected $sMessage = "";
  protected $sCode = "";
  protected $sQuery = "";

}
