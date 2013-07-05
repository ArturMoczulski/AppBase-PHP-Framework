<h2>Group <i><?php echo $oGroup->title ?></i></h2>

<?php 
$aIgnoreProperties = array("id","salt","encrypted_password"); 
$this->getHelper("Model")->renderTable(
  $oUserModel, 
  $oGroup->users, 
  $aIgnoreProperties,
  array(
    array("sControllerName"=>"users", "sActionName"=>"edit")
  ));
?>
