
<?php
//ini_set( 'memory_limit', '256' );

/**
 * Plugin Name: Festinger Vault ONDER HANSEN
 * description: Festinger vault - The largest plugin market
 * Version: 4.1.0-h2
 * Author: Festinger Vault
 * License: GPLv2 or later
 * Text Domain: festingervault
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! defined( 'FV_PLUGIN_DIR' ) ) {
	define( 'FV_PLUGIN_DIR', dirname( __FILE__ ) );
}
if ( ! defined( 'FV_PLUGIN_ROOT_PHP' ) ) {
	define( 'FV_PLUGIN_ROOT_PHP', dirname( __FILE__ ) . '/' . basename( __FILE__ ) );
}
if ( ! defined( 'FV_PLUGIN_ABSOLUTE_PATH' ) ) {
	define( 'FV_PLUGIN_ABSOLUTE_PATH', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'FV_REST_API_URL' ) ) {
	define( 'FV_REST_API_URL', 'https://engine.festingervault.com/api/' ); // Add to this base URL to make it specific to your plugin or theme.
}
define( 'FV_PLUGIN_VERSION', \get_plugin_data(__FILE__)['Version'] );

require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
require_once( FV_PLUGIN_DIR . '/functions/ajax_functions.php' );
require_once( FV_PLUGIN_DIR . '/classes/plugin-update-checker.php' );

add_action( 'rest_api_init', function() {
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
} );

/**
 * Callback of Rest Api Route "fv_endpoint/v1/fvforceupdateautoupdate"
 *
 * It looks like this function just builds the list of plugins and themes
 * from Festinger Vault that are installed locally and save it in options.
 *
 * @param WP_REST_Request $request
 * @return string 'succes' or 'failed'
 */
function fv_custom_endpoint_create_auto( $request ) {

	$getLicenseKey    = $request->get_param( 'license_key' );
	$getLicenseStatus = $request->get_param( 'enable_disable' );

	$license_enabled   = 1;
	$requested_plugins = [];
	$requested_themes  = [];

	$all_plugins          = fv_get_plugins();
	if ( ! empty( $all_plugins ) ) {
		foreach ( $all_plugins as $plugin_slug => $values ) {
			$version                = fv_esc_version( $values['Version'] );
			$slug                   = fv_get_slug( $plugin_slug );
			$requested_plugins[] = [
				'slug'    => $slug,
				'version' => $version,
				'dl_link' => ''
			];
		}
	}

	$allThemes = fv_get_themes();
	foreach( $allThemes as $theme ) {
		$get_theme_slug = fv_get_wp_theme_slug( $theme );
		$requested_themes[]=[
			'slug'    => $get_theme_slug,
			'version' => $theme->Version,
			'dl_link' => ''
		];
	}

	$query_base_url = FV_REST_API_URL . 'plugin-theme-updater';
	$query_args     = array(
		'license_key'     => fv_get_license_key(),
		'license_key_2'   => fv_get_license_key_2(),
		'license_d'       => fv_get_license_domain_id(),
		'license_d_2'     => fv_get_license_domain_id_2(),
		'all_plugin_list' => $requested_plugins,
		'all_theme_list'  => $requested_themes,
		'license_pp'      => $_SERVER['REMOTE_ADDR'],
		'license_host'    => $_SERVER['HTTP_HOST'],
		'license_mode'    => 'get_plugins_and_themes_matched_by_vault',
		'license_v'       => FV_PLUGIN_VERSION,
	);
	$query_pl_updater      = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response_pl_updater   = fv_remote_run_query( $query_pl_updater );
	$pluginUpdate_get_data = json_decode( wp_remote_retrieve_body( $response_pl_updater ) );

	// note: if is_wp_error(), can we still trust and use response->plugins and ->themes?

	if ( isset( $pluginUpdate_get_data->result )
	&&   fv_api_call_failed( $pluginUpdate_get_data->result ) ) {
		return ( 'failed' );
	}

	if ( ! $fv_is_active_license_key( $getLicenseKey ) || $license_enabled != $getLicenseStatus ) {
		fv_remove_auto_updates();
		return ( 'success' );
	}

	$remote_plugins_list = [];
	foreach( $pluginUpdate_get_data->plugins as $plugin ) {
		$remote_plugins_list[] = $plugin->slug;
	}
	$remote_themes_list = [];
	foreach( $pluginUpdate_get_data->themes as $theme ) {
		$remote_themes_list[] = $theme->slug;
	}
	fv_set_plugins_auto_update( $remote_plugins_list );
	fv_set_themes_auto_update( $remote_themes_list );

	return ( 'success' );
}

add_action( 'rest_api_init', function() {
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
} );

/**
 * Callback of Rest Api Route "fv_endpoint/v1/fvforceupdate"
 *
 * @param WP_REST_Request $request
 * @return string 'succes' or 'failed'
 */
function fv_custom_endpoint_create( $request ) {

	$_data_all_license_array = [];
	if ( fv_get_license_key() ) {
		array_push( $_data_all_license_array, fv_get_license_key() );
	}
	if ( fv_get_license_key_2() ) {
		array_push( $_data_all_license_array, fv_get_license_key_2() );
	}
	array_push( $_data_all_license_array, '98yiuyiy1861' );

	$get_fv_salt_id = $request->get_param( 'salt_id' );
	$get_fv_salt    = $request->get_param( 'salt' );

	if ( ! empty( $get_fv_salt_id ) && ! empty( $get_fv_salt ) ) {

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
		$response = fv_remote_run_query( $query );
		$response = json_decode( wp_remote_retrieve_body( $response ) );

		$push_update_result  = 1;
		$push_update_message = 'Failed';

		if ( $response->result == 1 && $response->status == 0 ) {

			if ( $response->data_method == 'domain'
			&& $_SERVER['HTTP_HOST'] == $response->domain_name
			&& in_array( $response->license_key, $_data_all_license_array ) ) {

				if ( $response->push_for == 'all' ) {
					fv_auto_update_download();
					$push_update_result  = 1;
					$push_update_message = 'All themes & plugins successfully updated';
				}
				if ( $response->push_for == 'theme' ) {
					fv_auto_update_download( 'theme' );
					$push_update_result  = 1;
					$push_update_message = 'All themes are successfully updated';

				}
				if ( $response->push_for == 'plugin' ) {
					fv_auto_update_download( 'plugin' );
					$push_update_result  = 1;
					$push_update_message = 'All plugins are successfully updated';
				}
			}

			if (  $response->data_method == 'license' && in_array( $response->license_key, $_data_all_license_array ) ) {

				if ( $response->push_for == 'all' ) {
					fv_auto_update_download();
					$push_update_result  = 1;
					$push_update_message = 'All themes & plugins successfully updated';
				}
				if ( $response->push_for == 'theme' ) {
					fv_auto_update_download( 'theme' );
					$push_update_result  = 1;
					$push_update_message = 'All themes are successfully updated';

				}
				if ( $response->push_for == 'plugin' ) {
					fv_auto_update_download( 'plugin' );
					$push_update_result  = 1;
					$push_update_message = 'All plugins are successfully updated';
				}
			}

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
			$query_232     = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
			$response23232 = fv_remote_run_query( $query_232 );
		}

		if ( $response->result == 0 && $response->status == 0 ) {

			$query_base_url = FV_REST_API_URL . 'salt-push-update-result';
			$query_args     = array(
				'salt_id'             => $get_fv_salt_id,
				'salt'                => $get_fv_salt,
				'push_update_status'  => 1,
				'push_update_message' => 'Already updated',
				'license_pp'          => $_SERVER['REMOTE_ADDR'],
				'license_host'        => $_SERVER['HTTP_HOST'],
				'license_mode'        => 'salt_push_update_result',
				'license_v'           => FV_PLUGIN_VERSION,
			 );
			$query_232     = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
			$response23232 = fv_remote_run_query( $query_232 );
		}
	}
}

/**
 * Get the name of a plugin based on the plugin slug.
 *
 * NOTE: can this be removed since it isn't called anymore?
 *
 * @param string $slug A plugins slug.
 * @return string Name of a plugin.
 */
function get_plugin_name_by_slug( $slug ) {

	$all_plugins = get_plugins();
	if ( empty( $all_plugins ) ) {
		return $slug;
	}
	foreach ( $all_plugins as $plugin_basename => $plugin_data ) {
		if ( $slug === fv_get_slug( $plugin_basename ) ) {
			return $values['Name'];
		}
	}
}

/**
 * Initial setup at activation.
 *
 * @return void
 */
function fv_activate() {

	fv_create_upload_dirs();
	fv_forget_white_label_switch();
	fv_forget_auto_update_lists();
}
register_activation_hook( __FILE__, 'fv_activate' );

/**
 * Creates the folder structure for uploading plugins and themes.
 *
 * @return void
 */
function fv_create_upload_dirs() {
	$upload_dir                      = wp_upload_dir();
	$fv_plugin_zip_upload_dir        = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins";
	$fv_plugin_zip_upload_dir_backup = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/backup";
	$fv_theme_zip_upload_dir         = $upload_dir["basedir"] . "/fv_auto_update_directory/themes";
	$fv_theme_zip_upload_dir_backup  = $upload_dir["basedir"] . "/fv_auto_update_directory/themes/backup";
	$files = array(
		array(
			'base' 		=> $fv_plugin_zip_upload_dir,
			'file' 		=> 'index.html',
			'content' 	=> ''
		 ),
		array(
			'base' 		=> $fv_plugin_zip_upload_dir_backup,
			'file' 		=> 'index.html',
			'content' 	=> ''
		 ),
		array(
			'base' 		=> $fv_theme_zip_upload_dir,
			'file' 		=> 'index.html',
			'content' 	=> ''
		 ),
		array(
			'base' 		=> $fv_theme_zip_upload_dir_backup,
			'file' 		=> 'index.html',
			'content' 	=> ''
		 )
	 );
	foreach ( $files as $file ) {
		if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
			if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
				fwrite( $file_handle, $file['content'] );
				fclose( $file_handle );
			}
		}
	}
}

/**
 * Cleans up at deactivation of Festinger Vault plugin.
 *
 * @return void
 */
function fv_deactivation() {

	if ( fv_has_license_1() ) {

		$query_base_url = FV_REST_API_URL . 'license-deactivation';
		$$query_args    = array(
			'license_key'  => fv_get_license_key(),
			'license_d'    => fv_get_license_domain_id(),
			'license_pp'   => $_SERVER['REMOTE_ADDR'],
			'license_host' => $_SERVER['HTTP_HOST'],
			'license_mode' => 'deactivation',
			'license_v'    => FV_PLUGIN_VERSION,
		 );
		$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response = fv_remote_run_query( $query );

		fv_forget_license();
	}

	if ( fv_has_license_2() ) {

		$query_base_url = FV_REST_API_URL . 'license-deactivation';
		$query_args     = array(
			'license_key'  => fv_get_license_key_2(),
			'license_d'    => fv_get_license_domain_id_2(),
			'license_pp'   => $_SERVER['REMOTE_ADDR'],
			'license_host' => $_SERVER['HTTP_HOST'],
			'license_mode' => 'deactivation',
			'license_v'    => FV_PLUGIN_VERSION,
		 );
		$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response = fv_remote_run_query( $query );

		fv_forget_license_2();
	}

	fv_forget_white_label_settings();
}
register_deactivation_hook( __FILE__, 'fv_deactivation' );


$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://update.festingervault.com/fv-updater/index.php?action=get_metadata&slug=festingervault',
	__FILE__,
	'festingervault'
 );

/**
 * Filters the result of the wordpress plugins rest api
 * to change requested info for this plugin.
 *
 * @param false|object|array $obj The result object or array. Default false.
 * @param string $action The type of information being requested from the Plugin Installation API.
 * @param object $arg Plugin API arguments.
 * @return false|object|array
 */
function fv_plugin_check_info( $obj, $action, $arg ) {

	if ( ( $action == 'query_plugins' || $action == 'plugin_information' )
	&& isset( $arg->slug )
	&& $arg->slug === 'festingervault' ) {
		$obj               = new stdClass();
		$obj->slug         = 'festingervault';
		$obj->name         = fv_perhaps_white_label_plugin_name();
		$obj->author       = fv_perhaps_white_label_plugin_author();
		$obj->requires     = '3.0';
		$obj->tested       = '3.3.1';
		$obj->last_updated = '2021-07-13';
		$obj->sections     = array(
			'description' => fv_perhaps_white_label_plugin_description(),
		 );

		return $obj;
	}

	return $obj;
}
add_filter( 'plugins_api', 'fv_plugin_check_info', 20, 3 );

/**
 * Perhaps whitelist this WordPress plugins in admins plugins page.
 *
 * @param array $plugins Array of installed plugins and their data.
 * @return array Filtered array of installed plugins data.
 */
function filter_admin_plugins_page( $plugins ) {

	$key = plugin_basename( FV_PLUGIN_DIR . '/festingervault.php' );

	$plugins[ $key ]['Name']        = fv_perhaps_white_label_plugin_name();
	$plugins[ $key ]['Description'] = fv_perhaps_white_label_plugin_description();

	$plugins[ $key ]['Author']      = fv_perhaps_white_label_plugin_author();
	$plugins[ $key ]['AuthorName']  = fv_perhaps_white_label_plugin_author();

	$plugins[ $key ]['AuthorURI']   = fv_perhaps_white_label_plugin_author_uri();
	$plugins[ $key ]['PluginURI']   = fv_perhaps_white_label_plugin_author_uri();

	return $plugins;
}
add_filter( 'all_plugins', 'filter_admin_plugins_page' );

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
function name_change_wl_fv( $translated_text, $text, $domain ) {
	if ( 'Festinger Vault' == $text ) {
		$translated_text = fv_perhaps_white_label_plugin_name();
	}
	return $translated_text;
}
add_filter( 'gettext', 'name_change_wl_fv', 20, 3 );

/**
 * Checks if user has access and adds plugin pages to the WordPress admin menu's.
 *
 * @return void
 */
