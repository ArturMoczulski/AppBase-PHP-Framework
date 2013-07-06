<?php

namespace Models;

class User extends \Core\Model\Model {

  public $password;

  public $aBelongsTo = array("Group");

  public $aValidation = array(
    "email" => array(
      "NotEmpty",
      "Email" )
  );

  public function afterSave() {
    parent::afterSave();
    // deleting password information as soon as it's not needed
    $this->password = null;
  }

  public function beforeInsert() {
    parent::beforeInsert();
    $this->generateSalt();
    $this->encryptPassword();
  }

  public function beforeUpdate() {
    parent::beforeUpdate();
    $this->encryptPassword();
  }

  public function authenticate($sPassword) {
    return $this->encrypted_password == crypt($sPassword, $this->salt);
  }

  protected function generateSalt() {
    $this->salt = md5(rand(0, 1023) . '@' . time());
  }

  protected function encryptPassword() {
    $this->encrypted_password = crypt($this->password, $this->salt);
  }

  public function toString() { return $this->email; }

}

?>
