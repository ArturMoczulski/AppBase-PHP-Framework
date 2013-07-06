<?php

namespace Models;

class Group extends \Core\Model\Model {

  const SUPERUSER = "superuser";
  const USER = "user";

  public function toString() { return $this->title; }

  protected $aDefaultAction = array("sControllerName"=>"groups","sActionName"=>"view");

  protected $aHasMany = array("User");

  protected $aValidation = array(
    "title" => array("NotEmpty") );

}
?>
