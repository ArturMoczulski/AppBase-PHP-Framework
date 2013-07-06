<?php

namespace Core\Model\Relations;

class HasMany extends Definition {

  public function save(\Core\Model\Model $oModel, $iDepth = 1) {

    $sRelationProperty = $this->getPropertyName();

    if( isset($oModel->$sRelationProperty) ) {
      foreach( $oModel->$sRelationProperty as $oRelatedModel ) {
        $oRelatedModel->save($iDepth);
      }
    }

  }

  public function load(\Core\Model\Model $oModel, $iDepth = 1) {

    $oRelatedClass = $this->getModelInstance();
    $aRelatedModels = $oRelatedClass->findBy(
      $this->getForeignKeyName(),
      $oModel->id,
      false, $iDepth);

    $sRelationProperty = $this->getPropertyName();
    $oModel->$sRelationProperty = $aRelatedModels;

  }

  public static function CreateFromSpec($mSpec, \Core\Model\Model $oModel) {
    if( is_array($mSpec) )
      return static::CreateFromArray($mSpec, $oModel);
    else
      return static::CreateFromName($mSpec, $oModel);
  }

  public static function CreateFromArray($mSpec, \Core\Model\Model $oModel) {

    if( !isset($mSpec['relationName']) )
      throw new \Core\Exceptions\RelationSpecificationInvalid();

    $sName = $mSpec['relationName'];

    if( !isset($mSpec['className']) )
      throw new \Core\Exceptions\RelationSpecificationInvalid();

    $sRelatedClass = "Models\\".$mSpec['className'];

    $sRelationForeignKey = "";
    if( isset($mSpec['foreignKeyName']) )
      $sRelationForeignKey = $mSpec['foreignKeyName'];
    else {
      $sRelationForeignKey = \Utils\NounInflector::Underscore(
        \Utils\Namespaces::Strip(get_class($oModel)))."_id";
    }

    return new self($sName, $sRelatedClass, $sName, $sRelationForeignKey);
  }

  public static function CreateFromName($mSpec, \Core\Model\Model $oModel) {
    
    $sRelatedClassName = $mSpec;

    $sName = \Utils\NounInflector::Underscore(
      \Utils\NounInflector::Pluralize($mSpec));

    $aModelClass = explode("\\", get_class($oModel));
    $sModelClass = end($aModelClass);

    $sRelationForeignKey = \Utils\NounInflector::Underscore($sModelClass)."_id";

    $sRelationProperty = $sName;

    return new self($sName, "Models\\".$mSpec, $sRelationProperty, $sRelationForeignKey); 

  }

}
