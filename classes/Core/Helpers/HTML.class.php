<?php
namespace Core\Helpers;

class HTML extends Helper {

  public function action($aAction, $aAttributes=array()) {
    $sOutput = "";

    if( !isset($aAction['sLinkName'])) $aAction['sLinkName'] = ucfirst($aAction['sActionName']);

    if( isset($aAction['sLinkName']) && isset($aAction['sControllerName']) && isset($aAction['sActionName'])) {
      $sOutput .= $this->link($aAction['sLinkName'], $aAction['sControllerName'], $aAction['sActionName'], isset($aAction['aArguments']) ? $aAction['aArguments'] : array(), "", "btn");
    }
    return $sOutput;
  }

  public function actions($aActions, $aAttributes=array()) {
    $sOutput = ' <div class="btn-group">';
    foreach( $aActions as $aAction ) {
      $sOutput .= $this->action($aAction);
    }
    $sOutput .= '              </div> ';
    return $sOutput;
  }

  public function actionsMenu($aActions, $aAttributes=array()) {
    $sOutput = '
                    <div class="well" style="padding: 8px 0;">
                        <ul class="nav nav-list">
                            <li class="nav-header">Actions</li>';
    foreach( $aActions as $aAction ) {
      if( isset($aAction['sLinkName']) && isset($aAction['sControllerName']) && isset($aAction['sActionName'])) {
        $sOutput .= '<li>';
        $sOutput .= $this->link($aAction['sLinkName'], $aAction['sControllerName'], $aAction['sActionName'], isset($aAction['aArguments']) ? $aAction['aArguments'] : array());
        $sOutput .= '</li>';
      }
    }
    $sOutput .= '              </ul>
                    </div>
      ';
    return $sOutput;
  }

  public function tag($sTagName, $sContent, $sId="", $sClassNames="", $aAttributes=array()) {
    $sAttributes = "";
    foreach( $aAttributes as $sAttributeName => $sAttributeValue) {
      $sAttributes .= " $sAttributeName=\"$sAttributeValue\"";
    }

    return 
      "<$sTagName".
        ($sClassNames ? " class=\"$sClassNames\"" : "").
        ($sId ? " id=\"$sId\"" : "").
        ($sAttributes ? $sAttributes : "") . ">".
      $sContent.
      "</$sTagName>";
  }

  public function link($sLinkName, $sControllerName, $sActionName, $aArguments = array(), $sId="", $sClassNames="" ) {

    $sArgumentsPart = implode("/",$aArguments);
    return $this->tag("a", $sLinkName, $sId, $sClassNames, array(
      "href"=>
        "$sControllerName" .
        ($sActionName ? "/$sActionName". 
        ($sArgumentsPart ? "/$sArgumentsPart" : "" ) : "")));
  }

  public function linkUrl($sLinkName, $sUrl, $aArguments = array(), $sClassNames="") {
    $aUrlParts = explode("/", ltrim($sUrl, "/"));
    if( !empty($aUrlParts) ) {
      $sControllerName = $aUrlParts[0];
      $sActionName = isset($aUrlParts[1]) ? $aUrlParts[1] : "";
      return $this->link($sLinkName, $sControllerName, $sActionName, $aArguments, $sClassNames);
    }
    return "";
  }

}

?>
