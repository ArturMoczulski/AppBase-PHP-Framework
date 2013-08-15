            <div class="row-fluid">
                <div class="span1">
                    <?php echo $this->getHelper("HTML")->actionsMenu(array(
                      array('sLinkName'=>'All groups', 'sControllerName'=>'groups', 'sActionName'=>'index'),
                      array('sLinkName'=>'Add group', 'sControllerName'=>'groups', 'sActionName'=>'save')));
                    ?>
                </div>
                
                <div class="span11">
<h2><?php echo $bAdd ? "Add" : "Edit" ?> group</h2>
                    #{validationErrors}
                    #{flashMessage}
<form method="POST">
    <fieldset>
        <input type="text" placeholder="Title" name="sTitle" value="<?php echo $oGroup->title ?>" />
    </fieldset>
    <input type="submit" class="btn" name="submit" value="<?php echo $bAdd ? "Add" : "Edit" ?>" />
</form>
                </div>
            </div>
