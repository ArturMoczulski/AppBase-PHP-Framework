<?php

namespace Utils;

class Namespaces {

  public static function Strip($sFullClassName) {
    $aParts = explode("\\", $sFullClassName);
    return end($aParts);
  }

}
