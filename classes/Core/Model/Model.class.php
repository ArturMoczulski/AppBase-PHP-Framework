<?php
namespace Core;

abstract class Model {

  public function __construct() {
    $this->sTableName = static::ResolveTableName(); // reference the actual inherited class of the object

    // TODO: this should really be resolved using cache
    $oSchemaStatement = \Core\Database\Database::GetInstance()->query("DESCRIBE ".$this->getTableName());
    $this->aProperties = $oSchemaStatement->fetchAll(\PDO::FETCH_COLUMN);

    $oPrimaryKeyStatement = \Core\Database\Database::GetInstance()->query("SHOW INDEX FROM ".$this->getTableName()." WHERE key_name = 'PRIMARY'");
    $sPrimaryKey = $oPrimaryKeyStatement->fetch(\PDO::FETCH_ASSOC);
    $sPrimaryKey = $sPrimaryKey['Column_name'];
    $this->sPrimaryKey = $sPrimaryKey;

    $this->initProperties();
    $this->initRelations();

    $this->initPropertiesValidation();
    $this->initRelationsValidation();
  }

  protected function initProperties() {
    foreach( $this->aProperties as $sPropertyName ) {
      if( !isset($this->$sPropertyName) )
        $this->$sPropertyName = null;
    }
  }

  protected function initRelations() {

    $this->oRelationsSchema = Relations\Schema::CreateFromSpec(
      $this,
      isset($this->aBelongsTo) ? $this->aBelongsTo : array(),
      isset($this->aHasMany) ? $this->aHasMany : array()
    );

    // crete the properties for relations
    foreach( $this->oRelationsSchema->getPropertyNames() as $sPropertyName )
      $this->$sPropertyName = null;

  }

  protected function initPropertiesValidation() {

    $this->aValidationRules = array();
    if( isset($this->aValidation) ) {

      foreach( $this->aValidation as $sPropertyName => $aRules ) {

        foreach( $aRules as $mKey => $mValue ) {

          $sRuleClass = "";
          $aConfig = null;
          if( is_array($mValue) ) {
            // handling configurable rules
            $sRuleClass = $mKey;
            $aConfig = $mValue;
          } else {
            // non configurable rules
            $sRuleClass = $mValue;
          }

          if( !isset($this->aValidationRules[$sPropertyName]) ) 
            $this->aValidationRules[$sPropertyName] = array();

          $sRuleFullClass = "\\Core\\Validation\\Rules\\".$sRuleClass;
          $oRule = new $sRuleFullClass();

          $oRule->setPropertyName($sPropertyName);
          if( $oRule instanceof \Core\Validation\Rules\ModelRule ) {
            $oRule->setModel($this);
          }

          if( method_exists($oRule, "configure") && $aConfig )
            $oRule->configure($aConfig);

          $this->aValidationRules[$sPropertyName] []= $oRule;

        }
      }
    }

  }

  protected function initRelationsValidation() {

    // belongs to
    if( isset($this->aBelongsTo) ) {
     
      foreach( $this->oRelationsSchema->getBelongsTo() as $oBelongsTo ) {

        if( !isset($this->aValidationRules[$oBelongsTo->getPropertyName()]) )
          $this->aValidationRules[$oBelongsTo->getPropertyName()]  = array();

        $oRule = new Validation\Rules\BelongsTo();
        $oRule->setPropertyName($oBelongsTo->getPropertyName());
        $oRule->setModel($this);

        $this->aValidationRules[$oBelongsTo->getPropertyName()] []= $oRule;

      }

    }

  }

  public static function ResolveTableName() { 
    $sClassName = \Utils\NounInflector::Underscore(
      \Utils\NounInflector::Pluralize(
        substr(get_called_class(), strlen("Models\\")))); 
    return $sClassName;
  }

  public function beforeSave() {}
  public function afterSave() { }

  public function beforeInsert() { }

  public function afterInsert() {}

  public function beforeUpdate() { } 
  public function afterUpdate() {}

  public function beforeDelete() {}
  public function afterDelete() {}

  public function getPrimaryKey() { return $this->sPrimaryKey; }

  public function getTableName() { return $this->sTableName; }

  public function getPropertyNames() { return $this->aProperties; }

  public function getProperties() {
    $aProperties = array();
    foreach( $this->getPropertyNames() as $sPropertyName ) {
      $sPropertyFriendlyName = $sPropertyName;
      if( substr($sPropertyName, strlen($sPropertyName)-strlen("_id")) == "_id" )
        $sPropertyFriendlyName = substr($sPropertyName, 0, strlen($sPropertyName)-strlen("_id"));
      $aProperties[$sPropertyFriendlyName] = isset($this->$sPropertyName) ? $this->$sPropertyName : null;
    }
    return $aProperties;
  }

