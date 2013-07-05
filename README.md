am_custom_framework
===================

Artur Moczulski's custom object oriented PHP application framework.

The purpose of this framework is to provide a very simple basis for highly customizable web applications. These libraries are meant to be a starting point for your applications providing standard solutions to generic problems and building a skeleton of conventions around your software. However, it is not meant to limit the development or create restraints. The framework is the initial point of the project, thus changes and customizations are highly encouraged. Comparing to other PHP frameworks out there, the codebase I have shared here reminds me of ArchLinux approach to creating your operating system. It is a highly configurable distro that requires and allows a lot of your own customization, but allows you do things completely your way.

This framework has been tested only for MySQL and probably supports only this database. There are DB abstration tools included, but no other database server has been tested or tried.

Installation
===

1. Deploy the project into desired directory.
2. Set up the database:
  1. Create a database and a database user.
  2. Adjust base migration files to use the right database. Modify the `use` statement at the beginning of all migration files.
  3. Run the migrations: `cat db/migrations/* | mysql -u[db_username] -p[db_password] [db_name]`
  4. Provide database connection credentials in config/db.php
3. Configure the application:
  1. Provide application's base URL in config/application.php.
  2. Provide database connection credentials in config/db.php
4. Go to application's base URL and follow the further instructions to create the superuser.
