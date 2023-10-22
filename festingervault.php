<?php
//ini_set( 'memory_limit', '256' );

/**
 * Festinger Vault Fork
 *
 * @package     festingervault
 * @author      Festinger Vault
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Festinger Vault Fork
 * Plugin URI:        https://github.com/hansschuijff/festinger-vault
 * GitHub Plugin URI: hansschuijff/festinger-vault
 * Version:           4.2.0.h2
 * Description:       Festinger vault - The largest plugin market
 * 					  Get access to 25K+ kick-ass premium WordPress themes and plugins. Now directly from your WP dashboard. Get automatic updates and one-click installation by installing the Festinger Vault plugin.
 * Author:            Hans Schuijff
 * Author URI:        https://dewitteprins.nl
 * Text Domain:       festinger-vault
 * Domain Path:       /languages
 * Requires at least: 6.x
 * Tested up to:      6.3.1
 * Requires PHP:      8.0
 * Tested PHP:        8.2.10
 * License:           GNU General Public License v2.0 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! defined( 'FV_PLUGIN_DIR' ) ) {
	define( 'FV_PLUGIN_DIR', dirname( __FILE__ ) );
}
if ( ! defined( 'FV_PLUGIN__FILE__' ) ) {
	define( 'FV_PLUGIN__FILE__', __FILE__ );
}
if ( ! defined( 'FV_PLUGIN_ROOT_PHP' ) ) {
	define( 'FV_PLUGIN_ROOT_PHP', trailingslashit( dirname( __FILE__ ) ) . basename( __FILE__ ) );
}
if ( ! defined( 'FV_PLUGIN_ABSOLUTE_PATH' ) ) {
	define( 'FV_PLUGIN_ABSOLUTE_PATH', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'FV_REST_API_URL' ) ) {
	define( 'FV_REST_API_URL', 'https://engine.festingervault.com/api/' ); // Add to this base URL to make it specific to your plugin or theme.
}
if ( ! defined( 'FV_TEXTDOMAIN' ) ) {
	define( 'FV_TEXTDOMAIN', \get_plugin_data(__FILE__)['TextDomain'] );
}
define( 'FV_PLUGIN_VERSION', \get_plugin_data(__FILE__)['Version'] );

require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
require_once( FV_PLUGIN_DIR . '/functions/ajax_functions.php' );
require_once( FV_PLUGIN_DIR . '/includes/polyfills.php' );

// This include contains probably dead code, but just in case for now.
require_once( FV_PLUGIN_DIR . '/includes/dead.php' );

/**
 * Check for updates of this plugin.
 */
// require_once( FV_PLUGIN_DIR . '/classes/plugin-update-checker.php' );
// require_once( FV_PLUGIN_DIR . '/includes/puc.php' );

/**
 * Registers the REST API Routes/Endpoints for this plugin.
 *
 * @return void
 */
function fv_register_rest_routes() {

	register_rest_route( 'fv_endpoint/v1', '/fvforceupdateautoupdate', [
		'method'              => WP_REST_Server::READABLE,
		'callback'            => 'fv_custom_endpoint_create_auto',
		'permission_callback' => '__return_true',
		'args'                => [
			'license_key' => [
				'required' => true,
				'type'     => 'string',
			],
			'data_id' => [
				'required' => true,
				'type'     => 'string',
			],
			'domain_name' => [
				'required' => true,
				'type'     => 'string',
			],
		],
	] );

	register_rest_route( 'fv_endpoint/v1', '/fvforceupdate', [
		'method'              => WP_REST_Server::READABLE,
		'callback'            => 'fv_custom_endpoint_create',
		'permission_callback' => '__return_true',
		'args'                => [
			'salt_id' => [
				'required' => true,
				'type'     => 'number',
			],
			'salt' => [
				'required' => true,
				'type'     => 'string',
			],
		],
	] );
}
add_action( 'rest_api_init', 'fv_register_rest_routes' );

/**
 * Callback of Rest Api Route/endpoint "fv_endpoint/v1/fvforceupdateautoupdate"
 *
 * Calls api to find matching plugins and themes
 * and saves the slugs of them in the auto-update lists.
 *
 * @param WP_REST_Request $request
 * @return string 'succes' or 'failed'
 */
function fv_custom_endpoint_create_auto( $request ) {

	// get parameters from the rest request.
	$req_license_key    = $request->get_param( 'license_key' );
	$req_license_status = $request->get_param( 'enable_disable' );

	// if invalid license, just empty auto update liest..
	if ( ! fv_is_active_license_key( $req_license_key )
	|| ! fv_is_license_enabled( $req_license_status  )  ) {

		fv_remove_auto_updates();
		return ( 'success' );
	}

	// get installed plugins and themes.
	$plugins  = fv_get_installed_plugins_for_api_request();
	$themes   = fv_get_installed_themes_for_api_request();

	$fv_plugins  = array();
	$fv_themes   = array();

	foreach ( fv_array_split( $plugins, count( $themes ), 80 ) as $plugins ) {

		$fv_api = fv_get_remote_matches( $plugins, $themes );

		// if license fails, or domain is blocked,just bail out.
		if ( isset( $fv_api->result )
		&&   fv_api_call_failed( $fv_api->result ) ) {
			return ( 'failed' );
		}

		// only send themes in the first iterations (if there are more)
		$themes = array();

		$fv_plugins = array_merge( $fv_plugins, $fv_api->plugins );
		$fv_themes  = array_merge( $fv_themes,  $fv_api->themes );
	}

	$fv_plugin_slugs = array_keys( fv_remove_duplicate_plugins( $fv_plugins ) );
	$fv_theme_slugs  = array_keys( fv_remove_duplicate_themes( $fv_themes ) );

	fv_set_plugins_auto_update_list( $fv_plugin_slugs );
	fv_set_themes_auto_update_list( $fv_theme_slugs );

	return ( 'success' );
}

/**
 * Callback of Rest Api Route "fv_endpoint/v1/fvforceupdate"
 *
 * @param WP_REST_Request $request
 * @return string 'succes' or 'failed'
 */
function fv_custom_endpoint_create( $request ) {

	// build an array with all active license keys.

	$License_keys   = fv_get_license_keys();
	$License_keys[] = '98yiuyiy1861';

	$get_fv_salt_id = $request->get_param( 'salt_id' );
	$get_fv_salt    = $request->get_param( 'salt' );

	if ( empty( $get_fv_salt_id ) || empty( $get_fv_salt ) ) {
		return 'failed';
	}

	$query_base_url = FV_REST_API_URL . 'salt-verification';
	$query_args     = array(
		'salt_id'      => $get_fv_salt_id,
		'salt'         => $get_fv_salt,
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'salt_verification',
		'license_v'    => FV_PLUGIN_VERSION,
	);
	$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response = fv_run_remote_query( $query );
	$response = json_decode( wp_remote_retrieve_body( $response ) );

	$push_update_result   = 1;
	$push_update_message  = 'Failed';
	$run_salt_push_update = false;

	// What is result 1 and status 0?
	if ( $response->result == 1 && $response->status == 0 ) {

		if ( in_array( $response->license_key, $License_keys )
		&& ( ( 'license' === $response->data_method )
		||   (  'domain' === $response->data_method && $response->domain_name === $_SERVER['HTTP_HOST'] ) ) ) {

			switch ( $response->push_for ) {
				case 'all':
					fv_auto_update_download();
					$push_update_message = 'All themes & plugins successfully updated';
					break;

				case 'plugin':
					fv_auto_update_download( 'plugin' );
					$push_update_message = 'All plugins are successfully updated';
					break;

				case 'theme':
					fv_auto_update_download( 'theme' );
					$push_update_message = 'All themes are successfully updated';
					break;

				default:
					break;
			}
		}
		$run_salt_push_update = true;
	}

	if ( $response->result == 0 && $response->status == 0 ) {
		$push_update_message = 'Already updated';
		$run_salt_push_update = true;
	}

	if ( $run_salt_push_update ) {
		$query_base_url = FV_REST_API_URL . 'salt-push-update-result';
		$query_args     = array(
			'salt_id'             => $get_fv_salt_id,
			'salt'                => $get_fv_salt,
			'push_update_status'  => $push_update_result,
			'push_update_message' => $push_update_message,
			'license_pp'          => $_SERVER['REMOTE_ADDR'],
			'license_host'        => $_SERVER['HTTP_HOST'],
			'license_mode'        => 'salt_push_update_result',
			'license_v'           => FV_PLUGIN_VERSION,
			);
		$query         = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response      = fv_run_remote_query( $query );
	}
}

/**
 * Initial setup at activation.
 *
 * @return void
 */
function fv_activate() {
	fv_build_upload_dirs();
	fv_forget_white_label_switch();
	fv_forget_auto_update_lists();
}
register_activation_hook( __FILE__, 'fv_activate' );

/**
 * Creates the folder structure for uploading plugins and themes.
 *
 * @return void
 */
function fv_build_upload_dirs() {

	$dirs = array(
		'base',
		'plugins',
		'plugins-backups',
		'themes',
		'themes-backups',
		'bulk-install',
		'bulk-extract',
	);

	foreach ( $dirs as $dir ) {

		$dir = fv_get_upload_dir( $dir, must_exist: false );

		// make upload-dir
		if ( wp_mkdir_p( $dir ) ) {

			fv_mkfile( trailingslashit( $dir ) . 'index.html' );
		}
	}
}

/**
 * Make a file with a given content.
 *
 * This function does not overwrite a file if it already exists.
 *
 * @param string $file Full path filename.
 * @param string $content content to write to the file.
 * @return boolean True/false does file exists?
 */
function fv_mkfile( string $file, string $content = '' ) : bool {
	// nothing to do.
	if ( file_exists( $file ) ) {
		return true;
	}
	// Directory must already exist.
	if ( ! is_dir( pathinfo( $file )['dirname'] ) ) {
		return false;
	}
	// make file with content.
	if ( $file_handle = @fopen( $file, 'w' ) ) {
		fwrite( $file_handle, $content );
		fclose( $file_handle );
	}
	// It should exist now.
	return file_exists( $file );
}

/**
 * Cleans up at deactivation of Festinger Vault plugin.
 *
 * @return void
 */
function fv_deactivation() {

	if ( fv_has_license_1() ) {
		$response = fv_run_remote_deactivate_license( fv_get_license_key(), fv_get_license_domain_id() );
		fv_forget_license_1();
	}

	if ( fv_has_license_2() ) {
		$response = fv_run_remote_deactivate_license( fv_get_license_key_2(), fv_get_license_domain_id_2() );
		fv_forget_license_2();
	}

	fv_forget_white_label_settings();
}
register_deactivation_hook( __FILE__, 'fv_deactivation' );

/**
 * White labels (based on settings) some of the data retrieved
 * by the WordPress.org API requests.
 *
 * This data is presented in the plugins wp-admin page (details).
 *
 * Possible $action:
 *   query_plugins
 *   plugin_information
 *   hot_tags
 *   hot_categories
 *
 * @see https://developer.wordpress.org/reference/functions/plugins_api/
 *
 * @param object|array       $obj    The result object or array. Default false.
 * @param string             $action The type of information being requested from the Plugin Installation API.
 * @param object             $arg    Plugin API arguments.
 * @return object|array|WP_Error
 */
function fv_perhaps_white_label_plugin_api_result( $res, $action, $args ) {

	if ( ! is_wp_error( $res )
	&&     in_array( $action, array( 'plugin_information', 'query_plugins' ), true )
	&&     isset( $args->slug ) && $args->slug === fv_get_slug( plugin_basename( __FILE__) ) ) {

		$res->name     = fv_perhaps_white_label_plugin_name( $res->name );
		$res->author   = fv_perhaps_white_label_plugin_author( $res->author );
		$res->sections = array(
			'description' => fv_perhaps_white_label_plugin_description( $res->sections['description'] ),
		);
	}

	return $res;
}
add_filter( 'plugins_api_result', 'fv_perhaps_white_label_plugin_api_result', 20, 3 );

/**
 * Perhaps whitelist this WordPress plugins in admins plugins page.
 *
 * Note: this doesn't change the result of get_plugin_data() or get_plugins();
 *
 * @param array $plugins Array of installed plugins and their data.
 * @return array Filtered array of installed plugins data.
 */
function fv_perhaps_white_label_all_plugins_filter( $plugins ) {
	$key = plugin_basename( FV_PLUGIN_DIR . '/festingervault.php' );

	$plugins[ $key ]['Name']        = fv_perhaps_white_label_plugin_name( $plugins[ $key ]['Name'] );
	$plugins[ $key ]['Description'] = fv_perhaps_white_label_plugin_description( $plugins[ $key ]['Description'] );

	$plugins[ $key ]['Author']      = fv_perhaps_white_label_plugin_author( $plugins[ $key ]['Author'] );
	$plugins[ $key ]['AuthorName']  = fv_perhaps_white_label_plugin_author( $plugins[ $key ]['AuthorName'] );

	$plugins[ $key ]['AuthorURI']   = fv_perhaps_white_label_plugin_author_uri( $plugins[ $key ]['AuthorURI'] );
	$plugins[ $key ]['PluginURI']   = fv_perhaps_white_label_plugin_author_uri( $plugins[ $key ]['PluginURI'] );

	return $plugins;
}
add_filter( 'all_plugins', 'fv_perhaps_white_label_all_plugins_filter' );

/**
 * Change the plugin name to the name from the settings,
 * to enable white labeling.
 *
 * Question: FV doesn't seem to use translations. Why do this?
 *
 * @param string $translated_text Translated string value.
 * @param string $text   Untranslated string value.
 * @param string $domain Textdomain.
 * @return void
 */
function fv_perhaps_white_label_plugin_translation( $translated_text, $text, $domain ) {

	if ( FV_TEXTDOMAIN === $domain
	&&   'Festinger Vault' === $text ) {
		$translated_text = fv_perhaps_white_label_plugin_name( $text );
	}
	return $translated_text;
}
add_filter( 'gettext', 'fv_perhaps_white_label_plugin_translation', 20, 3 );

/**
 * Checks if user has access and adds plugin pages to the WordPress admin menu's.
 *
 * @return void
 */
function fv_add_pages_to_admin_menu() {

	if ( ! fv_current_user_has_access() ) {
		echo "Permission denied";
		return;
	}

	add_menu_page(
		page_title: fv_perhaps_white_label_plugin_name(),
		menu_title: fv_perhaps_white_label_plugin_name(),
		capability: 'read',
		menu_slug:  'festinger-vault',
		callback:   'fv_render_vault_page',
		icon_url:   fv_perhaps_white_label_plugin_icon_url(),
		position:   99
	);

	add_submenu_page(
		parent_slug: 'festinger-vault',
		page_title:  'All Plugins',
		menu_title:  'Vault',
		capability:  'read',
		menu_slug:   'festinger-vault',
		callback:    'fv_render_vault_page'
	);

	// Only add Activation page when white labeling is not enabled
	if ( ! fv_should_white_label() ) {

		add_submenu_page(
			parent_slug: 'festinger-vault',
			page_title:  'Activation',
			menu_title:  'Activation',
			capability:  'manage_options',
			menu_slug:   'festinger-vault-activation',
			callback:    'fv_render_activation_page'
		);
	}

	add_submenu_page(
		parent_slug: 'festinger-vault',
		page_title:  'Plugin Updates',
		menu_title:  'Plugin Updates',
		capability:  'manage_options',
		menu_slug:   'festinger-vault-updates',
		callback:    'fv_render_plugin_updates_page'
	);

	add_submenu_page(
		parent_slug: 'festinger-vault',
		page_title:  'Theme Updates',
		menu_title:  'Theme Updates',
		capability:  'manage_options',
		menu_slug:   'festinger-vault-theme-updates',
		callback:    'fv_render_theme_updates_page'
	);

	// Only add History and Settings page when white labeling is not enabled
	if ( ! fv_should_white_label() ) {

		add_submenu_page(
			parent_slug: 'festinger-vault',
			page_title:  'History',
			menu_title:  'History',
			capability:  'manage_options',
			menu_slug:   'festinger-vault-theme-history',
			callback:    'fv_render_history_page'
		);

		add_submenu_page(
			parent_slug: 'festinger-vault',
			page_title:  'Settings',
			menu_title:  'Settings',
			capability:  'manage_options',
			menu_slug:   'festinger-vault-settings',
			callback:    'fv_render_settings_page'
		);
	}
}
add_action( 'admin_menu', 'fv_add_pages_to_admin_menu' );

/**
 * Replace dashes and underscores in a string by spaces and change the first letter to uppercase.
 *
 * @param string $string A string value.
 * @return string String without dashes, underscores and capitalized.
 */
function fv_slug_to_title( $string ) {
	$rem_dash    = str_replace( "-", " ", $string );
	$rem_unscore = str_replace( "_", " ", $rem_dash );
	return ucfirst( $rem_unscore );
}

/**
 * Enqueue stylesheets and scripts.
 *
 * @param string $hook Hook suffix for the current admin page (so you can check the current admin page ).
 * @return void
 */
function fv_enqueue_styles_and_scripts( $hook ) {

	// bail out if not on a festinger-vault admin page.
	if ( false === strpos( get_current_screen()->base, 'festinger-vault' ) ) {
		return;
	}
	fv_enqueue_styles();
	fv_enqueue_scripts();
}
add_action( 'admin_enqueue_scripts', 'fv_enqueue_styles_and_scripts' );

/**
 * Enqueue styles css files.
 *
 * @return void
 */
function fv_enqueue_styles() : void {
	// wp_enqueue_style(
	// 	handle: 'pagicss',
	// 	src:    'https://pagination.js.org/dist/2.6.0/pagination.css',
	// 	deps:   array(),
	// 	ver:    FV_PLUGIN_VERSION
	// );
	wp_enqueue_style(
		handle: 'fwv_font_style',
		src:    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'
	);
	wp_enqueue_style(
		handle: 'fv_bootstrap',
		src:    'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css',
		deps:   array(),
		ver:    FV_PLUGIN_VERSION
	);
	wp_enqueue_style(
		handle: 'custom-alert-css',
		src:    '//cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css',
		deps:   array(),
		ver:    FV_PLUGIN_VERSION
	);
	wp_enqueue_style(
		handle: 'custom-dt-css',
		src:    'https://cdn.datatables.net/1.10.23/css/jquery.dataTables.css',
		deps:   array(),
		ver:    FV_PLUGIN_VERSION
	);
	wp_enqueue_style(
		handle: 'roboto-dt-css',
		src:    'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap',
		deps:   array(),
		ver:    FV_PLUGIN_VERSION
	);
	wp_enqueue_style(
		handle: 'fv_festinger_css',
		src:    FV_PLUGIN_ABSOLUTE_PATH.'assets/css/wp_festinger_vault.css',
		deps:   array(
			'custom-dt-css', // jQuery datatables css is full of !important qualifiers, so we must load our css later.
			'fv_bootstrap'   // Making sure we can override bootstrap css when needed.
		),
		ver:    FV_PLUGIN_VERSION
	);
}

/**
 * Enqueue js scripts.
 *
 * @return void
 */
