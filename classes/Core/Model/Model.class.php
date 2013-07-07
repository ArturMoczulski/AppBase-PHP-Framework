<?php
namespace Core\Model;

/**
 * \Core\Model\Model
 *
 * The base DAO and data wrapper class. It provides
 * methods to deal with the model data through
 * DAO as well as serves as the wrapper around
 * the actual data retrieved from the database.
 *
 * It undertakes the following responsibilities:
 * * automatically resolves table names,
 * * ensures the data object have all the expected
 * object properties, for both table columns and
 * relations,
 * * handles data validation,
 * * provides interface for retrieving models from
 * the storage,
 * * provides logic for saving models and relations,
 * * allows for pre and post save logic by overloadable
 * hooks.
 *
 * TODO: DAO methods need to be changed to static; 
 * the code convention has to follow to capitalize
 * the first letter of the method
 * TODO: needs adding DB transactions
 * TODO: think about seperating the DAO and data wrapper
 * part of the class
 */
abstract class Model {

  public function __construct() {

    // reference the actual inherited class of the object
    $this->sTableName = static::ResolveTableName(); 

    // TODO: this should really be resolved using cache
    $sSchemaQuery = "DESCRIBE ".$this->getTableName();
    $oSchemaStatement = \Core\Database\Database::GetInstance()->query($sSchemaQuery);
    if( !($oSchemaStatement instanceof \PDOStatement) )
      throw new \Core\Exceptions\ErrorRetrievingData($sSchemaQuery, $oSchemaStatement);
    else
      $this->aProperties = $oSchemaStatement->fetchAll(\PDO::FETCH_COLUMN);

    // figuring ou the primary key
    $sPrimaryKeyQuery = "SHOW INDEX FROM ".$this->getTableName()." WHERE key_name = 'PRIMARY'";
    $oPrimaryKeyStatement = \Core\Database\Database::GetInstance()->query($sPrimaryKeyQuery);
    if( !($oPrimaryKeyStatement instanceof \PDOStatement) ) {
      throw new \Core\Exceptions\ErrorRetrievingData($sPrimaryKeyQuery, $oPrimaryKeyStatement);
    } else {
      $sPrimaryKey = $oPrimaryKeyStatement->fetch(\PDO::FETCH_ASSOC);
      $sPrimaryKey = $sPrimaryKey['Column_name'];
      $this->sPrimaryKey = $sPrimaryKey;
    }

    // initiating properties
    $this->initProperties();
    $this->initRelations();

    // initiaiting relations schema
    $this->initPropertiesValidation();
    $this->initRelationsValidation();
  }

  /**
   * ensures the data object has all the expected 
   * object properties
   */
  protected function initProperties() {
    foreach( $this->aProperties as $sPropertyName ) {
      if( !isset($this->$sPropertyName) )
        $this->$sPropertyName = null;
    }
  }

  /**
   * ensures the data object has all the expected
   * object properties
   */
  protected function initRelations() {

    // creating the realtions schema
    $this->oRelationsSchema = Relations\Schema::CreateFromSpec(
      $this,
      isset($this->aBelongsTo) ? $this->aBelongsTo : array(),
      isset($this->aHasMany) ? $this->aHasMany : array()
    );

    // crete the properties for relations
    foreach( $this->oRelationsSchema->getPropertyNames() as $sPropertyName )
      $this->$sPropertyName = null;

  }

  /**
   * create the validation rules objects
   */
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

  /**
   * create the validation rules object for relations
   */
  protected function initRelationsValidation() {

    // belongs to
    if( isset($this->aBelongsTo) ) {
     
      foreach( $this->oRelationsSchema->getBelongsTo() as $oBelongsTo ) {

        if( !$oBelongsTo->isRequired() ) continue;

        if( !isset($this->aValidationRules[$oBelongsTo->getPropertyName()]) )
          $this->aValidationRules[$oBelongsTo->getPropertyName()]  = array();

        $oRule = new \Core\Validation\Rules\BelongsTo();
        $oRule->setPropertyName($oBelongsTo->getPropertyName());
        $oRule->setModel($this);

        $this->aValidationRules[$oBelongsTo->getPropertyName()] []= $oRule;

      }

    }

  }

  /**
   * automatic table name resolution
   */
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

  /**
   * returns an associative array of model's properties
   */
  public function getProperties() {
    $aProperties = array();

    foreach( $this->getPropertyNames() as $sPropertyName ) {
      $sPropertyFriendlyName = $sPropertyName;

      if( substr($sPropertyName, strlen($sPropertyName)-strlen("_id")) == "_id" ) {
        // dealing with foreign keys
        $sPropertyFriendlyName = substr($sPropertyName, 0, strlen($sPropertyName)-strlen("_id"));
      }

      $aProperties[$sPropertyFriendlyName] = isset($this->$sPropertyName) ? $this->$sPropertyName : null;
    }

    return $aProperties;
  }

