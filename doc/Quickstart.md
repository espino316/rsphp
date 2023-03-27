# RSPhp doc
## Requirements
This framework is designed to run under apache, with .htaccess files enabled, is the component that perform the url segments and secure the application, leaving the config files out of the redirection.

## Start point
The application start point is the folder *public/index.pgp*, the .htaccess file will redirect the traffic there. This file define the constants and start the framework.

### Start the framework
The main class is **RS.php** file, the method *startUp* start the application:
* Get the method (GET, POST, ETC).
* Get the headers.
* Define the base url.
* Load the configurations.
* Get the inputs.
* Process the inputs.
* Perform the routing.

## Configuration files
RSPhp framework have the following configurations files, in the *config* folder:
* db_conns.php
    * This file stores the connection to databases.
        * The password is encrypted, not stored in plain text.
        * Also, we can specify to use an environment variable if prefered.
* app_config.php
* routes.php
