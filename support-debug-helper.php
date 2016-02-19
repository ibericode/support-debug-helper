<?php
/*
Plugin Name: Support Debug Helper
Version: 1.0
Plugin URI: https://google.com/
Author: DvanKooten
Author URI: http://dvk.co/
Description: Helper plugin for support engineers to debug issues.
Text Domain: support-debug-helper
*/

function sdh_settings_page() {
	echo '<div class="wrap">';

	echo '<h1>Support Debug Helper</h1>';
	?>
	<div class="card">
		<h2>Code Usage</h2>
		<p>Search for code inside all active plugins & active themes.</p>

		<form method="GET">
			<p>
				<label>Search Pattern</label><br />
				<input type="text" name="_sdh_pattern" value="" class="regular-text" placeholder="Example: mc4wp_lists" />
			</p>

			<p>
				<input type="submit" value="Search" class="button" />
			</p>

			<?php wp_nonce_field( 'search_code_usage', '_sdh_nonce' ); ?>

		</form>
	</div>
<?php
	echo '</div>';
}

/**
 * Register menu item
 */
add_action( 'admin_menu', function() {
	add_submenu_page( 'tools.php', "Support Debug Helper", "Support Debug Helper", 'manage_options', 'support-debug-helper', 'sdh_settings_page' );
});


/**
 * Listen for actions
 */
add_action( 'admin_init', function() {

	if( empty( $_GET['_sdh_pattern'] ) ) {
		return;
	}

	// ensure we are logged-in
	if( ! current_user_can( 'manage_options' ) ) {
		exit;
	}

	// nonce validation
	check_admin_referer( 'search_code_usage', '_sdh_nonce' );

	// get pattern
	$pattern = trim( stripslashes( $_GET['_sdh_pattern'] ) );

	// search through plugins
	$plugin_dir = WP_PLUGIN_DIR;
	$command = sprintf( 'grep -r -n --include \*.php "%s" %s' , $pattern, $plugin_dir );
	$output = shell_exec( $command );

	// add some newlines in between
	$output .= PHP_EOL . PHP_EOL . PHP_EOL;

	// search through themes
	$theme_dir = get_theme_root();
	$command = sprintf( 'grep -r -n --include \*.php "%s" %s' , $pattern, $theme_dir );
	$output .= shell_exec( $command );

	// strip abspath from output
	$output = str_replace( ABSPATH, '/', $output );

	// print result
	die( '<pre>' . esc_html( $output ) . '</pre>' );
});