function fv_enqueue_scripts() : void {

	fv_jquery_upgrade();
	fv_enqueue_third_party_scripts();

	wp_enqueue_script(
		handle: 'script-js',
		src:    FV_PLUGIN_ABSOLUTE_PATH . 'assets/js/scripts.js',
		deps:   array( 'jquery' ),
		ver:    FV_PLUGIN_VERSION
	);

	// Pass plugin data script.js
	wp_localize_script(
		handle:      'script-js',
		object_name: 'plugin_ajax_object',
		l10n:        array(
			'ajax_url'                       => admin_url( 'admin-ajax.php' ),
			'get_all_active_plugins_js'      => json_encode( fv_get_active_plugins_slugs()  ),
			'get_all_inactive_plugins_js'    => json_encode( fv_get_inactive_plugins_slugs() ),
			'get_all_active_themes_js'       => json_encode( fv_get_active_themes_slugs() ),
			'get_all_inactive_themes_js'     => json_encode( fv_get_inactive_themes_slugs() ),
			'fv_this_plugin_prefix'          => sanitize_title( get_plugin_data( FV_PLUGIN__FILE__ )['Name'] ),
			'fv_white_label_is_active'       => fv_should_white_label() ? 1 : 0,
			'fv_default_product_img_url'     => fv_get_default_product_image_url(),
			'fv_has_download_credits'        => fv_has_download_credits() ? 1 : 0,
			'fv_current_screen'              => get_current_screen()->base
		)
	);
}

/**
 * Gets the url to a default image to use when plugins or themes don't have an image.
 *
 * @return string
 */
function fv_get_default_product_image_url() {
	return fv_perhaps_white_label_default_product_image_url(
		'https://festingervault.com/wp-content/uploads/2020/12/unnamed-1.jpg'
	);
}

/**
 * Total available Credits > 0?
 *
 * @return int 1 when total credits > 0 otherwise 0
 */
function fv_has_download_credits() : bool {

	/* why? can't we skip this? */
	fv_run_remote_licensed_info_list( fv_get_plugins_data(), fv_get_themes_data() );

	$fv_api   = fv_run_remote_get_all_license_data();

	$total_credits  = $fv_api->license_1->license_data->plan_credit_available
					+ $fv_api->license_2->license_data->plan_credit_available;

	if ( $total_credits > 0 ) {
		return true; //
	}
	if ( $fv_api->license_1->license_data->license_type == 'onetime'
	||   $fv_api->license_2->license_data->license_type == 'onetime' ) {
		return true;
	}
	return false; // no more credits
}

/**
 * Upgrade buildin jQuery version to 3.4.1.
 *
 * @return void
 */
function fv_jquery_upgrade() : void {
	wp_deregister_script( // Deregisters the built-in version of jQuery
		handle: 'jquery'
	);
	wp_register_script(
		handle: 'jquery',
		src: FV_PLUGIN_ABSOLUTE_PATH.'assets/js/jquery-3.4.1.min.js',
		deps: false,
		ver: FV_PLUGIN_VERSION,
		args: true
	);
	wp_enqueue_script(
		handle: 'jquery'
	);
}

/**
 * Enqueue third party scripts used by this plugin.
 *
 * @return void
 */
function fv_enqueue_third_party_scripts() : void {

	wp_enqueue_script(
		handle: 'jquery-cookie',
		src:    'https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js',
		deps:   array( 'jquery' ),
		ver: '   1.4.1',
		args: true
	);
	wp_enqueue_script(
		handle: 'custom-alert-js',
		src:    FV_PLUGIN_ABSOLUTE_PATH . 'assets/js/jquery-confirm.min.js',
		deps:   array( 'jquery' ),
		ver:    FV_PLUGIN_VERSION
	);
	// wp_enqueue_script(
	// 	handle: 'pagi-js',
	// 	src:    'https://pagination.js.org/dist/2.6.0/pagination.js',
	// 	deps:   array( 'jquery' ),
	// 	ver:    FV_PLUGIN_VERSION
	// );
	wp_enqueue_script(
		handle: 'pagid-js',
		src:    FV_PLUGIN_ABSOLUTE_PATH . 'assets/js/bootstrap.bundle.min.js',
		deps:   array( 'jquery' ),
		ver:    FV_PLUGIN_VERSION
	);
	wp_enqueue_script(
		handle: 'dt-js',
		src:    FV_PLUGIN_ABSOLUTE_PATH . 'assets/js/jquery.dataTables.js',
		deps:   array( 'jquery' ),
		ver:    FV_PLUGIN_VERSION
	);
	wp_enqueue_script(
		handle: 'bootstrap-toggle',
		src:    'https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js',
		deps:   array( 'jquery' ),
		ver:    FV_PLUGIN_VERSION
	);
}

/**
 * Performs a data request query, mostly used for license data and history.
 *
 * @param array $query_args $args to add to the query
 * @return array|WP_Error query response.
 */
function fv_run_remote_request_data( array $query_args ): array|WP_Error {

	$query_base_url = FV_REST_API_URL . 'request-data';
	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );

	return fv_run_remote_query( $query );
}

/**
 * Calls the FV API to activate a license.
 *
 * @param string $key    License key.
 * @param string $domain Domain id.
 * @return stdClass|WP_Error Json decoded api call result.
 */
function fv_run_remote_activate_license( string $license_key ) : stdClass|WP_Error {

	if ( empty( $license_key ) ) {
		$fv_api              = new stdClass;
		$fv_api->result      = 'failed';
		$fv_api->license_key = $license_key;
		$fv_api->msg         = 'No license to activate.';
		$fv_api->ld_tm       = time();
		return $fv_api;
	}

	$query_base_url = FV_REST_API_URL . 'license-activation';
	$query_args     = array(
		'license_key'  => $license_key,
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'activation',
		'license_v'    => FV_PLUGIN_VERSION,
	 );

	$query           = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response        = fv_run_remote_query( $query );

	return json_decode( wp_remote_retrieve_body( $response ) );
}

/**
 * Calls the FV API to deactivate a license.
 *
 * @param string $key     License key.
 * @param string $domain  Domain id.
 * @return stdClass|WP_Error Json decoded api call result.
 */
function fv_run_remote_deactivate_license( string $key, string $domain ) : stdClass|WP_Error {

	if ( empty( $key ) || empty( $domain ) ) {
		return false;
	}

	$query_base_url = FV_REST_API_URL . 'license-deactivation';
	$query_args     = array(
		'license_key'  => $key,
		'license_d'    => $domain,
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'deactivation',
		'license_v'    => FV_PLUGIN_VERSION,
	);
	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );

	return json_decode( wp_remote_retrieve_body(
		fv_run_remote_query( $query )
	 ) );
}

/**
 * Informs the api about an installed theme or plugin update.
 *
 * @param string $slug Theme or plugin slug.
 * @param string $verion Theme or plugin version.
 * @return array|WP_Error Unaltered output of wp_remote_post() call.
 */
function fv_run_remote_update_request_load( string $slug, string $version ) : array|WP_Error {

	$query_base_url = FV_REST_API_URL . 'update-request-load';
	$query_args     = array(
		'license_key'          => fv_get_license_key(),
		'license_key_2'        => fv_get_license_key_2(),
		'license_d'            => fv_get_license_domain_id(),
		'license_d_2'          => fv_get_license_domain_id_2(),
		'plugin_theme_slug'    => $slug,
		'plugin_theme_version' => $version,
		'license_pp'           => $_SERVER['REMOTE_ADDR'],
		'license_host'         => $_SERVER['HTTP_HOST'],
		'license_mode'         => 'update_request_load',
		'license_v'            => FV_PLUGIN_VERSION,
	);
	$query = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );

	return fv_run_remote_query( $query );
}

/**
 * Activates the plugin.
 *
 * @return void
 */
function fv_do_license_activation_form() : void {


	$fv_api = fv_run_remote_activate_license( license_key: $_POST['licenseKeyInput'] );

	// Save license in settings.
	fv_save_license( array(
		'license-key' => $fv_api->l_dat,
		'domain-id'   => $fv_api->data_security_dom,
		'_ls_d_sf'    => $fv_api->ld_dat
	));

	// why request data if the data isn't used?
	fv_run_remote_request_data( array(
		'ld_tm'    => $fv_api->ld_tm,
		'ld_type'  => 'license_activation',
		'l_dat'    => $fv_api->l_dat,
		'ld_dat'   => $_SERVER['HTTP_HOST'],
		'rm_ip'    => $_SERVER['REMOTE_ADDR'],
		'status'   => $fv_api->result,
		'req_time' => time(),
		'res'      => '1'
	));

	// print result for js to process.
	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_activate_license_form_submit',        'fv_do_license_activation_form' );
add_action( 'wp_ajax_nopriv_fv_activate_license_form_submit', 'fv_do_license_activation_form' );

/**
 * Deactivate plugin.
 *
 * @return void
 */
function fv_do_license_deactivation_form(): void {

	$fv_api = fv_run_remote_deactivate_license( $_POST['license_key'], $_POST['license_d'] );

	// why request this data if the data isn't used?
	$query_args = array(
		'ld_tm'    => $fv_api->ld_tm,             // timestamp.
		'ld_type'  => 'deactivation',
		'l_dat'    => $fv_api->license_key,
		'ld_dat'   => $_SERVER['HTTP_HOST'],
		'rm_ip'    => $_SERVER['REMOTE_ADDR'],
		'req_time' => time(),
	);
	if ( 'success' === $fv_api->result ) {
		fv_forget_license_by_key( $_POST['license_key'] );
		$query_args['status'] = $fv_api->result;
		$query_args['res']    = '1';
	} else {
		$query_args['status'] = $fv_api->msg;
		$query_args['res']    = '0';
	}
	fv_run_remote_request_data( $query_args );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_deactivate_license_form_submit',          'fv_do_license_deactivation_form' );
add_action( 'wp_ajax_nopriv_fv_deactivate_license_form_submit',   'fv_do_license_deactivation_form' );

/**
 * Handles Search requests in the Vault page.
 *
 * @return void
 */
function fv_do_search_vault_for_plugins_and_themes_form() {

	// $starttime              = microtime( true );

	$fv_cache_status        = 0;

	// $fv_check_cache         = false;
	// if ( 1 === fv_get_remote_domain_caching() ) {
	// 	$fv_check_cache = get_transient( '__fv_ca_dt_aa' );
	// 	if ( false !== $fv_check_cache ) {
	// 		$fv_cache_status = 1;
	// 	}
	// }

	$searchedValue = isset( $_POST['ajax_search'] ) ? $_POST['ajax_search'] : '';
	$pagenmber     = isset( $_POST['page'] )        ? $_POST['page']        : '1';

	$query_base_url = FV_REST_API_URL . 'search-data';
	$query_args     = array(
		'license_key'          => fv_get_license_key(),
		'license_key_2'        => fv_get_license_key_2(),
		'datasrc'	           => $searchedValue,
		'page'	               => $pagenmber,
		'license_d'            => '',
		'license_pp'           => $_SERVER['REMOTE_ADDR'],
		'license_host'         => $_SERVER['HTTP_HOST'],
		'license_mode'         => 'search_query',
		'license_cache_status' => $fv_cache_status,
		'license_v'            => FV_PLUGIN_VERSION,
		'queryd'               => 'wordpress',
	);
	$query        = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response     = fv_run_remote_query( $query );
	$license_data = wp_remote_retrieve_body( $response );

	echo $license_data;


	// $decoded_license_data = json_decode($license_data);
	// if ( $fv_cache_status_server == 1 ) {
	// 	if ( is_array( $decoded_license_data ) && ! empty( $license_data ) ) {
	// 		if ( count( $decoded_license_data ) > 6000 ) {
	// 			delete_transient( '__fv_ca_dt_aa' );
	// 			set_transient( '__fv_ca_dt_aa', $license_data );
	// 		}
	// 	}
	// }
	// $searchedValueContent_type = isset( $searchedValue['content_type'] ) ? $searchedValue['content_type'] : '';
	// if ( FALSE !== $fv_check_cache ) {
	// 	$fv_check_cache2 = json_decode( $fv_check_cache );
	// }
	// $fv_con_tp = '';
	// if ( 1 === $fv_cache_status_server ) {
	// 	if ( FALSE != $fv_check_cache ) {
	// 		$fv_cache_status = 1;
	// 	}
	// }
	// if ( 'mylist' === $searchedValueContent_type ) {
	// 	echo $license_data;
	// } else {
	// 	if ( 1 === $fv_cache_status_server ) {
	// 		$fv_check_cache   = json_decode( $fv_check_cache );
	// 	} else {
	// 		$fv_check_cache   = json_decode( $license_data );
	// 		$fv_check_cache2  = $fv_check_cache;
	// 		$fv_cache_status  = 1;
	// 	}
	// 	if ( 1 === $fv_cache_status ) {
	// 		$searchedValuefilter_type = isset( $searchedValue['filter_type'] ) ? $searchedValue['filter_type'] : '';
	// 		$searchedFiltertype       = $searchedValuefilter_type;
	// 		if ( ! empty( $searchedFiltertype ) && $searchedFiltertype != 'all' ) {
	// 			$arrayOfObjects = $fv_check_cache;
	// 			$fv_check_cache = array_filter(
	// 				$arrayOfObjects,
	// 				function( $e ) use ( $searchedFiltertype ) {
	// 					if ( $e->type_slug == $searchedFiltertype ) {
	// 						return $e;
	// 					}
	// 				}
	// 			);
	// 			$fv_check_cache = array_values($fv_check_cache);
	// 		}
	// 		$searchedFilterCategoty = isset( $searchedValue['filter_category'] ) ? $searchedValue['filter_category'] : '';
	// 		if ( ! empty( $searchedFilterCategoty ) && $searchedFilterCategoty != 'all' ) {
	// 			$arrayOfObjects = $fv_check_cache;
	// 			$fv_check_cache = array_filter(
	// 				$arrayOfObjects,
	// 				function( $e ) use ( $searchedFilterCategoty ) {
	// 					if ( $e->category_slug == $searchedFilterCategoty ) {
	// 						return $e;
	// 					}
	// 				}
	// 			);
	// 			$fv_check_cache = array_values($fv_check_cache);
	// 		}
	// 		if ( empty( $searchedFiltertype ) && empty( $searchedFilterCategoty ) ) {
	// 			$fv_check_cache = $fv_check_cache2;
	// 		}
	// 		if( empty( $searchedValue ) ) {
	// 			echo json_encode( $fv_check_cache );
	// 		} else {
	// 			if ( $searchedValueContent_type == 'popular' ) {
	// 				$fv_con_tp = 'hits';
	// 			}
	// 			if ( $searchedValueContent_type == 'recent' ) {
	// 				$fv_con_tp = 'modified';
	// 			}
	// 			if ( $searchedValueContent_type == 'featured' ) {
	// 				$fv_con_tp = 'featured';
	// 			}
	// 			if ( ! empty( $searchedValueContent_type ) ) {
	// 				$fv_column_arr = array_column( $fv_check_cache, $fv_con_tp );
	// 				array_multisort( $fv_column_arr, SORT_DESC, $fv_check_cache );
	// 			}
	// 			$searchedFilterCategoty = isset( $searchedValue['filter_category'] ) ? $searchedValue['filter_category'] : '';
	// 			$searchedValue          = isset( $searchedValue['search_data'] )     ? $searchedValue['search_data']     : '';
	// 			$arrayOfObjects         = $fv_check_cache;
	// 			$neededObject           = array_filter(
	// 				$arrayOfObjects,
	// 				function( $e ) use ( $searchedValue ) {
	// 					if ( preg_match( "/{$searchedValue}/i", $e->title ) ) {
	// 						return $e;
	// 					}
	// 				}
	// 			);
	// 			echo json_encode( array_values( $neededObject ) );
	// 		}
	// 	} else {
	// 		echo $license_data;
	// 	}
	// }
	// $endtime = microtime(true);
	// $duration = $endtime - $starttime; //calculates total time taken
	// update_option( '__fc_chk_dur_set', $duration );
}
add_action( 'wp_ajax_fv_search_ajax_data',        'fv_do_search_vault_for_plugins_and_themes_form' );
add_action( 'wp_ajax_nopriv_fv_search_ajax_data', 'fv_do_search_vault_for_plugins_and_themes_form' );

/**
 * Gets all the license data from remote.
 *
 * @return stdClass|WP_Error json decoded result of api-call.
 */
function get_all_data_return_fresh(): stdClass|WP_Error {

	/* why? can't we skip this? */
	fv_run_remote_licensed_info_list( fv_get_plugins_data(), fv_get_themes_data() );

	return fv_run_remote_get_all_license_data();
}

/**
 * Get domain_caching from FV API.
 *
 * @return integer
 */
function fv_get_remote_domain_caching() : int {

	fv_run_remote_licensed_info_list( fv_get_plugins_data(), fv_get_themes_data() );

	$fv_api = fv_run_remote_get_all_license_data();

	return $fv_api->domain_caching;
}

/**
 * Get all license data by calling the FV Rest API.
 *
 * @return stdClass|WP_Error The json decoded body of the api-call.
 */
function fv_run_remote_get_all_license_data() : null|stdClass|WP_Error {

	$query_base_url = FV_REST_API_URL . 'get-all-license-data';
	$query_args     = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'get_all_license_data',
		'license_v'     => FV_PLUGIN_VERSION,
	);
	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );

	return json_decode( wp_remote_retrieve_body(
		fv_run_remote_query( $query ) )
	);
}

/**
 * Gets all license data and renders the Activation Page
 *
 * @return void
 */
function fv_render_activation_page() {

	$fv_api = fv_run_remote_get_all_license_data();

	fv_forget_not_found_licenses( $fv_api );
	fv_perhaps_forget_white_label_settings( $fv_api );

	// render activation page.
	include( FV_PLUGIN_DIR . '/sections/fv_activation.php' );

	fv_run_remote_licensed_info_list( fv_get_plugins_data(), fv_get_themes_data() );
}

/**
 * Forgets licenses that are not found.
 *
 * @param stdClass $fv_api
 * @return void
 */
function fv_forget_not_found_licenses( stdClass $fv_api ) : void {
	/* License 1 not found */
	if (  fv_license_not_found( $fv_api->validation->license_1 ) ) {
		fv_forget_license_by_key( $fv_api->license_1->license_data->license_key );
	}
	/* License 2 not found */
	if (  fv_license_not_found( $fv_api->validation->license_2 ) ) {
		fv_forget_license_by_key( $fv_api->license_2->license_data->license_key );
	}
}

/**
 * Forgets white label settings if none of the licenses supports them.
 *
 * @param stdClass $fv_api
 * @return void
 */
function fv_perhaps_forget_white_label_settings( stdClass $fv_api ) : void {
	if ( ! fv_license_allows_white_label( $fv_api ) ) {
		fv_forget_white_label_settings();
	}
}

/**
 * Does any active license allowes white labeling?
 *
 * @param stdClass $fv_api
 * @return boolean
 */
function fv_license_allows_white_label( stdClass $fv_api ) : bool {
	return $fv_api->license_1->options->white_label === 'yes'
		|| $fv_api->license_2->options->white_label === 'yes';
}

/**
 * Clear all white labeling settings.
 *
 * @return void
 */
function fv_forget_white_label_settings(): void  {

	$options = fv_get_white_label_option_keys('all');

	foreach ( $options as $option ) {
		fv_delete_option( $option );
	}
}

