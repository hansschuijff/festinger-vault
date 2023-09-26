
<?php
//ini_set( 'memory_limit', '256' );

/**
 * Plugin Name: Festinger Vault ONDER HANSEN
 * description: Festinger vault - The largest plugin market
 * Version: 4.1.0
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
define( 'FV_PLUGIN_VERSION', '4.1.0' );

require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
require_once( FV_PLUGIN_DIR . '/functions/ajax_functions.php' );
require_once( FV_PLUGIN_DIR . '/classes/plugin-update-checker.php' );

add_action( 'rest_api_init', function() {
	register_rest_route( 'fv_endpoint/v1', '/fvforceupdateautoupdate', [
		'method'   => WP_REST_Server::READABLE,
		'callback' => 'fv_custom_endpoint_create_auto',
		'args'     => [
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

	$requested_plugins = [];
	$requested_themes  = [];

	$all_plugins          = fv_get_plugins();

	if ( ! empty( $all_plugins ) ) {
		foreach ( $all_plugins as $plugin_slug => $values ) {
			$version                = fv_esc_version( $values['Version'] );
			$slug                   = get_plugin_slug_from_data( $plugin_slug, $values );
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

	$plugin_api_param = array(
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

	$query_pl_updater      = esc_url_raw( add_query_arg( $plugin_api_param, FV_REST_API_URL .'plugin-theme-updater' ) );
	$response_pl_updater   = fv_remote_run_query( $query_pl_updater );
	$pluginUpdate_get_data = json_decode( wp_remote_retrieve_body( $response_pl_updater ) );

	// note: if is_wp_error(), can we still trust and use response->plugins and ->themes?

	if ( ! isset( $pluginUpdate_get_data->result )
	||   ! in_array( $pluginUpdate_get_data->result, array( 'domainblocked', 'failed' ) ) ) {

		$remote_plugins_list = [];
		foreach( $pluginUpdate_get_data->plugins as $plugin ) {
			$remote_plugins_list[] = $plugin->slug;
		}

		$remote_themes_list = [];
		foreach( $pluginUpdate_get_data->themes as $theme ) {
			$remote_themes_list[] = $theme->slug;
		}

		if ( ( $getLicenseKey == fv_get_license_key() || $getLicenseKey == fv_get_license_key_2() ) && $getLicenseStatus == 1 ) {
			update_option( 'fv_plugin_auto_update_list', $remote_plugins_list );
			update_option( 'fv_themes_auto_update_list', $remote_themes_list );
		} else {
			update_option( 'fv_plugin_auto_update_list', [] );
			update_option( 'fv_themes_auto_update_list', [] );
		}

		return ( 'success' );
	}

	return ( 'failed' );
}

add_action( 'rest_api_init', function() {
	register_rest_route( 'fv_endpoint/v1', '/fvforceupdate', [
		'method'   => WP_REST_Server::READABLE,
		'callback' => 'fv_custom_endpoint_create',
		'args'     => [
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
		$api_params = array(
		    'salt_id'      => $get_fv_salt_id,
		    'salt'         => $get_fv_salt,
		    'license_pp'   => $_SERVER['REMOTE_ADDR'],
		    'license_host' => $_SERVER['HTTP_HOST'],
		    'license_mode' => 'salt_verification',
		    'license_v'    => FV_PLUGIN_VERSION,
		 );

		$query    = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'salt-verification' ) );
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

			$api_params_2222 = array(
				'salt_id'             => $get_fv_salt_id,
				'salt'                => $get_fv_salt,
				'push_update_status'  => $push_update_result,
				'push_update_message' => $push_update_message,
				'license_pp'          => $_SERVER['REMOTE_ADDR'],
				'license_host'        => $_SERVER['HTTP_HOST'],
				'license_mode'        => 'salt_push_update_result',
				'license_v'           => FV_PLUGIN_VERSION,
			 );

			$query_232     = esc_url_raw( add_query_arg( $api_params_2222, FV_REST_API_URL . 'salt-push-update-result' ) );
			$response23232 = fv_remote_run_query( $query_232 );
		}

		if ( $response->result == 0 && $response->status == 0 ) {

			$api_params_2222 = array(
				'salt_id'             => $get_fv_salt_id,
				'salt'                => $get_fv_salt,
				'push_update_status'  => 1,
				'push_update_message' => 'Already updated',
				'license_pp'          => $_SERVER['REMOTE_ADDR'],
				'license_host'        => $_SERVER['HTTP_HOST'],
				'license_mode'        => 'salt_push_update_result',
				'license_v'           => FV_PLUGIN_VERSION,
			 );

			$query_232     = esc_url_raw( add_query_arg( $api_params_2222, FV_REST_API_URL . 'salt-push-update-result' ) );
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
		if ( $slug === get_plugin_slug_from_data( $plugin_basename, $plugin_data ) ) {
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

	if ( get_option( 'wl_fv_plugin_wl_enable' ) == true ) {
		delete_option( 'wl_fv_plugin_wl_enable' );
	}
	if ( get_option( 'fv_plugin_auto_update_list' ) == true ) {
		delete_option( 'fv_plugin_auto_update_list' );
	}
	if ( get_option( 'fv_themes_auto_update_list' ) == true ) {
		delete_option( 'fv_themes_auto_update_list' );
	}
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

		$api_params = array(
			'license_key'  => fv_get_license_key(),
			'license_d'    => fv_get_license_domain_id(),
			'license_pp'   => $_SERVER['REMOTE_ADDR'],
			'license_host' => $_SERVER['HTTP_HOST'],
			'license_mode' => 'deactivation',
			'license_v'    => FV_PLUGIN_VERSION,
		 );
		$query    = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'license-deactivation' ) );
		$response = fv_remote_run_query( $query );

		fv_forget_license()
	}

	if ( fv_has_license_2() ) {

		$api_params = array(
			'license_key'  => fv_get_license_key_2(),
			'license_d'    => fv_get_license_domain_id_2(),
			'license_pp'   => $_SERVER['REMOTE_ADDR'],
			'license_host' => $_SERVER['HTTP_HOST'],
			'license_mode' => 'deactivation',
			'license_v'    => FV_PLUGIN_VERSION,
		 );
		$query    = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'license-deactivation' ) );
		$response = fv_remote_run_query( $query );

		fv_forget_license_2();
	}

	if ( get_option( 'wl_fv_plugin_agency_author_wl_' ) == true ) {
		delete_option( 'wl_fv_plugin_agency_author_wl_' );
	}
	if ( get_option( 'wl_fv_plugin_author_url_wl_' ) == true ) {
		delete_option( 'wl_fv_plugin_author_url_wl_' );
	}
	if ( get_option( 'wl_fv_plugin_slogan_wl_' ) == true ) {
		delete_option( 'wl_fv_plugin_slogan_wl_' );
	}
	if ( get_option( 'wl_fv_plugin_icon_url_wl_' ) == true ) {
		delete_option( 'wl_fv_plugin_icon_url_wl_' );
	}
	if ( get_option( 'wl_fv_plugin_name_wl_' ) == true ) {
		delete_option( 'wl_fv_plugin_name_wl_' );
	}
	if ( get_option( 'wl_fv_plugin_description_wl_' ) == true ) {
		delete_option( 'wl_fv_plugin_description_wl_' );
	}
	if ( get_option( 'wl_fv_plugin_wl_enable' ) == true ) {
		delete_option( 'wl_fv_plugin_wl_enable' );
	}
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
		$obj->name         = get_adm_men_name();
		$obj->author       = get_adm_men_author();
		$obj->requires     = '3.0';
		$obj->tested       = '3.3.1';
		$obj->last_updated = '2021-07-13';
		$obj->sections     = array(
			'description' => get_adm_men_description(),
		 );

		return $obj;
	}

	return $obj;
}
add_filter( 'plugins_api', 'fv_plugin_check_info', 20, 3 );

/**
 * Filters the plugins list for this plugin,
 * to change some plugin_data values based on setttings.
 *
 * @param array $plugins Array of installed plugins and their data.
 * @return array Filtered array of installed plugins data.
 */
