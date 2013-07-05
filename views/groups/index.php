<h2>Groups</h2>
#{flashMessage}
<ul class="actions">
  <li><?php echo $this->getHelper("HTML")->link("Add group", "groups", "save", array(), null, "action"); ?></li>
</ul>
<?php 
$aIgnoreProperties = array("id"); 
$this->getHelper("Model")->renderTable(
  $oModel, 
  $aData, 
  $aIgnoreProperties,
  array( 
    array("sControllerName"=>"groups","sActionName"=>"delete"),
    array("sControllerName"=>"groups","sActionName"=>"save","sLinkName"=>"Edit" )
  ));
?>