/**
 * Gets themes data and renders theme updates page.
 *
 * @return void
 */
function fv_render_theme_updates_page(): void {
	/**
	 * If no licenses are activated, build page with initial data.
	 */
	if ( ! fv_has_any_license() ) {
		$fv_api = fv_updates_empty_api_result();
		include( FV_PLUGIN_DIR . '/sections/fv_theme_updates.php' );
	}

	$req_themes       = fv_get_installed_themes_for_api_request();
	$fv_api           = fv_get_remote_themes( $req_themes );
	$fv_themes        = array();
	$fv_theme_updates = array();

	if ( ! isset( $fv_api->result )
	||   ! fv_api_call_failed( $fv_api->result ) ) {
		/**
		 * Some pre-processing so rendering is easier.
		 */
		if ( isset( $fv_api->themes ) ) {
			$fv_themes           = (array) $fv_api->themes;
			$fv_themes           = fv_remove_duplicate_themes( $fv_themes );
			$fv_themes           = fv_add_current_themes_data( $fv_themes );
			$fv_theme_updates    = fv_get_theme_updates( $fv_themes );
		}
	}

	include( FV_PLUGIN_DIR . '/sections/fv_theme_updates.php' );
}

/**
 * Collect data and render plugin Updates page.
 *
 * @return void
 */
function fv_render_plugin_updates_page(): void {

	$fv_plugins        = array();
	$fv_plugin_updates = array();

	/**
	 * If no licenses are activated, build page with initial data.
	 */
	if ( ! fv_has_any_license() ) {
		$fv_api = fv_updates_empty_api_result();
		include( FV_PLUGIN_DIR . '/sections/fv_plugin_updates.php' );
	}

	$req_plugins = fv_get_installed_plugins_for_api_request();

	/**
	 * Split the requested plugins array and Iterate the api call,
	 * when number of plugins would make the query too large.
	 */
	foreach ( fv_array_equal_split( array: $req_plugins, chunkSize: 75 ) as $p ) {

		$fv_api = fv_get_remote_plugins( $p );

		if ( isset( $fv_api->result )
		&&   fv_api_call_failed( $fv_api->result ) ) {
			/**
			 * Since either the license failed or the domain is blocked
			 * no further iterations are needed.
			 */
			break;
		}

		$fv_plugins = array_merge( $fv_plugins, (array) $fv_api->plugins );
	}
	/**
	 * Some pre-processing so rendering is easier.
	 */
	if ( ! empty( $fv_plugins ) ) {
		$fv_plugins           = fv_remove_duplicate_plugins( $fv_plugins );
		$fv_plugins           = fv_add_current_plugins_data( $fv_plugins );
		$fv_plugin_updates    = fv_get_plugin_updates( $fv_plugins );
	}

	/**
	 * Render the plugin update page.
	 */
	include( FV_PLUGIN_DIR . '/sections/fv_plugin_updates.php' );
}

/**
 * Returns an minimal api result object
 * with properties that are
 * used by the themes and plugins update pages.
 *
 * @return stdClass
 */
function fv_updates_empty_api_result() : stdClass {
	$fv_api                      = new stdClass();
	$fv_api->result              = 'no-license';
	$fv_api->msg                 = '';
	$fv_api->manual_force_update = 'No';
	$fv_api->plugins             = array();
	$fv_api->themes              = array();
	return $fv_api;
}

/**
 * Call FV api for a list of matching plugins from FV.
 *
 * @param array $plugins
 * @param string $context 'get' for building pages and 'update' when selecting for force updates. Default: 'get'.
 * @return stdClass|WP_Error|false json decoded result of api call.
 */
function fv_get_remote_plugins( array $plugins, string $context = 'get' ) : stdClass|WP_Error|false {
	return fv_get_remote_matches( plugins: $plugins, context: $context );
}

/**
 * Call FV api for a list of matching themes from FV.
 *
 * @param array $themes
 * @param string $context 'get' for building pages and 'update' when selecting for force updates. Default: 'get'.
 * @return stdClass|WP_Error|false json decoded result of api call.
 */
function fv_get_remote_themes( array $themes, string $context = 'get' ) : stdClass|WP_Error|false {
	return fv_get_remote_matches( themes: $themes, context: $context );
}

/**
 * Call FV api for a list of matching plugins and/or themes from FV.
 *
 * @param array $plugins
 * @param array $themes
 * @param string $context 'get' for building pages and 'update' when selecting for force updates. Default: 'get'.
 * @return stdClass|WP_Error|false json decoded result of api call.
 */
function fv_get_remote_matches( array $plugins = array(), array $themes = array(), string $context = 'get' ) : stdClass|WP_Error|false {

	if ( ! is_array( $plugins ) || ! is_array( $themes ) ) {
		return false;
	}

	/**
	 * If no license was activated, no need to go remote.
	 */
	if ( ! fv_has_any_license() ) {
		return fv_updates_empty_api_result();
	}

	// build Query
	$query_base_url = FV_REST_API_URL . 'plugin-theme-updater';
	$query_args     = array(
		'license_key'     => fv_get_license_key(),
		'license_key_2'   => fv_get_license_key_2(),
		'license_d'       => fv_get_license_domain_id(),
		'license_d_2'     => fv_get_license_domain_id_2(),
		'all_plugin_list' => $plugins,
		'all_theme_list'  => $themes,
		'license_pp'      => $_SERVER['REMOTE_ADDR'],
		'license_host'    => $_SERVER['HTTP_HOST'],
		'license_mode'    => 'get_plugins_and_themes_matched_by_vault',
		'license_v'       => FV_PLUGIN_VERSION,
	);
	if ( 'update' === $context ) {
		$query_args['license_mode']    = 'up_dl_plugs_thms';
		$query_args['loadNotAll']      = 'yes';
	}
	$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );

	return json_decode( wp_remote_retrieve_body( fv_run_remote_query( $query ) ) );
}

/**
 * Removes all non-digits and non-dots from a version.
 *
 * NOTE: for comparison purposes the below pattern leads to incorrect results.
 *       version_compare() does a better job for that and this pattern makes
 *
 *       A comparison with the version from this plugin fails
 *       on a version like '4.3.2RC1'
 *       just removing the RC part would make the version "4.3.21"
 *       and that would be a higher version then f.i. '4.3.3'
 *
 *       version_compare() states that it first replaces _, - and + with a dot .
 *       and then and also inserts dots . before and after any non number and compares each section.
 *
 *       For version comparison, better use php's version_compare() without first using this function.
 *
 * @param string $slug
 * @return string
 */
function fv_esc_version( string $slug ): string {
	// just turn this functionality off for now.
	return esc_attr( $slug );

	// this pattern makes compare_version fail, so better not use.
	$version = preg_replace( "/[^0-9.]/", "", $slug );
	return $version;
}

/**
 * Renders the history (download log) page.
 *
 * @return void
 */
function fv_render_history_page(): void {

	$fv_api_downloads = fv_get_remote_history();

	include( FV_PLUGIN_DIR . '/sections/fv_history.php' );
}

/**
 * Gets the download history from the FV API.
 *
 * @return arary|WP_Error|null Array with (stdClass) downloads.
 */
function fv_get_remote_history(): array|WP_Error|null {

	if ( ! fv_has_any_license() ) {
		return null;
	}

	$query_base_url = FV_REST_API_URL . 'get-license-history';
	$query_args     = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'get_history',
		'license_v'     => FV_PLUGIN_VERSION,
	);
	$query = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );

	return json_decode( wp_remote_retrieve_body(
		fv_run_remote_query( $query )
	));
}

/**
 * Handles license refill form submissions.
 *
 * @return void
 */
function fv_do_license_refill_form(): void {

	$query_base_url = FV_REST_API_URL . 'refill-license';
	$query_args     = array(
		'license_key'  => $_POST['license_key'],
		'refill_key'   => $_POST['refill_key'],
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'refill_history',
		'license_v'    => FV_PLUGIN_VERSION,
	);
	$query       = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response    = fv_run_remote_query( $query );
	$refill_data = json_decode( wp_remote_retrieve_body( $response ) );

	$query_args = array(
		'ld_tm'    => $refill_data->ld_tm,
		'ld_type'  => 'refill_history',
		'l_dat'    => $_POST['refill_key'],
		'ld_dat'   => $_SERVER['HTTP_HOST'],
		'rm_ip'    => $_SERVER['REMOTE_ADDR'],
		'status'   => $refill_data->result,
		'req_time' => time(),
		'res'      => '1'
	);
	if ( $refill_data->result !== 'success' ) {
		$query_args['status'] = $refill_data->msg;
		$query_args['res']    = '0';
	}
	fv_run_remote_request_data( $query_args );

	echo json_encode( $refill_data );
}
add_action( 'wp_ajax_fv_refill_license_form_submit',        'fv_do_license_refill_form' );
add_action( 'wp_ajax_nopriv_fv_refill_license_form_submit', 'fv_do_license_refill_form' );

/**
 * Gets fv (shortened) slugs of all plugins.
 *
 * @return array Fv (shortened) slugs of all plugins.
 */
function fv_get_plugins_slugs(): array {
	return fv_get_plugins_data( slugs_only: true );
}

/**
 * Gets data of all plugins.
 *
 * @param bool $slug_only true if only slugs are needed.
 * @return array Array of fv_slugs or fv_slugs and names of plugins.
 */
function fv_get_plugins_data( bool $slugs_only = false ): array {
	$plugins        = fv_get_plugins();
	$plugins_data   = array();
	foreach( $plugins as $basename => $plugin ) {
		$data = fv_get_slug( $basename );
		if ( ! $slugs_only ) {
			$data = array(
				'name' => urlencode( $plugin['Name'] ),
				'slug' => $data,
			);
		}
		$plugins_data[] = $data;
	}
	return $plugins_data;
}

/**
 * Gets fv (shortened) slugs of active plugins.
 *
 * @return array Fv (shortened) slugs of active plugins.
 */
function fv_get_active_plugins_slugs(): array {
	return fv_get_active_plugins_data( slugs_only: true );
}

/**
 * Gets fv (shortened) slugs and name of active plugins.
 *
 * @param bool $slug_only If true only slugs are returned.
 * @return array Fv (shortened) slugs and names of active plugins.
 */
function fv_get_active_plugins_data( bool $slugs_only = false ): array {

	$active_plugins_basenames = get_option( 'active_plugins', default_value: array() );
	if ( $slugs_only && ! $active_plugins_basenames ) {
		return array();
	}

	$plugins      = fv_get_plugins();
	$plugins_data = array();
	foreach( $active_plugins_basenames as $basename ) {

		// skip if plugin doesn't exist ( should be a wp-org plugin)
		if ( ! isset( $plugins[ $basename ] ) ) {
			continue;
		}
		$data = fv_get_slug( $basename );

		if ( ! $slugs_only ) {
			$data = array(
				'name' => isset( $plugins[ $basename ]['Name'] ) ? urlencode( $plugins[ $basename ]['Name'] ) : '',
				'slug' => $data,
			);
		}
		$plugins_data[] = $data;
	}

	return $plugins_data;
}

/**
 * Gets fv (shortened) slugs of inactive plugins.
 *
 * @return array Fv (shortened) slugs of inactive plugins.
 */
function fv_get_inactive_plugins_slugs(): array {
	return fv_get_inactive_plugins_data( slugs_only: true );
}

/**
 * Gets fv (shortened) slugs and name of inactive plugins.
 *
 * @param bool $slug_only If true only slugs are returned.
 * @return array Fv (shortened) slugs and names of inactive plugins.
 */
function fv_get_inactive_plugins_data( bool $slugs_only = false ): array {

	$plugins                  = fv_get_plugins();
	$active_plugins_basenames = get_option( 'active_plugins', default_value: array() );

	if ( ! $active_plugins_basenames ) {
		if ( $slugs_only ) {
			return array_keys( $plugins );
		}
	}

	$plugins_data = array();
	foreach( $plugins as $basename => $plugin ) {

		if ( in_array( $basename, $active_plugins_basenames ) ) {
			continue;
		}

		$data = fv_get_slug( $basename );
		if ( ! $slugs_only ) {
			$data = array(
				'name' => isset( $plugin['Name'] ) ? urlencode( $plugin['Name'] ) : '',
				'slug' => $data,
			);
		}
		$plugins_data[] = $data;
	}
	return $plugins_data;
}

/**
 * Gets stylesheet slugs of all themes.
 *
 * @return array Stylesheet slugs of all themes.
 */
function fv_get_themes_slugs(): array {
	return array_keys( fv_get_themes() );
}

/**
 * Gets stylesheet slugs and names of all themes.
 *
 * @param bool $slug_only If true only stylesheet slugs are returned.
 * @return array Stylesheet slugs and names of all themes.
 */
function fv_get_themes_data( bool $slug_only = false ): array {

	if ( $slug_only ) {
		return fv_get_themes_slugs();
	}

	$themes      = fv_get_themes();
	$themes_data = array();
	foreach( $themes as $stylesheet => $theme ) {
		$themes_data[] = array(
			'name' => urlencode( $theme['Name'] ),
			'slug' => $stylesheet,
		);
	}
	return $themes_data;
}

/**
 * Gets stylesheet slugs of active themes.
 *
 * @return array Stylesheet slugs of active themes.
 */
function fv_get_active_themes_slugs(): array {
	return fv_get_active_themes_data( slug_only: true );
}

/**
 * Gets stylesheet slugs and names of active themes.
 *
 * @param bool $slug_only If true only stylesheet slugs are returned.
 * @return array Stylesheet slugs and names of active themes.
 */
function fv_get_active_themes_data( bool $slug_only = false ): array {

	$active_theme = wp_get_theme();
	if ( ! $active_theme->exists() ) {
		return array();
	}
	if ( $slug_only ) {
		return array( $active_theme->stylesheet );
	}
	return array( array(
		'name' => urlencode( $active_theme->Name ),
		'slug' => $active_theme->stylesheet,
	));
}

/**
 * Gets stylesheet slugs of inactive themes.
 *
 * @return array Stylesheet slugs of inactive themes.
 */
function fv_get_inactive_themes_slugs() {
	return fv_get_inactive_themes_data( slug_only: true );
}

/**
 * Gets stylesheet slugs and names of inactive themes.
 *
 * @param bool $slug_only If true only stylesheet slugs are returned.
 * @return array Stylesheet slugs and names of inactive themes.
 */
function fv_get_inactive_themes_data( bool $slug_only = false ): array {

	$themes = fv_get_themes();
	unset( $themes[ fv_get_active_theme_stylesheet() ] );

	if ( $slug_only ) {
		return array_keys( $themes );
	}

	$themes_data = array();
	foreach ( $themes as $stylesheet => $theme ) {
		$themes_data[] = array(
			'name' => urlencode( $theme->Name ),
			'slug' => $stylesheet,
		);
	}
	return $themes_data;
}

/**
 * Gets the stylesheet slug of the active theme.
 *
 * @return string|false Stylesheet slug of active theme, or false if no active theme exists.
 */
function fv_get_active_theme_stylesheet(): string|false {
	return wp_get_theme()->exists() ? wp_get_theme()->stylesheet : false;
}

/**
 * Returns the content-length of a file from the files header.
 *
 * @param string $file_url
 * @param boolean $format_size
 * @return int
 */
function fv_curl_get_remote_file_size( string $file_url, bool $format_size = true ): int {

	$head = array_change_key_case( get_headers( $file_url, 1 ), CASE_LOWER );

	// content-length of download (in bytes ), read from Content-Length: field
	$clen = isset( $head['content-length'] ) ? $head['content-length'] : 0;

	// cannot retrieve file size, return “-1”
	if ( ! $clen ) {
		return 0;
	}

	// right now $format_size doesn't seem to have impact on the return value.
	if ( ! $format_size ) {
		// return size in bytes
		return $clen;
	}

	return $clen;
}

/**
 * Download and write a file to a destination, using curl.
 *
 * @param string $url URL of the file that should be downloaded.
 * @param string $file Fully qualified destination filename.
 * @return integer Filesize.
 */
function fv_curl_download( string $url, string $to_file ) : int {

	$file_size = fv_curl_get_remote_file_size( $url );

	// Initialize the cURL session
	$curl_handle = curl_init( $url );

	// Save file into file location
	$destination = $to_file;

	// Open file
	$file_handle = fopen( $destination, 'wb' );

	// It set an option for a cURL transfer
	curl_setopt( $curl_handle, CURLOPT_FILE, $file_handle );
	curl_setopt( $curl_handle, CURLOPT_HEADER, 0 );

	// Perform a cURL session
	curl_exec( $curl_handle );

	// Closes a cURL session and frees all resources
	curl_close( $curl_handle );

	// Close file
	fclose( $file_handle );

	return $file_size;
}

/**
 * Move an existing file to an existing destination.
 *
 * @param string $src Fully qualified name of existing file.
 * @param string $dest Fully qualified name of existing directory.
 * @return bool  Returns true on success or false on failure.
 */
function fv_move_file( string $file, string $to_file ): bool {
	if ( $file === $to_file ) {
		return file_exists( $file );
	}
	if ( false === copy( $file, $to_file ) ) {
		return false;
	}
	return fv_delete_file( $file );
}

/**
 * Move an existing file to an existing destination.
 *
 * @param string $file Fully qualified name of existing file.
 * @return bool  Returns true on success or false on failure.
 */
function fv_delete_file( string $file ): bool {
	if ( ! file_exists( $file ) ) {
		return true;
	}
	return unlink( $file );
}

/**
 * Download and write a file to a destination, using curl.
 *
 * @param string $url URL of the file that should be downloaded.
 * @param string $dest_dir Destination directory.
 * @param string $dest_file Destination filename.
 * @return integer Filesize.
 */
function fv_download( string $url, string $to_file ) : int {

	$tmp_file = download_url( $url, $timeout = 300 );

	if ( is_wp_error( $tmp_file ) ) {
		return fv_curl_download( url: $url, to_file: $to_file );
	}

	fv_move_file( file: $tmp_file, to_file: $to_file );

	return wp_filesize( $to_file );
}

/**
 * Removes theme backups by scanning the backups directory
 * for directories that contain the stylesheet.
 *
 * @param string $stylesheet The themes stylesheet slug.
 * @return void
 */
function fv_delete_theme_backup( string $stylesheet ) : void {
	foreach( scandir( fv_get_upload_dir('themes-backups') ) as $dir ) {
		if ( $dir === $stylesheet ) {
			fv_delete_directory( fv_get_upload_dir('themes-backups') . $dir );
		}
	}
}

/**
 * Copies an installed theme to the theme backups directory.
 *
 * @param string $stylesheet Stylesheet slug of a theme in the themes directory.
 * @return boolean True on succes, otherwise false.
 */
function fv_backup_theme( string $stylesheet ) : bool {

	$theme_dir  = trailingslashit( get_theme_root() ) . $stylesheet;
	$backup_dir = fv_get_upload_dir('themes-backups')   . $stylesheet;

	return fv_backup_to( dir: $theme_dir, to: $backup_dir );
}

/**
 * Downloads and installs a single theme update.
 *
 * @param string $stylesheet   Theme styleheet name, or theme dir slug.
 * @param string $download_url Download url for plugin zip-file.
 * @return integer Downloadsize (of zip-file) in bytes.
 */
