<h2><?php echo $bAdd ? "Add" : "Edit" ?> group</h2>
#{validationErrors}
#{flashMessage}
<form method="POST">

  <fieldset>

    <label for="sTitle">Title</label>
    <input type="text" name="sTitle" value="<?php echo $oGroup->title ?>" />

  </fieldset>
  <input type="submit" name="submit" value="Save" />
</form>
