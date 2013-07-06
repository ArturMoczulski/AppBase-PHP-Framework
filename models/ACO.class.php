<?php

namespace Models;

class ACO extends \Core\Model\Model {

  public function getTableName() { return "aco"; }

  public function toString() { return isset($this->name) ? $this->name : ""; }

  protected $aHasMany = array("Permission");

  protected $aValidation = array(
    'name' => array('NotEmpty'),
    'default_access' => array('NotEmpty') );

}