function fv_install_remote_theme( string $stylesheet, string $download_url ) : int {

	// note: pathinfo(...)['filename'] just removes any extensions from the stylesheet.
	$zip_file = fv_get_upload_dir('themes') . pathinfo( $stylesheet )['filename'] . '.zip';

	$download_size = fv_download(
		url:     $download_url,
		to_file: $zip_file
	);

	if ( ! file_exists( $zip_file ) ) {
		return 0;
	}

	fv_delete_theme_backup( $stylesheet );
	fv_backup_theme( $stylesheet );
	fv_unzip_file_to( file: $zip_file, to: get_theme_root() );
	fv_delete_file( $zip_file );

	return $download_size;
}

/**
 * Downloads and installs a single plugin update.
 *
 * @param string $basename     Plugin basename or plugin dir slug.
 * @param string $download_url Download url for plugin zip-file.
 * @return integer Downloadsize (of zip-file) in bytes.
 */
function fv_install_remote_plugin( string $basename, string $download_url ) : int {

	$slug     = fv_get_plugin_dir_slug( $basename );
	$zip_file = fv_get_upload_dir('plugins') . pathinfo( $slug )['filename'] . '.zip';

	$download_size = fv_download(
		url:     $download_url,
		to_file: $zip_file
	);

	if ( ! file_exists( $zip_file ) ) {
		return 0;
	}

	fv_delete_plugin_backup( $slug );
	fv_backup_plugin( $slug );
	fv_unzip_file_to( file: $zip_file, to: WP_PLUGIN_DIR );
	fv_delete_file( $zip_file );

	return $download_size;
}

/**
 * Scan directory and sort files by mutation time.
 *
 * @param string $directory Directory path.
 * @param int $order Order parameter of scandir(). SCANDIR_SORT_ASCENDING, SCANDIR_SORT_DESCENDING, SCANDIR_SORT_NONE. Default SCANDIR_SORT_ASCENDING.
 * @return array|false
 */
function fv_scandir( string $dir, int $order = SCANDIR_SORT_ASCENDING ): array|false {
	$allowed_order = array( SCANDIR_SORT_ASCENDING, SCANDIR_SORT_DESCENDING, SCANDIR_SORT_NONE );

	if ( ! in_array( $order, $allowed_order  ) ) {
		$order = SCANDIR_SORT_ASCENDING;
	}

	if ( ! is_dir( $dir ) ) {
		return array();
	}

	$files = array();
    foreach ( scandir( $dir, $order ) as $file ) {

		if ( str_starts_with( $file, '.' ) ) {
			// skip hidden files and '.' and '..'
			continue;
		}

        $files[ $file ] = filemtime( $dir . '/' . $file );
    }

	// Sort files numeric on filemtime().
	switch ( $order ) {
		case SCANDIR_SORT_ASCENDING:
			asort( $files, SORT_NUMERIC);
			break;

		case SCANDIR_SORT_DESCENDING:
			arsort( $files, SORT_NUMERIC);
			break;

		default:
			// Don't sort
			break;
	}
	// format unix timestamps = date( "d-m-Y H:i:s", $timestamp );

	// return array of file-names.
    return array_keys( $files );
}

/**
 * Removes plugin backups by scanning the backups directory
 * for directories that contain the dir from the basename.
 *
 * @param string $basename The plugins basename.
 * @return void
 */
function fv_delete_plugin_backup( string $basename ) : void {

	if ( empty( $basename ) ) {
		return;
	}

	$plugin_dir = fv_get_plugin_dir_slug( $basename );

	// Single file plugin doesn't contain directories.
	if ( empty( $plugin_dir) ) {
		$plugin_dir = $basename;
	}

	$backup_dir = fv_get_upload_dir('plugins-backups') . $plugin_dir;

	// delete multi-file plugin backup
	if ( is_dir( fv_get_upload_dir('plugins-backups') . $plugin_dir ) ) {
		fv_delete_directory( $backup_dir );
		return;
	}

	// delete single file plugin backup.
	if ( file_exists( $backup_dir ) ) {
		fv_delete_file( $backup_dir );
	}
}

/**
 * Copies an installed plugin to the plugin backups directory.
 *
 * @param string $basename Basename of a plugin in the plugin directory.
 * @return boolean True on succes, otherwise false.
 */
function fv_backup_plugin( string $basename ) : bool {

	$plugin_dir = trailingslashit( WP_PLUGIN_DIR )     . fv_get_plugin_dir_slug( $basename );
	$backup_dir = fv_get_upload_dir('plugins-backups') . fv_get_plugin_dir_slug( $basename );

	return fv_backup_to( dir: $plugin_dir, to: $backup_dir );
}

/**
 * Copies the content of a directory to another directory.
 *
 * @param string $dir Dir to backup.
 * @param string $to  Dir to copy the contents of $dir to.
 * @return boolean True on succes, otherwise false.
 */
function fv_backup_to( string $dir, string $to ) : bool {

	// nothing to backup.
	if ( ! file_exists( $dir ) ) {
		return true;
	}

	// dest not available, or not a dir.
	if ( ! is_dir( $dir )
	||     file_exists( $to ) ) {
		return false;
	}

	// copy $dir to destination.
	return fv_copy_recursive( $dir, $to );
}

/**
 * Auto update plugins and themes.
 *
 * @param string $should_update Optional. 'theme', 'plugin', or 'all'. Default 'all'.
 * @param array  $single_update_data {
 *     Optional. Data of a single plugin or theme. Default: array().
 *
 *     @type string $type    'theme' or 'plugin'.
 *     @type string $name    Optional. Name of the plugin/theme.
 *     @type string $slug    Plugin/theme slug.
 *     @type string $version Plugin/theme version.
 * }
 * @return void
 */
function fv_auto_update_download( $should_update = 'all', $single_update_data = array() ) {
	fv_do_bulk_update( $should_update, $single_update_data, auto_update_only: true );
}

/**
 * Auto update plugins and themes.
 *
 * @param string $should_update Optional. 'theme', 'plugin', or 'all'. Default 'all'.
 * @param array  $single_update_data {
 *     Optional. Data of a single plugin or theme. Default: array().
 *
 *     @type string $type    'theme' or 'plugin'.
 *     @type string $name    Optional. Name of the plugin/theme.
 *     @type string $slug    Plugin/theme slug.
 *     @type string $version Plugin/theme version.
 * }
 * @return void
 */
function fv_auto_update_download_instant( $should_update = 'all', $single_update_data = array() ) {
	fv_do_bulk_update( $should_update, $single_update_data, auto_update_only: false );
}

/**
 * Auto update plugins and themes.
 *
 * @param string $should_update Optional. 'theme', 'plugin', or 'all'. Default 'all'.
 * @param array  $single_update_data {
 *     Optional. Data of a single plugin or theme. Default: array().
 *
 *     @type string $type    'theme' or 'plugin'.
 *     @type string $name    Optional. Name of the plugin/theme.
 *     @type string $slug    Plugin/theme slug.
 *     @type string $version Plugin/theme version.
 * }
 * @param bool  $auto_update_only when true only those that have auto update toggle checked are considered, otherwise all plugins and themes.
 * @return void
 */
function fv_do_bulk_update( string $should_update = 'all', array $single_update_data = array(), bool $auto_update_only = true ): void {

	if ( ! fv_has_any_license() ) {
		return;
	}

	$should_update_themes = false;
	if ( 'all'  === $should_update
	||  'theme' === $should_update ) {
		$should_update_themes = true;
	}

	$should_update_plugins = false;
	if ( 'all'    === $should_update
	||  'plugin' === $should_update ) {
		$should_update_plugins = true;
	}

	// validate input
	$single_theme_update = false;
	$single_plugin_update = false;
	if ( ! empty ( $single_update_data ) ) {

		if ( empty( $single_update_data['type'] )
		||	 $single_update_data['type'] !== $should_update ) {
			return;
		}
		if ( empty( $single_update_data['slug'] )
		||   empty( $single_update_data['version'] ) ) {
			return;
		}

		switch ( $single_update_data['type'] ) {
			case 'plugin':
				$single_plugin_update = true;
			break;

			case 'theme':
				$single_theme_update = true;
				break;

			default:
				// invalid args.
				return;
				break;
		}
	}

	// Collect plugin_data for request
	$plugins = array();
	if ( $should_update_plugins ) {

		if ( $single_plugin_update ) {
			$plugins[] = array(
				'slug'    => $single_update_data['slug'] ,
				'version' => $single_update_data['version'] ,
				'dl_link' => ''
			);
		} else {
			if ( $auto_update_only ) {
				// only plugins that have autoload checked.
				$plugins = fv_get_auto_update_plugins_for_api_request();
			} else {
				// all plugins.
				$plugins = fv_get_installed_plugins_for_api_request();
			}
		}
	}

	// Collect theme_data for request
	$themes = array();
	if ( $should_update_themes ) {
		if ( $single_theme_update ) {
			$themes[] = array(
				'slug'    => $single_update_data['slug'] ,
				'version' => $single_update_data['version'] ,
				'dl_link' => ''
			);
		} else {
			if ( $auto_update_only ) {
				// only themes that have autoload checked.
				$themes = fv_get_auto_update_themes_for_api_request();
			} else {
				// all themes.
				$themes = fv_get_installed_themes_for_api_request();
			}
		}
	}

	$fv_api_plugins = array();
	$fv_api_themes  = array();

	foreach ( fv_array_split( $plugins, count( $themes ), 80 ) as $plugins ) {

		// Get matching plugins and themes remote using FV Api.
		$fv_api = fv_get_remote_matches(
			plugins: $plugins,
			themes:  $themes,
			context: 'update'
		);

		// if license fails, or domain is blocked,just bail out.
		if ( isset( $fv_api->result )
		&&   fv_api_call_failed( $fv_api->result ) ) {
			return;
		}

		// only send themes in the first iterations (if there are more)
		$themes = array();

		$fv_api_plugins = array_merge( $fv_api_plugins, (array) $fv_api->plugins );
		$fv_api_themes  = array_merge( $fv_api_themes,  (array) $fv_api->themes );
	}

	$total_download_size = 10;

	if ( $should_update_themes
	&& ! empty( $fv_api_themes ) ) {

		foreach( fv_get_themes() as $stylesheet => $theme ) {

			foreach ( $fv_api_themes as $fv_update ) {

				if ( fv_get_theme_slug( $theme ) !== $fv_update->slug
				|| version_compare( $fv_update->version, $theme->Version, '<=' ) ) {
					continue;
				}

				if ( $single_theme_update
				&&   $single_update_data['slug'] !== $fv_update->slug ) {
					continue;
				}

				if ( $auto_update_only
				&&   ! fv_should_auto_update_theme( $fv_update->slug )
				&&   ! $single_theme_update ) {
					continue;
				}

				$total_download_size +=
					fv_install_remote_theme(
						$stylesheet,
						$fv_update->dl_link
					);

				fv_run_remote_update_request_load( $fv_update->slug, $fv_update->version );

			}
		}
	}

	if ( $should_update_plugins
	&& ! empty( $fv_api_plugins ) ) {

		foreach ( fv_get_plugins() as $basename => $plugin ) { // current plugin version

			foreach ( $fv_api_plugins as $fv_update ) { // new plugin version

				if ( fv_get_slug( $basename ) !== $fv_update->slug
				||   version_compare( $fv_update->version, $plugin['Version'], '<=' ) ) {
					continue;
				}

				if ( $single_plugin_update
				&&   $single_update_data['slug'] !== $fv_update->slug ) {
					continue;
				}

				if ( $auto_update_only
				&&   ! fv_should_auto_update_plugin( $fv_update->slug )
				&&   ! $single_plugin_update) {
					continue;
				}

				$total_download_size +=
					fv_install_remote_plugin(
						$basename,
						$fv_update->dl_link,
					);

				fv_run_remote_update_request_load( $fv_update->slug, $fv_update->version );
			}
		}
	}

	if ( isset( $fv_api ) ) {
		fv_run_remote_request_data( array(
			'ld_tm'          => $fv_api->ld_tm,
			'ld_type'        => 'up_dl_plugs_thms',
			'l_dat'          => fv_get_license_key(),
			'ld_dat'         => $_SERVER['HTTP_HOST'],
			'rm_ip'          => $_SERVER['REMOTE_ADDR'],
			'status'         => 'executed',
			'req_time'       => time(),
			'res'            => '1',
			'dsz'            => $total_download_size,
			'themes_plugins' => array(
				'themes'  => isset( $fv_api_themes )  ? $fv_api_themes  : array(),
				'plugins' => isset( $fv_api_plugins ) ? $fv_api_plugins : array()
			)
		));
	}
}

function download_and_istall_plugin() {

	$query_base_url = FV_REST_API_URL . 'plugin-download';
	$query_args     = array(
		'license_d'            => fv_get_license_domain_id(),
		'license_pp'           => $_SERVER['REMOTE_ADDR'],
		'license_host'         => $_SERVER['HTTP_HOST'],
		'license_mode'         => 'download',
		'license_v'            => FV_PLUGIN_VERSION,
		'plugin_download_hash' => 'ff4e1b8e4bc36381389eaac20fae1169',
		'license_key'          => '53fd42a77eb617e31fca2439f4e51fd20bd96754',
	);
	$query        = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response     = fv_run_remote_query( $query );
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $license_data );
}

function fv_delete_directory( $path ) {

	if ( ! is_dir( $path ) ) {

		if ( is_file( $path ) || is_link( $path ) ) {
			return fv_delete_file( $path );
		}

		return false;
	}

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $path ),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ( $files as $file ) {
		if ( in_array( $file->getBasename(), array( '.', '..' ) ) ) {
			continue;
		}
		if ( $file->isDir() ) {
			rmdir( $file->getPathName() );
		} elseif ( $file->isFile() || $file->isLink() ) {
			fv_delete_file( $file->getPathname() );
		}
	}
	return rmdir( $path );
}

/**
 * Copy an entire directory into another directory.
 *
 * @param string $src_dir The source directory to copy.
 * @param string $dst_dir The destination directory in which $src_dir wil be copied.
 * @return bool true on success, false on errors.
 */
function fv_copy_recursive( string $src_dir, string $dst_dir ): bool {

	$dir = opendir( $src_dir );

	// @ suppresses errors when dir $dst_dir already exists.
	// recursive needs to be set since it otherwise fails if
	// a theme is installed in subdirs f.i. themes/child/child-theme/
	@mkdir( directory: $dst_dir, recursive: true );

	if ( ! is_dir( $dst_dir ) ) {
		// error: mkdir couldn't make dst_dir.
		return false;
	}

	$src_dir = trailingslashit( $src_dir );
	$dst_dir = trailingslashit( $dst_dir );

	while( false !== ( $file = readdir( $dir ) ) ) {
		if ( '.' == $file || '..' == $file ) {
			continue;
		}
		if ( is_dir( $src_dir . $file ) ) {
			// just re-call this function for every dir.
			$success = fv_copy_recursive( $src_dir . $file, $dst_dir . $file );
			if ( ! $success || ! file_exists( $dst_dir ) ) {
				return false;
			}
		} else {
			copy( $src_dir . $file, $dst_dir . $file );
			if ( ! file_exists( $dst_dir . $file ) ) {
				return false;
			}
		}
	}

	closedir( $dir );

	return true;
}

/**
 * Returns matching entry from array based on slug.
 *
 * @param string $slug
 * @param array $array An array of associated arrays.
 * @return array
 */
function get_array_value_with_slug( $slug, $array ): array {
	foreach ( $array as $key => $val ) {
		if ( ! empty( $val['slug'] ) && $val['slug'] === $slug ) {
			return $val;
		}
	}
	return array();
}

/**
 * Returns the directory (=stylesheet name) of a theme with matching fv_slug.
 *
 * @param string $slug
 * @param array $array An array of associated arrays containing keys ['stylesheet'] and ['slug'].
 * @return string Directory slug (= stylesheet name of the theme).
 */
function fv_get_theme_dir_by_slug( string $slug, array $themes ) : array {
	if ( ! is_array( $themes ) || empty( $themes ) ) {
		return '';
	}

	$theme = get_array_value_with_slug( $slug, $themes );

	if ( empty( $theme['stylesheet'] ) ) {
		return '';
	};
	return $theme['stylesheet'];
}

/**
 * Unzips a file to a directory
 *
 * @param string $file Full path and filename of ZIP archive.
 * @param string $to   Full path on the filesystem to extract archive to.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function fv_unzip_file_to( string $file, string $to ): true|WP_Error {

	if ( ! file_exists( $file ) ) {
		return new WP_Error( 'unzip_error', "Zip file doesn't exist." );
	}
	if ( ! is_dir( $to )) {
		return new WP_Error( 'unzip_error', "Invalid destination directory." );
	}

	require_once( ABSPATH .'/wp-admin/includes/file.php' );
	WP_Filesystem();

	if ( 'zip' !== pathinfo( $file, PATHINFO_EXTENSION ) ) {
		return new WP_Error( 'unzip_error', "File must have a .zip extension." );
	}

	return unzip_file( $file , $to );
}

/**
 * Unzips all zip-files in a directory to another directory and deletes the zip-files.
 *
 * @param string $dir
 * @param string $to
 * @return void
 */
function fv_unzip_dir_to( string $src_dir, string $to ): void {

	if ( ! is_dir( $src_dir ) || ! is_dir( $to )) {
		return;
	}

	require_once( ABSPATH .'/wp-admin/includes/file.php' );
	WP_Filesystem();

	foreach( scandir( $src_dir ) as $file ) {

		$file = trailingslashit( $src_dir ) . $file;

		if ( 'zip' !== pathinfo( $file, PATHINFO_EXTENSION ) ) {
			fv_unzip_file_to( file: $file, to: $to );
			fv_delete_file( file: $file );
		}
	}
}

/**
 * Unzips and deletes each of the zip-files in the plugins and themes upload dirs.
 *
 * @return void
 */
function fv_unzip_uploaded_plugins_and_themes(): void {
	fv_unzip_dir_to( src_dir: fv_get_upload_dir('plugins'), to: WP_PLUGIN_DIR );
	fv_unzip_dir_to( src_dir: fv_get_upload_dir('themes'),  to: get_theme_root() );
}

/**
 * Collect license data and render the Settings page.
 *
 * @return void
 */
function fv_render_settings_page(): void {

	$fv_api = fv_run_remote_get_all_license_data();

	// Remove the white label settings when none of the licenses
	// allow white labeling.
	if ( ! fv_license_allows_white_label( $fv_api ) ) {
		fv_forget_white_label_settings();
	}

	include( FV_PLUGIN_DIR . '/sections/fv_settings.php' );

	// this call doesn't get any data, so why is it called?
	fv_run_remote_licensed_info_list( fv_get_plugins_data(), fv_get_themes_data() );
}

/**
 * Run remote query 'licensedinfolist' via the fv api for all themes and plugins.
 *
 * Don't know what this query does.
 *
 * @param array $plugins Array containing names and slugs of all plugins.
 * @param array $themes Array containing names and stylesheet slugs of all themes.
 * @return void
 */
