# wp_healthcheck_module

A very simple plugin that checks the status of the server. It provides two endpoints:

**/ping**: Responds a JSON object with the ping time.
**/health**: Responds a JSON object with statuses of the web server, database and outbound connection.

If you're using Apache, you must have a .htaccess and a .conf file properly configured to allow rewrite rules. You will also need to have the `mod_rewrite` module enabled. No additional configuration needed for Nginx servers.
