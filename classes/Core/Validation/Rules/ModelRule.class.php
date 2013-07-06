<?php

namespace Core\Validation\Rules;

abstract class ModelRule extends Rule {

  public function __construct(\Core\Model\Model $oModel = null, $sPropertyName = "") { 
    parent::__construct($sPropertyName);
    $this->oModel = $oModel;  
  }

  public function setModel(\Core\Model\Model $oModel) { $this->oModel = $oModel; }
  public function getModel() { return $this->oModel; }

  protected function validateOnInsert() { return $this->bOnInsert; }
  protected function validateOnUpdate() { return $this->bOnUpdate; }

  protected $oModel = null;
  protected $bOnInsert = true;
  protected $bOnUpdate = true;

}