  /**
   * The main save method.
   * use the $iDepth parameter for saving more complicated, deeply nested data
   */
  public function save($iDepth = 1) {

    $this->beforeSave();

    $oSaveResult = new \Core\Validation\SaveResult();

    // TODO: add a transaction

    if( $iDepth > 0 ) {
      /* Belongs to relations saving;
       * they need to be saved first to allow the correct 
       * foreign key population order */
      $oSaveResult->merge($this->saveBelongsTo($iDepth)); 
    }

    $oSaveResult->addValidationErrors($this->validate());

    if( $oSaveResult->success() ) {

      if( !$this->isNew() ) { 

        // update already existing data
        // a "where" filter for update query
        $oFilter = new \Core\Database\DataFilter();
        $oFilter->addConstraint(
          new \Core\Database\DataFilterConstraint(
            $this->getPrimaryKey(),
            \Core\Database\DataFilterConstraint::EQUAL,
            $this->{$this->getPrimaryKey()}));
        $this->update($oFilter);

      } else {

        // insert for new data
        $this->insert();
      }

      if( $iDepth > 0 ) {
        /* Has many relations saving;
         * those are being saved after the main model to
         * allow the correct foreign key propagation order */
        $oSaveResult->merge($this->saveHasMany($iDepth));
      }

      $this->afterSave();

    }

    return $oSaveResult; 
  }

  protected function saveBelongsTo($iDepth = 1) {

    $oSaveBelongsToResult = new \Core\Validation\SaveResult();

    foreach( $this->oRelationsSchema->getBelongsTo() as $oBelongsTo ) {
      $oSaveBelongsToResult->merge($oBelongsTo->save($this, $iDepth-1));
    }

    return $oSaveBelongsToResult;

  }

  protected function saveHasMany($iDepth = 1) {

    $oSaveHasMany = new \Core\Validation\SaveResult();

    foreach( $this->oRelationsSchema->getHasMany() as $oHasMany ) {
      $oSaveHasMany->merge($oHasMany->save($this, $iDepth-1));
    }

    return $oSaveHasMany;
  
  }

  /**
   * use to retrieve data from the storage by property value, i.e.:
   * $oUser->findBy("email", "artur.moczulski@gmail.com", true);
   * or
   * $oUser->findBy("id", 1, true);
   *
   * $sPropertyName - the column name
   * $sValue - the value you're matching
   * $bSingleObject - true if you want to get a single object or 
   * false if you want to get an array of matching objects
   * $iDepth - indicate how deep you want to go with retrieving
   * relations; use wisely as too deep finds might cause infinite
   * loops, crashing your application
   *
   * @returns \Core\Model\Model subclass or an array of these
   */
  public function findBy($sPropertyName, $sValue, $bSingleObject = false, $iDepth = 1) {
    $oFilter = new \Core\Database\DataFilter();
    $oFilter->addConstraint(
      new \Core\Database\DataFilterConstraint(
        $sPropertyName, 
        \Core\Database\DataFilterConstraint::EQUAL, 
        $sValue));
    return $this->find($oFilter, $bSingleObject, $iDepth);
  }