function plugins_page( $plugins ) {

	$key = plugin_basename( FV_PLUGIN_DIR . '/festingervault.php' );

	$plugins[ $key ]['Name']        = get_adm_men_name();
	$plugins[ $key ]['Description'] = get_adm_men_description();

	$plugins[ $key ]['Author']      = get_adm_men_author();
	$plugins[ $key ]['AuthorName']  = get_adm_men_author();

	$plugins[ $key ]['AuthorURI']   = get_adm_men_author_uri();
	$plugins[ $key ]['PluginURI']   = get_adm_men_author_uri();

	return $plugins;
}
add_filter( 'all_plugins', 'plugins_page' );

/**
 * Change the plugin name to the name from the settings,
 * to enable whitelisting.
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
		$translated_text = get_adm_men_name();
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
		page_title: get_adm_men_name(),
		menu_title: get_adm_men_name(),
		capability: 'read',
		menu_slug:  'festinger-vault',
		callback:   'festinger_vault_plugins_inside',
		icon_url:   get_adm_men_img(),
		position:   99
	 );

	add_submenu_page(
		parent_slug: 'festinger-vault',
		page_title:  'All Plugins',
		menu_title:  'Vault',
		capability:  'read',
		menu_slug:   'festinger-vault',
		callback:    'festinger_vault_plugins_inside'
	 );

	// Only add Activation page when white labeling is not enabled
	if ( get_option( 'wl_fv_plugin_wl_enable' ) != 1 ) {

		add_submenu_page(
			parent_slug: 'festinger-vault',
			page_title:  'Activation',
			menu_title:  'Activation',
			capability:  'manage_options',
			menu_slug:   'festinger-vault-activation',
			callback:    'festinger_vault_activation_function'
		 );
	}

	add_submenu_page(
		parent_slug: 'festinger-vault',
		page_title:  'Plugin Updates',
		menu_title:  'Plugin Updates',
		capability:  'manage_options',
		menu_slug:   'festinger-vault-updates',
		callback:    'festinger_vault_plugin_updates_function'
	 );

	add_submenu_page(
		parent_slug: 'festinger-vault',
		page_title:  'Theme Updates',
		menu_title:  'Theme Updates',
		capability:  'manage_options',
		menu_slug:   'festinger-vault-theme-updates',
		callback:    'festinger_vault_theme_updates_function'
	 );

	// Only add History and Settings page when white labeling is not enabled
	if ( get_option( 'wl_fv_plugin_wl_enable' ) != 1 ) {

		add_submenu_page(
			parent_slug: 'festinger-vault',
			page_title:  'History',
			menu_title:  'History',
			capability:  'manage_options',
			menu_slug:   'festinger-vault-theme-history',
			callback:    'festinger_vault_theme_history_function'
		 );

		add_submenu_page(
			parent_slug: 'festinger-vault',
			page_title:  'Settings',
			menu_title:  'Settings',
			capability:  'manage_options',
			menu_slug:   'festinger-vault-settings',
			callback:    'festinger_vault_settings_function'
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
function remove_under_middle_score( $string ) {
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
	if ( get_option( 'wl_fv_plugin_wl_enable' ) == true ) {
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
 * @param array $params $args to add to the query
 * @return void
 */
function request_data_activation( $params ) {
	$query    = esc_url_raw( add_query_arg( $params, FV_REST_API_URL . 'request-data' ) );
	$response = fv_remote_run_query( $query );
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

	$api_params = array(
		'license_key'  => $_POST['licenseKeyInput'],
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'activation',
		'license_v'    => FV_PLUGIN_VERSION,
	 );

	$query        = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'license-activation' ) );
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

	$api_params = array(
		'license_key'  => $_POST['license_key'],
		'license_d'    => $_POST['license_d'],
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'deactivation',
		'license_v'    => FV_PLUGIN_VERSION,
	 );

	$query        = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'license-deactivation' ) );
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

	$api_params = array(
		'license_key'  => $_POST['license_key'],
		'license_d'    => $_POST['license_d'],
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'deactivation',
		'license_v'    => FV_PLUGIN_VERSION,
	 );

	$query        = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'license-deactivation' ) );
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

	$api_params = array(
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

	$query        = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'search-data' ) );
	$response     = fv_remote_run_query( $query );
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

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

	$api_params = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'get_all_license_data',
		'license_v'     => FV_PLUGIN_VERSION,
	 );

	$query            = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'get-all-license-data' ) );
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
function festinger_vault_activation_function() {

	$api_params = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'get_all_license_data',
		'license_v'     => FV_PLUGIN_VERSION,
	 );

	$query            = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'get-all-license-data' ) );
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

	/* White labling not allowed in license */
	if ( $all_license_data->license_1->options->white_label == 'no'
	&&   $all_license_data->license_2->options->white_label == 'no' ) {
		fv_forget_white_label_settings();
	}

	// render activation page.
	include( FV_PLUGIN_DIR . '/sections/fv_activation.php' );

	get_plugin_theme_data_details( 'all_plugins_themes' );
}