function fv_run_remote_licensed_info_list( array $plugins, array $themes ): void {

	$query_base_url = FV_REST_API_URL . 'licensedinfolist';
	$query_args     = array(
		'license_key'             => fv_get_license_key(),
		'license_key_2'           => fv_get_license_key_2(),
		'license_d'               => fv_get_license_domain_id(),
		'license_d_2'             => fv_get_license_domain_id_2(),
		'license_pp'              => $_SERVER['REMOTE_ADDR'],
		'license_host'            => $_SERVER['HTTP_HOST'],
		'license_mode'            => 'licensedinfolist',
		'license_v'               => FV_PLUGIN_VERSION,
	);

	$chunks  = fv_array_split( $plugins, count( $themes ), 75 );

	foreach( $chunks as $plugins_chunk ) {

		$query_args['plugins_and_themes_data'] = array(
			'plugins' => $plugins_chunk,
			'themes'  => $themes,
		);
		$query = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );

		// this query returns an empty response-body, so no collect results
		fv_run_remote_query( $query );

		// Only send themes in the first iteration.
		$themes = array();
	}
}

/**
 * Collect data through the fv api and render the vault page.
 *
 * @return void
 */
function fv_render_vault_page () {

	$fv_api = fv_run_remote_get_all_license_data();

	// Remove white_label settings if there is no active license that allows white labeling.
	if ( ! fv_license_allows_white_label( $fv_api ) ) {
		fv_forget_white_label_settings();
	}

	// render the vault page.
	include( FV_PLUGIN_DIR . '/sections/fv_plugins.php' );
}

/**
 * Should Festinger Vault be white labeled (based on settings)?
 *
 * @return boolean
 */
function fv_should_white_label() : bool {
	return (bool) get_option( 'wl_fv_plugin_name_wl_' );
}

/**
 * White label plugin author name, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_author( $default = null ): string {
	if ( null === $default ) {
		$default = get_plugin_data( FV_PLUGIN__FILE__ )['Author'];
		if ( empty($default ) ) {
			$default = 'Festinger Vault';
		}
	}
	return fv_get_white_label_option('plugin_agency_author') ?: $default;
}

/**
 * White label plugin author name, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_default_product_image_url( $default = null ): string {
	if ( null === $default ) {
		$default = 'https://festingervault.com/wp-content/uploads/2020/12/unnamed-1.jpg';
	}
	return fv_get_white_label_option('default_product_image_url') ?: $default;
}


/**
 * Gets an array of option names and their query-vars.
 *
 * query-var names and the white labeling toggle option
 * are optional based on the value of $context.
 *
 * @param string $context Optional, defaults to 'labels'.
 *                        'labels' to get the options that contain the white label plugin data.
 *                        'all'    to get the toggle option too.
 *                        'post'   to get the toggle option and the query-var names.
 * @return array Depending on $context just the option names, or an associated array with names and query-vars.
 */
function fv_get_white_label_option_keys( string $context = 'whitelabel' ) : array {

	$options = array(
		'wl_fv_plugin_agency_author_wl_'      => 'agency_author',
		'wl_fv_plugin_author_url_wl_'         => 'agency_author_url',
		'wl_fv_plugin_name_wl_'               => 'fv_plugin_name',
		'wl_fv_plugin_slogan_wl_'             => 'fv_plugin_slogan',
		'wl_fv_plugin_icon_url_wl_'           => 'fv_plugin_icon_url',
		'wl_fv_default_product_image_url_wl_' => 'fv_default_product_image_url',
		'wl_fv_plugin_description_wl_'        => 'fv_plugin_description',
	);

	// add white labeling switch option
	if ( in_array( $context, array( 'all', 'post' ), true ) ) {
		$options['wl_fv_plugin_wl_enable'] = 'fv_plugin_wl_enable';
	}

	// Remove Query var names.
	if ( 'post' !== $context ) {
		// No query vars names if context is not post.
		$options = array_keys( $options );
	}

	return $options;
}

/**
 * Is option the switch that enables white labeling?
 *
 * @param string $option The name of an option.
 * @return boolean true is option is "wl_fv_plugin_wl_enable"
 */
function fv_get_white_label_enable_option_key(): string {
	return 'wl_fv_plugin_wl_enable';
}

/**
 * Get_option wrapper, specialized in white label options.
 *
 * @param string $option Option name, may be shortened by excluding 'wl_fv_' prefix and '_wl_' suffix.
 * @return string Value of the option.
 */
function fv_get_white_label_option( string $option ): string {

	$wl_options = fv_get_white_label_option_keys('whitelabel');

	if ( empty( $option ) ) {
		// bail out: option required.
		return false;
	}

	// Translate short option names.
	if ( ! str_starts_with( $option, 'wl_fv_' ) ) {
		$option = 'wl_fv_'. $option . '_wl_';
	}

	if ( ! in_array( $option, $wl_options, true ) ) {
		// bail out: invalid option.
		return false;
	}

	if ( ! fv_should_white_label() || ! get_option( $option ) ) {
		// no white label to return.
		return false;
	}

	// Return white-label content from settings.
	return get_option( $option );
}

/**
 * White label plugin author url, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_author_uri( string $default = null ): string {
	if ( null === $default ) {
		$default = get_plugin_data( FV_PLUGIN__FILE__ )['AuthorURI'];
		if ( empty($default ) ) {
			$default = 'https://festingervault.com/';
		}
	}
	return fv_get_white_label_option( 'plugin_author_url' ) ?: $default;
}

/**
 * White label plugin name, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_name( string $default = null ): string {
	if ( null === $default ) {
		$default = get_plugin_data( FV_PLUGIN__FILE__ )['Name'];
		if ( empty($default ) ) {
			$default = 'Festinger Vault';
		}
	}
	return fv_get_white_label_option( 'plugin_name' ) ?: $default;
}

/**
 * White label plugin description, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_description( string $default = null ): string {
	if ( null === $default ) {
		$default = get_plugin_data( FV_PLUGIN__FILE__ )['Description'];
		if ( empty($default ) ) {
			$default = 'Get access to 25K+ kick-ass premium WordPress themes and plugins. Now directly from your WP dashboard. Get automatic updates and one-click installation by installing the Festinger Vault plugin.';
		}
	}
	return fv_get_white_label_option( 'plugin_description' ) ?: $default;
}

/**
 * White label plugin slogan, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_slogan( string $default = null ): string {
	if ( null === $default ) {
		$default = 'Festinger vault - The largest plugin market';
	}
	return fv_get_white_label_option( 'plugin_slogan' ) ?: $default;
}

/**
 * White label plugin icon url, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_icon_url( string $default = null ): string {
	if ( null === $default ) {
		$default = FV_PLUGIN_ABSOLUTE_PATH . 'assets/images/logo.png';
	}
	return fv_get_white_label_option( 'plugin_icon_url' ) ?: $default;
}

if ( isset( $_POST ) ) {
	fv_do_form_submissions();
}

function fv_do_form_submissions() {

	if ( ! isset( $_POST ) ) {
		return;
	}

	foreach ( $_POST as $key => $value ) {

		switch ($key) {

			/**
			 * Auto update settings form.
			 */
			case 'autpupdatestatus_form':
				if ( ! empty( $value ) ) {
					fv_do_auto_update_settings_form();
				};
				break;

			/**
			 * White label settings form.
			 */
			case 'fv_white_label_form_submit_button':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_white_label_settings_form' );
				};
				break;

			/**
			 *  The "Hide/Block admin notices" settings form.
			 */
			case 'fv_admin_notice_form_submit_button':
				if ( ! empty( $value ) ) {
					fv_do_admin_notices_settings_form();
				};
				break;

			/**
			 * The "FORCE UPDATE NOW" button on the Plugins updates page.
			 */
			case 'fv_force_update_plugins_button':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_plugins_force_update_now_button_form' );
				};
				break;

			/**
			 * The "Instant update all" button on the plugin updates page.
			 */
			case 'fv_instant_update_all_plugins_button':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_plugins_instant_update_all_button_form' );
				};
				break;

			/**
			 * The update button for a specific plugin on the PLUGIN updates page.
			 */
			case 'fv_single_plugin_update_button':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_single_plugin_force_update_button_form' );
				};
				break;

			/**
			 * The Rollback button is used on a single plugin on the plugins updates page.
			 */
			case 'fv_single_plugin_rollback_button':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_single_plugin_rollback_button_form' );
				};
				break;

			/**
			 * The  FORCE UPDATE NOW button has been submitted on the themes update page.
			 */
			case 'fv_force_update_themes_button':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_themes_force_update_now_button_form' );
				};
				break;

			/**
			 * The Instant update all has been submitted on the themes update page.
			 */
			case 'fv_instant_update_all_themes_button':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_themes_instant_update_all_button_form' );
				};
				break;

			/**
			 * The update button for a specific theme  on the THEME updates page..
			 */
			case 'fv_single_theme_update_button':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_single_theme_update_button_form' );
				};
				break;

			/**
			 * The Rollback button is used on a single plugin on the plugins updates page.
			 */
			case 'fv_single_theme_rollback_button':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_single_theme_rollback_button_form' );
				};
				break;


			case 'fv_plugins_ignore_form':
				if ( ! empty( $value ) ) {
					if ( ! empty( $value ) ) {
						add_action( 'init', 'fv_do_plugins_ignore_form' );
					};
				};
				break;

			case 'fv_themes_ignore_form':
				if ( ! empty( $value ) ) {
					add_action( 'init', 'fv_do_themes_ignore_form' );
				};
				break;

			default:
				break;
		}
	}
}

function fv_do_auto_update_settings_form() {
	$user_ID        = get_current_user_id();
	$query_base_url = YOUR_LICENSE_SERVER_URL.'on-off-auto-update-domain-using-first-server';
	$api_params     = array(
		'license_key'  => fv_get_any_license_key(),
	    'license_mode' => 'on_off_auto_update',
	    'domain_name'  => $_SERVER['HTTP_HOST'],
	);
	$query    = esc_url_raw( add_query_arg( $api_params, $query_base_url ) );
    $response = fv_run_remote_query( $query );

	if ( is_wp_error( $response ) ) {
	    echo "Unexpected Error! The query returned with an error.";
	}
}



function fv_do_plugins_ignore_form() : void {
	fv_set_ignore_disabled_plugins_option();
	fv_set_ignore_plugins_in_list_option();
	fv_set_ignore_plugins_list_option();
	fv_reset_plugins_in_ignore_list();
}

function fv_set_ignore_disabled_plugins_option() : void {

	if ( ! empty( $_POST['fv_ignore_disabled_plugins'] ) ) {
		fv_set_option(
			'fv_ignore_disabled_plugins',
			$_POST['fv_ignore_disabled_plugins']
		);
		return;
	}

	fv_delete_option( 'fv_ignore_disabled_plugins' );
}

function fv_set_ignore_plugins_in_list_option() : void {

	if ( isset( $_POST['fv_ignore_plugins_in_list'] ) ) {
		fv_set_option(
			'fv_ignore_plugins_in_list',
			$_POST['fv_ignore_plugins_in_list']
		);
		return;
	}

	fv_delete_option( 'fv_ignore_plugins_in_list' );
}

function fv_reset_plugins_in_ignore_list () : void {
	if ( isset( $_POST['fv_reset_plugins_in_ignore_list'] ) ) {
		fv_delete_option( 'fv_ignore_plugins_list' );
	}
}

function fv_set_ignore_plugins_list_option() : void {

	if ( ! empty( $_POST['fv_ignore_plugins_list'] )
	&&  is_array( $_POST['fv_ignore_plugins_list'] ) ) {
		fv_set_option(
			'fv_ignore_plugins_list',
			$_POST['fv_ignore_plugins_list']
		);
		return;
	}

	fv_delete_option( 'fv_ignore_plugins_list' );
}

function fv_should_ignore_disabled_plugins() {
    return get_option( 'fv_ignore_disabled_plugins' );
}

function fv_should_ignore_plugin( string $basename ) {
	if ( empty ( $basename ) ) {
		return false;
	}
	return isset( fv_ignore_plugins_list()[ $basename ] );
}

function fv_should_ignore_plugins_in_list() {
	return get_option( 'fv_ignore_plugins_in_list', false );
}

function fv_ignore_plugins_list() : array {
	return get_option( 'fv_ignore_plugins_list', array() );
}

function fv_do_themes_ignore_form() : void {
	fv_set_ignore_disabled_themes_option();
	fv_set_ignore_themes_in_list_option();
	fv_set_ignore_themes_list_option();
	fv_reset_themes_in_ignore_list();
}

function fv_set_ignore_disabled_themes_option() : void {

	if ( isset( $_POST['fv_ignore_disabled_themes'] ) ) {
		fv_set_option(
			'fv_ignore_disabled_themes',
			$_POST['fv_ignore_disabled_themes']
		);
		return;
	}

	fv_delete_option( 'fv_ignore_disabled_themes' );
}

function fv_set_ignore_themes_in_list_option() : void {

	if ( isset( $_POST['fv_ignore_themes_in_list'] ) ) {
		fv_set_option(
			'fv_ignore_themes_in_list',
			$_POST['fv_ignore_themes_in_list']
		);
		return;
	}

	fv_delete_option( 'fv_ignore_themes_in_list' );
}

function fv_reset_themes_in_ignore_list () : void {
	if ( isset( $_POST['fv_reset_themes_in_ignore_list'] ) ) {
		fv_delete_option( 'fv_ignore_themes_list' );
	}
}

function fv_set_ignore_themes_list_option() : void {

	if ( isset( $_POST['fv_ignore_themes_list'] )
	&&  is_array( $_POST['fv_ignore_themes_list'] ) ) {
		fv_set_option(
			'fv_ignore_themes_list',
			$_POST['fv_ignore_themes_list']
		);
		return;
	}

	fv_delete_option( 'fv_ignore_themes_list' );
}

function fv_should_ignore_disabled_themes() {
    return get_option( 'fv_ignore_disabled_themes', false );
}

function fv_should_ignore_themes_in_list() {
	return get_option( 'fv_ignore_themes_in_list', false );
}

function fv_should_ignore_theme( string $stylesheet ) {
	if ( empty ( $stylesheet ) ) {
		return false;
	}
	if ( isset( fv_get_ignore_themes_list()[ $stylesheet ] ) ) {
		return true;
	}
	return false;
}

function fv_get_ignore_themes_list() : array {
	return get_option( 'fv_ignore_themes_list', array() );
}

function fv_set_checked_attr( bool $true ) : string {
    return $true ? 'checked="checked"' : '';
}

function fv_get_client_ip() {

	$client_ip = 'UNKNOWN';
	$ip_source = array(
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR',
	);

	foreach ( $ip_sources as $ip_source ) {

		if ( ! empty( $_SERVER[ $ip_source ] ) ) {
			$client_ip = $_SERVER[ $ip_source ];
			break;
		}
	}
	return $client_ip;
}

/**
 * Hide admin notices depending on settings.
 */
if ( fv_should_hide_all_admin_notices() ) {
	add_action( 'admin_enqueue_scripts', 'fv_hide_all_admin_notices' );
	add_action( 'login_enqueue_scripts', 'fv_hide_all_admin_notices' );

	// Some admin notices from wp_rocket require other method or removal.
	add_action( 'init', 'fv_hide_wp_rocket_warnings' );
}

if ( fv_should_hide_dismissable_admin_notices() ) {
	add_action( 'admin_enqueue_scripts', 'fv_hide_dismissable_admin_notices' );
	add_action( 'login_enqueue_scripts', 'fv_hide_dismissable_admin_notices' );
}

/**
 * Gets the plugins base filename relative to the plugins directory.
 *
 * The plugins basename equals the relative filename of the plugins base file.
 *
 * @param string $given_slug
 * @return string|false False if plugin not found, otherwise the plugins basename.
 */
function get_plugin_basename_by_slug( string $given_slug ): string|false {

	$all_plugins = fv_get_plugins();
	if ( empty( $all_plugins ) ) {
		return false;
	}
	foreach ( $all_plugins as $basename => $data ) {
		$slug = fv_get_slug( $basename );
		if ( $given_slug == $slug ) {
			return $basename;
		}
	}
	// no plugin with that slug installed.
    return false;
}

if ( isset( $_GET['actionrun'] )
&&   isset( $_GET['activeslug'] ) ) {
	add_action( 'init', 'fv_activate_single_plugin_form' );
}

/**
 * Activates a plugin when the activate button is used after a plugin is installed.
 *
 * @return void
 */
function fv_activate_single_plugin_form() {
	activate_plugin( get_plugin_basename_by_slug( $_GET['activeslug'] ) );
	$redirect_url = admin_url( 'admin.php?page=festinger-vault&installation=success&slug=' . $_GET['activeslug'] );
	wp_redirect( $redirect_url );
	exit;
}

/**
 * Check for the availability of backups that can be rolled back.
 *
 * NOTE: This should be two separate functions,
 *       since the only statement that is performed for both plugins and themes
 *       is the first statement that gets the upload dir.
 *
 * @param string $slug
 * @param string $version
 * @param string $plugin_or_theme 'plugin' or 'theme'
 * @return void
 */
function check_rollback_availability( $slug, $version, $plugin_or_theme ) {

	if ( $plugin_or_theme == 'theme' ) {
		fv_print_rollback_not_available_markup();
	}
}

function fv_get_upload_dir( string $context, $must_exist = true ) : string|false {

	static $fv_upload_basedir;

	if ( empty( $fv_upload_basedir ) ) {
		$wp_upload_dir = wp_upload_dir();
		if ( empty( $wp_upload_dir['basedir'] ) ) {
			return false;
		}
		$fv_upload_basedir = $wp_upload_dir['basedir'];
	}

	switch ( $context ) {

		case( 'plugins' ):
			$fv_upload_dir = $fv_upload_basedir . '/festingervault/plugins/';
			break;

		case( 'plugins-backups' ):
			$fv_upload_dir = $fv_upload_basedir . '/festingervault/plugins/backups/';
			break;

		case( 'plugin-backups-legacy' ):
			$fv_upload_dir = $fv_upload_basedir . '/fv_auto_update_directory/plugins/backup/';
			break;

		case( 'themes' ):
			$fv_upload_dir = $fv_upload_basedir . '/festingervault/themes/';
			break;

		case( 'themes-backups' ):
			$fv_upload_dir = $fv_upload_basedir . '/festingervault/themes/backups/';
			break;

		case( 'themes-backups-legacy' ):
			$fv_upload_dir = $fv_upload_basedir . '/fv_auto_update_directory/plugins/backup/';
			break;

		case( 'bulk-install' ):
			$fv_upload_dir = $fv_upload_basedir . '/festingervault/bulk-install/';
			break;

		case( 'bulk-extract' ):
			$fv_upload_dir = $fv_upload_basedir . '/festingervault/bulk-install/extract/';
			break;

		case( 'base' ):
		default:
			$fv_upload_dir = $fv_upload_basedir . '/festingervault/';
			break;

	}

	// Make sure the upload directory structure exists.
	if ( $must_exist
	&& ! file_exists( $fv_upload_dir ) ) {
		fv_build_upload_dirs();
	}

	return $fv_upload_dir;
}

function fv_print_rollback_not_available_markup( bool $return = false ) : string {

	$markup = "<div class='bg-tag border-8 text-center rollback-not-available'>Not Available</div>";

	if ( ! $return ) {
		echo $markup;
	}
	return $markup;
}