function festinger_vault_admin_menu_section() {

	$user_has_access = 0;
	if ( fv_current_user_has_access() ) {
		$user_has_access = 1;
	}

	// QUESTION: Where would this echo be visible
	//           and is it clear to the user that it is about Festinger Vault?
	//           should there be a message when this callback is run
	//           on every admin page just to build the admin menu?
	//           probably is should remain silent and just don't add the menu-pages.
	if ( ! $user_has_access ) {
		echo "Permission denied";
		// wp_redirect( admin_url( './' ) );
		// exit;
		return;
	}

	add_menu_page(
		page_title: fv_perhaps_white_label_plugin_name(),
		menu_title: fv_perhaps_white_label_plugin_name(),
		capability: 'read',
		menu_slug:  'festinger-vault',
		callback:   'render_festinger_vault_page',
		icon_url:   fv_perhaps_white_label_plugin_icon_url(),
		position:   99
	 );

	add_submenu_page(
		parent_slug: 'festinger-vault',
		page_title:  'All Plugins',
		menu_title:  'Vault',
		capability:  'read',
		menu_slug:   'festinger-vault',
		callback:    'render_festinger_vault_page'
	 );

	// Only add Activation page when white labeling is not enabled
	if ( ! fv_should_white_label() ) {

		add_submenu_page(
			parent_slug: 'festinger-vault',
			page_title:  'Activation',
			menu_title:  'Activation',
			capability:  'manage_options',
			menu_slug:   'festinger-vault-activation',
			callback:    'render_festinger_vault_activation_page'
		 );
	}

	add_submenu_page(
		parent_slug: 'festinger-vault',
		page_title:  'Plugin Updates',
		menu_title:  'Plugin Updates',
		capability:  'manage_options',
		menu_slug:   'festinger-vault-updates',
		callback:    'render_festinger_vault_plugin_updates_page'
	 );

	add_submenu_page(
		parent_slug: 'festinger-vault',
		page_title:  'Theme Updates',
		menu_title:  'Theme Updates',
		capability:  'manage_options',
		menu_slug:   'festinger-vault-theme-updates',
		callback:    'render_festinger_vault_theme_updates_page'
	 );

	// Only add History and Settings page when white labeling is not enabled
	if ( ! fv_should_white_label() ) {

		add_submenu_page(
			parent_slug: 'festinger-vault',
			page_title:  'History',
			menu_title:  'History',
			capability:  'manage_options',
			menu_slug:   'festinger-vault-theme-history',
			callback:    'render_festinger_vault_theme_history_page'
		 );

		add_submenu_page(
			parent_slug: 'festinger-vault',
			page_title:  'Settings',
			menu_title:  'Settings',
			capability:  'manage_options',
			menu_slug:   'festinger-vault-settings',
			callback:    'render_festinger_vault_settings_page'
		 );
	}
}
add_action( 'admin_menu', 'festinger_vault_admin_menu_section' );

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
function festinger_vault_admin_styles( $hook ) {

	$current_screen = get_current_screen();

	// bail out if not on a festinger-vault admin page.
	if ( false === strpos( haystack: $current_screen->base, needle: 'festinger-vault' ) ) {
		return;
	}

	wp_enqueue_style( 'pagicss',          'https://pagination.js.org/dist/2.6.0/pagination.css', array(), FV_PLUGIN_VERSION );
	wp_enqueue_style( 'fwv_font_style',   'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css' );
	wp_enqueue_style( 'fv_bootstrap',     'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css', array(), FV_PLUGIN_VERSION );
	wp_enqueue_style( 'fv_festinger_css', FV_PLUGIN_ABSOLUTE_PATH.'assets/css/wp_festinger_vault.css', array(), FV_PLUGIN_VERSION );
	wp_enqueue_style( 'custom-alert-css', '//cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css', array(), FV_PLUGIN_VERSION );
	wp_enqueue_style( 'custom-dt-css',    'https://cdn.datatables.net/1.10.23/css/jquery.dataTables.css', array(), FV_PLUGIN_VERSION );
	wp_enqueue_style( 'roboto-dt-css',    'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap', array(), FV_PLUGIN_VERSION );

	wp_deregister_script( 'jquery' ); // Deregisters the built-in version of jQuery
	wp_register_script( 'jquery',  FV_PLUGIN_ABSOLUTE_PATH.'assets/js/jquery-3.4.1.min.js' , false, FV_PLUGIN_VERSION, true );
	wp_enqueue_script( 'jquery' );

	wp_enqueue_script( 'jquery-cookie', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js', array( 'jquery' ), '1.4.1', true );

	wp_enqueue_script( 'custom-alert-js', FV_PLUGIN_ABSOLUTE_PATH.'assets/js/jquery-confirm.min.js' ,array( 'jquery' ), FV_PLUGIN_VERSION );
	wp_enqueue_script( 'pagi-js', 'https://pagination.js.org/dist/2.6.0/pagination.js' ,array( 'jquery' ), FV_PLUGIN_VERSION );
	wp_enqueue_script( 'pagid-js', FV_PLUGIN_ABSOLUTE_PATH.'assets/js/bootstrap.bundle.min.js' ,array( 'jquery' ), FV_PLUGIN_VERSION );
	wp_enqueue_script( 'dt-js', FV_PLUGIN_ABSOLUTE_PATH.'assets/js/jquery.dataTables.js' ,array( 'jquery' ), FV_PLUGIN_VERSION );
	wp_enqueue_script( 'bootstrap-toggle', 'https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js' ,array( 'jquery' ), FV_PLUGIN_VERSION );

	$show_title_img_fv_link = 1;
	if ( fv_should_white_label()  ) {
		$show_title_img_fv_link = 0;
	}

	wp_enqueue_script( 'script-js', FV_PLUGIN_ABSOLUTE_PATH.'assets/js/scripts.js' ,array( 'jquery' ), FV_PLUGIN_VERSION );

	// passes data from php to js.
	wp_localize_script( 'script-js', 'plugin_ajax_object', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'get_all_active_plugins_js'		 => get_plugin_theme_data( 'active_plugins' ) ,
		'get_all_inactive_plugins_js'	 => get_plugin_theme_data( 'inactive_plugins' ),
		'get_all_active_themes_js'		 => get_plugin_theme_data( 'active_themes' ),
		'get_all_inactive_themes_js'     => get_plugin_theme_data( 'inactive_themes' ),
		'show_title_img_fv_link'     	 => $show_title_img_fv_link,
		'cdl_allow' 					 => get_all_data_return_fresh( 'dllimit' ),
		'get_curr_screen'				 => $current_screen->base
	 ) );
}

/**
 * Performs a data request query, used mosly used for license data and history.
 *
 * @param array $query_args $args to add to the query
 * @return void
 */
function request_data_activation( $query_args ) {
	$query_base_url = FV_REST_API_URL . 'request-data';
	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response       = fv_remote_run_query( $query );
}

add_action( 'admin_enqueue_scripts', 'festinger_vault_admin_styles' );
add_action( 'wp_ajax_fv_activation_ajax', 'fv_activation_ajax' );
add_action( 'wp_ajax_nopriv_fv_activation_ajax', 'fv_activation_ajax' );

/**
 * Activates the plugin.
 *
 * @return void
 */
function fv_activation_ajax() {

	$query_base_url = FV_REST_API_URL . 'license-activation';
	$query_args     = array(
		'license_key'  => $_POST['licenseKeyInput'],
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'activation',
		'license_v'    => FV_PLUGIN_VERSION,
	 );
	$query        = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response     = fv_remote_run_query( $query );
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// save domain and license key in settings.
	fv_save_license( array(
		'license-key' => $license_data->l_dat,
		'domain-id'   => $license_data->data_security_dom,
		'_ls_d_sf'    => $license_data->ld_dat
	));

	request_data_activation([
		'ld_tm'    => $license_data->ld_tm,
		'ld_type'  => 'license_activation',
		'l_dat'    => $license_data->l_dat,
		'ld_dat'   => $_SERVER['HTTP_HOST'],
		'rm_ip'    => $_SERVER['REMOTE_ADDR'],
		'status'   => $license_data->result,
		'req_time' => time(),
		'res'      => '1'
	] );

	echo json_encode( $license_data );
}

add_action( 'wp_ajax_fv_deactivation_ajax', 'fv_deactivation_ajax' );
add_action( 'wp_ajax_nopriv_fv_deactivation_ajax', 'fv_deactivation_ajax' );

/**
 * Deactivate plugin.
 *
 * @return void
 */
function fv_deactivation_ajax() {

	$query_base_url = FV_REST_API_URL . 'license-deactivation';
	$query_args     = array(
		'license_key'  => $_POST['license_key'],
		'license_d'    => $_POST['license_d'],
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'deactivation',
		'license_v'    => FV_PLUGIN_VERSION,
	 );
	$query        = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response     = fv_remote_run_query( $query );
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( $license_data->result == 'success' ) {

		if ( fv_has_license_1() ) {
			fv_forget_license();
		} else {
			fv_forget_license_2();
		}

		request_data_activation([
			'ld_tm'    => $license_data->ld_tm,
			'ld_type'  => 'deactivation',
			'l_dat'    => $license_data->license_key,
			'ld_dat'   => $_SERVER['HTTP_HOST'],
			'rm_ip'    => $_SERVER['REMOTE_ADDR'],
			'status'   => $license_data->result,
			'req_time' => time(),
			'res'      => '1'
		] );
		echo json_encode( $license_data );

	} else {

		request_data_activation([
			'ld_tm'    => $license_data->ld_tm,
			'ld_type'  => 'deactivation',
			'l_dat'    => $license_data->license_key,
			'ld_dat'   => $_SERVER['HTTP_HOST'],
			'rm_ip'    => $_SERVER['REMOTE_ADDR'],
			'status'   => $license_data->msg,
			'req_time' => time(),
			'res'      => '0'
		] );
		echo json_encode( $license_data );
	}
}

add_action( 'wp_ajax_fv_deactivation_ajax_2', 'fv_deactivation_ajax_2' );
add_action( 'wp_ajax_nopriv_fv_deactivation_ajax_2', 'fv_deactivation_ajax_2' );

/**
 *
 * Deactivate plugin.
 *
 * Same as fv_deactivation_ajax(),
 * but favors second activation key in delete_options.
 *
 * @return void
 */
function fv_deactivation_ajax_2() {

	$query_base_url = FV_REST_API_URL . 'license-deactivation';
	$query_args     = array(
		'license_key'  => $_POST['license_key'],
		'license_d'    => $_POST['license_d'],
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'deactivation',
		'license_v'    => FV_PLUGIN_VERSION,
	 );
	$query        = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response     = fv_remote_run_query( $query );
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( $license_data->result == 'success' ) {

		if ( fv_has_license_2() ) {
			fv_forget_license_2();
		} else {
			fv_forget_license();
		}

		request_data_activation([
			'ld_tm'    => $license_data->ld_tm,
			'ld_type'  => 'deactivation',
			'l_dat'    => $license_data->l_dat,
			'ld_dat'   => $_SERVER['HTTP_HOST'],
			'rm_ip'    => $_SERVER['REMOTE_ADDR'],
			'status'   => $license_data->result,
			'req_time' => time(),
			'res'      => '1'
		] );
		echo json_encode( $license_data );

	} else {

		request_data_activation([
			'ld_tm'    => $license_data->ld_tm,
			'ld_type'  => 'deactivation',
			'l_dat'    => $license_data->l_dat,
			'ld_dat'   => $_SERVER['HTTP_HOST'],
			'rm_ip'    => $_SERVER['REMOTE_ADDR'],
			'status'   => $license_data->msg,
			'req_time' => time(),
			'res'      => '0'
		] );
		echo json_encode( $license_data );
	}
}

add_action( 'wp_ajax_fv_search_ajax_data', 'fv_search_ajax_data' );
add_action( 'wp_ajax_nopriv_fv_search_ajax_data', 'fv_search_ajax_data' );

/**
 * Handles Search ajax request.
 *
 * @return void
 */
function fv_search_ajax_data() {

	$starttime               = microtime( true );

	$fv_cache_status = 0;

	$fv_cache_status_server = get_all_data_return_fresh( 'cchsts' );
	$fv_check_cache         = FALSE; //get_transient( '__fv_ca_dt_aa' );

	if ( $fv_cache_status_server == 1 ) {

		$fv_check_cache = get_transient( '__fv_ca_dt_aa' );

		if ( FALSE != $fv_check_cache ) {
			$fv_cache_status = 1;
		}
	}

	$searchedValue = isset( $_POST['ajax_search'] ) ? $_POST['ajax_search'] : '';
	$pagenmber     = isset( $_POST['page'] ) ? $_POST['page'] : '1';

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
	$response     = fv_remote_run_query( $query );
	$license_data = wp_remote_retrieve_body( $response );

	echo( $license_data );

	/*
	$decoded_license_data = json_decode( $license_data );

	if ( $fv_cache_status_server == 1 ) {
		if ( is_array( $decoded_license_data ) && ! empty( $license_data ) ) {
			if ( count( $decoded_license_data ) > 6000 ) {
				delete_transient( '__fv_ca_dt_aa' );
				set_transient( '__fv_ca_dt_aa', $license_data );
			}
		}
	}

	$searchedValueContent_type = isset( $searchedValue['content_type'] ) ? $searchedValue['content_type'] : '';
	if ( $fv_check_cache != FALSE ) {
		$fv_check_cache2 = json_decode( $fv_check_cache );
	}
	$fv_con_tp = '';

	if ( $fv_cache_status_server == 1 ) {
		if ( FALSE != $fv_check_cache ) {
			$fv_cache_status = 1;
		}
	}

	if ( $searchedValueContent_type == 'mylist' ) {
		echo( $license_data );
	} else {

		if ( $fv_cache_status_server == 1 ) {
			$fv_check_cache = json_decode( $fv_check_cache );
		} else {
			$fv_check_cache = json_decode( $license_data );
			$fv_check_cache2 = ( $fv_check_cache );
			$fv_cache_status  = 1;
		}

		if ( $fv_cache_status == 1 ) {
			$searchedValuefilter_type = isset( $searchedValue['filter_type'] ) ? $searchedValue['filter_type'] : '';
			$searchedFiltertype       = ( $searchedValuefilter_type );
			if ( ! empty( $searchedFiltertype ) && $searchedFiltertype != 'all' ) {
				$arrayOfObjects = ( $fv_check_cache );

				$fv_check_cache = array_filter(
					$arrayOfObjects,
					function ( $e ) use ( $searchedFiltertype ) {
						if ( $e->type_slug == $searchedFiltertype ) {
							return $e;
						}
					}
				 );

				$fv_check_cache = ( array_values( $fv_check_cache ) );

			}

			$searchedFilterCategoty = isset( $searchedValue['filter_category'] ) ? $searchedValue['filter_category'] : '';
			if ( ! empty( $searchedFilterCategoty ) && $searchedFilterCategoty != 'all' ) {
				$arrayOfObjects = ( $fv_check_cache );

				$fv_check_cache = array_filter(
					$arrayOfObjects,
					function ( $e ) use ( $searchedFilterCategoty ) {
						if ( $e->category_slug == $searchedFilterCategoty ) {
							return $e;
						}
					}
				 );

				$fv_check_cache = ( array_values( $fv_check_cache ) );
			}

			if ( empty( $searchedFiltertype ) && empty( $searchedFilterCategoty ) ) {
				$fv_check_cache = ( $fv_check_cache2 );
			}

			if ( empty( $searchedValue ) ) {
				echo json_encode( $fv_check_cache );
			} else {

				if ( $searchedValueContent_type == 'popular' ) {
					$fv_con_tp = 'hits';
				}

				if ( $searchedValueContent_type == 'recent' ) {
					$fv_con_tp = 'modified';
				}

				if ( $searchedValueContent_type == 'featured' ) {
					$fv_con_tp = 'featured';
				}

				if ( ! empty( $searchedValueContent_type ) ) {
					$fv_column_arr = array_column( $fv_check_cache, $fv_con_tp );
					array_multisort( $fv_column_arr, SORT_DESC, $fv_check_cache );
				}
			$searchedFilterCategoty = isset( $searchedValue['filter_category'] ) ? $searchedValue['filter_category'] : '';

				$searchedValue = isset( $searchedValue['search_data'] ) ? $searchedValue['search_data'] : '';

				$arrayOfObjects = ( $fv_check_cache );

				$neededObject = array_filter(
					$arrayOfObjects,
					function ( $e ) use ( $searchedValue ) {
						if ( preg_match("/{$searchedValue}/i", $e->title ) ) {
							return $e;
						}
					}
				 );

				echo json_encode(array_values( $neededObject ) );
			}
		} else {
			echo( $license_data );
		}
	}

	$endtime  = microtime( true );
	$duration = $endtime - $starttime; //calculates total time taken
	update_option( '__fc_chk_dur_set', $duration );
	*/

}

/**
 * Gets all the license data from remote.
 *
 * @param string $data
 * @return int|array
 */
