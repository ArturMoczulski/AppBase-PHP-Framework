            <div class="row-fluid">
                <div class="span1">
                    <?php echo $this->getHelper("HTML")->actionsMenu(array(
                      array('sLinkName'=>'All users', 'sControllerName'=>'users', 'sActionName'=>'index'),
                      array('sLinkName'=>'Add user', 'sControllerName'=>'users', 'sActionName'=>'save')));
                    ?>
                </div>
                
                <div class="span11">
                    <h2>Users</h2>
                    #{flashMessage}
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
                </div>
            </div>