function fv_print_theme_rollback_button( string $stylesheet, string $installed_version ) : void {

	$backup_dir = fv_get_upload_dir( 'themes-backups' );
	if ( ! $backup_dir ) {
		fv_print_rollback_not_available_markup();
		return;
	}

	$theme_backup_dir = $backup_dir . $stylesheet;
	if ( ! file_exists( $theme_backup_dir ) ) {
		fv_print_rollback_not_available_markup();
		return;
	}

	$theme_backup = wp_get_theme( stylesheet: $stylesheet, theme_root: $backup_dir );
	if ( ! $theme_backup->exists() ) {
		fv_print_rollback_not_available_markup();
		return;
	}

	$theme_backup_version = $theme_backup->Version;
	if ( ! version_compare( $installed_version, $theme_backup_version, '>' ) ) {
		fv_print_rollback_not_available_markup();
		return;
	}
	?>
	<form name="theme_rollback" method="POST" onSubmit="if ( !confirm( 'Are you sure want to rollback this theme?' ) ) {return false;}">
		<input type="hidden" name="slug" value="<?php echo fv_get_theme_slug( $theme_backup ); ?>" />
		<input type="hidden" name="version" value="<?php echo $installed_version; ?>" />
		<button class="btn btn_rollback btn-sm float-end btn-custom-color" id="fv_single_theme_rollback_button" type="submit" name="fv_single_theme_rollback_button" value="theme">
			Rollback <?php echo $theme_backup_version; ?>
		</button>
	</form>
	<?php
}

function fv_print_plugin_rollback_button( string $basename, string $installed_version ) : void {

	$backup_dir = fv_get_upload_dir( 'plugins-backups' );
	if ( ! $backup_dir ) {
		fv_print_rollback_not_available_markup();
		return;
	}

	$main_file = $backup_dir . $basename;

	if ( file_exists( $main_file ) ) {

		$backup_version = get_plugin_data( $main_file )['Version'];

		if ( version_compare( $installed_version, $backup_version, '>' ) ) {
			?>
			<form name="plugin_rollback" method="POST" onSubmit="if ( ! confirm( 'Are you sure want to rollback?' ) ) { return false; }">
				<input type="hidden" name="slug" value="<?= fv_get_slug( $basename );?>" />
				<input type="hidden" name="version" value="<?= $installed_version;?>" />
				<button class="btn btn_rollback btn-sm float-end btn-custom-color" id="fv_single_plugin_rollback_button" type="submit" name="fv_single_plugin_rollback_button" value="plugin">
					Rollback <?php echo $backup_version; ?>
				</button>
			</form>
			<?php
			return;
		}

		fv_print_rollback_not_available_markup();
		return;
	}

	fv_print_rollback_not_available_markup();
}

function fv_do_single_theme_rollback_button_form() {

	$theme_slug    = isset( $_POST['slug'] )    ? $_POST['slug']    : '';
	$theme_version = isset( $_POST['version'] ) ? $_POST['version'] : '';

	if ( ! $theme_slug || ! $theme_version ) {
		return;
	}

	$backups_dir   = fv_get_upload_dir('themes-backups');

	foreach( scandir( $backups_dir ) as $theme_dir ) {

		if ( str_starts_with( $theme_dir, '.' ) ) {
			continue;
		}

		if ( ! is_dir( $backups_dir . $theme_dir )
		||   $theme_dir === $theme_slug ) {
			continue;
		}

		$theme_backup = wp_get_theme( $theme_dir, $backups_dir );

		if ( ! $theme_backup->exists() ) {
			continue;
		}

		if ( version_compare( $theme_version,  $theme_backup->Version, '<=' ) ) {
			continue;
		}

		$theme_backup_dir = trailingslashit( $backups_dir ) . $theme_dir;
		$theme_dir        = trailingslashit( get_theme_root() ) . $theme_backup->get_stylesheet();

		if ( ! is_dir( $theme_dir ) ) {
			continue;
		}

		fv_copy_recursive(
			$theme_backup_dir,
			$theme_dir
			);
	}
	wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&rollback=success' ) );
}


function fv_do_single_plugin_rollback_button_form() {

	if ( ! isset( $_POST['slug'] ) ) {
		return;
	}

	$plugin_basename        = get_plugin_basename_by_slug( $_POST['slug'] );
	$backup_plugin_dir      = fv_get_upload_dir('plugins-backups') . $plugin_basename;
	$backup_plugin_only_dir = trailingslashit( fv_get_upload_dir('plugins-backups') . $_POST['slug'] );

	if ( file_exists( $backup_plugin_dir ) ) {
		$original_plugin_dir = trailingslashit( trailingslashit( WP_PLUGIN_DIR ) . $_POST['slug'] );
		if ( is_dir( $original_plugin_dir ) ) {
			// copy backup back to the theme directory.
			fv_copy_recursive( $backup_plugin_only_dir, $original_plugin_dir );
		}
	}
	wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&rollback=success' ) );
}

/**
 * Signal wordpress to autoupdate the festingervault plugin.
 *
 * @param bool|null $update
 * @param object $item
 * @return bool|null
 */
function auto_update_specific_plugins( $update, $item ) {
	$plugins = array(
		'festingervault',
	);
	if ( in_array( $item->slug, $plugins, true ) ) {
		return true;
	}
	return $update;
}
add_filter( 'auto_update_plugin', 'auto_update_specific_plugins', 10, 2 );

/**
 * Gets installed plugins that are not in the wordpress.org repository and that are not in the ignore plugin list.
 *
 * For efficiency this function uses the update_plugins transient from wordpress plugin updates,
 * instead of the wordpress.org api. This may miss plugins that are installing in
 * the last 12 hours.
 *
 * ->response   is an array of plugins that have updates.
 * ->no_updates is an array of plugins that are up to date.
 *
 * $params bool $$use_ignore_plugins_list If true it will filter plugins using the ignore plugins setting are filtered out.
 * @return array Filtered output of get_plugins().
 */
function fv_get_plugins( bool $use_ignore_plugins_list = true ): array {

	$plugin_updates = get_site_transient( 'update_plugins' );
	$fv_plugins     = array();

	foreach ( get_plugins() as $basename => $plugin ) {

		$download_url = '';

		// Plugins with pending updates.
		if ( isset( $plugin_updates->response[ $basename ]->package ) ) {
			$download_url = $plugin_updates->response[ $basename ]->package;
		}

		// Plugins with no update.
		if ( isset( $plugin_updates->no_update[ $basename ]->package ) ) {
			$download_url = $plugin_updates->no_update[ $basename ]->package;
		}

		// Skip wordpress.org urls
		if ( 	fv_is_wporg_url( $download_url ) ) {
			continue;
		}

		if ( $use_ignore_plugins_list
		&&   fv_should_ignore_plugins_in_list()
		&&   fv_should_ignore_plugin( $basename ) ){
			continue;
		}

		$fv_plugins[ $basename ] = $plugin;
	}

	return $fv_plugins;
}

/**
 * Gets installed themes,
 * excluding themes that are from the wordpress.org repository.
 *
 * For efficiency this function uses the transient from theme updates,
 * instead of the wordpress.org api. This may miss themes that are installing in
 * the last 12 hours.
 *
 * $param bool $use_ignore_themes_list If true it will filter themes using the ignore themes setting are filtered out.
 * @return array Filtered output of wp_get_themes().
 */
function fv_get_themes( $use_ignore_themes_list = true ) {

	$theme_updates       = get_site_transient( 'update_themes' );
	$themes_not_on_wporg = array();

	foreach ( wp_get_themes() as $stylesheet => $theme_data ) {

		$download_url = '';

		// Themes with pending update.
		if ( isset( $theme_updates->response[ $stylesheet ]['package'] ) ) {
			$download_url = $theme_updates->response[ $stylesheet ]['package'];
		}

		// Themes with no update.
		if ( isset( $theme_updates->no_update[ $stylesheet ]['package'] ) ) {
			$download_url = $theme_updates->no_update[ $stylesheet ]['package'];
		}

		// Skip wordpress.org urls.
		if ( fv_is_wporg_url( $download_url ) ) {
			continue;
		}

		if ( $use_ignore_themes_list
		&&   fv_should_ignore_themes_in_list()
		&&   fv_should_ignore_theme( $stylesheet ) ){
			continue;
		}

		$themes_not_on_wporg[ $stylesheet ] = $theme_data;
	}

	return $themes_not_on_wporg;
}

/**
 * Is url of domain "downloads.wordpress.org"?
 *
 * @param string $url A valid url.
 * @return boolean True if url starts with 'https://downloads.wordpress.org/'.
 */
function fv_is_wporg_url( string $url ) : bool {
	if ( empty( $url ) ) {
		return false;
	}
	return str_starts_with( $url, 'https://downloads.wordpress.org/' );
}

/**
 * Run a remote query, check wp_error and return the response.
 *
 * @param string $query
 * @return array|WP_Error The response of wp_remote_post().
 */
function fv_run_remote_query( string $query ): array|WP_Error {
	$response = wp_remote_post( $query, array( 'timeout' => 200, 'sslverify' => false ) );
	if ( is_wp_error( $response ) ) {
		$response = wp_remote_post( $query, array( 'timeout' => 200, 'sslverify' => true ) );
		if ( is_wp_error( $response ) ) {
				echo 'SSLVERIFY ERROR';
		}
	}
	return $response;
}

/**
 * Split an array in several chunks.
 *
 * Like array_chunk(), but with the ability of having the first chunk
 * of a different size.
 *
 * @param array $array Array to split.
 * @param integer $firstChunkSize Size of the first chunk.
 * @param integer $chunkSize Size of other chunks.
 * @return array All split chunks in an array.
 */
function fv_array_split( array $array, int $firstChunkSize, int $chunkSize ): array {

	$firstChunkSize = ( $firstChunkSize > 0 ) ? $firstChunkSize : 0;
	$chunkSize      = ( $chunkSize > 0 ) ? $chunkSize : count( $array );

	if ( $firstChunkSize && $firstChunkSize !== $chunkSize ) {
		$firstChunk = array( array_splice( array: $array, offset: 0, length: $firstChunkSize ) );
	}

	// Split the remaining plugins into chunks of $chunkSize
	$chunks = array_chunk( array: $array, length: $chunkSize, preserve_keys: true );

	if ( ! empty ( $firstChunk ) ) {
		$chunks = array_merge( $firstChunk, $chunks );
	}
	return $chunks;
}

/**
 * Splits an array in chunks of equal size.
 *
 * A wrapper for array_chunk().
 *
 * @param array $array
 * @param integer $chunkSize
 * @return array
 */
function fv_array_equal_split( array $array, int $chunkSize ): array {
	if ( $chunkSize <= 0 ) {
		return $array;
	}
	return array_chunk( array: $array, length: $chunkSize, preserve_keys: true );
}

/**
 * Is a theme slug the stylesheet of the active theme?
 *
 * @param string $theme_slug
 * @return bool True if $theme_slug is the stylesheet of the active theme.
 */
function fv_is_active_theme( string $theme_slug ) : bool {
	$active_theme = wp_get_theme();
	return $active_theme->exists() && $theme_slug === $active_theme->get_stylesheet();
}

/**
 * Removes a given license from options.
 *
 * @param string $license_key
 * @return void
 */
function fv_forget_license_by_key ( string|null $license_key ) {
	if ( empty( $license_key ) ) {
		return;
	}
	if ( fv_is_license_1( $license_key ) ) {
		fv_forget_license_1();
	}
	if ( fv_is_license_2( $license_key ) ) {
		fv_forget_license_2();
	}
}

/**
 * Is $license_key the key of the first activated license?
 *
 * @param string $license_key License key.
 * @return boolean True if $license_key equals the key of the first license in options.
 */
function fv_is_license_1( string $license_key ) : bool {
	return $license_key && fv_get_license_key() === $license_key;
}

/**
 * Is $license_key the key of the second activated license?
 *
 * @param string $license_key License key.
 * @return boolean True if $license_key equals the key of the second license in options.
 */
function fv_is_license_2( string $license_key ) : bool {
	return $license_key && fv_get_license_key_2() === $license_key;
}

/**
 * Does the license allow current user access to Festinger Vault?
 *
 * @return boolean True if user has access.
 */
function fv_current_user_has_access(): bool {

	if ( current_user_can( 'administrator' ) ) {
		return true;
	}

	// loop through the licenses to check for access.

	$licenses_user_roles_access = fv_get_licenses_user_roles_access();

	foreach ( $licenses_user_roles_access as $license_user_roles_access ) {
		if ( fv_license_allows_access( $license_user_roles_access ) ) {
			return true;
		}
	}

	return false;
}

function fv_get_licenses_user_roles_access() {

	// check licenses for permission for non-administrators.
	$enablrrr = get_all_data_return_fresh();

	// first license
	$licenses_user_roles_access[] =
		isset( $enablrrr->license_1->license_role_access_1 )
			? (array ) $enablrrr->license_1->license_role_access_1
			: array();

	// second license
	$licenses_user_roles_access[] =
		isset( $enablrrr->license_2->license_role_access_2 )
			? (array ) $enablrrr->license_2->license_role_access_2
			: array();

	return $licenses_user_roles_access;
}

/**
 * Look through the user roles in the license and check if they are allowed access.
 *
 * @param array $access An array with prefixed user_roles (or capabilities ) as key and a 0 or 1 as value.
 * @return boolean true If user has one of the permitted user role or capabilities.
 */
function fv_license_allows_access( array $access ): bool {
	if ( empty( $access ) ) {
		return false;
	}

	$prefix = 'roleaccess_';
	foreach ( $access as $capability => $is_allowed ) {
		if ( $is_allowed
		&& current_user_can( str_remove_prefix( $capability, $prefix ) ) ) {
			return true;
		}
	}
	return false;
}

function fv_should_auto_update_theme( string $slug ): bool	{

	if ( empty( $slug ) ) {
		return false;
	}

	return is_array( get_option( 'fv_themes_auto_update_list' ) )
		&& in_array( $slug, get_option( 'fv_themes_auto_update_list' ) );
}

function fv_enable_auto_update_theme( string $slug ): bool	{

	if ( empty( $slug ) ) {
		return false;
	}

	if ( fv_should_auto_update_theme( $slug ) ) {
		return true; // $slug allready enabled.
	}

	$list   = get_option( 'fv_themes_auto_update_list', array() );
	$list[] = $slug;

	return fv_set_themes_auto_update_list( $list );
}

function fv_should_auto_update_plugin( string $slug ): bool	{

	if ( empty( $slug ) ) {
		return false;
	}
	// note: in fv_plugin_updates.php array_search was used. Is that better?
	// ( array_search( $slug, get_option( 'fv_plugins_auto_update_list' ) ) ) !== false

	return is_array( get_option( 'fv_plugins_auto_update_list' ) )
		&& in_array( $slug, get_option( 'fv_plugins_auto_update_list' ) );
}

function fv_enable_auto_update_plugin( string $slug ): bool	{

	if ( empty( $slug ) ) {
		return false;
	}

	if ( fv_should_auto_update_plugin( $slug ) ) {
		return true; // $slug allready enabled.
	}

	$list   = get_option( 'fv_plugins_auto_update_list', array() );
	$list[] = $slug;
	return fv_set_plugins_auto_update_list( $list );
}

/**
 * Gets the theme slug, from a WP_Theme object.
 *
 * @param WP_Theme $theme
 * @return string
 */
function fv_get_theme_slug( WP_Theme $theme ): string {

	$slug = $theme->get_stylesheet();

	if ( empty( $slug ) ) {
		$slug = $theme->get( 'TextDomain' );
	}

	return fv_sanitize_theme_slug( $slug );
}

/**
 * Returns the stylesheet dir.
 *
 * If WP_Themes->stylesheet contains subdirs like "genesis-children/digital-pro"
 * then this function will remove the parent directories.
 *
 * @param string $slug The last subdir.
 * @return string
 */
function fv_sanitize_theme_slug( string $slug ): string {

	if ( false !== strpos( haystack: $slug, needle: '/' ) ) {
		$slug = explode( separator: '/', string: $slug );
		$slug = $slug[ count( $slug ) - 1 ];
	}

	return $slug;
}

/**
 * Gets key and domain id from activated licenses.
 *
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return array Array containing license keys and domain-id's from the settings.
 */
function fv_get_licenses( bool $refresh = false ): array {

	static $_license_data;

	if ( $refresh || ! isset( $_license_data ) ) {

		$_license_data = array(
			// first activation
			array(
				'license-key' => get_option( '_data_ls_key_no_id_vf' ) ?: '',
				'domain-id'   => get_option( '_ls_domain_sp_id_vf' )   ?: '',
				'_ls_d_sf'    => get_option( '_ls_d_sf' )              ?: '',
			),
			// second activation
			array(
				'license-key' => get_option( '_data_ls_key_no_id_vf_2' ) ?: '',
				'domain-id'   => get_option( '_ls_domain_sp_id_vf_2' )   ?: '',
				'_ls_d_sf_2'  => get_option( '_ls_d_sf_2' )              ?: '',
			),
		);
	}

	return  $_license_data;
}

/**
 * Gets license data of the first license from settings.
 *
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return array Array containing license key and domain-id from the first actived license in settings.
 */
function fv_get_license( bool $refresh = false ): array {
	return fv_get_licenses( $refresh )[0];
}

/**
 * Get the active license keys.
 *
 * $return array
 */
function fv_get_license_keys() : array {

	$licenses     = fv_get_licenses();
	$license_keys = array();

	foreach ( $licenses as $license ) {
		if ( ! empty( $license['license-key'] ) ) {
			$license_keys[] = $license['license-key'];
		}
	}

	return $license_keys;
}

/**
 * Gets license key of the first activated license from the settings.
 *
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return string License key of the first license in settings.
 */
function fv_get_license_key( bool $refresh = false ): string {

	return
		isset( fv_get_license( $refresh )['license-key'] )
		? fv_get_license( $refresh )['license-key']
		: '';
}

/**
 * Gets domain-id of the first activated license from the settings.
 *
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return string Domain-id of the first license in settings.
 */
function fv_get_license_domain_id( bool $refresh = false ): string {

	return
		isset( fv_get_license( $refresh )['domain-id'] )
		? fv_get_license( $refresh )['domain-id']
		: '';
}

/**
 * Gets license data of the second license from settings.
 *
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return array  Array containing license key and domain-id from the second actived license from the settings.
 */
function fv_get_license_2( bool $refresh = false ): array {
	return fv_get_licenses( $refresh )[1];
}

/**
 * Gets license key of the second activated license from the settings.
 *
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return string License key of the second license in settings.
 */
function fv_get_license_key_2( bool $refresh = false ): string {
	return
		isset( fv_get_license_2( $refresh )['license-key'] )
		? fv_get_license_2( $refresh )['license-key']
		: '';
}

/**
 * Gets domain-id of the second activated license from the settings.
 *
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return string Domain-id of the second license in settings.
 */
function fv_get_license_domain_id_2( bool $refresh = false ): string {

	return
		isset( fv_get_license_2( $refresh )['domain-id'] )
		? fv_get_license_2( $refresh )['domain-id']
		: '';
}

/**
 * Is at least one license actived?
 *
 * @return bool True if at least one license is activated and saved in settings.
 */
function fv_has_any_license(): bool {

	return ( fv_has_license_1() || fv_has_license_2() );
}

