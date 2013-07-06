<?php

namespace Core\Relations;

abstract class Definition {


  public function __construct($sName, $sClassName, $sPropertyName, $sForeignKeyName) {
    $this->sName = $sName;
    $this->sClassName = $sClassName;
    $this->sForeignKeyName = $sForeignKeyName;
    $this->sPropertyName = $sPropertyName;
  }

  public abstract function load(\Core\Model\Model $oModel, $iDepth = 1);
  public abstract function save(\Core\Model\Model $oModel, $iDepth = 1);

  public function getName() { return $this->sName; }
  public function getClassName() { return $this->sClassName; }
  public function getForeignKeyName() { return $this->sForeignKeyName; }
  public function getPropertyName() { return $this->sPropertyName; }

  protected function getModelInstance() { 
    $sClass = $this->sClassName;
    return new $sClass();
  }

  protected $sName;
  protected $sClassName;
  protected $sForeignKeyName;
  protected $sPropertyName;

}