function get_all_data_return_fresh( $data = null ) {

	get_plugin_theme_data_details( 'all_plugins_themes' );

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
	$query            = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response         = fv_remote_run_query( $query );
	$all_license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( $data == 'dllimit' ) {
		if ( $all_license_data->license_1->license_data->plan_credit_available == 0
		&&   $all_license_data->license_2->license_data->plan_credit_available == 0 ) {
			if ( $all_license_data->license_1->license_data->license_type == 'onetime'
			||   $all_license_data->license_2->license_data->license_type == 'onetime' ) {
				return 1;
			} else {
				return 0;
			}
		} else {
			return 1;
		}
	}

	if ( $data == 'cchsts' ) {
		return $all_license_data->domain_caching;
	}

	return $all_license_data;
}

/**
 * Gets all license data and renders the Activation Page
 *
 * @return void
 */
function render_festinger_vault_activation_page() {

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
	$query            = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response         = fv_remote_run_query( $query );
	$all_license_data = json_decode( wp_remote_retrieve_body( $response ) );

	/* License 1 not found */
	if ( $all_license_data->license_1->license_data->license_key
	&&   $all_license_data->license_1->license_data->license_status == 'notfound' ) {
		fv_forget_license_by_key( $all_license_data->license_1->license_data->license_key );
	}

	/* License 2 not found */
	if ( $all_license_data->license_2->license_data->license_key
	&&   $all_license_data->license_2->license_data->license_status == 'notfound' ) {
		fv_forget_license_by_key( $all_license_data->license_2->license_data->license_key );
	}

	/* White labeling not allowed in license */
	if ( $all_license_data->license_1->options->white_label == 'no'
	&&   $all_license_data->license_2->options->white_label == 'no' ) {
		fv_forget_white_label_settings();
	}

	// render activation page.
	include( FV_PLUGIN_DIR . '/sections/fv_activation.php' );

	get_plugin_theme_data_details( 'all_plugins_themes' );
}

/**
 * Clear all white labeling settings.
 *
 * @return void
 */
function fv_forget_white_label_settings() {

	$options = fv_get_wl_option_keys('all');

	foreach ( $options as $option ) {
		fv_delete_option( $option );
	}
}

/**
 * Gets themes data and renders theme updates page.
 *
 * @return void
 */
