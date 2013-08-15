            <div class="row-fluid">
                <div class="span1">
                    <?php echo $this->getHelper("HTML")->actionsMenu(array(
                      array('sLinkName'=>'All groups', 'sControllerName'=>'groups', 'sActionName'=>'index'),
                      array('sLinkName'=>'Add group', 'sControllerName'=>'groups', 'sActionName'=>'save')));
                    ?>
                </div>
                
                <div class="span11">
<h2>Groups</h2>
#{flashMessage}
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
                </div>
            </div>