/**
 * Gets license key of the first available activated license from the settings.
 *
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return string License key of the first license in settings.
 */
function fv_get_any_license_key( bool $refresh = false ): string {
	return fv_has_license_1()
		 ? fv_get_license_key( $refresh )
		 : fv_get_license_key_2( $refresh );
}

/**
 * Gets domain-id of the first available activated license from the settings.
 *
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return string Domain-id of the first license in settings.
 */
function fv_get_any_license_domain_id( bool $refresh = false ): string {
	return fv_has_license_1()
		 ? fv_get_license_domain_id( $refresh )
		 : fv_get_license_domain_id_2( $refresh );
}

/**
 * Gets domain-id connected to a given license_key.
 *
 * @param string $license_key License key of which we need the domain_id.
 * @param boolean $refresh set tot true to refresh the statically saved options.
  * @return string Domain-id belonging to $license_key, of '' if $license_key is not a registered license key.
 */
function fv_get_license_key_domain_id( string $licence_key, bool $refresh = false ): string {
	if ( ! fv_is_active_license_key( $licence_key, $refresh = false ) ) {
		return '';
	}
	if ( $licence_key === fv_get_license_key( $licence_key, $refresh ) ) {
		return fv_get_license_domain_id( $refresh );
	}
	return fv_get_license_domain_id_2( $refresh );
}

/**
 * Is the first license actived?
 *
 * @return bool True if the first license is activated and saved in settings.
 */
function fv_has_license_1(): bool {
	return ( ! empty( fv_get_license_key() ) && ! empty( fv_get_license_domain_id() ) );
}

/**
 * Is the second license actived?
 *
 * @return bool True if the second license is activated and saved in settings.
 */
function fv_has_license_2(): bool {
	return ( ! empty( fv_get_license_key_2() ) && ! empty( fv_get_license_domain_id_2() ) );
}

/**
 * Delete the first license from the options.
 *
 * @return void
 */
function fv_forget_license_1() {
	fv_delete_option( '_data_ls_key_no_id_vf' );
	fv_delete_option( '_ls_domain_sp_id_vf' );
	fv_delete_option( '_ls_d_sf' );
}

/**
 * Delete the second license from the options.
 *
 * @return void
 */
function fv_forget_license_2() {
	fv_delete_option( '_data_ls_key_no_id_vf_2' );
	fv_delete_option( '_ls_domain_sp_id_vf_2' );
	fv_delete_option( '_ls_d_sf_2' );
}

/**
 * Are all license_data elements present and filled?
 *
 * @param array $license_data {
 *     @type string 'license-key' License key (returned by api as 'l_dat' attribute).
 *     @type string 'domain-id'   Domain id (returned by api as 'data_security_dom' attribute).
 *     @type string '_ls_d_sf'    Returned by api as 'ld_dat' attribute.
 * }
 * @return bool True when all elements are filled, otherwise false.
 */
function fv_is_license_complete( array $license_data ) {
	return ( ! empty( $license_data['license-key'] )
		&&   ! empty( $license_data['domain-id'] )
		&&   ! empty( $license_data['_ls_d_sf'] ) );
}

/**
 * Save licence data in options.
 *
 * @param array $license_data {
 *     @type string 'license-key' License key (returned by api as 'l_dat' attribute).
 *     @type string 'domain-id'   Domain id (returned by api as 'data_security_dom' attribute).
 *     @type string '_ls_d_sf'    Returned by api as 'ld_dat' attribute.
 * }
 * @return bool false when data incomplete, otherwise true.
 */
function fv_save_license( array $license_data ) {

	if ( ! fv_is_license_complete( $license_data ) ) {
		return false;
	}

	if ( fv_has_license_1() && fv_has_license_2() ) {
		return false;
	}

	if ( fv_has_license_1() ) {
		return fv_save_license_2( $license_data );
	}

	return fv_save_license_1( $license_data );
}

/**
 * Save licence data in first license options.
 *
 * @param array $license_data {
 *     @type string 'license-key' License key (returned by api as 'l_dat' attribute).
 *     @type string 'domain-id'   Domain id (returned by api as 'data_security_dom' attribute).
 *     @type string '_ls_d_sf'    Returned by api as 'ld_dat' attribute.
 * }
 * @return bool false when data incomplete, otherwise true.
 */
function fv_save_license_1( array $license_data ) {

	if ( ! fv_is_license_complete( $license_data ) ) {
		return false;
	}

	fv_set_option( '_data_ls_key_no_id_vf', $license_data['license-key'] );
	fv_set_option( '_ls_domain_sp_id_vf',   $license_data['domain-id'] );
	fv_set_option( '_ls_d_sf',              $license_data['_ls_d_sf'] );

	return true;
}

/**
 * Save licence data in first license options.
 *
 * @param array $license_data {
 *     @type string 'license-key' License key (returned by api as 'l_dat' attribute).
 *     @type string 'domain-id'   Domain id (returned by api as 'data_security_dom' attribute).
 *     @type string '_ls_d_sf'    Returned by api as 'ld_dat' attribute.
 * }
 * @return bool false when data incomplete, otherwise true.
 */
function fv_save_license_2( array $license_data ) {

	if ( ! fv_is_license_complete( $license_data ) ) {
		return false;
	}

	fv_set_option( '_data_ls_key_no_id_vf_2', $license_data['license-key'] );
	fv_set_option( '_ls_domain_sp_id_vf_2',   $license_data['domain-id'] );
	fv_set_option( '_ls_d_sf_2',              $license_data['_ls_d_sf'] );

	return true;
}

/**
 * Determine message, to display when a button form is succesfully processed.
 *
 * @param string $context 'plugins' or 'themes'
 * @return string
 */
function fv_get_succes_message( string $context = 'plugins' ): string {

    if ( isset( $_GET['force'] ) && 'success' == $_GET['force'] ) {
        return "Force update for {$context} run successfully!";
    }
    if ( isset( $_GET['rollback'] ) && 'success' == $_GET['rollback'] ) {
        return 'Rollback run successfully!';
    }
    if ( isset( $_GET['instant'] ) && 'success' == $_GET['instant'] ) {
        return 'Instant update run successfully!';
    }
    return '';
}

/**
 * Determines the slug of a plugin for use in Festinger Vault.
 *
 * @param string $basename Plugin basename.
 * @return string|false
 */
function fv_get_slug( string $basename ): string|false {

	if ( empty( $basename ) ) {
		// basename required and should not be empty.
		return false;
	}

	/**
	 * Legacy code taking textdomain as fv_slug
	 * Problem: textdomain if far from unique as identifier.
	 *
	 * WordPress will always add a texdomain to the plugin_data
	 * even if the plugin itself doesn't define that.
	 * It will just use the basename to build a textdomain.
	 *
	 * Only if plugin isn't found, will the textdomain be empty.
	 */
	$plugin = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $basename );

	if ( ! empty( $plugin['TextDomain'] ) ) {
		return $plugin['TextDomain'];
	}

	// We could even stop here and return false,
	// since we know the basename is not of any installed plugin.

	$fv_slug  = explode( '/', $basename, )[0];

	// Remove .php in case the plugin was just one file in the plugins directory
	return str_remove_suffix( $fv_slug, '.php' );
}

/**
 * Determines the slug of a plugin for use in Festinger Vault.
 *
 * @param string $basename Plugin basename or dir slug.
 * @return string|false
 */
function fv_get_plugin_dir_slug( string $basename ): string|false {

	if ( empty( $basename ) ) {
		// basename required and should not be empty.
		return false;
	}

	$dir  = explode( '/', $basename, )[0];

	// Single file plugin, so no dir.
	if ( false !== strpos( $dir, '.php') ) {
		return '';
	}

	return $dir;
}

/**
 * Does plugin basename point to a single php file?
 *
 * @param string $basename Plugin basename.
 * @return bool true if basename doesn't contain a dir separator ('/') and does contain '.php'
 */
function fv_is_single_file_plugin( string $basename ): bool {

	if ( empty( $basename ) ) {
		// basename required and should not be empty.
		return false;
	}

	if ( false === strpos( $basename, '/')
	&&   false !== strpos( $basename, '.php') ) {
		return true;
	}

	return false;
}


/**
 * Remove duplicate plugins in the result array returned by the api.
 *
 * @param array $fv_plugins An array with data of plugins returned by the FV API per plugin a stdClass object with slug, version, dl_link and pkg_str.
 * @return array An associated array with only the data of the newest plugin versions from $fv_plugins.
 *               Note: the plugin data is converted from stdClass to an array.
 */
function fv_remove_duplicate_plugins( array $fv_plugins ): array {
	return remove_duplicates_from_fv_req_array( $fv_plugins );
}

/**
 * Remove duplicate themes in the result array returned by the api.
 *
 * @param array $fv_themes An array with data of themes returned by the FV API per theme a stdClass object with slug, version, dl_link and pkg_str.
 * @return array An associated array with only the data of the newest theme versions from $fv_themes.
 *               Note: the theme data is converted from stdClass to an array.
 */
function fv_remove_duplicate_themes( array $fv_themes ): array {
	return remove_duplicates_from_fv_req_array( $fv_themes );
}

/**
 * Take a result array (can be themes or plugins) of the api.
 * And make sure only there are only unique slugs.
 * Always remember only the highest version and remove all others.
 *
 * @param array $arrays An array with data of themes or plugins returned by the FV API
 *                      expects array item to be stdClass object with slug, version, dl_link and pkg_str.
 * @return array $arrays without duplicates (with slug as key) and stdClasses converted to arrays.
 */
function remove_duplicates_from_fv_req_array( array $arrays ) : array {

	$uniq = array();
	foreach( $arrays as $a ) {

		if ( ! isset( $a->slug )
        ||   ! isset( $a->version ) ) {
			// Skip incomplete $a
            continue;
        }

		if ( isset( $uniq[ $a->slug ] )
        && ! version_compare( $a->version, $uniq[ $a->slug ]['version'], '>') ) {
            // skip lower version duplicates
			continue;
        }

		$uniq[ $a->slug ] = (array) $a;
    }
    return $uniq;
}

/**
 * Adds relevant data of current plugin to $fv_plugins
 * and makes plugin basename the array key (for better comparison).
 *
 * @param array $fv_plugins An associated array of plugins with $fv_slug as key and a stdClass object with slug, version, dl_link and pkg_str as returned by api.
 * @return array A copy of $fv_plugins but now with the plugins basename as key.
 */
function fv_add_current_plugins_data( array $fv_plugins ): array {

    $fv_plugins_new = array();

    foreach( fv_get_plugins() as $basename => $plugin_data ) {

        // Festinger Vault id's plugins with a shortened slug than WordPress.
        $fv_slug = fv_get_slug( $basename );
        if ( ! $fv_slug ) {
            continue;
        }
        if ( ! isset( $fv_plugins[ $fv_slug ] ) ) {
            // No such plugin in Festinger Vault
            continue;
        }
		$fv_plugins_new[ $basename ]                      = $fv_plugins[ $fv_slug ];
		$fv_plugins_new[ $basename ]['name']              = $plugin_data['Name'];
		$fv_plugins_new[ $basename ]['description']       = $plugin_data['Description'];
		$fv_plugins_new[ $basename ]['installed-version'] = $plugin_data['Version'];
    }

	return $fv_plugins_new;
}

/**
 * Adds relevant data of current plugin to $fv_plugins
 * and makes plugin basename the array key (for better comparison).
 *
 * @param array $fv_plugins An associated array of plugins with $fv_slug as key and a stdClass object with slug, version, dl_link and pkg_str as returned by api.
 * @return array A copy of $fv_plugins but now with the plugins basename as key.
 */
function fv_add_current_themes_data( array $themes ): array {

	$themes_new = array();

	foreach( fv_get_themes() as $stylesheet => $theme ) {

		$fv_slug = fv_sanitize_theme_slug( $stylesheet );

		if ( isset( $themes[ $fv_slug ] ) ) {
			$themes_new[ $stylesheet ]                      = $themes[ $fv_slug ];
			$themes_new[ $stylesheet ]['name']              = $theme->Name;
			$themes_new[ $stylesheet ]['description']       = $theme->Description;
			$themes_new[ $stylesheet ]['installed-version'] = $theme->Version;
		}
    }

	return $themes_new;
}

/**
 * Removes plugins that are not an update
 * (= newer than the currently installed version).
 *
 * Builds on the result of fv_add_current_plugins_data(),
 * so the array key should already be the plugins basename.
 *
 * @param array $plugins An associated array of plugins with the plugins basename as key.
 * @return array An array containing only those $plugins that have a newer version in the Vault.
 */
function fv_get_plugin_updates( array $plugins ): array {
	return fv_get_updates( $plugins );
}

/**
 * Removes themes that are not an update
 * (= newer than the currently installed version).
 *
 * Builds on the result of fv_add_current_theme_data(),
 * so the array key should already be the themes stylesheet.
 *
 * @param array $themes An associated array of themes with the theme stylesheet as key.
 * @return array An array containing only those $themes that have a newer version in the Vault.
 * 				 The theme-info is not copied in the result, since only the key-value will be checked.
 */
function fv_get_theme_updates( array $themes ): array {
	return fv_get_updates( $themes );
}

/**
 * Build an array containing keys of $arrays
 * who's items 'version' contains a higher version
 * than 'installed-version'
 *
 * contains logic that is used by:
 * fv_add_current_plugin_data();
 * and
 * fv_add_current_theme_data();
 *
 * @param array $arrays An associated array of associated arrays, with at least the keys 'version' and 'installed-version'.
 * @return array An array containing the keys of only those arrays-items that contain a newer version than the installed-version.
 */
function fv_get_updates( array $arrays ): array {

	$updates = array();

    foreach( $arrays as $key => $details ) {
		/**
		 * Prevent false positive when '.0' is added by fv at the end.
		 */
		if ( $details['version'] !== $details['installed-version']
		&&   str_ends_with( $details['version'], '.0' ) ) {
			$details['version'] = substr( $details['version'], 0, -2 );
		}
		// Skip if not a newer version
		if ( version_compare( $details['version'], $details['installed-version'], '<=' ) ) {
			continue;
		}
		$updates[ $key ] = true;
    }
	return $updates;
}

/**
 * Translates a plugins package type to a human readable string.
 *
 * @param string $pkg_str should be either '0' or '1'
 * @return string
 */
function fv_get_package_type( string $pkg_str ): string {
    switch ( $pkg_str ) {
        case '1':
            return 'Onetime';
            break;

        case '0':
            return 'Recurring';
            break;

        default:
        break;

    }
    return 'Unknown';
}

/**
 * Gets an array of installed plugins formatted for the FV API.
 *
 * @return array
 */
function fv_get_installed_plugins_for_api_request(): array {

	$plugins = fv_get_plugins();
	if ( ! $plugins ) {
		return array();
	}

	$fv_api_plugins = array();

	foreach ( $plugins as $basename => $plugin ) {

		$fv_api_plugins[] = [
			'slug'    => fv_get_slug( $basename ),
			'version' => fv_esc_version( $plugin['Version'] ),
			'dl_link' => ''
		];
	}
	return $fv_api_plugins;
}

/**
 * Gets an array of installed plugins with auto-update enabled formatted for the FV API.
 *
 * @return array
 */
function fv_get_auto_update_plugins_for_api_request(): array{

	$plugins = fv_get_plugins();
	if ( ! $plugins ) {
		return array();
	}

	$fv_api_plugins = array();

	foreach ( $plugins as $basename => $plugin ) {

		$slug = fv_get_slug( $basename );

		if ( fv_should_auto_update_plugin( $slug ) ) {
			$fv_api_plugins[] = [
				'slug'    => $slug,
				'version' => fv_esc_version( $plugin['Version'] ),
				'dl_link' => ''
			];
		}
	}

	return $fv_api_plugins;
}

/**
 * Gets an array of installed themes formatted for the FV API.
 *
 * @return array
 */
function fv_get_installed_themes_for_api_request(): array {

	$themes = fv_get_themes();
	if ( ! $themes ) {
		return array();
	}

	$fv_api_themes = array();

	foreach ( $themes as $theme ) {

		$get_theme_slug = fv_get_theme_slug( $theme );

		$fv_api_themes[] = [
			'slug'    => $get_theme_slug,
			'version' => $theme->Version,
			'dl_link' => ''
		];

	}

	return $fv_api_themes;
}

/**
 * Gets an array of installed themes  with auto-update enabled formatted for the FV API.
 *
 * @return array
 */
function fv_get_auto_update_themes_for_api_request() : array {

	$themes = fv_get_themes();
	if ( ! $themes ) {
		return array();
	}

	$fv_api_themes = array();

	foreach( $themes as $theme ) {

		$slug = fv_get_theme_slug( $theme );

		if ( fv_should_auto_update_theme( $slug ) ) {
			$fv_api_themes[] = [
				'slug'    => $slug,
				'version' => $theme->Version,
				'dl_link' => ''
			];
		}
	}
	return $fv_api_themes;
}

function fv_active_license( string $status ) : bool {
	return 'valid' === $status;
}

function fv_license_found( string $license_validation ) : bool {
	return ! fv_license_not_found( $license_validation );
}

function fv_license_not_found( string $license_validation ) : bool {
	return 'notfound' === $license_validation;
}

function fv_domain_blocked( string $result ) : bool {
	return 'domainblocked' === $result;
}

function fv_license_failed( string $result ) : bool {
	return in_array( $result, array( 'failed', 'no-license' ), true );
}

function fv_api_call_failed( string $result ) : bool {
	return in_array( $result, array( 'domainblocked', 'failed', 'no-license' ) );
}

/**
 * Process a submitted white label settings form
 *
 * @return void
 */
function fv_do_white_label_settings_form() {

	$options = fv_get_white_label_option_keys('post');

	foreach ( $options as $option => $query_var ) {

		if ( isset( $_POST[ $query_var ] ) ) {
			fv_set_option( $option, htmlspecialchars( $_POST[ $query_var ] ) );
			continue;
		}
		if ( $option !== fv_get_white_label_enable_option_key() ) {
			fv_delete_option( $option );
		}
	}
	if ( fv_should_white_label() ) {
		// if white labeling is active redirect to plugins Vault
		wp_redirect( admin_url( 'admin.php?page=festinger-vault' ) );
		exit;
	}
	// otherwise stay in settings page.
	wp_redirect( admin_url( 'admin.php?page=festinger-vault-settings' ) );
}

/**
 * Process a submitted block admin notices settings form
 *
 * @return void
 */
function fv_do_admin_notices_settings_form() {

	$options = array(
		// Setting: Block only dismissable admin notices
		'fv_hide_dismissable_admin_notices' => 'fv_hide_dismissable_admin_notices',
		// Setting: Block All admin notices
		'fv_hide_all_admin_notices' => 'fv_hide_all_admin_notices',
	);

	foreach ( $options as $option => $query_var ) {
		if ( empty( $_POST[ $query_var ] ) ) {
			fv_delete_option( $option );
		} else {
			fv_set_option( $option, $_POST[ $query_var ] );
		}
	}
}

/**
 * Initiates all plugins forced updates.
 *
 * @return void
 */
function fv_do_plugins_force_update_now_button_form() {

	fv_auto_update_download( 'plugin' );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&force=success' ) );
}

/**
 * Initiates all themes forced updates.
 *
 * @return void
 */
