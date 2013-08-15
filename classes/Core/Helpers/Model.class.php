<?php
namespace Core\Helpers;

class Model extends Helper {

  protected $aHelpers = array("HTML");

  public function tableHeader(\Core\Model\Model $oModel, $aIgnoreProperties = array()) {

    $aHeader = array();
    foreach($oModel->getProperties() as $sPropertyName => $mPropertyValue ) {
      if( in_array( $sPropertyName, $aIgnoreProperties )) continue;
      $aHeader []= ucfirst($sPropertyName);
    }

    return $aHeader;
  }

  public function renderTableHeader(\Core\Model\Model $oModel, $aIgnoreProperties = array(), $bShowActions = true ) {
    echo "<tr>";
    foreach($this->tableHeader($oModel, $aIgnoreProperties) as $sPropertyName) {
      echo "<th>".$sPropertyName."</th>";
    }
    if( $bShowActions )
      echo "<th>Actions</th>";
    echo "</tr>";
  }

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

  public function renderTableRowAction($sControllerName, $sActionName, $aArguments, $sLinkName = "") {
    if( !$sLinkName ) $sLinkName = ucfirst($sActionName);
    echo $this->getHelper("HTML")->link($sLinkName, $sControllerName, $sActionName, $aArguments, null, "action");
  }

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

  public function renderTable(\Core\Model\Model $oModel, $mData, $aIgnoreProperties = array(), $aActions = array(), $aAttributes = array('class'=>'table table-bordered table-striped table-hover')) {
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