function render_festinger_vault_theme_updates_page() {

	$themes           = get_installed_themes_for_api_request();
	$fv_api           = fv_get_remote_themes( $themes );

	$fv_themes        = array();
	$fv_theme_updates = array();

	if ( ! isset( $fv_api->result )
	||   ! in_array( $fv_api->result, array( 'domainblocked', 'failed' ) ) ) {
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
function render_festinger_vault_plugin_updates_page() {

	$plugins = get_installed_plugins_for_api_request();

	/**
	 * Split the requested plugins array and Iterate the api call, when number of plugins would make the query too large.
	 */
	foreach ( fv_array_equal_split( array: $plugins, chunkSize: 75 ) as $plugins ) {

		$fv_api = fv_get_remote_plugins( $plugins );

		if ( isset( $fv_api->result )
		&&   fv_api_call_failed( $fv_api->result ) ) {
			/**
			 * Since either the license failed or the domain is blocked
			 * no further iterations are needed.
			 */
			break;
		}

		/**
		 * collect returned plugins in $fv_plugins
		 */
		if ( empty( $fv_plugins ) ) {
			$fv_plugins = (array) $fv_api->plugins;
		} else {
			$fv_plugins = array_merge( $fv_plugins, (array) $fv_api->plugins );
		}
	}

	/**
	 * Some pre-processing so rendering is easier.
	 */
	$fv_plugins           = fv_remove_duplicate_plugins( $fv_plugins );
	$fv_plugins           = fv_add_current_plugins_data( $fv_plugins );
	$fv_plugin_updates    = fv_get_plugin_updates( $fv_plugins );

	/**
	 * Render the plugin update page.
	 */
	include( FV_PLUGIN_DIR . '/sections/fv_plugin_updates.php' );
}

/**
 * Call FV api for a list of matching plugins from FV.
 *
 * @param array $plugins
 * @return stdClass|false json decoded result of api call.
 */
function fv_get_remote_plugins( array $plugins ) : stdClass|false {
	return fv_get_remote_matches( plugins: $plugins );
}

/**
 * Call FV api for a list of matching themes from FV.
 *
 * @param array $themes
 * @return stdClass|false json decoded result of api call.
 */
function fv_get_remote_themes( array $themes ) : stdClass|false {
	return fv_get_remote_matches( themes: $themes );
}

/**
 * Call FV api for a list of matching plugins and/or themes from FV.
 *
 * @param array $plugins
 * @param array $themes
 * @return stdClass|false json decoded result of api call.
 */
function fv_get_remote_matches( array $plugins = array(), array $themes = array() ) : stdClass|false {

	if ( empty( $plugins ) && empty( $themes ) ) {
		return false;
	}
	if ( ! is_array( $plugins ) || ! is_array( $themes ) ) {
		return false;
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
	$query = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );

	return	json_decode( wp_remote_retrieve_body(
		fv_remote_run_query( $query )
	));
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
function fv_esc_version( $slug ) {
	// just turn this functionality off for now.
	return esc_attr( $slug );

	$version = preg_replace( "/[^0-9.]/", "", $slug );
	return $version;
}

/**
 * Renders a list of installed themes that are also in Festinger Vault
 *
 * Doesn't seem to be called anymore, so dead code.
 *
 * @return void
 */
function activeThemesVersions() {

	$allThemes            = fv_get_themes();
	$activeTheme          = wp_get_theme();

	$requested_plugins = [];
	$requested_themes  = [];

	// build an array of installed plugins

	$all_plugins = fv_get_plugins(); // Filters out any plugin from wordpress.org repo.

	if ( ! empty( $all_plugins ) ) {
		foreach ( $all_plugins as $plugin_slug => $values ) {
			$version                = fv_esc_version( $values['Version'] );
			$slug                   = fv_get_slug( $plugin_slug );
			$requested_plugins[] = [
				'slug'    => $slug,
				'version' => $version,
				'dl_link' => ''
			];

		}
	}

	// Build an array of installed themes.

	$allThemes = fv_get_themes(); // Filters out any plugin from wordpress.org repo.

	foreach( $allThemes as $theme ) {
		$get_theme_slug = fv_get_wp_theme_slug( $theme );
		$requested_themes[] = [
			'slug'    => $get_theme_slug,
			'version' => $theme->Version,
			'dl_link' => ''
		];
	}

	// Call Festinger vault api and match result with installed plugins and themes.

	if ( fv_has_any_license() ) {

		$query_base_url = FV_REST_API_URL . 'plugin-theme-updater';
		$query_args     = array(
			'license_key'     => fv_get_license_key(),
			'license_key_2'   => fv_get_license_key_2(),
			'license_d'       => fv_get_license_domain_id(),
			'license_d_2'     => fv_get_license_domain_id_2(),
			'all_plugin_list' => $requested_plugins,
			'all_theme_list'  => $requested_themes,
			'license_pp'      => $_SERVER['REMOTE_ADDR'],
			'license_host'    => $_SERVER['HTTP_HOST'],
			'license_mode'    => 'get_plugins_and_themes_matched_by_vault',
			'license_v'       => FV_PLUGIN_VERSION,
		);
		$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response          = fv_remote_run_query( $query );
		$license_histories = json_decode( wp_remote_retrieve_body( $response ) );

		// render a list of installed themes that are also in Festinger Vault

		$fvault_themes_slugs = [];
		foreach( $license_histories->themes as $theme ) {
			$fvault_themes_slugs[] = $theme->slug;
		}

		foreach( $allThemes as $theme ) {

			if ( in_array( fv_get_wp_theme_slug( $theme ), $fvault_themes_slugs ) ) {

				$active_theme = '';
				if ( $activeTheme->Name == $theme->Name ) {
					$active_theme = "<span class='badge bg-info'>Active</span>";
				}

				echo '<tr>';
				echo "<td class='plugin_update_width_30'>
						{$theme->name} <br/>
					" . $active_theme . "
				</td>";
				echo "<td class='plugin_update_width_60'>". substr( $theme->Description, 0, 180 )."...
					 </td>";
				echo "<td>{$theme->Version}</td>";
				echo "<td>2.0</td>";
				echo "<td><center><input type='checkbox' checked data-toggle='toggle' data-size='xs'></center></td>";
				echo '</tr>';

			}
		}
	}
}

/**
 * Renders a list of plugins that are both in Festinger Vault and installed.
 *
 * Doesn't seem to be called anywhere.
 *
 * @return void
 */
function activePluginsVersions() {

	$allPlugins           = fv_get_plugins();
	$requested_themes  = [];

	if ( ! empty( $all_plugins ) ) {

		foreach ( $all_plugins as $plugin_slug=>$values ) {

			$version                = fv_esc_version( $values['Version'] );
			$slug                   = fv_get_slug( $plugin_slug );
			$requested_plugins[] = [
				'slug'    => $slug,
				'version' => $version,
				'dl_link' => ''
			];
		}
	}

	$allThemes            = fv_get_themes();
	$requested_plugins = [];
	foreach( $allThemes as $theme ) {
		$get_theme_slug = fv_get_wp_theme_slug( $theme );
		$requested_themes[]=[
			'slug'    => $get_theme_slug,
			'version' => $theme->Version,
			'dl_link' => ''
		];
	}

	/* Find matches of plugins and themes in Festinger Vault */

	if ( fv_has_any_license() ) {

		$query_base_url = FV_REST_API_URL . 'plugin-theme-updater';
		$query_args     = array(
			'license_key'     => fv_get_license_key(),
			'license_key_2'   => fv_get_license_key_2(),
			'license_d'       => fv_get_license_domain_id(),
			'license_d_2'     => fv_get_license_domain_id_2(),
			'all_plugin_list' => $requested_plugins,
			'all_theme_list'  => $requested_themes,
			'license_pp'      => $_SERVER['REMOTE_ADDR'],
			'license_host'    => $_SERVER['HTTP_HOST'],
			'license_mode'    => 'get_plugins_and_themes_matched_by_vault',
			'license_v'       => FV_PLUGIN_VERSION,
		);
		$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response          = fv_remote_run_query( $query );
		$license_histories = json_decode( wp_remote_retrieve_body( $response ) );

		// build an array of plugin slugs from festinger vault results
		$fvault_plugins_slugs = [];
		foreach( $license_histories->plugins as $plugin ) {
			$fvault_plugins_slugs[] = $plugin->slug;
		}

		$activePlugins = get_option( 'active_plugins' );

		foreach( $allPlugins as $key => $value ) {

			if ( in_array( fv_get_slug( $key ) ) ) {

				// installed plugin also in festinger vault.

				if ( in_array( $key, $activePlugins ) ) {

					// plugin is active
					echo '<tr>';
					echo "<td class='plugin_update_width_30'>
							{$value['Name']} <br/>
							<span class='badge bg-success'>Active</span>
							</td>";
					echo "<td class='plugin_update_width_60'>". substr( $value['Description'], 0, 180 )."...
							<br/>Slug: " . fv_get_slug( $key )."
					</td>";
					echo "<td>{$value['Version']}</td>";

					$repoVersion = fv_esc_version( $value['Version'] );

					echo "<td>{$repoVersion}</td>";
					echo "<td><center><input type='checkbox' checked data-toggle='toggle' data-size='xs'></center></td>";
					echo '</tr>';
				} else {

					// plugin is NOT active
					echo '<tr>';
					echo "<td class='plugin_update_width_30'>
							{$value['Name']} <br/>
							<span class='badge bg-danger'>Deactive</span>

							</td>";
					echo "<td class='plugin_update_width_60'>". substr( $value['Description'], 0, 180 )."...

							<br/>Slug: " . fv_get_slug( $key )."

						</td>";
					echo "<td>{$value['Version']}</td>";
					$repoVersion = fv_esc_version( $value['Version'] );
					echo "<td>{$repoVersion}</td>";
					echo "<td><center><input type='checkbox' checked data-toggle='toggle' data-size='xs'></center></td>";
					echo '</tr>';
				}
			}
		}
	}
}

/**
 * Renders the plugins history page.
 *
 * @return void
 */
function render_festinger_vault_theme_history_page() {

	if ( fv_has_any_license() ) {

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
		$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response          = fv_remote_run_query( $query );
		$license_histories = json_decode( wp_remote_retrieve_body( $response ) );

		include( FV_PLUGIN_DIR . '/sections/fv_history.php' );
	} else {
		$license_histories = NULL;

		include( FV_PLUGIN_DIR . '/sections/fv_history.php' );
	}
}

/**
 * Doesn't seem to be called anywhere
 *
 * @return void
 */
function festinger_vault_get_multi_purpose_data() {

	// if ( fv_has_any_license() ) {
	$query_args = FV_REST_API_URL . 'get-multi-purpose-data';
	$api_params = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'get_multi_purpose_data_status',
		'license_v'     => FV_PLUGIN_VERSION,
	);
	$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response          = fv_remote_run_query( $query );
	$license_histories = json_decode( wp_remote_retrieve_body( $response ) );

	return $license_histories;
	//}
}

add_action( 'wp_ajax_fv_license_refill_ajax', 'fv_license_refill_ajax' );
add_action( 'wp_ajax_nopriv_fv_license_refill_ajax', 'fv_license_refill_ajax' );

/**
 * Handles refill_history ajax calls.
 *
 * @return void
 */
function fv_license_refill_ajax() {

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
	$response    = fv_remote_run_query( $query );

	$refill_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( $refill_data->result == 'success' ) {

		request_data_activation([
			'ld_tm'    => $refill_data->ld_tm,
			'ld_type'  => 'refill_history',
			'l_dat'    => $_POST['refill_key'],
			'ld_dat'   => $_SERVER['HTTP_HOST'],
			'rm_ip'    => $_SERVER['REMOTE_ADDR'],
			'status'   => $refill_data->result,
			'req_time' => time(),
			'res'      => '1'
		] );

	} else {

		request_data_activation([
			'ld_tm'    => $refill_data->ld_tm,
			'ld_type'  => 'refill_history',
			'l_dat'    => $_POST['refill_key'],
			'ld_dat'   => $_SERVER['HTTP_HOST'],
			'rm_ip'    => $_SERVER['REMOTE_ADDR'],
			'status'   => $refill_data->msg,
			'req_time' => time(),
			'res'      => '0'
		] );
	}

	echo json_encode( $refill_data );
}

/**
 * Get data about installed themes and plugins.
 *
 * @param string $request_list Type of data requested.
 * @return array Array with themes and/or plugins as requested by $request_list.
 */
function get_plugin_theme_data( $request_list = 'all' ) {

	$request_list_plugins_only = array( 'active_plugins', 'inactive_plugins' );
	$request_list_themes_only  = array( 'active_themes', 'inactive_themes' );

	// collect themes data except when only plugin data is needed.
	if ( ! in_array( $request_list, $request_list_plugins_only ) ) {

		$allThemes           = fv_get_themes();
		$get_active_themes   = [];
		$get_inactive_themes = [];
		$all_themes_list     = [];

		foreach( $allThemes as $theme ) {
			if ( fv_is_active_theme( $theme->Name ) ) {
				$get_active_themes[] = $theme->get_template();
			} else {
				$get_inactive_themes[] = $theme->get_template();
			}
			$all_themes_list[] = $theme->get_template();
		}
	}

	// collect plugins data except when only theme data is needed.
	if ( ! in_array( $request_list, $request_list_themes_only ) ) {

		$allPlugins           = fv_get_plugins();
		$activePlugins        = get_option( 'active_plugins' );
		$all_plugins_list     = [];
		$get_active_plugins   = [];
		$get_inactive_plugins = [];

		foreach( $allPlugins as $plugin_basename => $plugin_data ) {

			$plugin_slug         = fv_get_slug( $plugin_basename );
			$all_plugins_list [] = $plugin_slug;

			if (  in_array( $plugin_basename, $activePlugins ) ) {
				$get_active_plugins[]   = $plugin_slug;
			} else {
				$get_inactive_plugins[] = $plugin_slug;
			}
		}
	}

	switch ( $request_list ) {
		case 'active_plugins':
			return json_encode( $get_active_plugins );
			break;

		case 'inactive_plugins':
			return json_encode( $get_inactive_plugins );
			break;

		case 'active_themes':
			return json_encode( $get_active_themes );
			break;

		case 'inactive_themes':
			return json_encode( $get_inactive_themes );
			break;

		case 'all_plugins_themes':
			return  json_encode( $final_return_list = [
				'plugins' => $all_plugins_list,
				'themes'  => $all_themes_list,
			] );
			break;

		default:
			return  json_encode( $final_return_list = [
				'active_plugins'   => $get_active_plugins,
				'inactive_plugins' => $get_inactive_plugins,
				'active_themes'    => $get_active_themes,
				'inactive_themes'  => $get_inactive_themes,
			] );
			break;
	}
}

/**
 * Returns the content-length of a file from the files header.
 *
 * @param string $file_url
 * @param boolean $formatSize
 * @return int
 */
function fv_curlRemoteFilesize( $file_url, $formatSize = true ) {

	$head = array_change_key_case( get_headers( $file_url, 1 ), CASE_LOWER );

	// content-length of download (in bytes ), read from Content-Length: field
	$clen = isset( $head['content-length'] ) ? $head['content-length'] : 0;

	// cannot retrieve file size, return “-1”
	if ( ! $clen ) {
		return 0;
	}

	// right now $formatSize doesn't seem to have impact on the return value.
	if ( ! $formatSize ) {
		// return size in bytes
		return $clen;
	}

	return $clen;
}

/**
 * Auto update plugins and themes.
 *
 * NOTE: If $single_plugin_theme_slug is filled,
 *       then this function must update a single theme or plugin
 *       otherwise it will perform a bulk force instant update
 *       of all themes or plugins that have the auto-update toggle checked.
 *
 * @param string|null $theme_plugin 'theme', 'plugin', or null
 * @param array $single_plugin_theme_slug {
 *     Optional. Data of a single plugin or theme.
 *
 *     @type string|null $name    Name of the plugin/theme, or null.
 *     @type string      $type    'theme' or 'plugin'.
 *     @type string|null $slug    Plugin/theme slug, or null.
 *     @type string|null $version Plugin/theme version, or null.
 * }
 * @return void
 */
function fv_auto_update_download( $theme_plugin = null, $single_plugin_theme_slug = array() ) {

	$t_dl_fl_sz           = 10;

	$all_plugins          = fv_get_plugins();
	$requested_plugins = [];

	if ( 'plugin' === $theme_plugin
	&& ! empty( $single_plugin_theme_slug )
	&&   count( $single_plugin_theme_slug ) > 0 ) {
		/**
		 * A single plugin update is requested, so plugin data is provided.
		 */
		$requested_plugins[] = [
			'slug'    => ! empty( $single_plugin_theme_slug['slug'] )    ? $single_plugin_theme_slug['slug']    : '',
			'version' => ! empty( $single_plugin_theme_slug['version'] ) ? $single_plugin_theme_slug['version'] : '',
			'dl_link' => ''
		];

	} elseif ( ! empty( $all_plugins ) ) {
		/**
		 * Instant update all is requested,
		 * so first collect data of all installed plugins
		 * that have auto-update toggle checked.
		 */
		foreach ( $all_plugins as $plugin_basename => $plugin_data ) {

			$version   = fv_esc_version( $plugin_data['Version'] );
			$slug      = fv_get_slug( $plugin_basename );

			if ( fv_should_auto_update_plugin( $slug ) ) {
				$requested_plugins[] = [
					'slug'    => $slug,
					'version' => $version,
					'dl_link' => ''
				];
			}
		}
	}

	$allThemes         = fv_get_themes();
	$requested_themes  = [];

	if ( 'theme' === $theme_plugin
	&& ! empty( $single_plugin_theme_slug )
	&&   count( $single_plugin_theme_slug ) > 0 ) {
		/**
		 * A single theme update is requested, so plugin data is provided.
		 */
		$requested_themes[] = [
			'slug'    => ! empty( $single_plugin_theme_slug['slug'] )    ? $single_plugin_theme_slug['slug']    : '',
			'version' => ! empty( $single_plugin_theme_slug['version'] ) ? $single_plugin_theme_slug['version'] : '',
			'dl_link' => ''
		];

	} elseif ( ! empty( $all_plugins ) ) {
		/**
		 * Instant update all is requested,
		 * so first collect data of all installed themes
		 * that have auto-update toggle checked.
		 */
		foreach( $allThemes as $theme ) {

			$get_theme_slug = fv_get_wp_theme_slug( $theme );

			if ( fv_should_auto_update_theme( $get_theme_slug ) ) {
				$requested_themes[] = [
					'slug'    => $get_theme_slug,
					'version' => $theme->Version,
					'dl_link' => ''
				];
			}
		}
	}

	// Get matching plugins and themes remote using FV Api.

	if ( fv_has_any_license() ) {

		$query_base_url = FV_REST_API_URL . 'plugin-theme-updater';
		$query_args     = array(
			'license_key'     => fv_get_license_key(),
			'license_key_2'   => fv_get_license_key_2(),
			'license_d'       => fv_get_license_domain_id(),
			'license_d_2'     => fv_get_license_domain_id_2(),
			'license_pp'      => $_SERVER['REMOTE_ADDR'],
			'license_host'    => $_SERVER['HTTP_HOST'],
			'license_mode'    => 'up_dl_plugs_thms',
			'loadNotAll'      => 'yes',
			'license_v'       => FV_PLUGIN_VERSION,
			'all_plugin_list' => $requested_plugins,
			'all_theme_list'  => $requested_themes,
		);
		$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response          = fv_remote_run_query( $query );
		$license_histories = json_decode( wp_remote_retrieve_body( $response ) );

		// now we need WP_Filesystem to actually perform the update(s).

		require_once( ABSPATH .'/wp-admin/includes/file.php' );

		WP_Filesystem();

		$upload_dir        = wp_upload_dir();

		if ( null == $theme_plugin
		||  'theme' == $theme_plugin ) {

			if ( ! empty( $license_histories->themes ) ) {

				$get_theme_directory=[];

				foreach( $allThemes as $theme ) {
					$get_theme_slug        = fv_get_wp_theme_slug( $theme );
					$get_theme_directory[] = array(
						'dir'     => $theme->get_stylesheet(),
						'slug'    => $get_theme_slug,
						'version' => $theme->Version,
					);
				}

				foreach ( $license_histories->themes as $u ) {
					foreach( $get_theme_directory as $single_th ) {

						if ( $single_th['slug'] == $u->slug
						&& version_compare( $u->version, $single_th['version'], '>' ) ) {

							//start of update

							if ( ! empty( $single_plugin_theme_slug ) ) {
								if ( count( $single_plugin_theme_slug ) > 0
								&& $single_plugin_theme_slug['slug'] == $u->slug ) {

									$pathInfo                = pathinfo( $u->slug );
									$fileName                = $pathInfo['filename'] . '.zip';
									$upload_dir              = wp_upload_dir();
									$fv_theme_zip_upload_dir = $upload_dir["basedir"]."/fv_auto_update_directory/themes/";
									$tmpfile                 = download_url( $u->dl_link, $timeout = 300 );

									if ( is_wp_error( $tmpfile ) ) {

										$chk_fl_dl_sz = fv_curlRemoteFilesize( $u->dl_link );
										if ( $chk_fl_dl_sz > 0 ) {
											$t_dl_fl_sz += $chk_fl_dl_sz;
										}

										// Initialize the cURL session
										$ch = curl_init( $u->dl_link );

										// Use basename() function to return
										// the base name of file
										$file_name = basename( $u->dl_link );

										// Save file into file location
										$save_file_loc = $fv_theme_zip_upload_dir.$fileName;

										// Open file
										$fp = fopen( $save_file_loc, 'wb' );

										// It set an option for a cURL transfer
										curl_setopt( $ch, CURLOPT_FILE, $fp );
										curl_setopt( $ch, CURLOPT_HEADER, 0 );

										// Perform a cURL session
										curl_exec( $ch );

										// Closes a cURL session and frees all resources
										curl_close( $ch );

										// Close file
										fclose( $fp );

									} else {
										copy( $tmpfile, $fv_theme_zip_upload_dir.$fileName );
										unlink( $tmpfile );
									}

									$determine_theme_dir = search_for_plugin_dir_by_slug( $u->slug, $get_theme_directory )['dir'];
									$backup_theme_dir    = $upload_dir["basedir"] . "/fv_auto_update_directory/themes/backup/";
									$get_all_themes      = scandir( $backup_theme_dir );

									foreach( $get_all_themes as $single_theme ) {
										if ( strpos( $single_theme, $u->slug ) !== false ) {
											delete_old_folder( $backup_theme_dir . $single_theme );
										}
									}

									$original_theme_dir             = get_theme_root() . '/' . $determine_theme_dir;
									$fv_theme_zip_upload_dir_backup = $upload_dir["basedir"] . "/fv_auto_update_directory/themes/backup/" . $determine_theme_dir . '-v-' . $single_th['version'];

									if ( is_dir( $original_theme_dir ) ) {
										fv_fs_recurse_copy( $original_theme_dir, $fv_theme_zip_upload_dir_backup ); // copy old version as backup
									}

									$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
									if ( $ext == 'zip' ) {

										$basename = pathinfo( $fileName,  PATHINFO_BASENAME );
										$un       = unzip_file( $fv_theme_zip_upload_dir . '/' . $basename, get_theme_root() );

										$query_base_url = FV_REST_API_URL . 'update-request-load';
										$query_args     = array(
											'license_key'          => fv_get_license_key(),
											'license_key_2'        => fv_get_license_key_2(),
											'license_d'            => fv_get_license_domain_id(),
											'license_d_2'          => fv_get_license_domain_id_2(),
											'plugin_theme_slug'    => $u->slug,
											'plugin_theme_version' => $u->version,
											'license_pp'           => $_SERVER['REMOTE_ADDR'],
											'license_host'         => $_SERVER['HTTP_HOST'],
											'license_mode'         => 'update_request_load',
											'license_v'            => FV_PLUGIN_VERSION,
										);
										$query_dif = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
										$response  = fv_remote_run_query( $query_dif );
										if ( ! is_wp_error( $un ) ) {
											unlink( $fv_theme_zip_upload_dir . '/' . $basename );
										}
									}
									//end of update
								}
							} else {

								if ( get_option( 'fv_themes_auto_update_list' ) == true
								&& in_array( $u->slug, get_option( 'fv_themes_auto_update_list' ) ) ) {

									$pathInfo                = pathinfo( $u->slug );
									$fileName                = $pathInfo['filename'] . '.zip';
									$upload_dir              = wp_upload_dir();
									$fv_theme_zip_upload_dir = $upload_dir["basedir"] . "/fv_auto_update_directory/themes/";
									$tmpfile                 = download_url( $u->dl_link, $timeout = 300 );

									if ( is_wp_error( $tmpfile ) == true ) {

										$chk_fl_dl_sz = fv_curlRemoteFilesize( $u->dl_link );
										if ( $chk_fl_dl_sz > 0 ) {
											$t_dl_fl_sz += $chk_fl_dl_sz;
										}
										// Initialize the cURL session
										$ch = curl_init( $u->dl_link );

										// Use basename() function to return
										// the base name of file
										$file_name = basename( $u->dl_link );

										// Save file into file location
										$save_file_loc = $fv_theme_zip_upload_dir.$fileName;

										// Open file
										$fp = fopen( $save_file_loc, 'wb' );

										// It set an option for a cURL transfer
										curl_setopt( $ch, CURLOPT_FILE, $fp );
										curl_setopt( $ch, CURLOPT_HEADER, 0 );

										// Perform a cURL session
										curl_exec( $ch );

										// Closes a cURL session and frees all resources
										curl_close( $ch );

										// Close file
										fclose( $fp );

									} else {
										copy( $tmpfile, $fv_theme_zip_upload_dir . $fileName );
										unlink( $tmpfile );
									}

									$determine_theme_dir = search_for_plugin_dir_by_slug( $u->slug, $get_theme_directory )['dir'];
									$backup_theme_dir    = $upload_dir["basedir"] . "/fv_auto_update_directory/themes/backup/";
									$get_all_themes      = scandir( $backup_theme_dir );

									foreach( $get_all_themes as $single_theme ) {
										if ( strpos( $single_theme, $u->slug ) !== false ) {
											delete_old_folder( $backup_theme_dir.$single_theme );
										}
									}

									$original_theme_dir             = get_theme_root().'/'.$determine_theme_dir;
									$fv_theme_zip_upload_dir_backup = $upload_dir["basedir"] . "/fv_auto_update_directory/themes/backup/" . $determine_theme_dir . '-v-'. $single_th['version'];

									if ( is_dir( $original_theme_dir ) ) {
										fv_fs_recurse_copy( $original_theme_dir, $fv_theme_zip_upload_dir_backup ); // copy old version as backup
									}

									$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
									if ( $ext == 'zip' ) {

										$basename = pathinfo( $fileName,  PATHINFO_BASENAME );
										$un       = unzip_file( $fv_theme_zip_upload_dir . '/' . $basename, get_theme_root() );

										$query_base_url = FV_REST_API_URL . 'update-request-load';
										$query_args     = array(
											'license_key'          => fv_get_license_key(),
											'license_key_2'        => fv_get_license_key_2(),
											'license_d'            => fv_get_license_domain_id(),
											'license_d_2'          => fv_get_license_domain_id_2(),
											'plugin_theme_slug'    => $u->slug,
											'plugin_theme_version' => $u->version,
											'license_pp'           => $_SERVER['REMOTE_ADDR'],
											'license_host'         => $_SERVER['HTTP_HOST'],
											'license_mode'         => 'update_request_load',
											'license_v'            => FV_PLUGIN_VERSION,
										);
										$query_dif = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
										$response  = fv_remote_run_query( $query_dif );

										if ( ! is_wp_error( $un ) ) {
											unlink( $fv_theme_zip_upload_dir . '/' . $basename );
										}
									}
									//end of update
								}
							}
						}
					}
				}
			}
		}

		if ( $theme_plugin == null || $theme_plugin == 'plugin' ) {

			if ( ! empty( $license_histories->plugins ) ) {

				$get_plugin_directory = [];

				if ( ! empty( $all_plugins ) ) {
					foreach ( $all_plugins as $plugin_basename => $data ) {

						$version = fv_esc_version( $data['Version'] );
						$slug    = fv_get_slug( $plugin_basename );

						$get_plugin_directory[] = [
							'dir' 	 => explode( '/', $plugin_basename )[0],
							'slug'	 => $slug,
							'version'=> $version
						];
					}
				}

				foreach ( $license_histories->plugins as $u ) { // new plugin version

					foreach ( $get_plugin_directory as $single_pl ) { // current plugin version

						// if ( $plugin_has_update( $single_Pl, $u ) ) {
						if ( $single_pl['slug'] == $u->slug
						&& version_compare( $u->version, $single_pl['version'], '>' ) ) {

							//y

							if ( ! empty( $single_plugin_theme_slug ) ) {

								if ( count( $single_plugin_theme_slug ) > 0
								&&   $single_plugin_theme_slug['slug'] == $u->slug ) {
									//start
									$pathInfo                 = pathinfo( $u->slug );
									$fileName                 = $pathInfo['filename'] . '.zip';
									$upload_dir               = wp_upload_dir();
									$fv_plugin_zip_upload_dir = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/";
									$tmpfile                  = download_url( $u->dl_link, $timeout = 300 );

									if ( is_wp_error( $tmpfile ) == true ) {

										// Download via wp wasn't succesfull, now try using cURL.

										$chk_fl_dl_sz = fv_curlRemoteFilesize( $u->dl_link );

										if ( $chk_fl_dl_sz > 0 ) {
											$t_dl_fl_sz += $chk_fl_dl_sz;
										}
										// Initialize the cURL session
										$ch            = curl_init( $u->dl_link );

										$file_name     = basename( $u->dl_link );
										$save_file_loc = $fv_plugin_zip_upload_dir . $fileName;

										// Open file so that cURL can write to it.
										$fp = fopen( $save_file_loc, 'wb' ); // w=write b=binary

										// Set options for a cURL transfer
										curl_setopt( $ch, CURLOPT_FILE, $fp ); // Sets the file that the transfer should be written to. Default: STDOUT (the browser window ).
										curl_setopt( $ch, CURLOPT_HEADER, 0 ); // True to include the header in the output. Here: false

										// Perform a cURL session
										curl_exec( $ch ); // This executes the cURL session that is setup wit curl_init() and curl_setopt()

										// Closes a cURL session and frees all resources
										curl_close( $ch );

										// Close file
										fclose( $fp );

									} else {
										$copy_result   = copy( $tmpfile, $fv_plugin_zip_upload_dir . $fileName );
										$unlink_result = unlink( $tmpfile );
									}

									$determine_plugin_dir            = search_for_plugin_dir_by_slug( $u->slug, $get_plugin_directory )['dir'];
									$original_plugin_dir             = WP_PLUGIN_DIR . '/' . $determine_plugin_dir;
									$fv_plugin_zip_upload_dir_backup = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/backup/" . $determine_plugin_dir;

									if ( is_dir( $original_plugin_dir ) ) {
										fv_fs_recurse_copy( $original_plugin_dir, $fv_plugin_zip_upload_dir_backup ); // copy old version as backup
									}

									$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
									if ( $ext=='zip' ) {
										$basename=pathinfo( $fileName,  PATHINFO_BASENAME );
										if (  is_dir( $original_plugin_dir ) ) {
										}
										$un = unzip_file( $fv_plugin_zip_upload_dir . '/' . $basename, WP_PLUGIN_DIR );

										$query_base_url = FV_REST_API_URL . 'update-request-load';
										$query_args     = array(
											'license_key'          => fv_get_license_key(),
											'license_key_2'        => fv_get_license_key_2(),
											'license_d'            => fv_get_license_domain_id(),
											'license_d_2'          => fv_get_license_domain_id_2(),
											'plugin_theme_slug'    => $u->slug,
											'plugin_theme_version' => $u->version,
											'license_pp'           => $_SERVER['REMOTE_ADDR'],
											'license_host'         => $_SERVER['HTTP_HOST'],
											'license_mode'         => 'update_request_load',
											'license_v'            => FV_PLUGIN_VERSION,
										);
										$query_dif = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
										$res       = fv_remote_run_query( $query_dif );

										if ( !is_wp_error( $un ) ) {
											$unlink_result = unlink( $fv_plugin_zip_upload_dir . '/' . $basename );
										}
									}
									//end of plugin update
									//end
								}
							} else {

								if ( get_option( 'fv_plugin_auto_update_list' ) == true
								&& in_array( $u->slug, get_option( 'fv_plugin_auto_update_list' ) ) ) {
									//start
									$pathInfo                 = pathinfo( $u->slug );
									$fileName                 = $pathInfo['filename'] . '.zip';
									$upload_dir               = wp_upload_dir();
									$fv_plugin_zip_upload_dir = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/";
									$tmpfile                  = download_url( $u->dl_link, $timeout = 300 );

									if ( is_wp_error( $tmpfile ) == true ) {

										$chk_fl_dl_sz = fv_curlRemoteFilesize( $u->dl_link );
										if ( $chk_fl_dl_sz > 0 ) {
											$t_dl_fl_sz += $chk_fl_dl_sz;
										}

										// Initialize the cURL session
										$ch = curl_init( $u->dl_link );

										$file_name = basename( $u->dl_link );

										$save_file_loc = $fv_plugin_zip_upload_dir . $fileName;

										// Open file
										$fp = fopen( $save_file_loc, 'wb' );

										// It set an option for a cURL transfer
										curl_setopt( $ch, CURLOPT_FILE, $fp );
										curl_setopt( $ch, CURLOPT_HEADER, 0 );

										// Perform a cURL session
										curl_exec( $ch );

										// Closes a cURL session and frees all resources
										curl_close( $ch );

										// Close file
										fclose( $fp );

									} else {
										copy( $tmpfile, $fv_plugin_zip_upload_dir . $fileName );
										unlink( $tmpfile );
									}

									$determine_plugin_dir            = search_for_plugin_dir_by_slug( $u->slug, $get_plugin_directory )['dir'];
									$original_plugin_dir             = WP_PLUGIN_DIR . '/' . $determine_plugin_dir;
									$fv_plugin_zip_upload_dir_backup = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/backup/" . $determine_plugin_dir;

									if (  is_dir( $original_plugin_dir ) ) {
										fv_fs_recurse_copy( $original_plugin_dir, $fv_plugin_zip_upload_dir_backup ); // copy old version as backup
									}

									$ext = pathinfo( $fileName, PATHINFO_EXTENSION );

									if ( $ext=='zip' ) {
										$basename = pathinfo( $fileName, PATHINFO_BASENAME );
										if ( is_dir( $original_plugin_dir ) ) {
										}
										$un = unzip_file( $fv_plugin_zip_upload_dir . '/' . $basename, WP_PLUGIN_DIR );

										$query_base_url = FV_REST_API_URL . 'update-request-load';
										$query_args     = array(
											'license_key'          => fv_get_license_key(),
											'license_key_2'        => fv_get_license_key_2(),
											'license_d'            => fv_get_license_domain_id(),
											'license_d_2'          => fv_get_license_domain_id_2(),
											'plugin_theme_slug'    => $u->slug,
											'plugin_theme_version' => $u->version,
											'license_pp'           => $_SERVER['REMOTE_ADDR'],
											'license_host'         => $_SERVER['HTTP_HOST'],
											'license_mode'         => 'update_request_load',
											'license_v'            => FV_PLUGIN_VERSION,
										);
										$query_dif = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
										$res       = fv_remote_run_query( $query_dif );

										if ( ! is_wp_error( $un ) ) {
											unlink( $fv_plugin_zip_upload_dir . '/' . $basename );
										}
									}
									//end of plugin update
									//end
								}
							}
							//x
						}
					}
				}
			}
		}

		$theme_plugins = [
			'themes'  => isset( $license_histories->themes )  ? $license_histories->themes  : [],
			'plugins' => isset( $license_histories->plugins ) ? $license_histories->plugins : []
		];

		if ( isset( $license_histories ) ) {
			request_data_activation([
				'ld_tm'          => $license_histories->ld_tm,
				'ld_type'        => 'up_dl_plugs_thms',
				'l_dat'          => fv_get_license_key(),
				'ld_dat'         => $_SERVER['HTTP_HOST'],
				'rm_ip'          => $_SERVER['REMOTE_ADDR'],
				'status'         => 'executed',
				'req_time'       => time(),
				'res'            => '1',
				'dsz'            => $t_dl_fl_sz,
				'themes_plugins' => $theme_plugins
			] );
		}
	}
}

function fv_auto_update_download_instant( $theme_plugin = null, $single_plugin_theme_slug = array() ) {

	$t_dl_fl_sz        = 10;
	$requested_plugins = [];
	$requested_themes  = [];
	$all_plugins       = fv_get_plugins();

	if ( ! empty( $all_plugins ) ) {

		foreach ( $all_plugins as $plugin_slug => $values ) {
			$version = fv_esc_version( $values['Version'] );
			$slug    = fv_get_slug( $plugin_slug );

			if ( ! empty( $single_plugin_theme_slug ) ) {
				if ( count( $single_plugin_theme_slug ) > 0 ) {
					$requested_plugins[] = [
						'slug'    => $single_plugin_theme_slug['slug'],
						'version' => $single_plugin_theme_slug['version'],
						'dl_link' => ''
					];
				}
			} else {
				// if ( get_option( 'fv_plugin_auto_update_list' ) == true
				// && in_array( $slug, get_option( 'fv_plugin_auto_update_list' ) ) ) {
					$requested_plugins[] = [
						'slug'    => $slug,
						'version' => $version,
						'dl_link' => ''
					];
				//}
			}
		}
	}

	$allThemes = fv_get_themes();
	foreach( $allThemes as $theme ) {
		$get_theme_slug = fv_get_wp_theme_slug( $theme );
		if ( empty( $get_theme_slug ) ) {
			$get_theme_slug = $theme->get( 'TextDomain' );
		}

		if ( ! empty( $single_plugin_theme_slug ) ) {
			if ( count( $single_plugin_theme_slug ) > 0 ) {
				$requested_themes[] = [
					'slug'    => $single_plugin_theme_slug['slug'],
					'version' => $single_plugin_theme_slug['version'],
					'dl_link' => ''
				];
			}
		} else {

			// if ( get_option( 'fv_themes_auto_update_list' ) == true
			// && in_array( $get_theme_slug, get_option( 'fv_themes_auto_update_list' ) ) ) {
				$requested_themes[]=[
					'slug'    => $get_theme_slug,
					'version' => $theme->Version,
					'dl_link' => ''
				];
			//}
		}
	}

	if ( fv_has_any_license() ) {

		$query_base_url = FV_REST_API_URL . 'plugin-theme-updater';
		$query_args     = array(
			'license_key'     => fv_get_license_key(),
			'license_key_2'   => fv_get_license_key_2(),
			'license_d'       => fv_get_license_domain_id(),
			'license_d_2'     => fv_get_license_domain_id_2(),
			'license_pp'      => $_SERVER['REMOTE_ADDR'],
			'license_host'    => $_SERVER['HTTP_HOST'],
			'license_mode'    => 'up_dl_plugs_thms',
			'loadNotAll'      => 'yes',
			'license_v'       => FV_PLUGIN_VERSION,
			'all_plugin_list' => $requested_plugins,
			'all_theme_list'  => $requested_themes,
		);
		$query = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response = wp_remote_post( $query, array( 'timeout' => 200, 'sslverify' => false ) );

		if (is_wp_error( $response ) ) {
			$response = wp_remote_post( $query, array( 'timeout' => 200, 'sslverify' => true ) );
			if ( is_wp_error( $response ) ) {
					echo 'SSLVERIFY ERROR';
			}
		}

		$license_histories = json_decode( wp_remote_retrieve_body( $response ) );

		require_once(ABSPATH .'/wp-admin/includes/file.php' );
		WP_Filesystem();
		$upload_dir      = wp_upload_dir();

		if ( $theme_plugin == null || $theme_plugin == 'theme' ) {

			if ( ! empty( $license_histories->themes ) ) {

				$get_theme_directory=[];

				foreach( $allThemes as $theme ) {
					$get_theme_slug = fv_get_wp_theme_slug( $theme );
					if ( empty( $get_theme_slug ) ) {
						$get_theme_slug = $theme->get( 'TextDomain' );
					}
					$get_theme_directory[]=[
						'dir'     => $theme->get_stylesheet(),
						'slug'    => $get_theme_slug,
						'version' => $theme->Version
					];
				}

				foreach ( $license_histories->themes as $u ) {

					foreach( $get_theme_directory as $single_th ) {
						if ( $single_th['slug'] == $u->slug
						&& version_compare( $u->version, $single_th['version'], '>' ) ) {

							//start of update

							if ( ! empty( $single_plugin_theme_slug ) ) {
								if ( count( $single_plugin_theme_slug ) > 0 && $single_plugin_theme_slug['slug'] == $u->slug ) {

									$pathInfo=pathinfo( $u->slug );
									$fileName=$pathInfo['filename'].'.zip';

									$upload_dir      = wp_upload_dir();
									$fv_theme_zip_upload_dir=$upload_dir["basedir"]."/fv_auto_update_directory/themes/";
									$tmpfile = download_url( $u->dl_link, $timeout = 300 );

									if ( is_wp_error( $tmpfile ) == true ) {
										$chk_fl_dl_sz = fv_curlRemoteFilesize( $u->dl_link );
										if ( $chk_fl_dl_sz > 0 ) {
											$t_dl_fl_sz += $chk_fl_dl_sz;
										}
										// Initialize the cURL session
										$ch = curl_init( $u->dl_link );

										// Use basename() function to return
										// the base name of file
										$file_name = basename( $u->dl_link );

										// Save file into file location
										$save_file_loc = $fv_theme_zip_upload_dir . $fileName;

										// Open file
										$fp = fopen( $save_file_loc, 'wb' );

										// It set an option for a cURL transfer
										curl_setopt( $ch, CURLOPT_FILE, $fp );
										curl_setopt( $ch, CURLOPT_HEADER, 0 );

										// Perform a cURL session
										curl_exec( $ch );

										// Closes a cURL session and frees all resources
										curl_close( $ch );

										// Close file
										fclose( $fp );

									} else {
										copy( $tmpfile, $fv_theme_zip_upload_dir . $fileName );
										unlink( $tmpfile );
									}

									$determine_theme_dir = search_for_plugin_dir_by_slug( $u->slug, $get_theme_directory )['dir'];

									$backup_theme_dir =  $upload_dir["basedir"]."/fv_auto_update_directory/themes/backup/";
									$get_all_themes = scandir( $backup_theme_dir );
									foreach( $get_all_themes as $single_theme ) {
										if ( strpos( $single_theme, $u->slug ) !== false ) {
											delete_old_folder( $backup_theme_dir . $single_theme );
										}
									}

									$original_theme_dir             = get_theme_root() . '/' . $determine_theme_dir;
									$fv_theme_zip_upload_dir_backup = $upload_dir["basedir"] . "/fv_auto_update_directory/themes/backup/" . $determine_theme_dir . '-v-' . $single_th['version'];

									if ( is_dir( $original_theme_dir ) ) {
										fv_fs_recurse_copy( $original_theme_dir, $fv_theme_zip_upload_dir_backup ); // copy old version as backup
									}

									$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
									if ( $ext=='zip' ) {
										$basename=pathinfo( $fileName,  PATHINFO_BASENAME );
										$un = unzip_file( $fv_theme_zip_upload_dir . '/' . $basename,get_theme_root() );

										$query_base_url = FV_REST_API_URL . 'update-request-load';
										$query_args     = array(
											'license_key'          => fv_get_license_key(),
											'license_key_2'        => fv_get_license_key_2(),
											'license_d'            => fv_get_license_domain_id(),
											'license_d_2'          => fv_get_license_domain_id_2(),
											'plugin_theme_slug'    => $u->slug,
											'plugin_theme_version' => $u->version,
											'license_pp'           => $_SERVER['REMOTE_ADDR'],
											'license_host'         => $_SERVER['HTTP_HOST'],
											'license_mode'         => 'update_request_load',
											'license_v'            => FV_PLUGIN_VERSION,
										);
										$query_dif = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
										$response  = fv_remote_run_query( $query_dif );
										if ( ! is_wp_error( $un ) ) {
											unlink( $fv_theme_zip_upload_dir . '/' . $basename );
										}
									}
									//end of update
								}
							} else {
								// if ( get_option( 'fv_themes_auto_update_list' ) == true
								// && in_array( $u->slug, get_option( 'fv_themes_auto_update_list' ) ) ) {
								$pathInfo=pathinfo( $u->slug );
								$fileName=$pathInfo['filename'].'.zip';

								$upload_dir      = wp_upload_dir();
								$fv_theme_zip_upload_dir=$upload_dir["basedir"]."/fv_auto_update_directory/themes/";
								$tmpfile = download_url( $u->dl_link, $timeout = 300 );

								if ( is_wp_error( $tmpfile ) == true ) {
									$chk_fl_dl_sz = fv_curlRemoteFilesize( $u->dl_link );
									if ( $chk_fl_dl_sz > 0 ) {
										$t_dl_fl_sz += $chk_fl_dl_sz;
									}
									// Initialize the cURL session
									$ch = curl_init( $u->dl_link );

									// Use basename() function to return
									// the base name of file
									$file_name = basename( $u->dl_link );

									// Save file into file location
									$save_file_loc = $fv_theme_zip_upload_dir.$fileName;

									// Open file
									$fp = fopen( $save_file_loc, 'wb' );

									// It set an option for a cURL transfer
									curl_setopt( $ch, CURLOPT_FILE, $fp );
									curl_setopt( $ch, CURLOPT_HEADER, 0 );

									// Perform a cURL session
									curl_exec( $ch );

									// Closes a cURL session and frees all resources
									curl_close( $ch );

									// Close file
									fclose( $fp );

								} else {
									copy( $tmpfile, $fv_theme_zip_upload_dir.$fileName );
									unlink( $tmpfile );
								}

								$determine_theme_dir = search_for_plugin_dir_by_slug( $u->slug, $get_theme_directory )['dir'];
								$backup_theme_dir =  $upload_dir["basedir"]."/fv_auto_update_directory/themes/backup/";
								$get_all_themes = scandir( $backup_theme_dir );
								foreach( $get_all_themes as $single_theme ) {
									if ( strpos( $single_theme, $u->slug ) !== false ) {
										delete_old_folder( $backup_theme_dir . $single_theme );
									}
								}

								$original_theme_dir             = get_theme_root() . '/' . $determine_theme_dir;
								$fv_theme_zip_upload_dir_backup = $upload_dir["basedir"] . "/fv_auto_update_directory/themes/backup/" . $determine_theme_dir . '-v-' . $single_th['version'];

								if ( is_dir( $original_theme_dir ) ) {
									fv_fs_recurse_copy( $original_theme_dir, $fv_theme_zip_upload_dir_backup ); // copy old version as backup
								}

								$ext = pathinfo( $fileName, PATHINFO_EXTENSION );

								if ( $ext == 'zip' ) {

									$basename=pathinfo( $fileName,  PATHINFO_BASENAME );
									$un= unzip_file( $fv_theme_zip_upload_dir . '/' . $basename, get_theme_root() );

									$query_base_url = FV_REST_API_URL . 'update-request-load';
									$query_args     = array(
										'license_key'          => fv_get_license_key(),
										'license_key_2'        => fv_get_license_key_2(),
										'license_d'            => fv_get_license_domain_id(),
										'license_d_2'          => fv_get_license_domain_id_2(),
										'plugin_theme_slug'    => $u->slug,
										'plugin_theme_version' => $u->version,
										'license_pp'           => $_SERVER['REMOTE_ADDR'],
										'license_host'         => $_SERVER['HTTP_HOST'],
										'license_mode'         => 'update_request_load',
										'license_v'            => FV_PLUGIN_VERSION,
									);
									$query_dif = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
									$response  = fv_remote_run_query( $query_dif );

									if ( ! is_wp_error( $un ) ) {
										unlink( $fv_theme_zip_upload_dir . '/' . $basename );
									}
								}
							}
						}
					}
				}
			}
		}

		if ( $theme_plugin == null || $theme_plugin == 'plugin' ) {

			if ( ! empty( $license_histories->plugins ) ) {
				$get_plugin_directory=[];

				if ( ! empty( $all_plugins ) ) {

					foreach ( $all_plugins as $plugin_slug => $values ) {
						$version = fv_esc_version( $values['Version'] );
						$slug    = fv_get_slug( $plugin_slug );
						$get_plugin_directory[] = [
													'dir' 	 => explode( '/',$plugin_slug )[0],
													'slug'	 => $slug,
													'version'=> $version
												  ];
					}
				}

				foreach ( $license_histories->plugins as $u ) {
					foreach( $get_plugin_directory as $single_pl ) {
						if ( $single_pl['slug'] == $u->slug
						&& version_compare( $u->version, $single_pl['version'], '>' ) ) {

							// y

							if ( ! empty( $single_plugin_theme_slug ) ) {
								if ( count( $single_plugin_theme_slug ) > 0 && $single_plugin_theme_slug['slug'] == $u->slug ) {

								// start

									$pathInfo=pathinfo( $u->slug );
									$fileName=$pathInfo['filename'].'.zip';

									$upload_dir      = wp_upload_dir();
									$fv_plugin_zip_upload_dir=$upload_dir["basedir"]."/fv_auto_update_directory/plugins/";

									$tmpfile = download_url( $u->dl_link, $timeout = 300 );

									if ( is_wp_error( $tmpfile ) == true ) {
										$chk_fl_dl_sz = fv_curlRemoteFilesize( $u->dl_link );
										if ( $chk_fl_dl_sz > 0 ) {
											$t_dl_fl_sz += $chk_fl_dl_sz;
										}
										// Initialize the cURL session
										$ch = curl_init( $u->dl_link );

										$file_name = basename( $u->dl_link );

										$save_file_loc = $fv_plugin_zip_upload_dir . $fileName;

										// Open file
										$fp = fopen( $save_file_loc, 'wb' );

										// It set an option for a cURL transfer
										curl_setopt( $ch, CURLOPT_FILE, $fp );
										curl_setopt( $ch, CURLOPT_HEADER, 0 );

										// Perform a cURL session
										curl_exec( $ch );

										// Closes a cURL session and frees all resources
										curl_close( $ch );

										// Close file
										fclose( $fp );

									} else {
										copy( $tmpfile, $fv_plugin_zip_upload_dir . $fileName );
										unlink( $tmpfile );
									}

									$determine_plugin_dir            = search_for_plugin_dir_by_slug( $u->slug, $get_plugin_directory )['dir'];
									$original_plugin_dir             = WP_PLUGIN_DIR . '/' . $determine_plugin_dir;
									$fv_plugin_zip_upload_dir_backup = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/backup/" . $determine_plugin_dir;

									if ( is_dir( $original_plugin_dir ) ) {
										fv_fs_recurse_copy( $original_plugin_dir, $fv_plugin_zip_upload_dir_backup ); // copy old version as backup
									}

									$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
									if ( $ext == 'zip' ) {
										$basename=pathinfo( $fileName,  PATHINFO_BASENAME );
										if ( is_dir( $original_plugin_dir ) ) {
										}
										$un= unzip_file( $fv_plugin_zip_upload_dir . '/' . $basename, WP_PLUGIN_DIR );

										$query_base_url = FV_REST_API_URL . 'update-request-load';
										$query_args     = array(
											'license_key'          => fv_get_license_key(),
											'license_key_2'        => fv_get_license_key_2(),
											'license_d'            => fv_get_license_domain_id(),
											'license_d_2'          => fv_get_license_domain_id_2(),
											'plugin_theme_slug'    => $u->slug,
											'plugin_theme_version' => $u->version,
											'license_pp'           => $_SERVER['REMOTE_ADDR'],
											'license_host'         => $_SERVER['HTTP_HOST'],
											'license_mode'         => 'update_request_load',
											'license_v'            => FV_PLUGIN_VERSION,
										);
										$query_dif = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
										$res       = fv_remote_run_query( $query_dif );
										if ( !is_wp_error( $un ) ) {
											unlink( $fv_plugin_zip_upload_dir.'/'.$basename );
										}
									}
									//end of plugin update
								//end
								}
							} else {
								// if ( get_option( 'fv_plugin_auto_update_list' ) == true
								// && in_array( $u->slug, get_option( 'fv_plugin_auto_update_list' ) ) ) {

									//start
									$pathInfo=pathinfo( $u->slug );
									$fileName=$pathInfo['filename'].'.zip';

									$upload_dir      = wp_upload_dir();
									$fv_plugin_zip_upload_dir=$upload_dir["basedir"]."/fv_auto_update_directory/plugins/";

									$tmpfile = download_url( $u->dl_link, $timeout = 300 );

									if ( is_wp_error( $tmpfile ) == true ) {
										$chk_fl_dl_sz = fv_curlRemoteFilesize( $u->dl_link );
										if ( $chk_fl_dl_sz > 0 ) {
											$t_dl_fl_sz += $chk_fl_dl_sz;
										}
										// Initialize the cURL session
										$ch = curl_init( $u->dl_link );

										$file_name = basename( $u->dl_link );

										$save_file_loc = $fv_plugin_zip_upload_dir.$fileName;

										// Open file
										$fp = fopen( $save_file_loc, 'wb' );

										// It set an option for a cURL transfer
										curl_setopt( $ch, CURLOPT_FILE, $fp );
										curl_setopt( $ch, CURLOPT_HEADER, 0 );

										// Perform a cURL session
										curl_exec( $ch );

										// Closes a cURL session and frees all resources
										curl_close( $ch );

										// Close file
										fclose( $fp );

									} else {
										copy( $tmpfile, $fv_plugin_zip_upload_dir.$fileName );
										unlink( $tmpfile );
									}

									$determine_plugin_dir = search_for_plugin_dir_by_slug( $u->slug, $get_plugin_directory )['dir'];
									$original_plugin_dir = WP_PLUGIN_DIR.'/'.$determine_plugin_dir;
									$fv_plugin_zip_upload_dir_backup=$upload_dir["basedir"]."/fv_auto_update_directory/plugins/backup/".$determine_plugin_dir;

									if ( is_dir( $original_plugin_dir ) ) {
										fv_fs_recurse_copy( $original_plugin_dir, $fv_plugin_zip_upload_dir_backup ); // copy old version as backup
									}

									$ext = pathinfo( $fileName, PATHINFO_EXTENSION );

									if ( $ext=='zip' ) {

										$basename=pathinfo( $fileName,  PATHINFO_BASENAME );
										if ( is_dir( $original_plugin_dir ) ) {
										}
										$un = unzip_file( $fv_plugin_zip_upload_dir . '/' . $basename, WP_PLUGIN_DIR );

										$query_base_url = FV_REST_API_URL . 'update-request-load';
										$query_args     = array(
											'license_key'          => fv_get_license_key(),
											'license_key_2'        => fv_get_license_key_2(),
											'license_d'            => fv_get_license_domain_id(),
											'license_d_2'          => fv_get_license_domain_id_2(),
											'plugin_theme_slug'    => $u->slug,
											'plugin_theme_version' => $u->version,
											'license_pp'           => $_SERVER['REMOTE_ADDR'],
											'license_host'         => $_SERVER['HTTP_HOST'],
											'license_mode'         => 'update_request_load',
											'license_v'            => FV_PLUGIN_VERSION,
										);
										$query_dif = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
										$res       = fv_remote_run_query( $query_dif );
										if ( !is_wp_error( $un ) ) {
											unlink( $fv_plugin_zip_upload_dir . '/' . $basename );
										}
									}
									//end of plugin update
								//end
								//}
							}
							//x
						}
					}
				}
			}
		}

		$theme_plugins = [
			'themes' => isset( $license_histories->themes ) ? $license_histories->themes : [],
			'plugins'=> isset( $license_histories->plugins ) ? $license_histories->plugins : []
		];

		if ( isset( $license_histories ) ) {
			request_data_activation([
				'ld_tm'          => $license_histories->ld_tm,
				'ld_type'        => 'up_dl_plugs_thms',
				'l_dat'          => fv_get_license_key(),
				'ld_dat'         => $_SERVER['HTTP_HOST'],
				'rm_ip'          => $_SERVER['REMOTE_ADDR'],
				'status'         => 'executed',
				'req_time'       => time(),
				'res'            => '1',
				'dsz'            => $t_dl_fl_sz,
				'themes_plugins' => $theme_plugins
			] );
		}
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
	$response     = fv_remote_run_query( $query );
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $license_data );
}

function delete_old_folder( $path ) {

	if ( is_dir( $path ) === true ) {

		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path ), RecursiveIteratorIterator::CHILD_FIRST );

		foreach ( $files as $file )
		{
			if (in_array( $file->getBasename(), array( '.', '..' ) ) !== true )
			{
				if ( $file->isDir() === true )
				{
					rmdir( $file->getPathName() );
				}

				else if (( $file->isFile() === true ) || ( $file->isLink() === true ) )
				{
					unlink( $file->getPathname() );
				}
			}
		}

		return rmdir( $path );
	}

	if ( ( is_file( $path ) === true ) || (is_link( $path ) === true ) ) {
		return unlink( $path );
	}
	return false;
}