  /**
   * use to retrieve more complex data sets using DataFilter objects
   *
   * $oFilter - previously created DataFilter object with appropriate
   * DataFilterConstraints
   *
   * @see findBy() for explanation on the rest of the arguments
   * @see findBy() for explanation on what this methos returns
   */
  public function find(\Core\Database\DataFilter $oFilter = null, $bSingleObject = false, $iDepth = 1) {

    if( !$oFilter ) $oFilter = new \Core\Database\DataFilter();

    // building the SQL query
    $sQuery = "SELECT * FROM ".$this->getTableName() . $oFilter->buildSql();

    // running the query
    $mStatement = \Core\Database\Database::GetInstance()->query($sQuery);

    // fetching the data into data wrapper objects
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

    // formatting the output
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

  /**
   * used by save() to insert new data into the storage
   */
  protected function insert() {

    $this->beforeInsert();

    // generating SQL for the insert query
    $aValues = array();
    foreach( $this->getPropertyNames() as $sPropertyName ) {
      if( !property_exists($this, $sPropertyName) )
        $aValues []= 'null';
      else {
        $aValues []= $this->$sPropertyName === null ? 
          'null' :
          \Core\Database\Database::GetInstance()->quote($this->$sPropertyName);
      }
    }

    $sSql = 
      "INSERT INTO ".  
        $this->getTableName() . 
        " (" .  implode(", ", $this->getPropertyNames()) . ") " .
      "VALUES " .
        " (" . implode(", ", $aValues) . ")";

    // running the query
    $mResult = \Core\Database\Database::GetInstance()->query($sSql);

    if( !($mResult instanceof \PDOStatement) )
      throw new \Core\Exceptions\ErrorSavingData($mResult);

    // updating the data wrapper object with the resulting id
    $iId = \Core\Database\Database::GetInstance()->lastInsertId();
    if( $iId )
      $this->id = $iId;

    $this->afterInsert();
  }

  /**
   * used by the save() method to update already existing data
   */
  protected function update(\Core\Database\DataFilter $oFilter = null) {

    $this->beforeUpdate();

    // generating SQL for the update query
    if( !$oFilter ) $oFilter = new \Core\Database\DataFilter();

    $sSql = "UPDATE ".$this->getTableName()." SET ";

    $aSetStatements = array();
    foreach( $this->getPropertyNames() as $sPropertyName ) {
      $sValue = 'null';
      if( property_exists($this, $sPropertyName) ) {
        $sValue = $this->$sPropertyName === null ? 
          'null' :
          \Core\Database\Database::GetInstance()->quote($this->$sPropertyName);
      } 
      $aSetStatements []= $sPropertyName . " = " . $sValue;
    }

    $sSql .= implode(", ", $aSetStatements);

    $sSql .= $oFilter->buildSql();

    // running the query
    $mResult = \Core\Database\Database::GetInstance()->query($sSql);

    if( !($mResult instanceof \PDOStatement) )
      throw new \Core\Exceptions\ErrorSavingData($mResult);

    $this->afterUpdate();
  }

  /**
   * use for deleting the object from the storage
   */
  public function delete() {
    $this->deleteBy("id", $this->id);
  }

  /**
   * DAO method for deleting data by matching value
   */
  public function deleteBy($sPropertyName, $sValue) {
    $oFilter = new \Core\Database\DataFilter();
    $oFilter->addConstraint(
      new \Core\Database\DataFilterConstraint(
        $sPropertyName, 
        \Core\Database\DataFilterConstraint::EQUAL, 
        $sValue));
    return $this->deleteByFilter($oFilter);
  }

  /**
   * DAO method for deleting data by complex filters
   * using DataFilter objects
   */
  public function deleteByFilter(\Core\Database\DataFilter $oFilter = null) {

    $this->beforeDelete();
    // TODO: add transactions
    
    if( !$oFilter ) $oFilter = new \Core\Database\DataFilter();

    // generating the query
    $sSql = "DELETE FROM ".$this->getTableName() . $oFilter->buildSql();

    // running the query
    $mResult = \Core\Database\Database::GetInstance()->query($sSql);

    if( !($mResult instanceof \PDOStatement) )
      throw new \Core\Exceptions\ErrorSavingData($mResult);

    $this->afterDelete();
  }

  /**
   * This method validates the data according to
   * validation rules defined in $aValidation
   * property.
   *
   * @see $aValidation property for more explanation
   */
  public function validate() {
    $aResults = $this->validateProperties();
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
  public function getDefaultAction() { 
    return isset($this->aDefaultAction) ? $this->aDefaultAction : null; 
  }

  public function toString() {
    return isset($this->id) ? $this->id : null;
  }

  protected $sTableName;  
  protected $aProperties;
  protected $sPrimaryKey;
  protected $aValidationRules;
  protected $oRelationsSchema;

  /**
   * \Core\Model\Model also supports properties which
   * should be defined only in inherting classes for
   * customizing the logic per-model.
   *
   * === $aValidation ===
   * This property allows you to define against
   * what rules the model data will be validated on 
   * save. The property should follow the below format:
   *
   * protected $aValidation = array(
   *   "property1_name" => array(
   *     "Rule1",
   *     "Rule2",
   *     "Rule3" => array("config_option1_value")
   *   ),
   *   "property2_name" = array("Rule1")
   * );
   *
   * Rule names you can use are exactly the same as
   * validation rule classes which can be found in 
   * classes/Validation/Rules. Also, as with Rule3
   * above the are rules which allow some extent
   * of customization.
   *
   * The following is a simple validation example
   *
   * protected $aValidation = array(
   *   "email" => array("NotEmpty", "Email")
   * );
   *
   *
   * === $aBelongsTo ===
   * Allows to define "belongs to" 1-to-1 relations 
   * between models. The below is format explanation:
   *
   * protected $aBelongsTo = array(
   *   "Model1",
   *   array(
   *     "className" => "Model2",
   *     "relationName" => "custom_relation_name",
   *     "foreignKeyName" => "custom_foreign_key_name" // optional
   *   )
   * );
   *
   * Model1 will be referenced by the current model
   * using the "model1" property and will populate it
   * with data matching the "model1_id" foreign key from
   * the Model1 table.
   *
   * Model2 will be referenced by the current model using
   * the "custom_relation_name" property and will populate
   * it with data matching the "custom_foreign_key_name"
   * foreign key from the Model2 table
   *
   * NOTE: "belongs to" relation assumes that the relation
   * defining model has the foreign key
   *
   * === $aHasMany ===
   * Allows to define "has many" 1-to-* relations between models.
   * The has many relation definition follows the same format
   * as $aBelongsTo.
   *
   * In case of non-custom raltion definitions, the property used 
   * for creating relations is an underscored and plural name of 
   * the referenced model class, i.e.
   * referecing "User" will result in the "users" property.
   *
   * NOTE: "has many" relation assumes that the related model
   * has the foreing key, and not the relation defining model. That's
   * oppposite to "belongs to" relation.
   *
   */

}

?>
