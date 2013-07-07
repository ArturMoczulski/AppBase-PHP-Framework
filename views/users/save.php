<h2><?php echo $bAdd ?  "Add" : "Edit" ?> user</h2>
#{validationErrors}
<form method="POST">

  <fieldset>

    <label for="sEmail">Email</label>
    <input type="text" name="sEmail" value="<?php echo $oUser->email ?>" />

    <?php if( $bAdd ) { ?>
    <fieldset>
      <label for="sPassword">New password:</label>
      <input type="password" name="sPassword" value="<?php echo $oUser->password ?>" />
      <label for="sPasswordConfirm">Repeat password:</label>
      <input type="password" name="sPasswordConfirm" />
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
  <input type="submit" name="submit" value="<?php echo $bAdd ? "Add" : "Edit" ?>" />
</form>