/**
 * Clear all whitelisting settings.
 *
 * @return void
 */
function fv_forget_white_label_settings() {

	$options = array(
		'wl_fv_plugin_agency_author_wl_',
		'wl_fv_plugin_author_url_wl_',
		'wl_fv_plugin_name_wl_',
		'wl_fv_plugin_slogan_wl_',
		'wl_fv_plugin_icon_url_wl_',
		'wl_fv_plugin_description_wl_',
		'wl_fv_plugin_wl_enable',
	 );

	foreach ( $options as $option ) {
		if ( get_option( $option ) ) {
			delete_option( $option );
		}
	}
}

/**
 * Gets themes data and renders theme updates page.
 *
 * @return void
 */
function festinger_vault_theme_updates_function() {

	$allThemes            = fv_get_themes();
	$activeTheme          = wp_get_theme();

	$requested_plugins = [];
	$requested_themes  = [];

	$all_plugins = fv_get_plugins();
	if ( ! empty( $all_plugins ) ) {
		foreach ( $all_plugins as $plugin_slug => $values ) {
			$version                = fv_esc_version( $values['Version'] );
			$slug                   = get_plugin_slug_from_data( $plugin_slug, $values );
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
		$requested_themes[] = [
			'slug'    => $get_theme_slug,
			'version' => $theme->Version,
			'dl_link' => ''
		];
	}

	$api_params = array(
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

	$query                     = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'plugin-theme-updater' ) );
	$response                  = fv_remote_run_query( $query );
	$license_histories         = json_decode( wp_remote_retrieve_body( $response ) );

	$fvault_themes_slugs      = [];
	$fvault_themes            = [];

	// Make an array of themes for comparison with installed themes
	if ( ! isset( $license_histories->result )
	||   ! in_array( $license_histories->result, array( 'domainblocked', 'failed' ) ) ) {

		foreach( $license_histories->themes as $theme ) {
			$fvault_themes_slugs[]  = $theme->slug;
			$fvault_themes[]        = $theme;
		}
	}

	$is_update_available = 0;
	if ( ! empty ( $fvault_themes_slugs ) ) {
		foreach( $allThemes as $theme ) {
			if ( in_array( fv_get_wp_theme_slug( $theme ), $fvault_themes_slugs ) ) {
				foreach( $fvault_themes as $single_t ) {
					if ( $single_t->slug == fv_get_wp_theme_slug( $theme )
					&& version_compare( $single_t->version, $theme['Version'], '>' ) ) {
						$is_update_available = 1;
					}
				}
			}
		}
	}

	include( FV_PLUGIN_DIR . '/sections/fv_theme_updates.php' );
}

/**
 * Collect data and render plugin Updates page.
 *
 * @return void
 */
