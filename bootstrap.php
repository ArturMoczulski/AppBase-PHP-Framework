<?php

/**
 * The main framework loading logic. This should be run prior
 * to any specific request handling or application logic. It
 * involves the following:
 * * loading config files,
 * * setting up class loading logic,
 * * setting up early exception and error handling.
 */

// TODO: need to decide what to do with error_reporting
error_reporting(-1);

/**
 * Loading required config files.
 */
$aConfigFiles = array("application", "route", "db");

foreach( $aConfigFiles as $sConfigFile ) {
  if( file_exists("config/$sConfigFile.php") )
    require_once "config/$sConfigFile.php";
  else
    throw new Exception("Configuration file \"$sConfigFile.php\" not found.");
}

/**
 * Hooking up the handler logic.
 */
spl_autoload_register("loadLogic");
set_exception_handler("handle_exception");
set_error_handler("handle_error");

/**
 * The main class loading logic, aka spl_autoload_register handler.
 */
function loadLogic($sLogicName) {

  $sFilePath = "";

  $aNamespaces = explode("\\", $sLogicName);
  array_pop($aNamespaces);
  $aNamespacesTemp = $aNamespaces;
  $sTopmostNamespace = array_shift($aNamespacesTemp);

  switch( $sTopmostNamespace ) {

    case "Controllers":
      /* controller source files are being loaded from the controllers/ 
       * directory and have a special ".controller.php" suffix */
      $sFilePath = str_replace("\\", "/", "controllers/".substr($sLogicName, strlen("Controllers\\")));
      if( count($aNamespaces) == 1 ) 
        $sFilePath .= ".controller.php"; // only controller files should be stored directly in controllers/ directory
      else
        $sFilePath .= ".class.php"; // controllers can not be stored anywhere else than directly in controllers/ directory
      break;

    case "Models":
      /* model course files are being loaded from the models/ directory */
      $sFilePath = str_replace("\\", "/", "models/".substr($sLogicName, strlen("Models\\"))).".class.php";
      break;

    default:
      /* any other classes are being loaded from the classes/ directory */
      $sFilePath = str_replace("\\","/","classes/".$sLogicName.".class.php");
      break;
  }

  if( file_exists($sFilePath) ) {
    include $sFilePath;
  } else {
    throw new \Exception("Class \"$sLogicName\" source file \"$sFilePath\" not found. ");
  }
}

function handle_exception($oException) {
  // TODO: need to improve exception handling
  print_r($oException);
}

function handle_error($errno, $errstr, $errfile, $errline) {
  // TODO: need to improve error handling
  echo "Error occurred: $errno, \"$errstr\", $errfile, $errline\n";
}


?>
