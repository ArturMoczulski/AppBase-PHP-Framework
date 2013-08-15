<?php

/**
 * @author Artur Moczulski <artur.moczulski@gmail.com>
 */

namespace Core\Helpers;

/**
 * Helps generating HTML directly from models.
 */
class Model extends Helper {

  /**
   * Uses the HTML helper
   */
  protected $aHelpers = array("HTML");

  /**
   * Builds an array of names for table headers
   * based on model's property names.
   *
   * @param \Core\Model\Model $oModel
   * @param array $aIgnoreProperties (optional) 
   * names of properties to ignore
   *
   * @return array
   */
  public function tableHeader(\Core\Model\Model $oModel, $aIgnoreProperties = array()) {

    $aHeader = array();
    foreach($oModel->getProperties() as $sPropertyName => $mPropertyValue ) {
      if( in_array( $sPropertyName, $aIgnoreProperties )) continue;
      $aHeader []= ucfirst($sPropertyName);
    }

    return $aHeader;
  }

  /**
   * Displays HTML for table header from model's
   * property names.
   *
   * @param \Core\Model\Model $oModel
   * @param array $aIgnoreProperties (optional)
   * names of properties to ignore
   * @param bool $bShowActions
   */
  public function renderTableHeader(\Core\Model\Model $oModel, $aIgnoreProperties = array(), $bShowActions = true ) {
    echo "<tr>";
    foreach($this->tableHeader($oModel, $aIgnoreProperties) as $sPropertyName) {
      echo "<th>".$sPropertyName."</th>";
    }
    if( $bShowActions )
      echo "<th>Actions</th>";
    echo "</tr>";
  }

  /**
   * Builds an array of values for table row
   * based on model's properties.
   *
   * @param \Core\Model\Model $oModel
   * @param array $aIgnoreProperties (optional)
   * names of properties to ignore
   *
   * @return string
   */
  public function tableRow(\Core\Model\Model $oModel, $aIgnoreProperties = array())  {
    $aRow = array();
    foreach( $oModel->getProperties() as $sPropertyName => $mPropertyValue ) {

      if( in_array($sPropertyName, $aIgnoreProperties)) continue;

      if( isset($oModel->$sPropertyName) && 
          ($oModel->$sPropertyName instanceof \Core\Model\Model) ) {

          $aDefaultAction = $oModel->$sPropertyName->getDefaultAction();

          if( $aDefaultAction ) {
            $aRow []= $this->getHelper("HTML")->link(
              $oModel->$sPropertyName->toString(),
              $aDefaultAction['sControllerName'],
              $aDefaultAction['sActionName'],
              array($oModel->$sPropertyName->id)
            );
          } else {
            $aRow []= $oModel->$sPropertyName->toString();
          }

      } else
        $aRow []= $mPropertyValue;
    }
    return $aRow;
  }

  /**
   * Displays HTML for a table row action.
   *
   * @param string $sControllerName
   * @param string $sActionName
   * @param array $aArguments
   * @param string $sLinkName (optional)
   */
  public function renderTableRowAction($sControllerName, $sActionName, $aArguments, $sLinkName = "") {
    if( !$sLinkName ) $sLinkName = ucfirst($sActionName);

    echo $this->getHelper("HTML")->link(
      $sLinkName, 
      $sControllerName, 
      $sActionName, 
      $aArguments, 
      null, 
      "action"
    );
  }

  /**
   * Displays a table row for a model object.
   *
   * @param \Core\Model\Model $oModel
   * @param array $aIgnoreProperties (optional)
   * names of properties to ignore
   * @param array $aActions (optional) actions
   * for rows
   */
  public function renderTableRow(\Core\Model\Model $oModel, $aIgnoreProperties = array(), $aActions = array() ) {
    echo "<tr>";
    foreach( $this->tableRow($oModel, $aIgnoreProperties) as $mPropertyValue )
      echo "<td>".$mPropertyValue."</td>";
    if( count($aActions) ) {
      echo "<td>";
      foreach($aActions as $iIndex => $aAction) {
        if( !isset($aAction['aArguments']) )
          $aActions[$iIndex]['aArguments'] = array($oModel->id);
      }
      echo $this->getHelper("HTML")->actions($aActions);
      echo "</td>";
    }
    echo "</tr>";
  }

  /**
   * Displays a table for a set of model objects.
   *
   * @param \Core\Model\Model $oModel
   * @param array $mData
   * @param array $aIgnoreProperties (optional)
   * @param array $aActions (optional)
   * @param array $aAttributes (optional)
   */
  public function renderTable(\Core\Model\Model $oModel, 
                              $mData, 
                              $aIgnoreProperties = array(), 
                              $aActions = array(), 
                              $aAttributes = array(
                                'class'=>'table table-bordered table-striped table-hover'
                              )) {
    $sAttributes = "";
    $bFirstAttr = true;
    foreach( $aAttributes as $sAttrName => $sAttrValue ) {
      $sAttributes .= ($bFirstAttr ? " " : "") . "$sAttrName=\"$sAttrValue\"";
      $bFirstAttr = false;
    }
    echo "<table$sAttributes>";
    $this->renderTableHeader($oModel, $aIgnoreProperties, count($aActions));
    if( count($mData) ) {
      foreach( $mData as $oModelInstance)  {
        $this->renderTableRow($oModelInstance, $aIgnoreProperties, $aActions);
      }
    } else
      echo "<td colspan=\"".(count($this->tableHeader($oModel, $aIgnoreProperties))+1)."\">No entries to display</td>";
    echo "</table>";
  }

}

?>
