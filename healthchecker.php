<?php
/*
Plugin Name: iBee Healthchecker
Description: A very simple plugin that provides information about the status of the server. Use /ping and /health.
Version: 1.0
Author: Nicolas Dominguez
*/

// Hook into 'init' to add rewrite rules
function healthchecker_add_rewrite_rule() {
	add_rewrite_rule( '^ping/?$', 'index.php?ping=1', 'top' );
	add_rewrite_rule( '^health/?$', 'index.php?health=1', 'top' );
}
add_action( 'init', 'healthchecker_add_rewrite_rule' );

// Add query variables
function healthchecker_add_query_vars( $vars ) {
	$vars[] = 'ping';
	$vars[] = 'health';
	return $vars;
}
add_filter( 'query_vars', 'healthchecker_add_query_vars' );

// Handle requests
function healthchecker_template_redirect() {

	// /ping
	if ( get_query_var( 'ping' ) ) {

		$ping_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

		header( 'Content-Type: application/json' );
		echo json_encode([
			'status' => 'OK',
			'ping_time' => round($ping_time * 1000, 2) . ' ms',
			'server_time' => date('Y-m-d H:i:s')
		]);
		exit;
	}

	// /health
	if ( get_query_var( 'health' ) ) {

		// Retrieve credentials from wp-config.php
		$db_name = DB_NAME;
		$db_user = DB_USER;
		$db_password = DB_PASSWORD;
		$db_host = DB_HOST;

		$web_service_status = 'OK';

		$db_connection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
		$database_status = $db_connection ? 'OK' : 'FAIL';

		$outbound_connection_status = @fsockopen("www.google.com", 80) ? 'OK' : 'FAIL';

		$overall_status = ($database_status === 'OK' && $outbound_connection_status === 'OK') ? 200 : 503;

		status_header($overall_status);

		header( 'Content-Type: application/json' );
		echo json_encode([
			'web_service' => $web_service_status,
			'database' => $database_status,
			'outbound_connection' => $outbound_connection_status
		]);
		exit;
	}
}
add_action( 'template_redirect', 'healthchecker_template_redirect' );

// Flush rewrite rules on plugin activation
function healthchecker_activate() {
	healthchecker_add_rewrite_rule();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'healthchecker_activate' );

// Flush rewrite rules on plugin deactivation
function healthchecker_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'healthchecker_deactivate');
