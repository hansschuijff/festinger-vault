<?php

/**
 * Adds/removes theme from the auto-update list after toggle switch is clicked in theme update page.
 *
 * @return void
 */
function fv_toggle_theme_auto_update() : void {

	if ( empty( $_POST['fv_theme_slug'] )
	|| ! isset( $_POST['fv_theme_auto_update_is_checked'] ) ) {
		return;
	}

	if ( 'true' === $_POST['fv_theme_auto_update_is_checked'] ) {
		$fv_theme_auto_update_is_checked = true;
	} else {
		$fv_theme_auto_update_is_checked = false;
	}

	// inform remote vault about new switch status.
	$query_base_url = FV_REST_API_URL . 'recurring-slug-cap';
	$query_args     = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'slug_type'     => 'theme',
		'captured_slug' => $_POST['fv_theme_slug'],
		'action_status' => $fv_theme_auto_update_is_checked ? 1 : 0,
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'captured_slug_st',
		'license_v'     => FV_PLUGIN_VERSION,
	);
	$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response = fv_run_remote_query( $query );
	$fv_api   = json_decode( wp_remote_retrieve_body( $response ) );

	if ( in_array( $fv_api->status, array( 'na', 'inserted', 'updated' ), true ) ) {
		fv_save_theme_auto_update( $_POST['fv_theme_slug'], $theme_switch_is_checked );
	}

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_toggle_theme_auto_update',        'fv_toggle_theme_auto_update' );
add_action( 'wp_ajax_nopriv_fv_toggle_theme_auto_update', 'fv_toggle_theme_auto_update' );

/**
 * Adds or removes a theme to/from the auto-update list.
 *
 * @param string $slug A theme's (stylesheet) slug.
 * @param boolean $true True/false should the theme auto-update?
 * @return void
 */
function fv_save_theme_auto_update( string $slug, bool $true ) : void {

	$fv_themes_auto_update_list = fv_get_themes_auto_update_list();

	$key = array_search( $slug, $fv_themes_auto_update_list, true );

	if ( $true ) {
		// add theme to list if necessary
		if ( false === $key ) {
			$fv_themes_auto_update_list[] = $slug;
		}
	} else {
		// remove $slug from list if necessary.
		if ( false !== $key ) {
			unset ( $fv_themes_auto_update_list[ $key ] );
		}
	}

	// save list
	fv_set_themes_auto_update_list( $fv_themes_auto_update_list );
}

/**
 * Get the themes auto-update list from options.
 *
 * @return array An array containing theme (stylesheet) slugs that are set to auto-update.
 */
function fv_get_themes_auto_update_list() : array {
	return get_option( 'fv_themes_auto_update_list', array() );
}

/**
 * Adds/removes plugin from the auto-update list after toggle switch is clicked in plugin update page.
 *
 * @return void
 */
function fv_toggle_plugin_auto_update() : void {

	if ( empty( $_POST['fv_plugin_slug'] )
	|| ! isset( $_POST['fv_plugin_auto_update_is_checked'] ) ) {
		return;
	}

	if ( 'true' === $_POST['fv_plugin_auto_update_is_checked'] ) {
		$plugin_auto_update_is_checked = true;
	} else {
		$plugin_auto_update_is_checked = false;
	}

	// inform remote vault about new switch status.
	$query_base_url = FV_REST_API_URL . 'recurring-slug-cap';
	$query_args     = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'slug_type'     => 'plugin',
		'captured_slug' => $_POST['fv_plugin_slug'],
		'action_status' => $plugin_auto_update_is_checked ? 1 : 0,
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'captured_slug_st',
		'license_v'     => FV_PLUGIN_VERSION,
	);
	$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response = fv_run_remote_query( $query );
	$fv_api   = json_decode( wp_remote_retrieve_body( $response ) );

	if ( in_array( $fv_api->status, array( 'na', 'inserted', 'updated' ), true ) ) {
		fv_save_plugin_auto_update_switch( $_POST['fv_plugin_slug'], $plugin_auto_update_is_checked );
	}

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_toggle_plugin_auto_update',        'fv_toggle_plugin_auto_update' );
add_action( 'wp_ajax_nopriv_fv_toggle_plugin_auto_update', 'fv_toggle_plugin_auto_update' );

