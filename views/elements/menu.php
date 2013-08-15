<?php

$aMenuItems = array();

// Groups
$aMenuItems["/groups"] = "Groups";

// Users
$aMenuItems["/users"]="Users";

// Permissions
$aMenuItems["/permissions"]="Permissions";
?>

  <?php $bSwitchUsers = isset($aSwitchableUsers) && count($aSwitchableUsers)>1 ?>

  <?php if(!empty($aMenuItems) || $bSwitchUsers) { ?>
        <div class="navbar">
            <div class="navbar-inner">
                <a class="brand" href="#"><?php echo $GLOBALS['Application']['name'] ?></a>
                <ul class="nav">
                    <?php foreach( $aMenuItems as $sUrl => $sLink ) { 
                        if( \Models\Permission::CheckByNameAndModel(ltrim($sUrl, "/"), $oLoggedUser->group) ) {
                          echo "<li".($sUrl == $sCurrentActionUrl  || $sUrl == $sRequestedPath ? " class=\"active\"" : "").">";
                          echo $this->getHelper("HTML")->linkUrl($sLink, $sUrl);
                          echo "</li>";
                        }
                      } 
                    ?>
                </ul>

                <ul class="nav pull-right">
                  <li><?php echo $this->getHelper("HTML")->linkUrl('Logout', '/logout'); ?></li>
                </ul>

                <?php if( $bSwitchUsers ) { ?>
                <form action="/users/switch" method="POST" class="navbar-form pull-right">
                  <select name="iUserId">
                  <?php foreach( $aSwitchableUsers as $oUser ) { ?>
                    <option value="<?php echo $oUser->id ?>"<?php echo $oUser->id == $oLoggedUser->id ? " selected=\"true\"" : "" ?>>
                      <?php echo $oUser->email ?>
                    </option>
                  <?php } ?>
                  </select>
                  <input class="btn" type="submit" name="submit" value="Switch" />
                </form>
                <?php } ?>
            </div>
        </div>
  <?php } ?>

