<?php
error_reporting(-1);

$aConfigFiles = array("application", "route", "db");

foreach( $aConfigFiles as $sConfigFile ) {
  if( file_exists("config/$sConfigFile.php") )
    require_once "config/$sConfigFile.php";
  else
    throw new Exception("Configuration file \"$sConfigFile.php\" not found.");
}

function loadLogic($sLogicName) {

  $sFilePath = "";

  $aNamespaces = explode("\\", $sLogicName);
  array_pop($aNamespaces);
  $aNamespacesTemp = $aNamespaces;
  $sTopmostNamespace = array_shift($aNamespacesTemp);

  switch( $sTopmostNamespace ) {
    case "Controllers":
      $sFilePath = str_replace("\\", "/", "controllers/".substr($sLogicName, strlen("Controllers\\")));
      if( count($aNamespaces) == 1 ) 
        $sFilePath .= ".controller.php"; // only controller files should be stored directly in controllers/ directory
      else
        $sFilePath .= ".class.php"; // controllers can not be stored anywhere else than directly in controllers/ directory
      break;
    case "Models":
      $sFilePath = str_replace("\\", "/", "models/".substr($sLogicName, strlen("Models\\"))).".class.php";
      break;
    default:
      $sFilePath = str_replace("\\","/","classes/".$sLogicName.".class.php");
      break;
  }
/*
  if( substr($sLogicName, strlen($sLogicName)-strlen("Controller")) == "Controller" && $sLogicName != "Core\\Controller" ) {
    $sFilePath = "controllers/".$sLogicName.".controller.php"; 
  } else if( substr($sLogicName, 0, strlen("Models\\")) == "Models\\" ) {
    $sFilePath = str_replace("\\", "/", "models/".substr($sLogicName, strlen("Models\\"))).".class.php";
  } else {
    $sFilePath = str_replace("\\","/","classes/".$sLogicName.".class.php");
  }
 */
  include $sFilePath;
}

function handle_exception($oException) {
  print_r($oException);
}

function handle_error($errno, $errstr, $errfile, $errline) {
  echo "Error occurred: $errno, \"$errstr\", $errfile, $errline\n";
}

spl_autoload_register("loadLogic");

set_exception_handler("handle_exception");

set_error_handler("handle_error");

?>