/**
 * Adds or removes a plugin to/from the auto-update list.
 *
 * @param string $slug A plugin's (stylesheet) slug.
 * @param boolean $true True/false should the plugin auto-update?
 * @return void
 */
function fv_save_plugin_auto_update_switch( string $slug, bool $true ) : void {

	$fv_plugins_auto_update_list = fv_get_plugins_auto_update_list();

	$key = array_search( $slug, $fv_plugins_auto_update_list, true );

	if ( $true ) {
		// add plugin to list if necessary
		if ( false === $key ) {
			$fv_plugins_auto_update_list[] = $slug;
		}
	} else {
		// remove $slug from list if necessary.
		if ( false !== $key ) {
			unset ( $fv_plugins_auto_update_list[ $key ] );
		}
	}

	// save list
	fv_set_plugins_auto_update_list( $fv_plugins_auto_update_list );
}

/**
 * Get the plugins auto-update list from options.
 *
 * @return array An array containing theme (stylesheet) slugs that are set to auto-update.
 */
function fv_get_plugins_auto_update_list() : array {
	return get_option( 'fv_plugins_auto_update_list', array() );
}

/**
 * Collects data of selected plugins/themes for the bulk action
 * confirmation pupup in the Vault page.
 *
 * @return void
 */
function fv_get_bulk_items_data_from_api() : void {

	$query_base_url = FV_REST_API_URL . 'get-pro-buttons-multiple';
	$query_args     = array(
		'license_key'  => fv_get_any_license_key(),
	    'license_d'    => fv_get_any_license_domain_id(),
	    'license_pp'   => $_SERVER['REMOTE_ADDR'],
	    'license_host' => $_SERVER['HTTP_HOST'],
	    'license_mode' => 'buttons_multiple',
	    'license_v'    => FV_PLUGIN_VERSION,
	    'product_hash' => $_POST['product_hash'],
	);
	$query        = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response     = fv_run_remote_query( $query );
	$fv_api       = json_decode( wp_remote_retrieve_body( $response ) );

	// echo the encoded plugin/theme data back to script.js.
	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_get_bulk_items_data_from_api',        'fv_get_bulk_items_data_from_api' );
add_action( 'wp_ajax_nopriv_fv_get_bulk_items_data_from_api', 'fv_get_bulk_items_data_from_api' );

/**
 * Click action of the download button in the Vault.
 *
 * The return is used to build a popup modal panel.
 *
 * @return void
 */
function fv_plugin_buttons_ajax() : void {

	$query_base_url = FV_REST_API_URL.'get-pro-buttons';
	$query_args     = array(
		'license_key'  => fv_get_any_license_key(),
	    'license_d'    => fv_get_any_license_domain_id(),
	    'license_pp'   => $_SERVER['REMOTE_ADDR'],
	    'license_host' => $_SERVER['HTTP_HOST'],
	    'license_mode' => 'buttons',
	    'license_v'    => FV_PLUGIN_VERSION,
	    'product_hash' => $_POST['product_hash'],
	);
	$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response = fv_run_remote_query( $query );
	$fv_api   = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_plugin_buttons_ajax', 'fv_plugin_buttons_ajax' );
add_action( 'wp_ajax_nopriv_fv_plugin_buttons_ajax', 'fv_plugin_buttons_ajax' );

/**
 * Callback for the request update button in the Vault page.
 *
 * On success of this function, script.js will show the popup.
 *
 * @return void
 */
function fv_plugin_support_link() : void {

	$genData = null;

	// fv_plugin_support_link
	if ( fv_has_any_license() ) {

		$genData = json_encode( [
			'result'              => 'success',
			'license_key'         => fv_get_any_license_key(),
			'data_support_link'   => $_POST['data_support_link'],
			'data_generated_slug' => $_POST['data_generated_slug'],
			'data_product_hash'   => $_POST['data_product_hash'],
			'data_generated_name' => $_POST['data_generated_name'],
		] );
	} else {

		// Use wp_redirect() to redirect the user to the external URL
		wp_redirect( $_POST['data_support_link'] );

		$genData = json_encode( [
			'result'      => 'redirect',
			'license_key' => fv_get_any_license_key(),
		] );

		// Make sure to exit after calling wp_redirect()
		exit;
	}

	//$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	echo $genData;

}
add_action( 'wp_ajax_fv_plugin_support_link',        'fv_plugin_support_link' );
add_action( 'wp_ajax_nopriv_fv_plugin_support_link', 'fv_plugin_support_link' );

/**
 * Callback for the Report Item button in the Vault page.
 *
 * @return void
 */
function fv_plugin_report_link() : void {

    $genData = '';

	if ( fv_has_any_license()  ) {
		$genData = json_encode( array(
			'result'              => 'success',
			'license_key'         => fv_get_any_license_key(),
			'data_support_link'   => $_POST['data_support_link'],
			'data_generated_slug' => $_POST['data_generated_slug'],
			'data_product_hash'   => $_POST['data_product_hash'],
			'data_generated_name' => $_POST['data_generated_name'],
		) );
	} else {

		// Define the URL you want to redirect to
		$redirect_url = $_POST['data_support_link'];

		// Use wp_redirect() to redirect the user to the external URL
		wp_redirect( $redirect_url );

		$genData = json_encode( array(
			'result'      => 'redirect',
			'license_key' => fv_get_any_license_key(),
		) );

		// Make sure to exit after calling wp_redirect()
		exit;
	}

	echo $genData;
}
add_action( 'wp_ajax_fv_plugin_report_link', 'fv_plugin_report_link' );
add_action( 'wp_ajax_nopriv_fv_plugin_report_link', 'fv_plugin_report_link' );

/**
 * Callback for the download button in the download popup modal in Vault page.
 *
 * @return void
 */
function fv_plugin_download_ajax() : void {

	$query_base_url = FV_REST_API_URL.'plugin-download';
	$query_args     = array(
	    'license_d'            => fv_get_any_license_domain_id(),
	    'license_pp'           => $_SERVER['REMOTE_ADDR'],
	    'license_host'         => $_SERVER['HTTP_HOST'],
	    'license_mode'         => 'download',
	    'license_v'            => FV_PLUGIN_VERSION,
	    'plugin_download_hash' => $_POST['plugin_download_hash'],
	    'license_key'          => $_POST['license_key'],
	    'mfile'                => $_POST['mfile'],
	);
	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response       = fv_run_remote_query( $query );
	$fv_api         = json_decode( wp_remote_retrieve_body( $response ) );

	// inform remote the result it just send has been received.
	$query_args     = array(
		'ld_tm'    => $fv_api->ld_tm,
		'ld_type'  => 'download',
		'l_dat'    => $fv_api->license_key,
		'ld_dat'   => $_SERVER['HTTP_HOST'],
		'rm_ip'    => $_SERVER['REMOTE_ADDR'],
		'status'   => $fv_api->result,
		'req_time' => time(),
		'res'      => '1'
	);
	if ( 'success' !== $fv_api->result ) {
		$query_args['status'] = $fv_api->msg;
		$query_args['res']    = '0';
	}
	fv_run_remote_request_data( $query_args );

	// echo query result back to script.js
	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_plugin_download_ajax', 'fv_plugin_download_ajax' );
add_action( 'wp_ajax_nopriv_fv_plugin_download_ajax', 'fv_plugin_download_ajax' );

/**
 * Callback for the install button of the bulk install/download popup in the Vault page.
 *
 * @return void
 */
function fv_plugin_download_ajax_bundle() {

	$query_base_url = FV_REST_API_URL . 'plugin-download-multiple';
	$query_args     = array(
	    'license_d'            => fv_get_any_license_domain_id(),
	    'license_pp'           => $_SERVER['REMOTE_ADDR'],
	    'license_host'         => $_SERVER['HTTP_HOST'],
	    'license_mode'         => 'download_bundle',
	    'license_v'            => FV_PLUGIN_VERSION,
	    'plugin_download_hash' => $_POST['plugin_download_hash'],
	    'license_key'          => $_POST['license_key'],
	    'mfile'                => $_POST['mfile'],
	);
	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response       = fv_run_remote_query( $query );
	$fv_api         = json_decode( wp_remote_retrieve_body( $response ) );

	$query_args = array(
		'ld_tm'    => $fv_api->ld_tm,
		'ld_type'  => 'download',
		'l_dat'    => $fv_api->license_key,
		'ld_dat'   => $_SERVER['HTTP_HOST'],
		'rm_ip'    => $_SERVER['REMOTE_ADDR'],
		'req_time' => time(),
		'status'   => $fv_api->result,
		'res'      => '1'
	);

	if ( 'success' !== $fv_api->result ) {
		$query_args['status'] = $fv_api->msg;
		$query_args['res']    = '0';
	}

	fv_run_remote_request_data( $query_args );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_plugin_download_ajax_bundle',        'fv_plugin_download_ajax_bundle' );
add_action( 'wp_ajax_nopriv_fv_plugin_download_ajax_bundle', 'fv_plugin_download_ajax_bundle' );

/**
 * Download and install multiple plugins/themes from a bulk zipfile.
 *
 * Callback for the install button of the bulk download popup in the Vault page.
 *
 * @return void
 */
function fv_plugin_install_bulk_ajax() {

	// Call API for bulk download details.

	$query_base_url = FV_REST_API_URL . 'plugin-download-multiple';
	$query_args     = array(
	    'license_d'            => fv_get_any_license_domain_id(),
	    'license_pp'           => $_SERVER['REMOTE_ADDR'],
	    'license_host'         => $_SERVER['HTTP_HOST'],
	    'license_mode'         => 'install_bundle',
	    'license_v'            => FV_PLUGIN_VERSION,
	    'plugin_download_hash' => $_POST['plugin_download_hash'],
	    'license_key'          => $_POST['license_key'],
	    'mfile'                => $_POST['mfile'],
	);
	$query        = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response     = fv_run_remote_query( $query );
	$fv_api       = json_decode( wp_remote_retrieve_body( $response ) );

	// Download an install multiple plugins and/or themes.

	$fv_bulk_zip_file = pathinfo( $fv_api->config->content_slug )['filename'] . '.zip';

	fv_bulk_install( url: $fv_api->links, file: $fv_bulk_zip_file );

	// Report back to the API
	$query_args = array(
		'ld_tm'    => $fv_api->config->ld_tm,
		'ld_type'  => 'download',
		'l_dat'    => $fv_api->config->license_key,
		'ld_dat'   => $_SERVER['HTTP_HOST'],
		'rm_ip'    => $_SERVER['REMOTE_ADDR'],
		'status'   => $fv_api->config->result,
		'req_time' => time(),
		'res'      => '1'
	);

	if ( 'success' !== $fv_api->config->result ) {
		$query_args['status'] = $fv_api->config->msg;
		$query_args['res']    = '0';
	}
	fv_run_remote_request_data( $query_args );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_plugin_install_bulk_ajax', 'fv_plugin_install_bulk_ajax' );
add_action( 'wp_ajax_nopriv_fv_plugin_install_bulk_ajax', 'fv_plugin_install_bulk_ajax' );

function fv_plugin_install_button_modal_generate() {

	$query_base_url = FV_REST_API_URL.'get-pro-buttons';
	$query_args      = array(
		'license_key'  => fv_get_any_license_key(),
	    'license_d'    => fv_get_any_license_domain_id(),
	    'license_pp'   => $_SERVER['REMOTE_ADDR'],
	    'license_host' => $_SERVER['HTTP_HOST'],
	    'license_mode' => 'buttons',
	    'license_v'    => FV_PLUGIN_VERSION,
	    'product_hash' => $_POST['product_hash'],
	 );

	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response       = fv_run_remote_query( $query );
	$fv_api         = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $fv_api );

}
add_action( 'wp_ajax_fv_plugin_install_button_modal_generate',        'fv_plugin_install_button_modal_generate' );
add_action( 'wp_ajax_nopriv_fv_plugin_install_button_modal_generate', 'fv_plugin_install_button_modal_generate' );

function fv_plugin_install_ajax() {

	$query_base_url = FV_REST_API_URL.'plugin-download';
	$query_args     = array(
	    'license_d'            => fv_get_any_license_domain_id(),
	    'license_pp'           => $_SERVER['REMOTE_ADDR'],
	    'license_host'         => $_SERVER['HTTP_HOST'],
	    'license_mode'         => 'install',
	    'license_v'            => FV_PLUGIN_VERSION,
	    'plugin_download_hash' => $_POST['plugin_download_hash'],
	    'license_key'          => $_POST['license_key'],
	    'mfile'                => $_POST['mfile'],
	);

	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response       = fv_run_remote_query( $query );
	$fv_api         = json_decode( wp_remote_retrieve_body( $response ) );
	$processed_data = json_encode( $fv_api );

	$chk_any = 0;

	if ( $fv_api->result == 'success'
	&&   $fv_api->content_type == 'plugin'
	&& ! empty( $fv_api->content_slug )
	&& ! empty( $fv_api->link ) ) {

		WP_Filesystem();

		$pathInfo                 = pathinfo( $fv_api->content_slug );
		$fileName                 = $pathInfo['filename'].'.zip';
		$upload_dir               = wp_upload_dir();
		$fv_plugin_zip_upload_dir = $upload_dir["basedir"] . "/fv_auto_update_directory/plugins/";
		$tmpfile                  = download_url( $fv_api->link, $timeout = 300 );

		if ( is_wp_error( $tmpfile ) == true ) {

			// Initialize the cURL session
			$ch = curl_init( $fv_api->link );

			$file_name = basename( $fv_api->link );

			// Save file into file location
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
		$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
		if ( $ext=='zip' ) {

			$basename = pathinfo( $fileName, PATHINFO_BASENAME );
			$un = unzip_file( $fv_plugin_zip_upload_dir . $basename, WP_PLUGIN_DIR );

			if ( !is_wp_error( $un ) ) {
				unlink( $fv_plugin_zip_upload_dir.$basename );
			}

			$chk_any            = 1;
			$final_success_data = [
				'result'                 => 'success',
				'slug'                   => $fv_api->content_slug,
				'content_type'           => $fv_api->content_type,
				'link'                   => $fv_api->link,
				'activation'             => admin_url( 'admin.php?page=festinger-vault&actionrun=activation&activeslug='.$fv_api->content_slug ),
				'plan_limit'             => $fv_api->plan_limit,
				'download_current_limit' => $fv_api->download_current_limit,
				'download_available'     => $fv_api->download_available,

			];
			echo json_encode( $final_success_data );
		}

	}

	if ( $fv_api->result == 'success'
	&&   $fv_api->content_type == 'theme'
	&& ! empty( $fv_api->content_slug )
	&& ! empty( $fv_api->link ) ) {

		WP_Filesystem();

		$pathInfo=pathinfo( $fv_api->content_slug );
		$fileName=$pathInfo['filename'].'.zip';

		$upload_dir      = wp_upload_dir();
		$fv_theme_zip_upload_dir=$upload_dir["basedir"]."/fv_auto_update_directory/themes/";

		$tmpfile = download_url( $fv_api->link, $timeout = 300 );

		if ( is_wp_error( $tmpfile ) == true ) {

			// Initialize the cURL session
			$ch = curl_init( $fv_api->link );

			// Use basename() function to return
			// the base name of file
			$file_name = basename( $fv_api->link );

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
		$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
		if ( $ext == 'zip' ) {
			$basename = pathinfo( $fileName,  PATHINFO_BASENAME );
			$un       = unzip_file( $fv_theme_zip_upload_dir . '/' . $basename, get_theme_root() );
			if ( ! is_wp_error( $un ) ) {
				unlink( $fv_theme_zip_upload_dir.'/'.$basename );
			}
		}
		$chk_any            = 1;
		$final_success_data = array(
			'result'                 => 'success',
			'finelame'               => $fileName,
			'slug'                   => $fv_api->content_slug,
			'link'                   => 'theme',
			'theme_preview'          => admin_url( 'themes.php?theme='.$fv_api->content_slug ),
			'plan_limit'             => $fv_api->plan_limit,
			'download_current_limit' => $fv_api->download_current_limit,
			'download_available'     => $fv_api->download_available,
		);
		echo json_encode( $final_success_data );
	}

	if ( $chk_any == 0 ) {
		$msg_data          = isset( $fv_api->msg ) ? $fv_api->msg : 'Something went wrong';
		$final_failed_data = [
			'result' => 'failed',
			'msg'    => $msg_data,
		];
		echo json_encode( $final_failed_data );
	}

	$query_args = array(
		'ld_tm'    => $fv_api->ld_tm,
		'ld_type'  => 'install',
		'l_dat'    => $fv_api->license_key,
		'ld_dat'   => $_SERVER['HTTP_HOST'],
		'rm_ip'    => $_SERVER['REMOTE_ADDR'],
		'status'   => $fv_api->result,
		'res'      => '1',
		'req_time' => time(),
	);

	if ( 'success' !== $fv_api->result ) {
		$query_args['status'] = $fv_api->msg;
		$query_args['res']    = '0';
	}

	fv_run_remote_request_data( $query_args );
}
add_action( 'wp_ajax_fv_plugin_install_ajax', 'fv_plugin_install_ajax' );
add_action( 'wp_ajax_nopriv_fv_plugin_install_ajax', 'fv_plugin_install_ajax' );

function get_plugins_and_themes_matched_by_vault( $plugin_theme, $get_slug ) {

    $requested_plugins = array();
    $requested_themes  = array();
    $all_plugins       = fv_get_plugins();

    if ( ! empty( $all_plugins ) ) {
        foreach ( $all_plugins as $plugin_slug => $values ) {
            $version             = fv_esc_version( $values['Version'] );
            $slug                = fv_get_slug( $plugin_slug );
            $requested_plugins[] = array(
				'slug'    => $slug,
				'version' => $version,
				'dl_link' => ''
			);
        }
    }

    $allThemes = fv_get_themes();
    foreach( $allThemes as $theme ) {
    	$get_theme_slug = $theme->get( 'TextDomain' );
    	if ( empty( $get_theme_slug ) ) {
    		$get_theme_slug = $theme->template;
    	}
        $requested_themes[] = array(
			'slug'    => $get_theme_slug,
			'version' => $theme->Version,
			'dl_link' => ''
		);
    }

	if ( fv_has_any_license() ) {
		$query_base_url    = FV_REST_API_URL . 'plugin-theme-updater';
		$query_args        = array(
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
	    $response          = fv_run_remote_query( $query );
		$fv_api            = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $plugin_theme == 'plugin' && ! empty( $slug ) ) {
			foreach( $fv_api->plugins as $plugin ) {
				if ( $slug == $plugin->slug ) {
					echo 1;
				}
			}
			echo 0;
		}
    }
}

function fv_discourse_post_new_version() {

    // commentdata = "Please update ".$_POST['data_generated_name']." to ".$_POST['versionNumber']." @FestingerUpdates";
	$query_base_url = FV_REST_API_URL.'discourse-input';
	$query_args     = array(
		'plugin_name' => $_POST['data_generated_name'],
		'comment'     => $_POST['versionNumber'],
		'postid'      => $_POST['lastNumericValue'],
		'title'       => $_POST['data_generated_name'],
		'license_key' => fv_get_any_license_key(),
	);
	$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response          = fv_run_remote_query( $query );
	$fv_api            = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $fv_api );

}
add_action( 'wp_ajax_fv_discourse_post_new_version', 'fv_discourse_post_new_version' );
add_action( 'wp_ajax_nopriv_fv_discourse_post_new_version', 'fv_discourse_post_new_version' );

function fv_discourse_post_new_report() {
	//commentdata = "Please update ".$_POST['data_generated_name']." to ".$_POST['versionNumber']." @FestingerUpdates";
	$query_base_url = FV_REST_API_URL . 'discourse-report';
	$query_args     = array(
		'plugin_name' => $_POST['data_generated_name'],
		'comment'     => $_POST['versionNumber'],
		'postid'      => $_POST['lastNumericValue'],
		'title'       => $_POST['data_generated_name'],
		'license_key' => fv_get_any_license_key(),
	);
	$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response          = fv_run_remote_query( $query );
	$fv_api            = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_discourse_post_new_report', 'fv_discourse_post_new_report' );
add_action( 'wp_ajax_nopriv_fv_discourse_post_new_report', 'fv_discourse_post_new_report' );

/*
	By clicking download button
	Modal will pop up and fetch demo contents download buttons
	Based on licenses
*/
function fv_fs_plugin_dc_buttons_ajax() {
	$query_base_url = FV_REST_API_URL . 'get-pro-dc-buttons-web';
	$query_args     = array(
	    'product_hash' => $_POST['product_hash'],
	    'data_dltype'  => $_POST['data_dltype'],
	    'license_host' => $_SERVER['HTTP_HOST'],
	    'license_mode' => 'dc_buttons_web_fv',
		'user_id'      => fv_get_any_license_key(),
	);

	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response       = fv_run_remote_query( $query );

	if ( is_wp_error( $response ) ) {
	    echo "Unexpected Error! The query returned with an error.";
	}

	echo json_encode( json_decode( wp_remote_retrieve_body( $response ) ) );
}
add_action( 'wp_ajax_fv_fs_plugin_dc_buttons_ajax', 'fv_fs_plugin_dc_buttons_ajax' );
add_action( 'wp_ajax_nopriv_fv_fs_plugin_dc_buttons_ajax', 'fv_fs_plugin_dc_buttons_ajax' );

function fv_fs_plugin_dc_contents_ajax() {

	$query_base_url   = FV_REST_API_URL . 'first-server-demo-contents-data-get';
	$query_args = array(
		'theme_plugin_id' => $_POST['product_hash'],
		'license_host'    => $_SERVER['HTTP_HOST'],
		'license_mode'    => 'first_server_return_demo_contents_fv',
	);
	$query            = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response         = fv_run_remote_query( $query );

	if ( is_wp_error( $response ) ) {
		echo "Unexpected Error! The query returned with an error.";
	}

	$fv_api = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_fs_plugin_dc_contents_ajax', 'fv_fs_plugin_dc_contents_ajax' );
add_action( 'wp_ajax_nopriv_fv_fs_plugin_dc_contents_ajax', 'fv_fs_plugin_dc_contents_ajax' );

function fv_fs_plugin_download_ajax_dc() {

	$query_base_url = FV_REST_API_URL . 'demo-content-download';
	$query_args     = array(
	    'license_host'        => $_SERVER['HTTP_HOST'],
	    'license_mode'        => 'download_web_dc',
	    'license_v'           => '1.0.0',
	    'plugin_download_hash'=> $_POST['plugin_download_hash'],
		'license_key'         => fv_get_any_license_key(),
	    'download_type'       => $_POST['download_type'],

	 );
	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response       = fv_run_remote_query( $query );
	// Check for error in the response
	if ( is_wp_error( $response ) ) {
		echo "Unexpected Error! The query returned with an error.";
	}
	$fv_api         = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_fs_plugin_download_ajax_dc', 'fv_fs_plugin_download_ajax_dc' );
add_action( 'wp_ajax_nopriv_fv_fs_plugin_download_ajax_dc', 'fv_fs_plugin_download_ajax_dc' );

function fv_has_true_value( bool|string $toggle ): bool {
	return ( true === $var || 'true' === $var  );
}

function fv_has_false_value( bool|string $toggle ): bool {
	return ( false === $var || 'false' === $var  );
}
