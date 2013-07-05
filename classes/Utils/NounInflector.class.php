<?php

namespace Utils;

class NounInflector {

  public static function Pluralize($sNoun) {
    if( !isset(self::$aExceptions[$sNoun]) ) {
      return $sNoun."s";
    } else
      return self::$aExceptions[$sNoun];
  }

  public static function Underscore($sCamelcaseNoun) {
    return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $sCamelcaseNoun));
  }

  public static function Beautify($sUnderscoredNoun) {
    $sBeautified = str_replace("_"," ", $sUnderscoredNoun);
    $sBeautified = ucfirst($sBeautified);
    return $sBeautified;
  }

  public static function Camelize($sUnderscoredNoun) {
    $sUnderscoredNoun[0] = strtoupper($sUnderscoredNoun[0]);
    $mFunc = create_function('$c', 'return strtoupper($c[1]);');
    return preg_replace_callback('/_([a-z])/', $mFunc, $sUnderscoredNoun);
  }

  public static function Singularize($sSearchPlural) {
    foreach(self::$aExceptions as $sSingular => $sPlural) {
      if( $sPlural == $sSearchPlural )  return $sSingular;
    }
    return substr($sSearchPlural, 0, strlen($sSearchPlural)-1);
  }

  protected static $aExceptions = array(
    "TicketStatus"=>"TicketStatuses",
    "Company"=>"Companies",
    "Hardware"=>"Hardware"
  );

}

?>
