<?php
namespace Core\Helpers;

class HTML extends Helper {

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