function festinger_vault_plugin_updates_function() {

	$allPlugins           = fv_get_plugins();
	$activePlugins        = get_option( 'active_plugins' );

	$requested_plugins = [];
	$requested_themes  = [];
	$all_plugins          = fv_get_plugins();

	if ( ! empty( $all_plugins ) ) {
		foreach ( $all_plugins as $plugin_slug=>$values ) {
			$version                = fv_esc_version( $values['Version'] );
			$slug                   = get_plugin_slug_from_data( $plugin_slug, $values );
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
		$requested_themes[] = [
			'slug'    => $get_theme_slug,
			'version' => $theme->Version,
			'dl_link' => ''
		];
	}

	$chunkSize                  = 95;
	$firstChunkSize             = $chunkSize - count( $requested_themes );
	$requested_pluginss      = fv_array_split( $requested_plugins, $firstChunkSize, $chunkSize );

	$fetching_plugin_lists      = [];
	$fetching_plugin_lists_full = [];
	$dwp_first_cycle            = true;

	foreach ( $requested_pluginss as $requested_plugins ) {

		$plugin_api_param = array(
			'license_key'     => fv_get_license_key(),
			'license_key_2'   => fv_get_license_key_2(),
			'license_d'       => fv_get_license_domain_id(),
			'license_d_2'     => fv_get_license_domain_id_2(),
			'all_plugin_list' => $requested_plugins,
			'all_theme_list'  => $dwp_first_cycle ? $requested_themes : [],
			'license_pp'      => $_SERVER['REMOTE_ADDR'],
			'license_host'    => $_SERVER['HTTP_HOST'],
			'license_mode'    => 'get_plugins_and_themes_matched_by_vault',
			'license_v'       => FV_PLUGIN_VERSION,
		 );

		$dwp_first_cycle       = false;
		$query_pl_updater      = esc_url_raw( add_query_arg( $plugin_api_param, FV_REST_API_URL . 'plugin-theme-updater' ) );
		$response_pl_updater   = fv_remote_run_query( $query_pl_updater );
		$pluginUpdate_get_data = json_decode( wp_remote_retrieve_body( $response_pl_updater ) );

		if ( ! isset( $pluginUpdate_get_data->result )
		||   ! in_array( $pluginUpdate_get_data->result, array( 'domainblocked', 'failed' ) ) ) {
			foreach( $pluginUpdate_get_data->plugins as $plugin ) {
				$fetching_plugin_lists[]      = $plugin->slug;
				$fetching_plugin_lists_full[] = $plugin;
			}
		}
	}

	$is_update_available = 0;
	$new_version         = '';

	// find out if there is any update available.
	if ( ! empty( $fetching_plugin_lists ) ) {
		foreach( $allPlugins as $p_basename => $p_details ) {
			if ( in_array( get_plugin_slug_from_data( $p_basename, $p_details ), $fetching_plugin_lists ) ) {
				foreach( $fetching_plugin_lists_full as $fv_plugin ) {
					if ( $fv_plugin->slug == get_plugin_slug_from_data( $p_basename, $p_details )
					&&   version_compare( $fv_plugin->version, $p_details['Version'], '>' ) ) {
						$is_update_available = 1;
						// When an update is found, we can break out both loops and go to the page rendering.
						break 2;
					}
				}
			}
		}
	}

	// render the plugin update page.
	include( FV_PLUGIN_DIR . '/sections/fv_plugin_updates.php' );
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
			$slug                   = get_plugin_slug_from_data( $plugin_slug, $values );
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

	if ( fv_has_license() ) {

		// API query parameters
		$api_params = array(
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

		$query             = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'plugin-theme-updater' ) );
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
			$slug                   = get_plugin_slug_from_data( $plugin_slug, $values );
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

	if ( fv_has_license() ) {

		$api_params = array(
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
		$query             = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'plugin-theme-updater' ) );
		$response          = fv_remote_run_query( $query );
		$license_histories = json_decode( wp_remote_retrieve_body( $response ) );

		// build an array of plugin slugs from festinger vault results
		$fetching_plugin_lists = [];
		foreach( $license_histories->plugins as $plugin ) {
			$fetching_plugin_lists[] = $plugin->slug;
		}

		$activePlugins = get_option( 'active_plugins' );

		foreach( $allPlugins as $key => $value ) {

			if ( in_array( get_plugin_slug_from_data( $key, $value ), $fetching_plugin_lists ) ) {

				// installed plugin also in festinger vault.

				if ( in_array( $key, $activePlugins ) ) {

					// plugin is active
					echo '<tr>';
					echo "<td class='plugin_update_width_30'>
							{$value['Name']} <br/>
							<span class='badge bg-success'>Active</span>
							</td>";
					echo "<td class='plugin_update_width_60'>". substr( $value['Description'], 0, 180 )."...
							<br/>Slug: ".get_plugin_slug_from_data( $key, $value )."
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

							<br/>Slug: ".get_plugin_slug_from_data( $key, $value )."

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
 * Determine slug of the plugin:
 *
 * @param string $slug_by_directory The plugins slug.
 * @param array $details_array The plugins data.
 * @return string The plugins textdomain if available, otherwise the dir part from the slug.
 */
function get_plugin_slug_from_data( $slug_by_directory, $details_array ) {

	$slug_by_directory = explode( '/', $slug_by_directory )[0];
	$final_slug        = '';

	// note that this comparison is redundant.
	// The code will always choose textdomain if filled.
	if ( $details_array['TextDomain'] == $slug_by_directory ) {
		$final_slug = $details_array['TextDomain'];
	} else {
		if ( empty( $details_array['TextDomain'] ) ) {
			$final_slug = $slug_by_directory;
		} else {
			$final_slug = $details_array['TextDomain'];
		}
	}
	return $final_slug;
}

/**
 * Renders the plugins history page.
 *
 * @return void
 */
function festinger_vault_theme_history_function() {

	if ( fv_has_license() ) {

		$api_params = array(
			'license_key'   => fv_get_license_key(),
			'license_key_2' => fv_get_license_key_2(),
			'license_d'     => fv_get_license_domain_id(),
			'license_d_2'   => fv_get_license_domain_id_2(),
			'license_pp'    => $_SERVER['REMOTE_ADDR'],
			'license_host'  => $_SERVER['HTTP_HOST'],
			'license_mode'  => 'get_history',
			'license_v'     => FV_PLUGIN_VERSION,
		 );

		$query             = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'get-license-history' ) );
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

	// if ( fv_has_license() ) {

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

		$query             = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'get-multi-purpose-data' ) );
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

	$api_params = array(
		'license_key'  => $_POST['license_key'],
		'refill_key'   => $_POST['refill_key'],
		'license_pp'   => $_SERVER['REMOTE_ADDR'],
		'license_host' => $_SERVER['HTTP_HOST'],
		'license_mode' => 'refill_history',
		'license_v'    => FV_PLUGIN_VERSION,
	 );

	$query       = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'refill-license' ) );
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

			$plugin_slug         = get_plugin_slug_from_data( $plugin_basename, $plugin_data );
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
		return $clen;
		// return size in bytes
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
			$slug      = get_plugin_slug_from_data( $plugin_basename, $plugin_data );

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

	if ( fv_has_license() ) {

		$api_params = array(
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

		$query             = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'plugin-theme-updater' ) );
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
					$get_theme_slug = fv_get_wp_theme_slug( $theme );
					if (  empty( $get_theme_slug ) ) {
						$get_theme_slug = $theme->get( 'TextDomain' );
					}
					$get_theme_directory[] = [
						'dir'     => $theme->get_stylesheet(),
						'slug'    => $get_theme_slug,
						'version' => $theme->Version];
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

									if ( is_wp_error( $tmpfile ) == true ) {

										$chk_fl_dl_sz = fv_curlRemoteFilesize( $u->dl_link );
										if ( $chk_fl_dl_sz > 0 ) {
											$t_dl_fl_sz+=$chk_fl_dl_sz;
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

										$api_params_dif = array(
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

										$query_dif = esc_url_raw( add_query_arg( $api_params_dif, FV_REST_API_URL . 'update-request-load' ) );
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

										$api_params_dif = array(
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

										$query_dif = esc_url_raw( add_query_arg( $api_params_dif, FV_REST_API_URL . 'update-request-load' ) );
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
						$slug    = get_plugin_slug_from_data( $plugin_basename, $data );

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

										$api_params_dif = array(
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

										$query_dif = esc_url_raw( add_query_arg( $api_params_dif, FV_REST_API_URL . 'update-request-load' ) );
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

										$api_params_dif = array(
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

										$query_dif = esc_url_raw( add_query_arg( $api_params_dif, FV_REST_API_URL . 'update-request-load' ) );
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
	$t_dl_fl_sz = 10;
	$requested_plugins=[];
	$requested_themes=[];
	$all_plugins = fv_get_plugins();

	if ( ! empty( $all_plugins ) ) {

		foreach ( $all_plugins as $plugin_slug => $values ) {
			$version = fv_esc_version( $values['Version'] );
			$slug    = get_plugin_slug_from_data( $plugin_slug, $values );

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

	if ( fv_has_license() ) {

		$api_params = array(
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

		$query = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'plugin-theme-updater' ) );

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
											$t_dl_fl_sz+=$chk_fl_dl_sz;
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

										$api_params_dif = array(
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
										$query_dif = esc_url_raw( add_query_arg( $api_params_dif, FV_REST_API_URL . 'update-request-load' ) );
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
										$t_dl_fl_sz+=$chk_fl_dl_sz;
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

									$api_params_dif = array(
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
									$query_dif = esc_url_raw( add_query_arg( $api_params_dif, FV_REST_API_URL . 'update-request-load' ) );
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

					foreach ( $all_plugins as $plugin_slug=>$values ) {
						$version=fv_esc_version( $values['Version'] );
						$slug=get_plugin_slug_from_data( $plugin_slug, $values );
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
											$t_dl_fl_sz+=$chk_fl_dl_sz;
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

										$api_params_dif = array(
											'license_key' => fv_get_license_key(),
											'license_key_2' => fv_get_license_key_2(),
											'license_d' => fv_get_license_domain_id(),
											'license_d_2' => fv_get_license_domain_id_2(),
											'plugin_theme_slug' => $u->slug,
											'plugin_theme_version' => $u->version,
											'license_pp' => $_SERVER['REMOTE_ADDR'],
											'license_host'=> $_SERVER['HTTP_HOST'],
											'license_mode'=> 'update_request_load',
											'license_v'=> FV_PLUGIN_VERSION,
										 );

										$query_dif = esc_url_raw( add_query_arg( $api_params_dif, FV_REST_API_URL . 'update-request-load' ) );
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
											$t_dl_fl_sz+=$chk_fl_dl_sz;
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

										$api_params_dif = array(
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

										$query_dif = esc_url_raw( add_query_arg( $api_params_dif, FV_REST_API_URL . 'update-request-load' ) );
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

	$api_params = array(
		'license_d'            => fv_get_license_domain_id(),
		'license_pp'           => $_SERVER['REMOTE_ADDR'],
		'license_host'         => $_SERVER['HTTP_HOST'],
		'license_mode'         => 'download',
		'license_v'            => FV_PLUGIN_VERSION,
		'plugin_download_hash' => 'ff4e1b8e4bc36381389eaac20fae1169',
		'license_key'          => '53fd42a77eb617e31fca2439f4e51fd20bd96754',
	 );

	$query        = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'plugin-download' ) );
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

function festinger_vault_settings_function() {

	$api_params = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'get_all_license_data',
		'license_v'     => FV_PLUGIN_VERSION,
	 );

	$query    = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'get-all-license-data' ) );
	$response = fv_remote_run_query( $query );

	if ( is_wp_error( $response ) ) {

		// there seems something is wrong here. Why is the next code run when is_wp_error?
		// Is there an ending curly bracket missing in the original code?
		// also it gets the same options twice and runs the same remote query twice.
		// this code doesn't seem right.
		// and the query had been run allready above this point,
		// so the same code is run three times when is_wp_error
		// occurs on the first time. The third time is unconditional.

		$api_params = array(
			'license_key'   => fv_get_license_key(),
			'license_key_2' => fv_get_license_key_2(),
			'license_d'     => fv_get_license_domain_id(),
			'license_d_2'   => fv_get_license_domain_id_2(),
			'license_pp'    => $_SERVER['REMOTE_ADDR'],
			'license_host'  => $_SERVER['HTTP_HOST'],
			'license_mode'  => 'get_all_license_data',
			'license_v'     => FV_PLUGIN_VERSION,
		 );

		$query    = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'get-all-license-data' ) );
		$response = fv_remote_run_query( $query );

		// just repeating the same code unconditionally seems senseless.
		// this can't be right. Is this just to repeat in the hope it will work?
		// then at least it should first check if it is needed.

		$api_params = array(
			'license_key'   => fv_get_license_key(),
			'license_key_2' => fv_get_license_key_2(),
			'license_d'     => fv_get_license_domain_id(),
			'license_d_2'   => fv_get_license_domain_id_2(),
			'license_pp'    => $_SERVER['REMOTE_ADDR'],
			'license_host'  => $_SERVER['HTTP_HOST'],
			'license_mode'  => 'get_all_license_data',
			'license_v'     => FV_PLUGIN_VERSION,
		 );

		$query    = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'get-all-license-data' ) );
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
				'slug' => get_plugin_slug_from_data( $key, $value )
			];
		} else {
			$all_plugins_list []    = [
				'name'=> urlencode( $value['Name'] ),
				'slug'=> get_plugin_slug_from_data( $key, $value )
			];
		}
		$get_inactive_plugins[] = [
			'name'=> urlencode( $value['Name'] ),
			'slug'=> get_plugin_slug_from_data( $key, $value )
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
				$api_params = array(
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

				$query    = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'licensedinfolist' ) );
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

function festinger_vault_plugins_inside () {

	$api_params = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'get_all_license_data',
		'license_v'     => FV_PLUGIN_VERSION,
	 );

	$query            = esc_url_raw( add_query_arg( $api_params, FV_REST_API_URL . 'get-all-license-data' ) );
	$response         = fv_remote_run_query( $query );
	$all_license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// remove white_list settings if the plugin isn't set to use them.
	if ( $all_license_data->license_1->options->white_label == 'no'
	&&   $all_license_data->license_2->options->white_label == 'no' ) {
		if ( get_option( 'wl_fv_plugin_agency_author_wl_' ) == true ) {
			delete_option( 'wl_fv_plugin_agency_author_wl_' );
		}
		if ( get_option( 'wl_fv_plugin_author_url_wl_' ) == true ) {
			delete_option( 'wl_fv_plugin_author_url_wl_' );
		}
		if ( get_option( 'wl_fv_plugin_slogan_wl_' ) == true ) {
			delete_option( 'wl_fv_plugin_slogan_wl_' );
		}
		if ( get_option( 'wl_fv_plugin_icon_url_wl_' ) == true ) {
			delete_option( 'wl_fv_plugin_icon_url_wl_' );
		}
		if ( get_option( 'wl_fv_plugin_name_wl_' ) == true ) {
			delete_option( 'wl_fv_plugin_name_wl_' );
		}
		if ( get_option( 'wl_fv_plugin_description_wl_' ) == true ) {
			delete_option( 'wl_fv_plugin_description_wl_' );
		}
		if ( get_option( 'wl_fv_plugin_wl_enable' ) == true ) {
			delete_option( 'wl_fv_plugin_wl_enable' );
		}
	}

	// render the vault page.
	include( FV_PLUGIN_DIR . '/sections/fv_plugins.php' );

	// get_plugin_theme_data_details( 'all_plugins_themes' );
}

function get_adm_men_author() {
	if ( get_option( 'wl_fv_plugin_agency_author_wl_' ) == true ) {
		return get_option( 'wl_fv_plugin_agency_author_wl_' );
	} else {
		return 'Festinger Vault';
	}
}
function get_adm_men_author_uri() {
	if ( get_option( 'wl_fv_plugin_author_url_wl_' ) == true ) {
		return get_option( 'wl_fv_plugin_author_url_wl_' );
	} else {
		return 'https://festingervault.com/';
	}
}

function get_adm_men_name() {
	if ( get_option( 'wl_fv_plugin_name_wl_' ) == true ) {
		return get_option( 'wl_fv_plugin_name_wl_' );
	} else {
		return 'Festinger Vault';
	}
}

function get_adm_men_description() {
	if ( get_option( 'wl_fv_plugin_description_wl_' ) == true ) {
		return get_option( 'wl_fv_plugin_description_wl_' );
	} else {
		return 'Get access to 25K+ kick-ass premium WordPress themes and plugins. Now directly from your WP dashboard. Get automatic updates and one-click installation by installing the Festinger Vault plugin.';
	}
}

function get_adm_men_slogan() {

	if ( get_option( 'wl_fv_plugin_slogan_wl_' ) == true ) {
		return get_option( 'wl_fv_plugin_slogan_wl_' );
	} else {
		return 'Get access to 25K+ kick-ass premium WordPress themes and plugins. Now directly from your WP dashboard. <br/>Get automatic updates and one-click installation by installing the Festinger Vault plugin.';
	}

}

function get_adm_men_img() {
	if ( get_option( 'wl_fv_plugin_icon_url_wl_' ) == true ) {
		return get_option( 'wl_fv_plugin_icon_url_wl_' );
	} else {
		return FV_PLUGIN_ABSOLUTE_PATH.'assets/images/logo.png';
	}
}

if ( isset( $_POST ) && ! empty( $_POST['fv_wl_submit'] ) && $_POST['fv_wl_submit'] ) {
	add_action( 'init', 'process_post222111' );
	function process_post222111() {

		delete_option( 'wl_fv_plugin_agency_author_wl_' );

		delete_option( 'wl_fv_plugin_author_url_wl_' );

		delete_option( 'wl_fv_plugin_slogan_wl_' );

		delete_option( 'wl_fv_plugin_icon_url_wl_' );

		delete_option( 'wl_fv_plugin_name_wl_' );

		delete_option( 'wl_fv_plugin_description_wl_' );

		if ( get_option( 'wl_fv_plugin_agency_author_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_agency_author_wl_', htmlspecialchars( $_POST['agency_author'] ) );
		} else {
			add_option( 'wl_fv_plugin_agency_author_wl_', htmlspecialchars( $_POST['agency_author'] ) );
		}

		if ( get_option( 'wl_fv_plugin_author_url_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_author_url_wl_', htmlspecialchars( $_POST['agency_author_url'] ) );
		} else {
			add_option( 'wl_fv_plugin_author_url_wl_', htmlspecialchars( $_POST['agency_author_url'] ) );
		}

		if ( get_option( 'wl_fv_plugin_slogan_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_slogan_wl_', htmlspecialchars( $_POST['fv_plugin_slogan'] ) );
		} else {
			add_option( 'wl_fv_plugin_slogan_wl_', htmlspecialchars( $_POST['fv_plugin_slogan'] ) );
		}

		if ( get_option( 'wl_fv_plugin_icon_url_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_icon_url_wl_', htmlspecialchars( $_POST['fv_plugin_icon_url'] ) );
		} else {
			add_option( 'wl_fv_plugin_icon_url_wl_', htmlspecialchars( $_POST['fv_plugin_icon_url'] ) );
		}

		if ( get_option( 'wl_fv_plugin_name_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_name_wl_', htmlspecialchars( $_POST['fv_plugin_name'] ) );
		} else {
			add_option( 'wl_fv_plugin_name_wl_', htmlspecialchars( $_POST['fv_plugin_name'] ) );
		}

		if ( get_option( 'wl_fv_plugin_description_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_description_wl_', htmlspecialchars( $_POST['fv_plugin_description'] ) );
		} else {
			add_option( 'wl_fv_plugin_description_wl_', htmlspecialchars( $_POST['fv_plugin_description'] ) );
		}

		if ( ! empty( $_POST['fv_plugin_wl_enable'] ) ) {

			if ( get_option( 'wl_fv_plugin_wl_enable' ) == true ) {
				update_option( 'wl_fv_plugin_wl_enable', htmlspecialchars( $_POST['fv_plugin_wl_enable'] ) );
			} else {
				add_option( 'wl_fv_plugin_wl_enable', htmlspecialchars( $_POST['fv_plugin_wl_enable'] ) );
			}

			wp_redirect( admin_url( 'admin.php?page=festinger-vault' ) );
			exit();

		}

		wp_redirect( admin_url( 'admin.php?page=festinger-vault-settings' ) );
	}

}

/*
if ( isset( $_POST ) ) {

	if ( ! empty( $_POST['fv_wl_submit'] ) && $_POST['fv_wl_submit'] ) {

			delete_option( 'wl_fv_plugin_agency_author_wl_' );

			delete_option( 'wl_fv_plugin_author_url_wl_' );

			delete_option( 'wl_fv_plugin_slogan_wl_' );

			delete_option( 'wl_fv_plugin_icon_url_wl_' );

			delete_option( 'wl_fv_plugin_name_wl_' );

			delete_option( 'wl_fv_plugin_description_wl_' );

		if ( get_option( 'wl_fv_plugin_agency_author_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_agency_author_wl_', htmlspecialchars( $_POST['agency_author'] ) );
		} else {
			add_option( 'wl_fv_plugin_agency_author_wl_', htmlspecialchars( $_POST['agency_author'] ) );
		}

		if ( get_option( 'wl_fv_plugin_author_url_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_author_url_wl_', htmlspecialchars( $_POST['agency_author_url'] ) );
		} else {
			add_option( 'wl_fv_plugin_author_url_wl_', htmlspecialchars( $_POST['agency_author_url'] ) );
		}

		if ( get_option( 'wl_fv_plugin_slogan_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_slogan_wl_', htmlspecialchars( $_POST['fv_plugin_slogan'] ) );
		} else {
			add_option( 'wl_fv_plugin_slogan_wl_', htmlspecialchars( $_POST['fv_plugin_slogan'] ) );
		}

		if ( get_option( 'wl_fv_plugin_icon_url_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_icon_url_wl_', htmlspecialchars( $_POST['fv_plugin_icon_url'] ) );
		} else {
			add_option( 'wl_fv_plugin_icon_url_wl_', htmlspecialchars( $_POST['fv_plugin_icon_url'] ) );
		}

		if ( get_option( 'wl_fv_plugin_name_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_name_wl_', htmlspecialchars( $_POST['fv_plugin_name'] ) );
		} else {
			add_option( 'wl_fv_plugin_name_wl_', htmlspecialchars( $_POST['fv_plugin_name'] ) );
		}

		if ( get_option( 'wl_fv_plugin_description_wl_' ) == true ) {
			update_option( 'wl_fv_plugin_description_wl_', htmlspecialchars( $_POST['fv_plugin_description'] ) );
		} else {
			add_option( 'wl_fv_plugin_description_wl_', htmlspecialchars( $_POST['fv_plugin_description'] ) );
		}

	if ( ! empty( $_POST['fv_plugin_wl_enable'] ) ) {

		if ( get_option( 'wl_fv_plugin_wl_enable' ) == true ) {
			update_option( 'wl_fv_plugin_wl_enable', htmlspecialchars( $_POST['fv_plugin_wl_enable'] ) );
		} else {
			add_option( 'wl_fv_plugin_wl_enable', htmlspecialchars( $_POST['fv_plugin_wl_enable'] ) );
		}
	}

}

}
*/

/**
 *  Processing of submitted "Hide/Block admin notices" settings form.
 */
if ( isset( $_POST ) && ! empty( $_POST['fv_admin_notice'] ) && $_POST['fv_admin_notice'] ) {

	// Setting: Block only dismissable admin notices
	if ( ! empty( $_POST['an_fv_dis_adm_not_hid'] ) ) {
		if ( get_option( 'an_fv_dis_adm_not_hid' ) == false ) {
			add_option( 'an_fv_dis_adm_not_hid', 1 );
		}
	} else {
		delete_option( 'an_fv_dis_adm_not_hid' );
	}

	// Setting: Block All admin notices
	if ( ! empty( $_POST['an_fv_all_adm_not_hid'] ) ) {
		if ( get_option( 'an_fv_all_adm_not_hid' ) == false ) {
			add_option( 'an_fv_all_adm_not_hid', 1 );
		}
	} else {
		delete_option( 'an_fv_all_adm_not_hid' );
	}
}

/**
 * Processing "FORCE UPDATE NOW" after button is used
 */
if ( isset( $_POST ) && ! empty( $_POST['pluginforceupdate'] ) && $_POST['pluginforceupdate'] ) {


	add_action( 'init', 'process_post222' );

	function process_post222() {

		fv_auto_update_download( 'plugin' );

		wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&force=success' ) );
	}
}

if ( isset( $_POST ) && ! empty( $_POST['pluginforceupdateinstant'] ) && $_POST['pluginforceupdateinstant'] ) {

	add_action( 'init', 'process_postinstant222' );

	function process_postinstant222() {
		fv_auto_update_download_instant( 'plugin' );
		wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&instant=success' ) );
	}
}

if ( isset( $_POST ) && ! empty( $_POST['singlepuginupdaterequest'] ) && $_POST['singlepuginupdaterequest'] ) {
	add_action( 'init', 'process_postSinglePluginUpdate' );
	function process_postSinglePluginUpdate() {

		$plugin_data_array = [
			'name'   => isset( $_POST['plugin_name'] ) ? $_POST['plugin_name'] : NULL,
			'type'   => 'plugin',
			'slug'   => isset( $_POST['slug'] ) ? $_POST['slug'] : NULL,
			'version'=> isset( $_POST['version'] ) ? $_POST['version'] : NULL,
		];
		fv_auto_update_download( 'plugin', $plugin_data_array );

		wp_redirect( admin_url( 'admin.php?page=festinger-vault-updates&force=success' ) );
	}

}

/**
 * Form was submitted asking a single theme force update.
 */
if ( isset( $_POST ) && ! empty( $_POST['singlethemeupdaterequest'] ) && $_POST['singlethemeupdaterequest'] ) {

	add_action( 'init', 'process_singlepuginupdaterequest' );
	/**
	 * Initiate a single theme's instant update.
	 *
	 * @return void
	 */
	function process_singlepuginupdaterequest() {

		$theme_data_array = [
			'name'   => isset( $_POST['theme_name'] ) ? $_POST['theme_name']:NULL,
			'type'   => 'theme',
			'slug'   => isset( $_POST['slug'] ) ? $_POST['slug']:NULL,
			'version'=> isset( $_POST['version'] ) ? $_POST['version']:NULL,
		];
		fv_auto_update_download( 'theme', $theme_data_array );

		wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&force=success' ) );
	}

}

if ( isset( $_POST ) && ! empty( $_POST['themeforceupdate'] ) && $_POST['themeforceupdate'] ) {
	add_action( 'init', 'process_post_theme' );
	function process_post_theme() {
		fv_auto_update_download( 'theme' );
		wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&force=success' ) );
	}
}

if ( isset( $_POST ) && ! empty( $_POST['themeforceupdate_instant'] ) && $_POST['themeforceupdate_instant'] ) {
	add_action( 'init', 'process_post_theme_instant' );
	function process_post_theme_instant() {
		fv_auto_update_download_instant( 'theme' );
		wp_redirect( admin_url( 'admin.php?page=festinger-vault-theme-updates&instant=success' ) );
	}
}

function fv_get_client_ip() {
	$ipaddress = '';
	if (isset( $_SERVER['HTTP_CLIENT_IP'] ) )
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) )
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) )
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if ( isset( $_SERVER['HTTP_FORWARDED'] ) )
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if ( isset( $_SERVER['REMOTE_ADDR'] ) )
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}

