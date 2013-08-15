            <div class="row-fluid">
                <div class="span1">
                    <?php echo $this->getHelper("HTML")->actionsMenu(array(
                      array('sLinkName'=>'All users', 'sControllerName'=>'users', 'sActionName'=>'index'),
                      array('sLinkName'=>'Add user', 'sControllerName'=>'users', 'sActionName'=>'save')));
                    ?>
                </div>
                
                <div class="span11">
                    <h2><?php echo $bAdd ?  "Add" : "Edit" ?> user</h2>
                    #{validationErrors}
                    #{flashMessage}
<form method="POST">

  <fieldset>

    <input type="text" name="sEmail" placeholder="Email" value="<?php echo $oUser->email ?>" />

    <?php if( $bAdd ) { ?>
    <fieldset>
      <input type="password" name="sPassword" placeholder="Password" value="<?php echo $oUser->password ?>" />
      <input type="password" placeholder="Confirm password" name="sPasswordConfirm" />
    </fieldset>      
    <?php } ?>

    <label for="iGroupId">Group</label>
    <select name="iGroupId">
    <?php foreach( $aGroups as $oGroup ) { ?>
      <option value="<?php echo $oGroup->id ?>"<?php echo $oGroup->id == $oUser->group->id ? " selected=\"true\"" : "" ?>>
        <?php echo $oGroup->title ?>
      </option>
    <?php } ?>
    </select>

  </fieldset>
  <input type="submit" class="btn" name="submit" value="<?php echo $bAdd ? "Add" : "Edit" ?>" />
</form>
                </div>
            </div>