  /**
   * use the $iDepth parameter for saving more complicated, deeply nested data
   */
  public function save($iDepth = 1) {
    $this->beforeSave();

    $oSaveResult = new Validation\SaveResult();

    // TODO: add a transaction
    // TODO: add support for save results from the related models

    if( $iDepth > 0 ) {
      // "belongs to" resources have to be saved first to have the foreign key generated
      $oSaveResult->merge($this->saveBelongsTo($iDepth)); 
    }

    $oSaveResult->addValidationErrors(
      $this->validate()
    );

    if( $oSaveResult->success() ) {

      if( !$this->isNew() ) { 
        $oFilter = new \Core\Database\DataFilter();
        $oFilter->addConstraint(new \Core\Database\DataFilterConstraint($this->getPrimaryKey(),\Core\Database\DataFilterConstraint::EQUAL,$this->{$this->getPrimaryKey()}));
        $this->update($oFilter);
      } else
        $this->insert();

      if( $iDepth > 0 ) {
        // "has many" resources are being saved after, as the main model has to generated the foreign key first
        $oSaveResult->merge($this->saveHasMany($iDepth));
      }

      $this->afterSave();

    }

    return $oSaveResult; 
  }

  protected function saveBelongsTo($iDepth = 1) {

    $oSaveBelongsToResult = new Validation\SaveResult();

    foreach( $this->oRelationsSchema->getBelongsTo() as $oBelongsTo ) {
      $oSaveBelongsToResult->merge($oBelongsTo->save($this, $iDepth-1));
    }

    return $oSaveBelongsToResult;

  }

  protected function saveHasMany($iDepth = 1) {

    $oSaveHasMany = new Validation\SaveResult();

    foreach( $this->oRelationsSchema->getHasMany() as $oHasMany ) {
      $oSaveHasMany->merge($oHasMany->save($this, $iDepth-1));
    }

    return $oSaveHasMany;
  
  }

  public function findBy($sPropertyName, $sValue, $bSingleObject = false, $iDepth = 1) {
    $oFilter = new \Core\Database\DataFilter();
    $oFilter->addConstraint(new \Core\Database\DataFilterConstraint($sPropertyName, \Core\Database\DataFilterConstraint::EQUAL, $sValue));
    return $this->find($oFilter, $bSingleObject, $iDepth);
  }

