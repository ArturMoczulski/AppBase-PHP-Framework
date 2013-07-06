<?php

/**
 * The main logic for processing HTTP requests
 */

// Loading the framework
require_once "bootstrap.php";

// Getting the request URL
$sRelativePath = $_SERVER['REQUEST_URI'];

// Routing
$oRouter = new Core\Router();
$sRelativePath = $oRouter->route($sRelativePath);

// Request dispatching
$oDispatcher = new Core\Dispatcher();
$oResponse = $oDispatcher->dispatchFromRelativePath($sRelativePath); // loading the appropriate controller action

// Rendering the the response
$oViewRenderer = new Core\ViewRenderer();
echo $oViewRenderer->render($oResponse); 

?>
