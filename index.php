<?php

/**
 * The main logic for processing HTTP requests
 */

// Loading the framework
require_once "bootstrap.php";

// Getting the request URL
$sRequestedPath = $_SERVER['REQUEST_URI'];

// Routing
$oRouter = new Core\Router();
$sRoutedPath = $oRouter->route($sRequestedPath);

// Request dispatching
$oDispatcher = new Core\Dispatcher();
$oResponse = $oDispatcher->dispatchFromRelativePath($sRoutedPath, $sRequestedPath);

// Rendering the the response
$oViewRenderer = new Core\ViewRenderer();
echo $oViewRenderer->render($oResponse); 

?>
