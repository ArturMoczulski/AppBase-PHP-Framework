<?php

/**
 * Configuration of the router. Maps shortcut URLs to correctly
 * formatted resource paths.
 */

$GLOBALS['Application']['routes'] = array(
  "/" => "/users/index", // mapping the main application page to the list of users
  "/login" => "/users/login", // mapping the /login page to the right users login action
  "/logout" => "/users/logout", // mapping /logout
  "/install" => "/AppInstallation/install"); // mapping /install

?>