function fv_do_themes_force_update_now_button_form() {

	fv_auto_update_download( 'theme' );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&force=success' ) );
}

/**
 * Initiates plugins instant auto updates.
 *
 * @return void
 */
function fv_do_plugins_instant_update_all_button_form() {

	fv_auto_update_download_instant( 'plugin' );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&instant=success' ) );
}

/**
 * Initiates themes instant auto updates.
 *
 * @return void
 */
function fv_do_themes_instant_update_all_button_form() {

	fv_auto_update_download_instant( 'theme' );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&instant=success' ) );
}

/**
 * Processes the force update single plugin form.
 *
 * @return void
 */
function fv_do_single_plugin_force_update_button_form() {

	fv_do_single_plugin_forced_update( array(
		'type'    => 'plugin',
		'name'    => isset( $_POST['plugin_name'] ) ? $_POST['plugin_name'] : NULL,
		'slug'    => isset( $_POST['slug'] )        ? $_POST['slug']        : NULL,
		'version' => isset( $_POST['version'] )     ? $_POST['version']     : NULL,
	));
}

/**
 * Undocumented function
 *
 * @param array $plugin_data
 * @return void
 */
function fv_do_single_plugin_forced_update( $plugin_data ) {

	fv_auto_update_download( 'plugin', $plugin_data );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&force=success' ) );
}

/**
 * Processes the force update single plugin form.
 *
 * @return void
 */
function fv_do_single_theme_update_button_form() {

	fv_do_single_theme_forced_update( array(
		'type'    => 'theme',
		'name'    => isset( $_POST['theme_name'] ) ? $_POST['theme_name'] : NULL,
		'slug'    => isset( $_POST['slug'] )       ? $_POST['slug']       : NULL,
		'version' => isset( $_POST['version'] )    ? $_POST['version']    : NULL,
	));
}

/**
 * Initiate a single theme's instant update.
 *
 * @param array $theme_data
 * @return void
 */
function fv_do_single_theme_forced_update( $theme_data ) {

	fv_auto_update_download( 'theme', $theme_data );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&force=success' ) );
}

/**
 * Should all admin notices be hidden?
 *
 * @return boolean True if 'fv_hide_all_admin_notices' option is set.
 */
function fv_should_hide_all_admin_notices() : bool {
	return (bool) get_option( 'fv_hide_all_admin_notices' );
}

/**
 * Should dismissable admin notices be hidden?
 *
 * @return boolean True if 'fv_hide_dismissable_admin_notices' option is set.
 */
function fv_should_hide_dismissable_admin_notices() : bool {
	return (bool) get_option( 'fv_hide_dismissable_admin_notices' );
}

/**
 * Hide dismissable notices only.
 *
 * @return void
 */
function fv_hide_dismissable_admin_notices() {
	echo '
		<style>
			.is-dismissible {
				display: none !important;
			}
		</style>';
}

/**
 * Hide all admin notices.
 *
 * @return void
 */
function fv_hide_all_admin_notices() : void {

	echo '
		<style>
			.wp-core-ui .notice{
				display: none !important;
			}
		</style>';

	// The rest of this function is not used at the moment, so commented out for reference.

	// global $wp_filter;
	// if ( is_user_admin() ) {
	// 	if ( isset( $wp_filter['user_admin_notices'] ) ) {
	// 	}
	// } elseif( isset( $wp_filter['admin_notices'] ) ) {
	// }
}

/**
 * Remove WP-Rocket admin notices.
 */
function fv_hide_wp_rocket_warnings() : void {
	remove_action( 'admin_notices', 'rocket_warning_htaccess_permissions' );
	remove_action( 'admin_notices', 'rocket_warning_config_dir_permissions' );
}

/**
 * Reset the white label switch to not white label the plugin.
 *
 * @return void
 */
function fv_forget_white_label_switch() : void {
	fv_delete_option( 'wl_fv_plugin_wl_enable' );
}

/**
 * Resets the auto-update switches for plugins and themes.
 *
 * @return void
 */
function fv_forget_auto_update_lists() : void {
	fv_delete_option( 'fv_plugins_auto_update_list' );
	fv_delete_option( 'fv_themes_auto_update_list' );
}

/**
 * Just a wrapper around delete_option that
 * includes first checking if the option exists.
 *
 * @param string $option
 * @return boolean
 */
function fv_delete_option( string $option ) : bool {
	if ( false !== get_option( $option ) ) {
		return delete_option( $option );
	}
	return true;
}

/**
 * Adds or updates an option.
 *
 * @param string $option The name of the option.
 * @param mixed $value The value of the option.
 * @param string|bool $autoload Should WordPress autoload the option?
 * @return boolean The result of the add_option or update_option call.
 */
function fv_set_option( string $option, mixed $value = '', string|bool $autoload = null ): bool {

	if ( false !== get_option( $option ) ) {
		if ( null === $autoload ) {
			return update_option( $option, $value );
		}
		return update_option( $option, $value, $autoload );
	}

	if ( null === $autoload ) {
		return add_option( $option, $value );
	}
	return add_option( $option, $value, $autoload );
}

/**
 * Checks if a license key is activated in this install.
 *
 * @param string $license_key License key to check.
 * @param boolean $refresh set tot true to refresh the statically saved options.
 * @return boolean True if entered key is one of the keys in options, otherwise false
 */
function fv_is_active_license_key( string $license_key, $refresh = false ) : bool {
	if ( empty( $license_key ) ) {
		return false;
	}

	return in_array( $license_key, array( fv_get_license_key(), fv_get_license_key_2() ), true );
}

/**
 * Save auto update list for plugins in options.
 *
 * @param array $list Slugs of plugins that need to be auto-updated.
 * @return void
 */
function fv_set_plugins_auto_update_list( array $list ) : bool {
	return fv_set_option( 'fv_plugins_auto_update_list', $list );
}

/**
 * Save auto update list for themes in options.
 *
 * @param array $list Slugs of themes that need to be auto-updated.
 * @return void
 */
function fv_set_themes_auto_update_list( array $list ) : bool {
	return fv_set_option( 'fv_themes_auto_update_list', $list );
}

/**
 * Initializes auto update list for plugins and themes in options.
 *
 * @return void
 */
function fv_remove_auto_updates() : void {
	fv_set_option( 'fv_plugins_auto_update_list', array() );
	fv_set_option( 'fv_themes_auto_update_list', array() );
}

/**
 * Translates the license status, as it is returned by the fv api,
 * to a human readable text.
 *
 * @param string $license_status Status as returned by the api.
 * @return string Human readable status.
 */
function fv_get_license_status_text( string $license_status ): string {
    switch ( $license_status ) {
        case 'valid':
            return 'Active';
            break;

        case 'invalid':
            return 'Suspended';
            break;
    }
    return ucfirst( $license_status );
}

/**
 * Calculates the number of hours (one decimal) untill midnight (time zone UTC).
 *
 * @return string
 */
function get_hours_to_midnight() : string {
    date_default_timezone_set( "UTC" );
    $now                 = date( 'Y-m-d H:i:s' );
    $next_day_start      = ( new DateTime( 'tomorrow' ) )->format( 'Y-m-d H:i:s' );
    $hours_to_midnight   = round( ( strtotime( $next_day_start ) - strtotime( $now ) ) / 3600, 1 );
    return (string) $hours_to_midnight;
}

/**
 * A fork of the push-notification FV adds after installation to the Vault page.
 *
 * Adds a script that replaces a div with id='push-notice-container'
 * with a notifaction.
 *
 * The script is adapted for the themes and plugins updates pages.
 *
 * @return void
 */
function fv_print_no_license_push_message() : void {
	?>
	<script type="text/javascript">
        $(document).ready(
            function(){
                var containerdiv = $("#push-notice-container").eq(0);
				console.log(containerdiv);
                containerdiv.replaceWith(
                    "<div class='container-fluid mt-4 push-notification' style='background-color: #201943 !important; padding: 1rem 0 !important; margin: 0 !important;'><div class='push-notification-div' style='background: #222222; box-shadow: #121212 -8px 12px 18px 0px; color: #ff2a9c; padding:5px 10px; margin-bottom:5px;font-family: Arial, Helvetica, sans-serif;font-size:16px;'><i class='fas fa-info'></i> <b class='push-notification-title' style='color:#FFF;'>Activate Your License</b> You're one step away from downloading 25K+ premium WordPress themes and plugins! You can find your license key from your <a href='https://festingervault.com/dashboard' target='_blank'>account's dashboard</a>. If you need any help, please send an email to hello@festingervault.com.</div> </div>"
                );
            }
        );
    </script>
    <?php
}

/**
 * Print buttons and messages in case of domain blocked
 * or license failure.
 *
 * It is build to fit in the plugins and themes updates pages.
 *
 * @param stdClass $fv_api A result object of the remote FV api call.
 * @return void
 */
function fv_print_api_call_failed_notices( stdClass $fv_api ) : void {
	if ( ! isset( $fv_api->result )
    ||   ! fv_api_call_failed( $fv_api->result ) ) {
        return;
    }
    ?>
    <button class="btn btn-sm float-end btn-custom-color btn-danger">
        <?php
        switch ( true ) {
            case fv_domain_blocked( $fv_api->result ):
                echo 'DOMAIN IS BLOCKED';
                break;

            case fv_license_failed( $fv_api->result ):
                echo 'NO ACTIVE LICENSE';
                break;

            default:
                break;
        }
        ?>
    </button>
    <div class="row" style="padding-top:20px; width: 100%; margin-left:auto; margin-right: auto;">
        <div class="alert alert-danger alert-dismissible" role="alert">
            <strong>Whoops!</strong>
            <?php
            switch ( true ) {
                case fv_domain_blocked( $fv_api->result ):
                    echo 'Your domain is blocked';
                    break;

                case fv_license_failed( $fv_api->result ):
                    echo 'No active license was found, please activate a license first';
                    break;

                default:
                    break;
            }
            echo $fv_api->msg ? ": " . $fv_api->msg : '';
            ?>
        </div>
    </div>
	</h4><div id='push-notice-container' class='push-notice-container'></div></div></div></div>
    <?php
}

/**
 * Checks license_status for active status (=1).
 *
 * @param string|integer $license_status
 * @return boolean True if license_status is 1.
 */
function fv_is_license_enabled( string|int $license_status ) : bool {
	return ( 1 != $license_status );
}

/**
 * Is filename a zip-file by extension?
 *
 * @return boolean True when filename ends with ".zip"
 */
function fv_is_zip_file( string $file ) : bool {
	return 'zip' === pathinfo( $file, PATHINFO_EXTENSION );
}

/**
 * In linux hidden files start with a dot.
 *
 * @param string $file Filename.
 * @return boolean True if filename starts with a . (dot).
 */
function fv_is_hidden_file( string $file ) : bool {
	return str_starts_with( pathinfo( $file )['filename'], '.' );
}

/**
 * Download and install a zip-archieve containing plugins and/or themes (as zip-files).
 *
 * @param string $url Download URL to the bulk-zipfile.
 * @param string $file Filename of the bulk-zip-file.
 * @return void
 */
function fv_bulk_install( string $url, string $file ) : void {

	if ( \function_exists( '\DeWittePrins\CoreFunctionality\log' ) ) {
		\DeWittePrins\CoreFunctionality\log(
			array(
				'method' => __METHOD__,
				'filter' => \current_filter(),
				'$url' => $url,
				'$file' => $file,
			)
		);
	}

	// First download a zip-file that contains the installation zip-files of the selected plugins/themes.

	$file_slug        = pathinfo( $file )['filename'];
	$fv_bulk_zip_file = fv_get_upload_dir('bulk-install') . $file_slug . '.zip';

	fv_download(
		url:     $url,
		to_file: $fv_bulk_zip_file
	);

	$zip = new ZipArchive;

	if ( ! file_exists( $fv_bulk_zip_file )
	||   true !== $zip->open( $fv_bulk_zip_file ) ) {
		return;
	}

	// Extract bulk zip-file to bulk extract dir.

	$zip->extractTo( fv_get_upload_dir('bulk-extract') );
	$zip->close();

	// Scan bulk extract dir for plugins/themes zip-files.

	foreach ( scandir( fv_get_upload_dir('bulk-extract') ) as $file ) {

		if ( fv_is_hidden_file( $file )
		|| ! fv_is_zip_file( $file ) ) {
			continue;
		}

		switch ( true ) {
			case false !== strpos( $file, '___theme___' ):
				$type       = 'theme';
				$stylesheet = fv_get_slug_from_bulk_extracted_filename( $file );
				$to_dir     = get_theme_root();
				// Enable auto update by default (so at first install)
				if ( ! fv_theme_slug_is_installed( $stylesheet ) ) {
					fv_enable_auto_update_theme( $stylesheet );
				}
				break;

			case false !== strpos( $file, '___plugin___' ):
				$type   = 'plugin';
				$slug   = fv_get_slug_from_bulk_extracted_filename( $file );
				$to_dir = WP_PLUGIN_DIR;
				// Enable auto update by default (so at first install)
				if ( !empty( $slug ) && ! fv_plugin_slug_is_installed( $slug ) ) {
					fv_enable_auto_update_plugin( $slug );
				}
				break;

			default:
				$type    = '';
				break;
		}

		if ( empty( $type ) ) {
			continue;
		}

		$zip_file = trailingslashit( fv_get_upload_dir('bulk-extract') ) . $file;

		fv_unzip_file_to( file: $zip_file, to: $to_dir );
		fv_delete_file( file: $zip_file );
	}
}

/**
 * Gets the timestamp from the bulk download url.
 *
 * A download link is expected to end with a timestamp followed by .zip.
 * Example:
 * "https://engine.festingervault.com/storage/bundles/1697404806.zip
 *
 * @param [type] $url
 * @return void
 */
function fv_get_timestamp_from_bulk_download_url( $url ) {

	// Remove query strings from the URL
	$url      = strtok( $url, '?' );

	// Find the last '/' character and extract the part behind it
	$last_slash_pos = strrpos( $url, '/' );

	if ( false !== $last_slash_pos ) {
		$filename = substr( $url, $last_slash_pos + 1 );
	} else {
		$filename = $url;
	}

	// Check if it ends with ".zip" and extract the timestamp (digits) directly before .zip
	if ( preg_match( '/(\d+)\.zip$/', $filename, $matches) ) {
		return $matches[1];
	}
	return ''; // No timestamp found.
}

/**
 * Gets slug from in filename.
 *
 * Expects filename to be in camelcase and have the following elements:
 * 1. slug
 * 2. post_id (digits)
 * 3. timestamp (digits)
 * 4. ___theme___.zip or ___plugin___.zip
 *
 * Examples:
 * 'avada-1-1689179390___theme___.zip',
 * 'cartflows-pro-53-1692618258___plugin___.zip',
 * 'rank-math-seo-pro-63-1696535124___plugin___.zip',
 *
 * @param string $filename
 * @return string The slug (first) part of the filename
 */
function fv_get_slug_from_bulk_extracted_filename( string $filename ): string {

   if ( preg_match('/^(.*?)-\d+-\d+___(plugin|theme)___.zip$/', $filename, $matches) ) {
		return $matches[1]; // slug
	}
	return ''; // No slug recognized.
}

/**
 * Is plugin with slug installed?
 *
 * @param string $slug
 * @return boolean
 */
function fv_plugin_slug_is_installed( string $slug ): bool {
	foreach ( fv_get_plugins() as $basename => $plugin ) {
		if ( fv_get_slug( $basename ) === $slug ) {
			return true;
		}
	}
	return false;
}

/**
 * Is theme with (stylesheet) slug installed?
 *
 * @param string $slug
 * @return boolean
 */
function fv_theme_slug_is_installed( string $slug ): bool {
	if ( wp_get_theme( $slug )->exists() ) {
		return true;
	}
	return false;
}

/**
 * Prepare string for use as seach value in an url.
 *
 * The result should be lowercase, only digits and letters, and words separated by + signs.
 * If a colon is in the string, it will be removed with everything after the colon.
 * Every char that is not a letter, digit, hyphen or space will be removed.
 *
 * @param string $str string to transform to a search value.
 * @return string Lowercase, only digits and letters, and words separated by + signs
 */
function fv_str_to_search_query_var( string $str ): string {

    // words to remove
    $words = array( 'plugin', 'version', 'add-on' );

    $is_not_a_slug = ( false !== strpos( $str, ' ' ) );
    $is_valid_slug = is_kebab_case( $str );

    // lowercase for better comparison
    $str = strtolower( $str );

    $str = str_replace_by_space( $str, $words );

    if ( $is_valid_slug ) {
        // this may be a slug,
        // so just replace the - by a space.
        $str = str_replace_by_space( $str, '-' );
    }

    if ( $is_not_a_slug ) {

        $str = str_remove_text_between_parentheses( $str );

        // Remove colon and everything after it.
        $str = str_trim_after( $str, ':' );

        // Remove hyphen and everything after it.
        $str = str_trim_after( $str, '-' );
    }

    // Replace characters that are not spaces, letters, or digits with a space.
    $str = str_letters_digits_and_spaces( $str );

    // remove spaces at the start or end of string
    $str = trim( $str );

    // Replace spaces with "+" signs and ensure no double spaces
    $str = str_spaces_to_plus( $str );

    return $str;
}

// Remove text between parentheses and the parentheses themselves
function str_remove_text_between_parentheses( string $str ): string {
    return preg_replace('/\([^)]*\)/', '', $str);
}

// Remove text after a given token (hyphen, colon, etc.).
function str_trim_after( string $str, string $token ): string {

    if ( empty( $token ) ) {
        return $str;
    }

    $pos = strpos( $str, $token );

    if ( false !== $pos ) {
        // Remove characters after the colon
        $str = substr( $str, 0, $pos );
    }

    return $str;
}

function str_replace_by_space( string $str, string|array $words ): string {
    $words = (array) $words;
    foreach ( $words as $word ) {
        $str = str_replace( $word, ' ', $str );
    }
    return $str;
}

function str_spaces_to_plus( $str ) {
    return preg_replace('/\s+/', '+', $str);
}

function str_letters_digits_and_spaces( $str ) {
    return preg_replace( '/[^a-zA-Z0-9\s]/', ' ', $str );
}

function is_kebab_case( $str ) {
    return 1 === preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $str );
}

/**
 * Fill plugins auto update list with all plugins.
 *
 * @return void
 */
function fv_set_all_plugins_auto_update(): void {

	$plugins = fv_get_plugins();
	if ( ! $plugins ) {
		return;
	}

	$auto_update_plugins = array();
	foreach ( $plugins as $basename => $plugin ) {
		if ( ! fv_should_ignore_plugin( $basename ) ) {
			$auto_update_plugins[ fv_get_slug( $basename ) ] = true;
		}
	}
	fv_set_plugins_auto_update_list( array_keys( $auto_update_plugins ) );
}

function fv_set_all_themes_auto_update(): void {

	$themes = fv_get_themes();
	if ( ! $themes ) {
		return;
	}

	$auto_update_themes = array();
	foreach ( $themes as $stylesheet => $theme ) {
		if ( ! fv_should_ignore_theme( $stylesheet ) ) {
			$auto_update_themes[ $stylesheet ] = true;
		}
	}
	fv_set_themes_auto_update_list( array_keys( $auto_update_themes ) );
}
