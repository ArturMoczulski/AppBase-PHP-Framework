<?php

namespace Core\Relations;

class Schema {

  public static function CreateFromSpec(\Core\Model $oModel, $aBelongsTo = array(), $aHasMany = array()) {

    $oSchema = new self();

    foreach( $aBelongsTo as $mRelationSpec ) 
      $oSchema->addBelongsTo($mRelationSpec);

    foreach( $aHasMany as $mRelationSpec )
      $oSchema->addHasMany($mRelationSpec, $oModel);

    return $oSchema;

  }

  public function addBelongsTo($mRelationSpec) { 
    $this->aBelongsTo []= BelongsTo::CreateFromSpec($mRelationSpec); 
  }

  public function addHasMany($mRelationSpec, \Core\Model $oModel) {
    $this->aHasMany []= HasMany::CreateFromSpec($mRelationSpec, $oModel);
  }

  public function getBelongsTo() { return $this->aBelongsTo; }
  public function getHasMany() { return $this->aHasMany; }

  public function getPropertyNames() {

    $aProperties = array();

    foreach( $this->getBelongsTo() as $oBelongsTo )
      $aProperties []= $oBelongsTo->getPropertyName();

    foreach( $this->getHasMany() as $oHasMany )
      $aProperties []= $oHasMany->getPropertyName();

    return $aProperties;
  }

  protected $aBelongsTo = array();
  protected $aHasMany = array();

}
