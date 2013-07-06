<?php

namespace Core\Relations;

class BelongsTo extends Definition {


  public function save(\Core\Model\Model $oModel, $iDepth = 1) {

    $sPropertyName = $this->getPropertyName();

    if( isset($oModel->$sPropertyName) ) {

      $oSaveResult = $oModel->$sPropertyName->save($iDepth);

      $sForeignKeyName = $this->getForeignKeyName();
      $oModel->$sForeignKeyName = $oModel->$sPropertyName->id;
      return $oSaveResult;
    }

    return null;
  }

  public function load(\Core\Model\Model $oModel, $iDepth = 1) {

    $oRelatedModel = $this->getModelInstance();
    $sForeignKeyName = $this->getForeignKeyName();
    $oRelatedModel = $oRelatedModel->findBy(
      "id", 
      $oModel->$sForeignKeyName,
      true, $iDepth);

    $sPropertyName = $this->getPropertyName();
    $oModel->$sPropertyName = $oRelatedModel;

  }

  public static function CreateFromSpec($mRelationSpec) {
    if( is_array($mRelationSpec) )
      return static::CreateFromArray($mRelationSpec);
    else
      return static::CreateFromName($mRelationSpec);
  }

  public static function CreateFromName($sName) {
    $sClassName = "Models\\".$sName;
    $sName = \Utils\NounInflector::Underscore($sName);
    $sPropertyName = \Utils\NounInflector::Underscore($sName);
    $sForeignKey = $sPropertyName . "_id";
    return new BelongsTo($sName, $sClassName, $sPropertyName, $sForeignKey);
  }

  public static function CreateFromArray($aRelationSpec) {

    if( !isset($aRelationSpec['relationName']))
      throw new \Core\Exceptions\RelationSpecificationInvalid();

    $sName = $aRelationSpec['relationName'];
    $sClassName = isset($aRelationSpec['className']) ? "Models\\".$aRelationSpec['className'] : "Models\\".$sName;
    $sPropertyName = \Utils\NounInflector::Underscore($sName);
    $sForeignKeyName = isset($aRelationSpec['foreignKeyName']) ? $aRelationSpec['foreignKeyName'] : $sPropertyName . "_id";

    return new BelongsTo($sName, $sClassName, $sPropertyName, $sForeignKeyName);

  }

}