/**
 * Copy an entire directory into another directory.
 *
 * @param string $src_dir The source directory to copy.
 * @param string $dst_dir The destination directory in which $src_dir wil be copied.
 * @return void
 */
function fv_fs_recurse_copy( string $src_dir, string $dst_dir ) {

	$dir = opendir( $src_dir );

	// @ suppresses errors when dir $dst_dir already exists.
	// recursive needs to be set since it otherwise fails if
	// a theme is installed in subdirs f.i. themes/child/child-theme/
	@mkdir( directory: $dst_dir, recursive: true );

	if ( ! is_dir( $dst_dir ) ) {
		return false;
	}

	while( false !== ( $file = readdir( $dir ) ) ) {
		if ( '.' == $file || '..' == $file ) {
			continue;
		}
		if ( is_dir( $src_dir . '/' . $file ) ) {
			fv_fs_recurse_copy( $src_dir . '/' . $file, $dst_dir . '/' . $file );
		} else {
			copy( $src_dir . '/' . $file, $dst_dir . '/' . $file );
			if ( ! file_exists( $dst_dir . '/' . $file ) ) {
				return false;
			}
		}
	}

	closedir( $dir );
}

/**
 * Returns matching entry from array based on slug.
 *
 * NOTE: The plugin name suggests this function is only used for plugins,
 *       but it's also used for themes.
 *
 * NOTE: Based on the name I would expect a dir to be returned.
 *
 * @param string $slug
 * @param array $array An array containging associated arrays.
 * @return void
 */
