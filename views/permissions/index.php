<h2>Permissions</h2>
#{validationErrors}
#{flashMessage}
<?php
if( !empty($aPermissions) ) {

  echo '<table class="table table-bordered table-striped table-hover">';

  echo "<thead>";
  echo "<th></th>";
  foreach( $aAROs as $oARO ) {
    echo "<th>".$oARO->toString()."</th>";
  }
  echo "</thead>";

  echo "<tbody>";
  foreach( $aPermissions as $sACOName => $aACOPermissions ) {

    echo "<tr>";
    echo "<th>".$sACOName."</th>";

    foreach( $aACOPermissions as $sAROName => $bPermission ) {

      echo "<td>". ($bPermission ? "x" : "") . "</td>";

    }

    echo "</tr>";

  }
  echo "</tbody>";

  echo "</table>";

}
?>

