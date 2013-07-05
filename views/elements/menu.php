<?php

$aMenuItems = array();

// Groups
$aMenuItems["/groups"] = "Groups";

// Users
$aMenuItems["/users"]="Users";

// Permissions
$aMenuItems["/permissions"]="Permissions";

// Logout
$aMenuItems["/logout"]="Logout";

?>
<div id='first-column'>

  <?php $bSwitchUsers = isset($aSwitchableUsers) && count($aSwitchableUsers)>1 ?>

  <?php if(!empty($aMenuItems) || $bSwitchUsers) { ?>
  <ul class='menu'>

    <?php if( $bSwitchUsers ) { ?>
    <li class="user-switch">
      <h4>Logged in as</h4>
      <form action="/users/switch" method="POST">
        <select name="iUserId">
        <?php foreach( $aSwitchableUsers as $oUser ) { ?>
          <option value="<?php echo $oUser->id ?>"<?php echo $oUser->id == $oLoggedUser->id ? " selected=\"true\"" : "" ?>>
            <?php echo $oUser->email ?>
          </option>
        <?php } ?>
        </select>
        <input type="submit" name="submit" value="Switch" />
      </form>
    </li>
    <?php } ?>

    <?php 
      foreach( $aMenuItems as $sUrl => $sLink ) { 
        if( \Models\Permission::CheckByNameAndModel(ltrim($sUrl, "/"), $oLoggedUser->group) ) {
          echo "<li>";
          echo $this->getHelper("HTML")->linkUrl($sLink, $sUrl);
          echo "</li>";
        }
      } 
    ?>

  </ul>
  <?php } ?>

</div>