function search_for_plugin_dir_by_slug( $slug, $array ) {
   foreach ( $array as $key => $val ) {
	   if ( $val['slug'] === $slug ) {
		   return $val;
	   }
   }
   return null;
}

function fv_auto_update_install() {

	require_once(ABSPATH .'/wp-admin/includes/file.php' );

	WP_Filesystem();

	$upload_dir               = wp_upload_dir();
	$fv_plugin_zip_upload_dir = $upload_dir["basedir"]."/fv_auto_update_directory/plugins";
	$files_inside_dir         = scandir( $fv_plugin_zip_upload_dir );

	foreach( $files_inside_dir as $ind_file ) {
		$ext = pathinfo( $ind_file, PATHINFO_EXTENSION );
		if ( $ext == 'zip' ) {
			$basename = pathinfo( $ind_file, PATHINFO_BASENAME );
			$un       = unzip_file( $fv_plugin_zip_upload_dir . '/' . $basename, WP_PLUGIN_DIR );
			if ( !is_wp_error( $un ) ) {
				unlink( $fv_plugin_zip_upload_dir.'/'.$basename );
			}
		}
	}

	$fv_theme_zip_upload_dir = $upload_dir["basedir"] . "/fv_auto_update_directory/themes";
	$files_inside_dir        = scandir( $fv_theme_zip_upload_dir );
	foreach( $files_inside_dir as $ind_file ) {
		$ext = pathinfo( $ind_file, PATHINFO_EXTENSION );
		if ( $ext=='zip' ) {
			$basename = pathinfo( $ind_file,  PATHINFO_BASENAME );
			$un = unzip_file( $fv_theme_zip_upload_dir . '/' . $basename, WP_CONTENT_DIR . '/themes' );
			if ( !is_wp_error( $un ) ) {
				unlink( $fv_theme_zip_upload_dir . '/' . $basename );
			}
		}
	}
}

