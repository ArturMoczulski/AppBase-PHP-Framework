<?php

/**
 * @author Artur Moczulski <artur.moczulski@gmail.com>
 */

namespace Core\Helpers;

/**
 * Helper used for easing HTML generation
 */
class HTML extends Helper {

  /**
   * Builds an action link.
   *
   * @param array $aAction array(
   *  'sLinkName' => ... ,
   *  'sControllerName' => ... ,
   *  'sActionName' => ... ,
   *  'aArguemnts' => ...
   * )
   *
   * @return string
   */
  public function action($aAction, $aAttributes=array()) {
    $sOutput = "";

    if( !isset($aAction['sLinkName'])) {
      $aAction['sLinkName'] = ucfirst($aAction['sActionName']);
    }

    if( isset($aAction['sLinkName']) && 
        isset($aAction['sControllerName']) && 
        isset($aAction['sActionName'])) {

      $sOutput .= $this->link(
        $aAction['sLinkName'], 
        $aAction['sControllerName'], 
        $aAction['sActionName'], 
        isset($aAction['aArguments']) ? $aAction['aArguments'] : array(), "", 
        "btn"
      );

    }
    return $sOutput;
  }

  /**
   * Builds a group of action links.
   *
   * @param array $aAction Array of actions, as required
   * by @see \Core\Helpers\HTML::action
   * @param array $aAttributes Currently not used
   *
   * @return string
   */
  public function actions($aActions, $aAttributes=array()) {
    $sOutput = ' <div class="btn-group">';
    foreach( $aActions as $aAction ) {
      $sOutput .= $this->action($aAction);
    }
    $sOutput .= '              </div> ';
    return $sOutput;
  }

  /**
   * Builds a menu containing actions.
   *
   * @param array $aActions Array of actions, as required
   * by @see \Core\Helper\HTML::action
   *
   * @return string
   */
  public function actionsMenu($aActions, $aAttributes=array()) {
    $sOutput = '
                    <div class="well" style="padding: 8px 0;">
                        <ul class="nav nav-list">
                            <li class="nav-header">Actions</li>';

    foreach( $aActions as $aAction ) {
      if( isset($aAction['sLinkName']) && isset($aAction['sControllerName']) && isset($aAction['sActionName'])) {
        $sOutput .= '<li>';
        $sOutput .= $this->link(
          $aAction['sLinkName'], 
          $aAction['sControllerName'], 
          $aAction['sActionName'], 
          isset($aAction['aArguments']) ? $aAction['aArguments'] : array()
        );
        $sOutput .= '</li>';
      }
    }
    $sOutput .= '              </ul>
                    </div>
      ';
    return $sOutput;
  }

  /**
   * Builds a HTML tag.
   *
   * @param string $sTagName
   * @param string $sContent
   * @param string $sId (optional)
   * @param string $sClassNames (optional)
   * @param array $aAttributes (optional) they will be used HTML 
   * exactly as passed in the array; array(
   *  $name => $value
   * )
   *
   * @return string
   */
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

  /**
   * Builds a HTML link to an action.
   *
   * @param string $sLinkName
   * @param string $sControllerName
   * @param string $sActionName
   * @param array $aArguments (optional)
   * @param string $sId (optional)
   * @param string $sClassNames (optional)
   *
   * @return string
   */
  public function link($sLinkName, 
                       $sControllerName, 
                       $sActionName, 
                       $aArguments = array(), 
                       $sId="", 
                       $sClassNames="" ) {

    $sArgumentsPart = implode("/",$aArguments);

    return $this->tag("a", $sLinkName, $sId, $sClassNames, array(
      "href"=>
        "$sControllerName" .
        ($sActionName ? "/$sActionName". 
        ($sArgumentsPart ? "/$sArgumentsPart" : "" ) : "")));
  }

  /**
   * Builds a HTML link to an explicit URL.
   *
   * @param string $sLinkName
   * $param string $sUrl
   * @param array $aArguments (optional)
   * @param string $sClassNames (optional)
   *
   * @return string
   */
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