  public function find(\Core\Database\DataFilter $oFilter = null, $bSingleObject = false, $iDepth = 1) {
    if( !$oFilter ) $oFilter = new \Core\Database\DataFilter();

    $sQuery = "SELECT * FROM ".$this->getTableName() . $oFilter->buildSql();

    $mStatement = \Core\Database\Database::GetInstance()->query($sQuery);

    if( !($mStatement instanceof \PDOStatement) ) {
      throw new \Core\Exceptions\ErrorRetrievingData($sQuery, $mStatement);
    } else {
      $oStatement = $mStatement;
      $oStatement->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), array());
    }

    $aResults = array();
    while( $oModel = $oStatement->fetch(\PDO::FETCH_CLASS) ) {
      if( !isset($oModel->id) ) continue;
      $oModel->aProperties = $this->aProperties;
      if( $iDepth ) $oModel->loadRelations($iDepth-1);
      $aResults []= $oModel;
    }

    if( $bSingleObject && count($aResults) <= 1 ) {
      return isset($aResults[0]) ? $aResults[0] : null;
    } else { 
      return $aResults;
    }
  }

  protected function loadRelations($iDepth = 1) {

    // Belongs to
    foreach( $this->oRelationsSchema->getBelongsTo() as $oBelongsTo ) 
      $oBelongsTo->load($this, $iDepth);

    // Has many
    foreach( $this->oRelationsSchema->getHasMany() as $oHasMany ) 
      $oHasMany->load($this, $iDepth);

  }

  protected function loadHasMany($iDepth = 1) {
    if( !isset($this->aHasMany) ) return;

    foreach( $this->aHasMany as $mRelation ) {

      if( is_array($mRelation) ) {

        $sRelationForeignKey = $mRelation['foreignKeyName'];
        $sRelationProperty = $mRelation['relationName'];
        $sRelationFullClass = "\\Models\\".$mRelation['className'];

      } else {
        $sRelationClassName = $mRelation;
        $aSelfClass = explode("\\",get_called_class());
        $sSelfClass = $aSelfClass[count($aSelfClass)-1];
        $sRelationForeignKey = \Utils\NounInflector::Underscore($sSelfClass)."_id";
        $sRelationProperty = \Utils\NounInflector::Underscore(\Utils\NounInflector::Pluralize($sRelationClassName));
        $sRelationFullClass = "\\Models\\".$sRelationClassName;
      }

      $oRelation = new $sRelationFullClass();
      $aRelations = $oRelation->findBy($sRelationForeignKey, $this->id, false, $iDepth);

      $this->$sRelationProperty = $aRelations;
    }
  }

  protected function insert() {

    $this->beforeInsert();

    $aValues = array();
    foreach( $this->getPropertyNames() as $sPropertyName ) {
      if( !property_exists($this, $sPropertyName) )
        $aValues []= 'null';
      else {
        $aValues []= \Core\Database\Database::GetInstance()->quote($this->$sPropertyName);
      }
    }

    $sSql = 
      "INSERT INTO ".  
        $this->getTableName() . 
        " (" .  implode(", ", $this->getPropertyNames()) . ") " .
      "VALUES " .
        " (" . implode(", ", $aValues) . ")";

    $mResult = \Core\Database\Database::GetInstance()->query($sSql);

    if( !($mResult instanceof \PDOStatement) )
      throw new Exceptions\ErrorSavingData($mResult);

    $iId = \Core\Database\Database::GetInstance()->lastInsertId();
    if( $iId )
      $this->id = $iId;

    $this->afterInsert();
  }

  protected function update(\Core\Database\DataFilter $oFilter = null) {

    $this->beforeUpdate();

    if( !$oFilter ) $oFilter = new \Core\Database\DataFilter();

    $sSql = "UPDATE ".$this->getTableName()." SET ";

    $aSetStatements = array();
    foreach( $this->getPropertyNames() as $sPropertyName ) {
      $sValue = 'null';
      if( property_exists($this, $sPropertyName) ) {
        $sValue = \Core\Database\Database::GetInstance()->quote($this->$sPropertyName);
      } 
      $aSetStatements []= $sPropertyName . " = " . $sValue;
    }

    $sSql .= implode(", ", $aSetStatements);

    $sSql .= $oFilter->buildSql();

    $mResult = \Core\Database\Database::GetInstance()->query($sSql);

    if( !($mResult instanceof \PDOStatement) )
      throw new Exceptions\ErrorSavingData($mResult);

    $this->afterUpdate();
  }

  public function delete() {
    $this->deleteBy("id", $this->id);
  }

  public function deleteBy($sPropertyName, $sValue) {
    $oFilter = new \Core\Database\DataFilter();
    $oFilter->addConstraint(new \Core\Database\DataFilterConstraint($sPropertyName, \Core\Database\DataFilterConstraint::EQUAL, $sValue));
    return $this->deleteByFilter($oFilter);
  }

  public function deleteByFilter(\Core\Database\DataFilter $oFilter = null) {
    $this->beforeDelete();
    // TODO: add transactions
    
    if( !$oFilter ) $oFilter = new \Core\Database\DataFilter();

    $sSql = "DELETE FROM ".$this->getTableName() . $oFilter->buildSql();

    $mResult = \Core\Database\Database::GetInstance()->query($sSql);

    if( !($mResult instanceof \PDOStatement) )
      throw new Exceptions\ErrorSavingData($mResult);

    $this->afterDelete();
  }

  public function validate() {
    $aResults = $this->validateProperties();
    $aResults = array_merge($aResults, $this->validateRelations());
    return $aResults;
  }

  protected function validateProperties() {

    $aResults = array();

    foreach( $this->aValidationRules as $sPropertyName => $aRules ) {

      foreach( $aRules as $oRule ) {

        $oResult = $oRule->attemptValidation($this->$sPropertyName, $this->isNew());

        if( $oResult && !$oResult->passed() ) {

          if( !isset($aResults[$sPropertyName]) ) 
            $aResults[$sPropertyName] = array();

          $aResults[$sPropertyName][] = $oResult;
        }

      } 

    }

    return $aResults;

  }

  protected function validateRelations() {
    return array();
  }

  /**
   * Checks if the object has been already inserted into DB
   */
  public function isNew() {
      return !(property_exists($this, $this->getPrimaryKey()) && $this->{$this->getPrimaryKey()});
  }

  /**
   * Used in the view to determine what action should be loaded
   * when changing model's names into links. This is not really
   * a part of DB model.
   */
  public function getDefaultAction() { return isset($this->aDefaultAction) ? $this->aDefaultAction : null; }

  public function toString() {
    return isset($this->id) ? $this->id : null;
  }

  protected $sTableName;  
  protected $aProperties;
  protected $sPrimaryKey;
  protected $aValidationRules;
  protected $oRelationsSchema;

}

?>
