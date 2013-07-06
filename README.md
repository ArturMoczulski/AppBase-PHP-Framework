am_custom_framework
===================

Artur Moczulski's custom object oriented PHP application framework.

The purpose of this framework is to provide a very simple basis for highly customizable web applications. These libraries are meant to be a starting point for your applications providing standard solutions to generic problems and building a skeleton of conventions around your software. However, it is not meant to limit the development or create restraints. The framework is the initial point of the project, thus changes and customizations are highly encouraged. Comparing to other PHP frameworks out there, the codebase I have shared here reminds me of ArchLinux approach to creating your operating system. It is a highly configurable distro that requires and allows a lot of your own customization, but allows you do things completely your way.

This framework has been tested only for MySQL and probably supports only this database. There are DB abstration tools included, but no other database server has been tested or tried.

Installation
===

1. Deploy the project into desired directory.
2. Set up the the virtual host to point application's base URL into the desired directory (see Apache 2 example virtual hosts file below).
3. Set up the database:
  1. Create a database and a database user.
  2. Adjust base migration files to use the right database. Run: `sed -i 's/custom_framework/[db_name]/' db/migrations/*`.
  3. Run the migrations: `cat db/migrations/* | mysql -u[db_username] -p[db_password] [db_name]`
4. Configure the application:
  1. Provide application's base URL in config/application.php.
  2. Provide database connection credentials in config/db.php
5. Go to application's base URL and follow the further instructions to create the superuser.

Example Apache2 virtual host configuration
------------------------------------------

    <VirtualHost *:80>

      ServerAdmin artur.moczulski@gmail.com
      ServerName [application_base_url]

      DocumentRoot [application_root_directory]

      LogLevel debug
      ErrorLog /var/log/apache2/[application_base_url]-error_log
      CustomLog /var/log/apache2/[application_base_url]-acces_log common

      RewriteEngine on
      
      <Directory [application_root_directory]>
        Options FollowSymLinks
        AllowOverride all
        Order deny,allow
        Allow from all
      </Directory>

    </VirtualHost>

Working with the framework
==========================

Conventions
-----------
* <b>URLs:</b> RESTful [base url]/[underscore controller name]/[camelcase action name with lower case first letter]/[argument #1]/[argument #1]/..., i.e. "www.example.com/users/view/1"
* <b>Table names:</b> underscored plural nouns, i.e. "users",
* <b>Table columns:</b> underscored, i.e. "email_address",
* <b>Foreign key names:</b> underscored with "_id" suffix, i.e. "user_id",
* Any class file names except controller classes have a ".class.php" suffix,
* <b>Model classes:</b> camelcase singular, i.e. "User";  the model class has to be enclosed in the "\Models\" namespace and extend "\Core\Model\Model"; all model classes are stored in models/ directory,
* <b>Controller classes:</b> camelcase plural, i.e. "Users"; the controller class hat to be enclosed in the "\Controllers\" namespace and extend "\Core\Controller\Controller"; all controller classes are stored in controllers/ directory; all controller class file names must have a ".controller.php" prefix instead of ".class.php",
* <b>Action names:</b> camelcase with "Action" suffix, i.e. loginAction,
* Only controller public methods with the "Action" suffix are considered actions,
* <b>Views:</b> views for controller's actions are stored in views/[underscored controller name]/[underscored action name].php;

Creating new models
-------------------
1. Create a new migration file in db/migrations/, i.e. "db/migrations/20130705_001.sql" with SQL to create the model table, i.e.:
    ```
    USE example_project;

    CREATE TABLE IF NOT EXISTS products (  
      id int(5) NOT NULL AUTO_INCREMENT,  
      name varchar(50) CHARACTER SET utf8 NOT NULL,  
      PRIMARY KEY (id)  
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;  
    ```
2. Run the migration file: `cat db/migrations/20130705_001.sql | mysql -u[db_user] -p[db_pass] [db_name]`.
3. Create the model class in models/, i.e. models/Product.class.php, i.e.
    ```php
    <?php

    namespace Models;

    class Product extends \Core\Model\Model {
    }
    
    ?>
    ```

Creating new controllers
------------------------
1. Create a new controller class in controllers/, i.e. "controllers/Products.controller.php" with the controller class, i.e.
    ```php
    <?php

    namespace Controllers;
    
    class Products extends \Core\Controller\Controller {
    }

    ?>
    ```
2. If you want to create a controller matching a model, i.e. \Models\Product, this is enough. However, if you need to use a different or additional models, define the `aModelsUsed` property with class names of models to use, i.e.
    ```php
    protected $aModelsUsed = array('\Models\Product', '\Models\Retailer');
    ```
3. If you don't want to use any models, define the `aModelsUsed` property as an empty array, like so:
    ```php
    protected $aModelsUsed = array();
    ```
4. Add actions to the controller; the method's parameters are arguments passed in the request URI after the action name, divided by "/" (i.e. www.example.com/products/view/1)
    ```php
    public function viewAction($iId) {
    }
    ```

Creating new views
------------------
Create a new file in the views directory for the right controller views/[underscore controller name]/. The file name should be underscored action name with ".php" suffix, i.e. `views/products/view.php`. You can use plain HTML / PHP code in the view file.
```html

<h2>Product details</h2>  
<p>This view shows details of a product</p>  
```
