<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='pl'>
    <head>
        <base href="<?php echo $GLOBALS['Application']['URL'] ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>AppBase PHP Framework</title>
        <link rel='stylesheet' type='text/css' href='css/index.css' />
    </head>
    <body>

      <header>
        <h1>AM custom framework</h1>
      </header>

      <div id="content-cotainer">
      <div id="app-content">

            <?php if( $oLoggedUser ) {
              include "views/elements/menu.php";
            } ?>

            <div id='content' class="#{controllerName}-#{actionName}-content-box">
		          #{renderedAction}	
            </div>

            <div class="clearfix"></div>

        </div>
        </div>

        <footer>
            <h6>Artur Moczulski 2013</h6>
        </footer>        
    </body>
</html>