if ( get_option( 'an_fv_all_adm_not_hid' ) == true ) {

	add_action( 'admin_enqueue_scripts', 'block_dismissable_admin_notices' );
	add_action( 'login_enqueue_scripts', 'block_dismissable_admin_notices' );

	function block_dismissable_admin_notices() {
	   echo '<style>.wp-core-ui .notice{ display: none !important; }</style>';
	}

	add_action( 'admin_enqueue_scripts', 'block_admin_notices' );
	add_action( 'login_enqueue_scripts', 'block_admin_notices' );

	function block_admin_notices() {

		global $wp_filter;

		if (is_user_admin() ) {
			if (isset( $wp_filter['user_admin_notices'] ) ) {
			}
		} elseif (isset( $wp_filter['admin_notices'] ) ) {
		}
	}

	add_action( 'init', 'remove_my_action' );
	function remove_my_action() {
		global $wp_filter; // why?
	}

	add_action( 'init', 'remove_my_action2' );
	function remove_my_action2() {
		global $wp_filter;  // why?
		remove_action( 'admin_notices', 'rocket_warning_htaccess_permissions' );
		remove_action( 'admin_notices', 'rocket_warning_config_dir_permissions' );
	}

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

		$slug = get_plugin_slug_from_data( $basename, $data );

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

if ( get_option( 'an_fv_dis_adm_not_hid' ) == true ) {

	add_action( 'admin_enqueue_scripts', 'block_dismissable_admin_notices2' );
	add_action( 'login_enqueue_scripts', 'block_dismissable_admin_notices2' );

	function block_dismissable_admin_notices2() {
	   echo '<style>.is-dismissible { display: none !important; }</style>';
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

	$upload_dir = wp_upload_dir();

	if ( $plugin_or_theme == 'plugin' ) {

		$plugin_base_file_get = get_plugin_basefile_by_slug( $slug );
		$backup_plugin_dir    = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/backup/" . $plugin_base_file_get;

		if ( file_exists( $backup_plugin_dir ) ) {
			if ( version_compare( $version, get_plugin_data( $backup_plugin_dir )['Version'], '>' ) ) {
		?>
			<form name="plugin_rollback" method="POST" onSubmit="if ( !confirm( 'Are you sure want to rollback?' ) ) {return false;}">
				<input type="hidden" name="slug" value="<?= $slug;?>" />
				<input type="hidden" name="version" value="<?= $version;?>" />
				<button class="btn btn_rollback btn-sm float-end btn-custom-color" id="pluginrollback" type="submit" name="pluginrollback"
					value="plugin">Rollback <?= get_plugin_data( $backup_plugin_dir )['Version'];?></button>
			</form>
		<?php
			} else {
				echo "<div class=' bg-tag border-8 text-center rollback-not-available'>Not Available</div>";
			}
		} else {
			echo "<div class='bg-tag border-8 text-center rollback-not-available'>Not Available</div>";
		}
	}

	if ( $plugin_or_theme == 'theme' ) {

		$backup_theme_dir =  $upload_dir["basedir"]."/fv_auto_update_directory/themes/backup/";
		$get_all_themes   = scandir( $backup_theme_dir );
		foreach( $get_all_themes as $single_theme ) {
			if ( strpos( $single_theme, $slug ) !== false ) {
				$theme_full    = explode( "-v-", $single_theme );
				$theme_name    = ! empty( $theme_full[0] ) ? $theme_full[0] : "";
				$theme_version = ! empty( $theme_full[1] ) ? $theme_full[1] : "";
				if ( file_exists( $backup_theme_dir . $single_theme ) ) {
					if ( ! empty( $theme_version )
					&& version_compare( $version, $theme_version, ">" ) ) {
						?>
						<form name="theme_rollback" method="POST"
							onSubmit="if ( !confirm( 'Are you sure want to rollback this theme?' ) ) {return false;}">
							<input type="hidden" name="slug" value="<?= $slug;?>" />
							<input type="hidden" name="version" value="<?= $version;?>" />
							<button class="btn btn-sm float-end btn-custom-color btn_rollback" id="themerollback" type="submit" name="themerollback"
								value="plugin">Rollback <?= $theme_version;?></button>
						</form>
						<?php
					} else {
						echo "<div class='btn non_active_button roleback-not-available'>Not Available</div>";
					}
				} else {
					echo "<div class='btn non_active_button roleback-not-available'>Not Available</div>";
				}
			}
		}
	}
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
function fv_get_plugins() {
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
		$response = wp_remote_post( $query_232, array( 'timeout' => 200, 'sslverify' => true ) );
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
		fv_forget_license()
	}
	if ( fv_get_license_key_2() == $license_key ) {
		fv_forget_license_2()
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

function fv_should_auto_update_plugin( string $slug ): bool	{

	if ( empty( $slug ) ) {
		return false;
	}

	return is_array( get_option( 'fv_plugin_auto_update_list' ) )
		&& in_array( $slug, get_option( 'fv_plugin_auto_update_list' ) );
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
function fv_has_license(): bool {

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
	delete_option( '_data_ls_key_no_id_vf' );
	delete_option( '_ls_domain_sp_id_vf' );
	delete_option( '_ls_d_sf' );
}

/**
 * Delete the second license from the options.
 *
 * @return void
 */
function fv_forget_license_2() {
	delete_option( '_data_ls_key_no_id_vf_2' );
	delete_option( '_ls_domain_sp_id_vf_2' );
	delete_option( '_ls_d_sf_2' );
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
	return ( empty ( $license_data['license-key'] )
		||   empty ( $license_data['domain-id'] )
		||   empty ( $license_data['_ls_d_sf'] ) );
}

/**
 * Save licence data in options.
 *
 * @param array $license_data {
 *     @type string 'license-key' License key (returned by api as 'l_dat' attribute).
 *     @type string 'domain-id'   Domain id (returned by api as 'data_security_dom' attribute).
 *     @type string '_ls_d_sf'    Returned by api as 'ld_dat' attribute.
 * }
 * @return void|false
 */
function fv_save_license( array $license_data ) {

	if ( ! fv_license_complete( $license_data ) ) {
		return false;
	}

	if ( fv_has_license_1() ) {
		return fv_save_license_2( $license_data );
	}
	if ( fv_has_license_2() ) {
		return fv_save_license_1( $license_data );
	}
	// Both licenses already saved.
	return false;
}

/**
 * Save licence data in first license options.
 *
 * @param array $license_data {
 *     @type string 'license-key' License key (returned by api as 'l_dat' attribute).
 *     @type string 'domain-id'   Domain id (returned by api as 'data_security_dom' attribute).
 *     @type string '_ls_d_sf'    Returned by api as 'ld_dat' attribute.
 * }
 * @return void|false
 */
function fv_save_license_1( array $license_data ) {

	if ( ! fv_license_complete( $license_data ) ) {
		return false;
	}

	add_option( '_data_ls_key_no_id_vf', $license_data['license-key'] );
	add_option( '_ls_domain_sp_id_vf',   $license_data['domain-id'] );
	add_option( '_ls_d_sf',              $license_data['_ls_d_sf'] );
}

/**
 * Save licence data in first license options.
 *
 * @param array $license_data {
 *     @type string 'license-key' License key (returned by api as 'l_dat' attribute).
 *     @type string 'domain-id'   Domain id (returned by api as 'data_security_dom' attribute).
 *     @type string '_ls_d_sf'    Returned by api as 'ld_dat' attribute.
 * }
 * @return void|false
 */
function fv_save_license_2( array $license_data ): void {

	if ( ! fv_license_complete( $license_data ) ) {
		return false;
	}

	add_option( '_data_ls_key_no_id_vf_2', $license_data['license-key'] );
	add_option( '_ls_domain_sp_id_vf_2',   $license_data['domain-id'] );
	add_option( '_ls_d_sf_2',              $license_data['_ls_d_sf'] );
}
