<!DOCTYPE html>
<html>
    <head>
        <base href="<?php echo $GLOBALS['Application']['URL'] ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $GLOBALS['Application']['name'] ?></title>
        <!-- <link rel='stylesheet' type='text/css' href='css/index.css' /> -->
        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-responsive.css">
    </head>
    <body>

        <?php if( $oLoggedUser ) {
    
        include "views/elements/menu.php"; ?>

        <div class="container-fluid #{controllerName}-#{actionName}-content-box">
            #{renderedAction}

            <footer>
                <h6>Artur Moczulski 2013</h6>
            </footer>        
        </div>

        <?php } else { ?>
        <div class="navbar">
            <div class="navbar-inner">
                <a class="brand" href="#"><?php echo $GLOBALS['Application']['name'] ?></a>
            </div>
        </div>

        <div class="contatiner #{controllerName}-#{actionName}-content-box">

            #{renderedAction}
            
            <footer>
                <h6>Artur Moczulski 2013</h6>
            </footer>        
        </div>
        <?php } ?>


        <script src="js/jquery.js"></script>
        <script src="js/bootstrap.js"></script>
    </body>
</html>
