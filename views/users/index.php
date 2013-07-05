<h2>Users</h2>
#{flashMessage}
<ul class="actions">
  <li><?php echo $this->getHelper("HTML")->link("Add user", "users", "save", array(), null, "action"); ?></li>
</ul>
<?php 
$aIgnoreProperties = array("id","salt","encrypted_password"); 
$this->getHelper("Model")->renderTable(
  $oModel, 
  $aData, 
  $aIgnoreProperties,
  array( 
    array("sControllerName"=>"users","sActionName"=>"delete"),
    array("sControllerName"=>"users","sActionName"=>"save","sLinkName"=>"Edit" )
  ));
?>