function render_festinger_vault_settings_page() {

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
	$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response = fv_remote_run_query( $query );

	if ( is_wp_error( $response ) ) {

		// there seems something is wrong here. Why is the next code run when is_wp_error?
		// Is there an ending curly bracket missing in the original code?
		// also it gets the same options twice and runs the same remote query twice.
		// this code doesn't seem right.
		// and the query had been run allready above this point,
		// so the same code is run three times when is_wp_error
		// occurs on the first time. The third time is unconditional.

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
		$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response = fv_remote_run_query( $query );

		// just repeating the same code unconditionally seems senseless.
		// this can't be right. Is this just to repeat in the hope it will work?
		// then at least it should first check if it is needed.

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
		$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
		$response = fv_remote_run_query( $query );
	}

	$all_license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( $all_license_data->license_1->options->white_label == 'no'
	&&   $all_license_data->license_2->options->white_label == 'no' ) {
		fv_forget_white_label_settings();
	}

	include( FV_PLUGIN_DIR . '/sections/fv_settings.php' );

	get_plugin_theme_data_details( 'all_plugins_themes' );

}

function get_plugin_theme_data_details( $request_list = 'all' ) {

	$get_active_themes    = [];
	$get_inactive_themes  = [];

	$get_active_plugins   = [];
	$get_inactive_plugins = [];

	$all_plugins_list     = [];
	$all_themes_list      = [];

	$allPlugins           = fv_get_plugins();
	$activePlugins        = get_option( 'active_plugins' );

	$allThemes            = fv_get_themes();

	foreach( $allThemes as $theme ) {
		if ( fv_is_active_theme( $theme->Name ) ) {
			$get_active_themes[]   = [
				'name' => urlencode( $theme['Name'] ),
				'slug' => $theme->get_template()
			];
		} else {
			$get_inactive_themes[] = [
				'name' => urlencode( $theme['Name'] ),
				'slug' => $theme->get_template()
			];
		}
		$all_themes_list[]     = [
			'name' => urlencode( $theme['Name'] ),
			'slug' => $theme->get_template()
		];
	}

	foreach( $allPlugins as $key => $value ) {
		if ( in_array( $key, $activePlugins ) ) {
			$all_plugins_list []    = [
				'name' => urlencode( $value['Name'] ),
				'slug' => fv_get_slug( $key )
			];
		} else {
			$all_plugins_list []    = [
				'name'=> urlencode( $value['Name'] ),
				'slug'=> fv_get_slug( $key )
			];
		}
		$get_inactive_plugins[] = [
			'name'=> urlencode( $value['Name'] ),
			'slug'=> fv_get_slug( $key )
		];
	}

	switch ( $request_list ) {
		case 'active_plugins':
			return json_encode( $get_active_plugins );
			break;

		case 'active_plugins':
			return json_encode( $get_inactive_plugins );
			break;

		case 'active_themes':
			return json_encode( $get_active_themes );
			break;

		case 'inactive_themes':
			return json_encode( $get_inactive_themes );
			break;

		case 'all_plugins_themes':
			$all_plugins_list_chunks = fv_array_split(
				array: $all_plugins_list,
				firstChunkSize: count( $all_themes_list ),
				chunkSize: 49
			 );
			foreach( $all_plugins_list_chunks as $splitted_list ) {
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
					'plugins_and_themes_data' => array(
						'plugins' => $splitted_list,
						'themes'  => $all_themes_list,
					 ),
				);
				$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
				$response = fv_remote_run_query( $query );
			}
			// QUESTION: Why is there no return in this case? All others return something.
			// actually this is the only branch currently called for and no return value is needed.
			// the rest of the branches are basically dead wood.
			break;

		default:
			return json_encode(
				$final_return_list = array (
					'active_plugins'   => $get_active_plugins,
					'inactive_plugins' => $get_inactive_plugins,
					'active_themes'    => $get_active_themes,
					'inactive_themes'  => $get_inactive_themes,
				 )
			 );
			break;
	}
}

function render_festinger_vault_page () {

	$query_base_url = FV_REST_API_URL . 'get-all-license-data';
	$query_args = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'get_all_license_data',
		'license_v'     => FV_PLUGIN_VERSION,
	);
	$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response = fv_remote_run_query( $query );
	$fv_api   = json_decode( wp_remote_retrieve_body( $response ) );

	// remove white_label settings if the plugin isn't set to use them.
	if ( $fv_api->license_1->options->white_label == 'no'
	&&   $fv_api->license_2->options->white_label == 'no' ) {
		fv_forget_white_label_settings();
	}

	// render the vault page.
	include( FV_PLUGIN_DIR . '/sections/fv_plugins.php' );

	// get_plugin_theme_data_details( 'all_plugins_themes' );
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
function fv_perhaps_white_label_plugin_author(): string {
	return fv_white_label('plugin_agency_author') ?: 'Festinger Vault';
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
function fv_get_wl_option_keys( string $context = 'whitelabel' ) : array {

	$options = array(
		'wl_fv_plugin_agency_author_wl_' => 'agency_author',
		'wl_fv_plugin_author_url_wl_'    => 'agency_author_url',
		'wl_fv_plugin_name_wl_'          => 'fv_plugin_name',
		'wl_fv_plugin_slogan_wl_'        => 'fv_plugin_slogan',
		'wl_fv_plugin_icon_url_wl_'      => 'fv_plugin_icon_url',
		'wl_fv_plugin_description_wl_'   => 'fv_plugin_description',
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
function fv_is_white_label_switch( string $option ): bool {
	return ( 'wl_fv_plugin_wl_enable' === $option );
}

function fv_white_label( string $option ): string {

	$wl_options = fv_get_wl_option_keys('whitelabel');

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
function fv_perhaps_white_label_plugin_author_uri(): string {
	return fv_white_label( 'plugin_author_url' ) ?: 'https://festingervault.com/';
}

/**
 * White label plugin name, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_name(): string {
	return fv_white_label( 'plugin_name' ) ?: 'Festinger Vault';
}

/**
 * White label plugin description, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_description(): string {
	return fv_white_label( 'plugin_description' ) ?: 'Get access to 25K+ kick-ass premium WordPress themes and plugins. Now directly from your WP dashboard. Get automatic updates and one-click installation by installing the Festinger Vault plugin.';
}

/**
 * White label plugin slogan, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_slogan(): string {
	return fv_white_label( 'plugin_slogan' ) ?: 'Get access to 25K+ kick-ass premium WordPress themes and plugins. Now directly from your WP dashboard. <br/>Get automatic updates and one-click installation by installing the Festinger Vault plugin.';
}

/**
 * White label plugin icon url, based on settings.
 *
 * @return string
 */
function fv_perhaps_white_label_plugin_icon_url(): string {
	return fv_white_label( 'plugin_icon_url' ) ?: FV_PLUGIN_ABSOLUTE_PATH.'assets/images/logo.png';
}

/**
 * The white label settings form has been submitted.
 */
if ( isset( $_POST ) && ! empty( $_POST['fv_wl_submit'] ) && $_POST['fv_wl_submit'] ) {
	add_action( 'init', 'fv_do_white_label_settings_form' );
}

/**
 *  The "Hide/Block admin notices" settings form has been submitted.
 */
if ( isset( $_POST ) && ! empty( $_POST['fv_admin_notice'] ) && $_POST['fv_admin_notice'] ) {
	fv_do_admin_notices_settings_form();
}

/**
 * The "FORCE UPDATE NOW" button is used
 */
if ( isset( $_POST )
&& ! empty( $_POST['pluginforceupdate'] ) && $_POST['pluginforceupdate'] ) {
	add_action( 'init', 'fv_do_plugins_forced_update' );
}

/**
 * The "Instant update all" button has been used
 */
if ( isset( $_POST )
&& ! empty( $_POST['pluginforceupdateinstant'] ) && $_POST['pluginforceupdateinstant'] ) {
	add_action( 'init', 'fv_do_plugins_instant_updates' );
}

/**
 * The update button for a specific single plugin has been used.
 */
if ( isset( $_POST )
&& ! empty( $_POST['singlepuginupdaterequest'] ) && $_POST['singlepuginupdaterequest'] ) {
	add_action( 'init', 'fv_do_single_plugin_forced_update_request' );
}

/**
 * The update button for a specific single theme has been used.
 */
if ( isset( $_POST )
&& ! empty( $_POST['singlethemeupdaterequest'] ) && $_POST['singlethemeupdaterequest'] ) {

	add_action( 'init', 'fv_do_single_theme_forced_update_request' );
}

/**
 * The update button for a specific single theme has been used.
 */
if ( isset( $_POST )
&& ! empty( $_POST['themeforceupdate'] ) && $_POST['themeforceupdate'] ) {
	add_action( 'init', 'fv_do_themes_forced_update' );
}

if ( isset( $_POST ) && ! empty( $_POST['themeforceupdate_instant'] ) && $_POST['themeforceupdate_instant'] ) {
	add_action( 'init', 'fv_do_themes_instant_updates' );
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
function get_plugin_basefile_by_slug( string $given_slug ): string|false {

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

function generatePluginActivationLinkUrl( $plugin ) {

	if (strpos( $plugin, '/' ) ) {
		$plugin = str_replace( '/', '%2F', $plugin );
	}

	$activateUrl        = sprintf(admin_url( 'plugins.php?action=activate&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
	$_REQUEST['plugin'] = $plugin;
	$activateUrl        = wp_nonce_url( $activateUrl, 'activate-plugin_' . $plugin );

	return $activateUrl;
}

if ( isset( $_GET['actionrun'] ) && isset( $_GET['activeslug'] ) ) {
	add_action( 'init', 'action_run_pl_act' );

	function action_run_pl_act() {
		activate_plugin( get_plugin_basefile_by_slug( $_GET['activeslug'] ) );
		$returndataurl = admin_url( 'admin.php?page=festinger-vault&installation=success&slug=' . $_GET['activeslug'] );
		wp_redirect( $returndataurl );
		//header( 'Location: '.$returndataurl );
		exit;
	}
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
		echo fv_no_backup_markup( return: true );
	}
}

function fv_get_upload_dir( string $context ) : string {

	static $fv_upload_dir;

	if ( empty( $fv_upload_dir ) ) {

		$wp_upload_dir = wp_upload_dir();

		if ( empty( $wp_upload_dir['basedir'] ) ) {
			return false;
		}
		$fv_upload_dir = $wp_upload_dir['basedir'] . '/fv_auto_update_directory' ;
	}

	switch ( $context ) {
		case( 'plugin' ):
			return $fv_upload_dir . '/plugins/';
			break;

		case( 'plugin-backup' ):
			return $fv_upload_dir . '/plugins/backup/';
			break;

		case( 'theme' ):
			return $fv_upload_dir . '/themes/';
			break;

		case( 'theme-backup' ):
			return $fv_upload_dir . '/themes/backup/';
			break;

		return $fv_upload_dir;
	}
}

function fv_no_backup_markup( bool $return = true ) : ?string {

	$markup = "<div class='bg-tag border-8 text-center rollback-not-available'>Not Available</div>";

	if ( $return ) {
		return $markup;
	}
	echo $markup;
}

function fv_print_theme_rollback_button( string $stylesheet, string $installed_version ) : void {

	$backup_dir = fv_get_upload_dir( 'theme-backup' );
	if ( ! $backup_dir ) {
		echo fv_no_backup_markup( return: true );
		return;
	}

	$theme_main = $backup_dir . $stylesheet;
	if ( ! file_exists( $theme_main ) ) {
		echo fv_no_backup_markup( return: true );
		return;
	}

	$theme_backup = wp_get_theme( stylesheet: $stylesheet, theme_root: $backup_dir );
	if ( ! $theme_backup->exists() ) {
		echo fv_no_backup_markup( return: true );
		return;
	}

	$backup_version = $theme_backup->Version;
	if ( ! version_compare( $installed_version, $backup_version, '>' ) ) {
		echo fv_no_backup_markup( return: true );
		return;
	}

	?>
		<form name="theme_rollback" method="POST" onSubmit="if ( !confirm( 'Are you sure want to rollback this theme?' ) ) {return false;}">
			<input type="hidden" name="slug" value="<? echo fv_get_slug( $theme_backup->stylesheet ); ?>" />
			<input type="hidden" name="version" value="<? echo $installed_version;?>" />
			<button class="btn btn_rollback btn-sm float-end btn-custom-color" id="themerollback" type="submit" name="themerollback" value="plugin">
				Rollback <?php echo $backup_version; ?>
			</button>
		</form>
		<?php
		// echo "<div class='btn non_active_button roleback-not-available'>Not Available</div>";
}

function fv_print_plugin_rollback_button( string $basename, string $installed_version ) : void {

	$backup_dir = fv_get_upload_dir( 'plugin-backup' );
	if ( ! $backup_dir ) {
		echo fv_no_backup_markup( return: true );
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
				<button class="btn btn_rollback btn-sm float-end btn-custom-color" id="pluginrollback" type="submit" name="pluginrollback" value="plugin">
					Rollback <?php echo $backup_version; ?>
				</button>
			</form>
			<?php
			return;
		}

		echo fv_no_backup_markup( return: true );
		return;
	}

	echo fv_no_backup_markup( return: true );
}




// NOTE: QUESTIONS:
// why is this not on a more visible location in the code
// and why is the function defined in the if-statement?
if ( isset( $_POST ) && ! empty( $_POST['themerollback'] ) && $_POST['themerollback'] ) {

	add_action( 'init', 'rollback_theme' );

	function rollback_theme() {

		$upload_dir       = wp_upload_dir();
		$backup_theme_dir = $upload_dir["basedir"] . "/fv_auto_update_directory/themes/backup/";
		$get_all_themes   = scandir( $backup_theme_dir );

		foreach( $get_all_themes as $single_theme ) {

			if ( strpos( $single_theme, $_POST['slug'] ) !== false ) {

				$theme_full    = explode( "-v-", $single_theme );
				$theme_name    = $theme_full[0];
				$theme_version = $theme_full[1];

				if ( file_exists( $backup_theme_dir . $single_theme ) ) {

					if ( version_compare( $_POST['version'],  $theme_version, '>' ) ) {

						$original_theme_dir = get_theme_root() . '/' . $_POST['slug'] . '/';

						if ( is_dir( $original_theme_dir ) ) {
							fv_fs_recurse_copy( $backup_theme_dir . $single_theme . '/', $original_theme_dir ); // copy old version as backup
						}
					}
				}
			}
		}
		wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&rollback=success' ) );
	}
}

// NOTE: QUESTIONS:
// why is this not on a more visible location in the code
// and why is the function defined in the if-statement?

if ( isset( $_POST ) && ! empty( $_POST['pluginrollback'] ) && $_POST['pluginrollback'] ) {
	add_action( 'init', 'rollback_plugin' );
	function rollback_plugin() {

		$upload_dir             = wp_upload_dir();
		$plugin_base_file_get   = get_plugin_basefile_by_slug( $_POST['slug'] );
		$backup_plugin_dir      = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/backup/" . $plugin_base_file_get;
		$backup_plugin_only_dir = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/backup/" . $_POST['slug'] . '/';

		if (file_exists( $backup_plugin_dir ) ) {
			// if ( version_compare( $_POST['version'], get_plugin_data( $backup_plugin_dir )['Version'], '>' ) ) {
			// if ( get_plugin_data( $backup_plugin_dir )['Version'] ) {

				$original_plugin_dir = WP_PLUGIN_DIR . '/' . $_POST['slug'] . '/';
				if ( is_dir( $original_plugin_dir ) ) {
					fv_fs_recurse_copy( $backup_plugin_only_dir, $original_plugin_dir ); // copy old version as backup
				}
			//}
		}
		wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&rollback=success' ) );
	}
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
 * Gets installed plugins that are not in the wordpress.org repository.
 *
 * @return array Filtered output of get_plugins().
 */
function fv_get_plugins(): array {
	return fv_remove_wp_org_plugins( get_plugins() );
}

/**
 * Remove plugins that are from the wordpress.org repository from a plugins array,
 * return the rest.
 *
 * Expects array key to be the plugin basename.
 *
 * For efficiency this function uses the update_plugins transient from wordpress plugin updates,
 * instead of the wordpress.org api. This may miss plugins that are installing in
 * the last 12 hours.
 *
 * ->response   is an array of plugins that have updates.
 * ->no_updates is an array of plugins that are up to date.
 *
 * @param array $plugins
 * @return array Filtered output of get_plugins().
 */
function fv_remove_wp_org_plugins( array $plugins ): array {

	$update_plugins       = get_site_transient( 'update_plugins' );
	$plugins_not_on_wporg = array();

	foreach ( $plugins as $plugin_basename => $plugin_data ) {

		// Check download url of plugins without updates.
		if ( isset( $update_plugins->response[ $plugin_basename ]->package ) ) {
			if ( fv_is_wporg_plugin( $update_plugins->response[ $plugin_basename ]->package ) ) {
				continue;
			}
		}

		// Check download url of plugins that have available updates.
		if ( isset( $update_plugins->no_update[ $plugin_basename ]->package ) ) {
			if ( fv_is_wporg_plugin( $update_plugins->no_update[ $plugin_basename ]->package ) ) {
				continue;
			}
		}

		$plugins_not_on_wporg[ $plugin_basename ] = $plugin_data;
	}

	return $plugins_not_on_wporg;
}

/**
 * Is plugin in the wordpress.org repo?
 *
 * @param string $plugin_url Plugin's download URL
 * @return boolean True when plugin is downloaded from the WordPress plugins repo.
 */
function fv_is_wporg_plugin( string $plugin_url ): bool {
	if ( empty( $plugin_url ) ) {
		return false;
	}
	return str_starts_with( $plugin_url, 'https://downloads.wordpress.org/plugin/' );
}

/**
 * Gets installed themes,
 * excluding themes that are from the wordpress.org repository.
 *
 * For efficiency this function uses the transient from theme updates,
 * instead of the wordpress.org api. This may miss themes that are installing in
 * the last 12 hours.
 *
 * @return array Filtered output of wp_get_themes().
 */
function fv_get_themes() {
	$themes        = wp_get_themes();
	$update_themes = get_site_transient( 'update_themes' );

	foreach ( $themes as $theme_slug => $theme_data ) {

		$download_link = '';
		if ( ! empty( $update_themes->response[ $theme_slug ]['package'] ) ) {
			$download_link = $update_themes->response[ $theme_slug ]['package'];
		}
		if ( ! empty( $update_themes->no_update[ $theme_slug ]['package'] ) ) {
			$download_link = $update_themes->no_update[ $theme_slug ]['package'];
		}
		if ( ! empty( $download_link )
		&& str_starts_with( $download_link, 'https://downloads.wordpress.org/theme/' ) ) {
			continue;
		}
		$themes_not_on_wporg[ $theme_slug ] = $theme_data;
	}

	return $themes_not_on_wporg;
}

function fv_remote_run_query( string $query ) {
	$response = wp_remote_post( $query, array( 'timeout' => 200, 'sslverify' => false ) );
	if (is_wp_error( $response ) ) {
		$response = wp_remote_post( $query, array( 'timeout' => 200, 'sslverify' => true ) );
		if ( is_wp_error( $response ) ) {
				echo 'SSLVERIFY ERROR';
		}
	}
	return $response;
}

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

function fv_array_equal_split( array $array, int $chunkSize ): array {
	if ( $chunkSize <= 0 ) {
		return $array;
	}
	return array_chunk( array: $array, length: $chunkSize, preserve_keys: true );
}

function fv_is_active_theme( $theme_name ) {
	$activeTheme = wp_get_theme();
	return $theme_name == $activeTheme->Name;
}

function fv_forget_license_by_key ( string $license_key ) {
	if ( fv_get_license_key() == $license_key ) {
		fv_forget_license();
	}
	if ( fv_get_license_key_2() == $license_key ) {
		fv_forget_license_2();
	}
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

if ( ! function_exists( 'str_remove_prefix' ) ) {
	function str_remove_prefix( string $str, string $prefix ): string {
		if ( str_starts_with( $str, $prefix ) ) {
			$str = substr( $str, strlen( $prefix ) );
		}
		return $str;
	}
}

function fv_should_auto_update_theme( string $slug ): bool	{

	if ( empty( $slug ) ) {
		return false;
	}
	// note: in fv_theme_updates.php array_search was used. Is that better?
	// ( array_search( $theme_slug, get_option( 'fv_themes_auto_update_list' ) ) ) !== false

	return is_array( get_option( 'fv_themes_auto_update_list' ) )
		&& in_array( $slug, get_option( 'fv_themes_auto_update_list' ) );
}

function fv_should_auto_update_plugin( string $slug ): bool	{

	if ( empty( $slug ) ) {
		return false;
	}
	// note: in fv_plugin_updates.php array_search was used. Is that better?
	// ( array_search( $slug, get_option( 'fv_plugin_auto_update_list' ) ) ) !== false

	return is_array( get_option( 'fv_plugin_auto_update_list' ) )
		&& in_array( $slug, get_option( 'fv_plugin_auto_update_list' ) );
}

/**
 * Gets the theme slug, from a WP_Theme object.
 *
 * @param WP_Theme $theme
 * @return string
 */
function fv_get_wp_theme_slug( WP_Theme $theme ): string {

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
 * @param boolean $refresh set tot true to refresh the staticly saved options.
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
 * @param boolean $refresh set tot true to refresh the staticly saved options.
 * @return array Array containing license key and domain-id from the first actived license in settings.
 */
function fv_get_license( bool $refresh = false ): array {
	return fv_get_licenses( $refresh )[0];
}

/**
 * Gets license key of the first activated license from the settings.
 *
 * @param boolean $refresh set tot true to refresh the staticly saved options.
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
 * @param boolean $refresh set tot true to refresh the staticly saved options.
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
 * @param boolean $refresh set tot true to refresh the staticly saved options.
 * @return array  Array containing license key and domain-id from the second actived license from the settings.
 */
function fv_get_license_2( bool $refresh = false ): array {
	return fv_get_licenses( $refresh )[1];
}

/**
 * Gets license key of the second activated license from the settings.
 *
 * @param boolean $refresh set tot true to refresh the staticly saved options.
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
 * @param boolean $refresh set tot true to refresh the staticly saved options.
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
function fv_forget_license() {
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
function fv_license_complete( array $license_data ) {
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

	if ( ! fv_license_complete( $license_data ) ) {
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

	if ( ! fv_license_complete( $license_data ) ) {
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

	if ( ! fv_license_complete( $license_data ) ) {
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

if ( ! function_exists( 'str_remove_suffix' ) ) {
	function str_remove_suffix( $str, $suffix ) {
		if ( ! str_ends_with( haystack: $str, needle: $suffix ) ) {
			return $str;
		}
		return substr( string: $str, offset: 0, length: -strlen( $suffix ) );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\str_contains' ) ) {
	/**
	 * Polyfill for `str_contains()` function added in PHP 8.0.
	 *
	 * Performs a case-sensitive check indicating if needle is
	 * contained in haystack.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the haystack.
	 * @return bool True if `$needle` is in `$haystack`, otherwise false.
	 */
	function str_contains( string $haystack, string $needle ): bool {
		return ( '' === $needle || false !== strpos( $haystack, $needle ) );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\str_starts_with' ) ) {
	/**
	 * Polyfill for `str_starts_with()` function added in PHP 8.0.
	 *
	 * Performs a case-sensitive check indicating if
	 * the haystack begins with needle.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the `$haystack`.
	 * @return bool True if `$haystack` starts with `$needle`, otherwise false.
	 */
	function str_starts_with( string $haystack, string $needle ): bool {
		return ( '' === $needle || 0 === strpos( $haystack, $needle ) );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\str_ends_with' ) ) {
	/**
	 * Polyfill for `str_ends_with()` function added in PHP 8.0.
	 *
	 * Performs a case-sensitive check indicating if
	 * the haystack ends with needle.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the `$haystack`.
	 * @return bool True if `$haystack` ends with `$needle`, otherwise false.
	 */
	function str_ends_with( $haystack, $needle ) {
		$len = strlen( $needle );
		if ( strlen( $haystack ) < $len ) {
			return false;
		}
		return 0 === substr_compare( haystack: $haystack, needle: $needle, offset: -$len, length: $len );
	}
}

/**
 * Determines the slug of a plugin for use in Festinger Vault.
 *
 * @param string $basename Plugin basename.
 * @param array $plugin_data
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

	// sort array asc by plugin basename
    ksort( $fv_plugins_new );
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
		// Skip if not a newer version
		if ( ! version_compare(
			$details['version'],
			$details['installed-version'],
			'>'
		) ) {
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
function get_installed_plugins_for_api_request(): array {

	$plugins = [];

	foreach ( fv_get_plugins() as $slug => $plugin_data ) {
		$plugins[] = [
			'slug'    => fv_get_slug( $slug ),
			'version' => fv_esc_version( $plugin_data['Version'] ),
			'dl_link' => ''
		];
	}

	return $plugins;
}

/**
 * Gets an array of installed themes formatted for the FV API.
 *
 * @return array
 */
function get_installed_themes_for_api_request(): array {

	$themes = [];

	foreach ( fv_get_themes() as $theme ) {

		$get_theme_slug = fv_get_wp_theme_slug( $theme );

		$themes[] = [
			'slug'    => $get_theme_slug,
			'version' => $theme->Version,
			'dl_link' => ''
		];

	}

	return $themes;
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
	return 'failed' === $result;
}

function fv_api_call_failed( string $result ) : bool {
	return in_array( $result, array( 'domainblocked', 'failed' ) );
}

/**
 * Process a submitted white label settings form
 *
 * @return void
 */
function fv_do_white_label_settings_form() {

	$options = fv_get_wl_option_keys('post');

	foreach ( $options as $option => $query_var ) {
		if ( isset( $_POST[ $query_var ] ) ) {
			fv_set_option( $option, htmlspecialchars( $_POST[ $query_var ] ) );
		} elseif ( ! fv_is_white_label_switch( $option ) ) {
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
		'an_fv_dis_adm_not_hid' => 'an_fv_dis_adm_not_hid',
		// Setting: Block All admin notices
		'an_fv_all_adm_not_hid' => 'an_fv_all_adm_not_hid',
	);

	foreach ( $options as $option => $query_var ) {
		if ( empty( $_POST[ $query_var ] ) ) {
			fv_delete_option( $option );
		} else {
			fv_set_option( $option, 1 );
		}
	}
}

/**
 * Initiates all plugins forced updates.
 *
 * @return void
 */
function fv_do_plugins_forced_update() {

	fv_auto_update_download( 'plugin' );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&force=success' ) );
}

/**
 * Initiates all themes forced updates.
 *
 * @return void
 */
function fv_do_themes_forced_update() {

	fv_auto_update_download( 'theme' );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&force=success' ) );
}

/**
 * Initiates plugins instant auto updates.
 *
 * @return void
 */
function fv_do_plugins_instant_updates() {

	fv_auto_update_download_instant( 'plugin' );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&instant=success' ) );
}

/**
 * Initiates themes instant auto updates.
 *
 * @return void
 */
function fv_do_themes_instant_updates() {

	fv_auto_update_download_instant( 'theme' );

	wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&instant=success' ) );
}


/**
 * Processes the force update single plugin form.
 *
 * @return void
 */
function fv_do_single_plugin_forced_update_request() {

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
function fv_do_single_theme_forced_update_request() {

	fv_do_single_plugin_forced_update( array(
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
 * @return boolean True if 'an_fv_all_adm_not_hid' option is set.
 */
function fv_should_hide_all_admin_notices() : bool {
	return (bool) get_option( 'an_fv_all_adm_not_hid' );
}

/**
 * Should dismissable admin notices be hidden?
 *
 * @return boolean True if 'an_fv_dis_adm_not_hid' option is set.
 */
function fv_should_hide_dismissable_admin_notices() : bool {
	return (bool) get_option( 'an_fv_dis_adm_not_hid' );
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
function fv_hide_wp_rocket_warnings() {
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
	fv_delete_option( 'fv_plugin_auto_update_list' );
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
		delete_option( $option );
	}
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
 * Checks if a license key is active in this install.
 *
 * @param string $license_key
 * @return boolean True if entered key is one of the keys in options, otherwise false
 */
function fv_is_active_license_key( string $license_key ) : bool {
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
function fv_set_plugins_auto_update_list( array $list ) : void {
	fv_set_option( 'fv_plugin_auto_update_list', $list );
}

/**
 * Save auto update list for themes in options.
 *
 * @param array $list Slugs of themes that need to be auto-updated.
 * @return void
 */
function fv_set_themes_auto_update_list( array $list ) : void {
	fv_set_option( 'fv_themes_auto_update_list', $list );
}

/**
 * Initializes auto update list for plugins and themes in options.
 *
 * @return void
 */
function fv_remove_auto_updates() : void {
	fv_set_option( 'fv_plugin_auto_update_list', array() );
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

        // default:
        //     break;
    }

    return ucfirst( $license_status );
}

function get_hours_to_midnight() : string {
    date_default_timezone_set( "UTC" );
    $now                 = date( 'Y-m-d H:i:s' );
    $next_day_start      = ( new DateTime( 'tomorrow' ) )->format( 'Y-m-d H:i:s' );
    $hours_to_midnight   = round( ( strtotime( $next_day_start ) - strtotime( $now ) ) / 3600, 1 );
    return (string) $hours_to_midnight;
}
