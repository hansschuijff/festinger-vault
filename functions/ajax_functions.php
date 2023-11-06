<?php

use PgSql\Lob;

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
		'license_v'     => FV_API_PLUGIN_VERSION,
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
		'license_v'     => FV_API_PLUGIN_VERSION,
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
	    'license_v'    => FV_API_PLUGIN_VERSION,
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
 * Gets remote data to download or install a single plugin or theme.
 *
 * @return void
 */
function fv_get_remote_product_download_data() : void {

	$query_base_url = FV_REST_API_URL . 'get-pro-buttons';
	$query_args     = array(
		'license_key'  => fv_get_any_license_key(),
	    'license_d'    => fv_get_any_license_domain_id(),
	    'license_pp'   => $_SERVER['REMOTE_ADDR'],
	    'license_host' => $_SERVER['HTTP_HOST'],
	    'license_mode' => 'buttons',
	    'license_v'    => FV_API_PLUGIN_VERSION,
	    'product_hash' => $_POST['product_hash'],
	);
	$query    = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response = fv_run_remote_query( $query );
	$fv_api   = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_array( $fv_api ) ) {
		$products = array();
		foreach ( $fv_api as $product ) {
			$product           = json_decode( $product );
			$version_installed = fv_get_installed_version_from_product_slug( $product->product_slug, $product->product_type  );
			if ( ! empty( $version_installed )) {
				$product->version_installed = $version_installed;
			}

			$products[] = json_encode( $product );
		}
		$fv_api = $products;
	}

	if ( is_object( $fv_api ) ) {
		$version_installed = fv_get_installed_version_from_product_slug( $fv_api->product_slug, $fv_api->product_type  );
		if ( ! empty( $version_installed )) {
			$fv_api->version_installed = $version_installed;
		}
		echo json_encode( $fv_api );
	}
	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_get_remote_product_download_data', 'fv_get_remote_product_download_data' );
add_action( 'wp_ajax_nopriv_fv_get_remote_product_download_data', 'fv_get_remote_product_download_data' );


function fv_get_installed_version_from_product_slug( $slug, $type ) {
	if ( empty( $slug )
	||   empty( $type ) ) {
		return '';
	}

	if ( 'wordpress-themes' === $type ) {
		$theme = wp_get_theme( $slug );
		if ( $theme->exists() ) {
			return $theme->Version;
		}
	}

	if ( 'wordpress-plugins' === $type ) {
		$plugins = get_plugins();
		foreach ( $plugins as $basename => $plugin ) {
			if ( fv_is_slug_in_basename( $slug, $basename ) ) {
				return $plugin['Version'];
			}
		}
	}
	return '';
}

function fv_is_slug_in_basename( string $slug, string $basename ):bool {
	if ( empty( $slug ) || empty( $basename ) ) {
		return false;
	}
	$chunks = explode( '/', $basename );
	foreach ( $chunks as $chunk ) {
		if ( $slug === $chunk ) {
			return true;
		}
	}
	return false;
}

/**
 * Callback for the request update button in the Vault page.
 *
 * Just checks locally if there is a active license
 * and redirects to the product support page if not.
 *
 * @return void
 */
function fv_vault_update_request_has_license() : void {

	if ( ! fv_has_any_license() ) {
		wp_redirect( $_POST['data_support_link'] );
		// Make sure to exit after calling wp_redirect()
		exit;
	}

	//$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	echo json_encode( array(
		'result'               => 'success',
		'license_key'          => fv_get_any_license_key(),
		'fv_item_support_link' => $_POST['fv_item_support_link'],
		'fv_item_slug'         => $_POST['fv_item_slug'],
		'fv_item_hash'         => $_POST['fv_item_hash'],
		'fv_item_name'         => $_POST['fv_item_name'],
	));

}
add_action( 'wp_ajax_fv_vault_update_request_has_license',        'fv_vault_update_request_has_license' );
add_action( 'wp_ajax_nopriv_fv_vault_update_request_has_license', 'fv_vault_update_request_has_license' );

/**
 * Callback for the Report Item button in the Vault page.
 *
 * @return void
 */
function fv_vault_item_report_has_license() : void {
	/**
	 * If no active license then redirect to item support URL.
	 */
	if ( ! fv_has_any_license()  ) {
		wp_redirect( $_POST['data_support_link'] );
		exit; // Make sure to exit after calling wp_redirect()
	}

	echo json_encode( array(
		'result'               => 'success',
		'fv_license_key'       => fv_get_any_license_key(),
		'fv_item_support_link' => $_POST['fv_item_support_link'],
		'fv_item_slug'         => $_POST['fv_item_slug'],
		'fv_item_hash'         => $_POST['fv_item_hash'],
		'fv_item_name'         => $_POST['fv_item_name'],
	) );
}
add_action( 'wp_ajax_fv_vault_item_report_has_license', 'fv_vault_item_report_has_license' );
add_action( 'wp_ajax_nopriv_fv_vault_item_report_has_license', 'fv_vault_item_report_has_license' );

/**
 * Callback for the download button in the download popup modal in Vault page.
 *
 * @return void
 */
function fv_plugin_download_ajax() : void {

	$query_base_url = FV_REST_API_URL.'plugin-download';
	$query_args     = array(
	    'license_pp'           => $_SERVER['REMOTE_ADDR'],
	    'license_host'         => $_SERVER['HTTP_HOST'],
	    'license_mode'         => 'download',
	    'license_v'            => FV_API_PLUGIN_VERSION,
	    'plugin_download_hash' => $_POST['fv_product_hash'],
	    'mfile'                => $_POST['fv_mfile'],
	    'license_key'          => $_POST['fv_license_key'],
	    'license_d'            => fv_get_license_key_domain_id( $_POST['fv_license_key'] ),
	);
	if ( ! fv_is_active_license_key( $query_args['license_key'] ) ) {
		$query_args['license_key'] = fv_get_any_license_key();
	    $query_args['license_d']   = fv_get_any_license_domain_id();
	}
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
	    'license_v'            => FV_API_PLUGIN_VERSION,
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
	    'license_v'            => FV_API_PLUGIN_VERSION,
	    'plugin_download_hash' => $_POST['plugin_download_hash'],
	    'license_key'          => $_POST['license_key'],
	    'mfile'                => $_POST['mfile'],
	);
	$query        = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response     = fv_run_remote_query( $query );
	$fv_api       = json_decode( wp_remote_retrieve_body( $response ) );

	// Download an install multiple plugins and/or themes.

	$fv_bulk_zip_file = pathinfo( $fv_api->config->content_slug, PATHINFO_FILENAME ) . '.zip';

	fv_bulk_install( url: $fv_api->links, file: $fv_bulk_zip_file );

	echo json_encode( $fv_api );

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
}
add_action( 'wp_ajax_fv_plugin_install_bulk_ajax', 'fv_plugin_install_bulk_ajax' );
add_action( 'wp_ajax_nopriv_fv_plugin_install_bulk_ajax', 'fv_plugin_install_bulk_ajax' );

/**
 * Install a single plugin or theme from Vault page.
 *
 * @return void
 */
function fv_vault_product_install() {

	$query_base_url = FV_REST_API_URL.'plugin-download';
	$query_args     = array(
	    'license_d'            => fv_get_any_license_domain_id(),
	    'license_pp'           => $_SERVER['REMOTE_ADDR'],
	    'license_host'         => $_SERVER['HTTP_HOST'],
	    'license_mode'         => 'install',
	    'license_v'            => FV_API_PLUGIN_VERSION,
	    'plugin_download_hash' => $_POST['fv_product_hash'],
	    'license_key'          => $_POST['fv_license_key'],
	    'mfile'                => $_POST['fv_mfile'],
	);
	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
    $response       = fv_run_remote_query( $query );
	$fv_api         = json_decode( wp_remote_retrieve_body( $response ) );

	$product_installed = false;

	if ( $fv_api->result       == 'success'
	&& ! empty( $fv_api->content_slug )
	&& ! empty( $fv_api->link ) ) {

		if ( 'plugin' === $fv_api->content_type ) {

			$slug = pathinfo( $fv_api->content_slug, PATHINFO_FILENAME );

			// enable auto-update by default.
			if ( ! fv_plugin_slug_is_installed( $slug ) ) {
				fv_enable_auto_update_plugin( $slug );
			}

			fv_install_remote_plugin(
				basename:     $slug,
				download_url: $fv_api->link
			);

			$product_installed = true;

			// Report success back to script.
			echo json_encode( array(
				'result'                 => 'success',
				'slug'                   => $fv_api->content_slug,
				'content_type'           => $fv_api->content_type,
				'link'                   => $fv_api->link,
				'activation'             => admin_url( 'admin.php?page=festinger-vault&actionrun=activation&activeslug='.$fv_api->content_slug ),
				'plan_limit'             => $fv_api->plan_limit,
				'download_current_limit' => $fv_api->download_current_limit,
				'download_available'     => $fv_api->download_available,
			));
		}

		if ( 'theme' === $fv_api->content_type ) {

			$stylesheet = pathinfo( $fv_api->content_slug, PATHINFO_FILENAME );

			if ( ! fv_theme_slug_is_installed( $stylesheet ) ) {
				fv_enable_auto_update_theme( $stylesheet );
			}

			fv_install_remote_theme(
				stylesheet:   $stylesheet,
				download_url: $fv_api->link
			);

			$product_installed = true;

			// Report success back to script.
			echo json_encode( array(
				'result'                 => 'success',
				'filename'               => $filename,
				'slug'                   => $stylesheet,
				'link'                   => 'theme',
				'theme_preview'          => admin_url( 'themes.php?theme=' . $stylesheet ),
				'plan_limit'             => $fv_api->plan_limit,
				'download_current_limit' => $fv_api->download_current_limit,
				'download_available'     => $fv_api->download_available,
			));
		}
	}

	if ( ! $product_installed ) {
		// Report failure back to script.
		$msg_data          = isset( $fv_api->msg ) ? $fv_api->msg : 'Something went wrong';
		$final_failed_data = array(
			'result' => 'failed',
			'msg'    => $msg_data,
		);
		echo json_encode( $final_failed_data );
	}

	// Report back to the remote vault API
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
add_action( 'wp_ajax_fv_vault_product_install',        'fv_vault_product_install' );
add_action( 'wp_ajax_nopriv_fv_vault_product_install', 'fv_vault_product_install' );

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
		    'license_v'       => FV_API_PLUGIN_VERSION,
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

/**
 * Post a update request form submission to the fv api (vault page).
 *
 * @return void
 */
function fv_vault_remote_request_update() {

    // commentdata = "Please update ".$_POST['fv_item_name']." to ".$_POST['versionNumber']." @FestingerUpdates";
	$query_base_url = FV_REST_API_URL . 'discourse-input';
	$query_args     = array(
		'license_key' => $_POST['fv_license_key'],
		'plugin_name' => $_POST['fv_item_name'],
		'title'       => $_POST['fv_item_name'],
		'postid'      => $_POST['fv_item_postid'],
		'comment'     => $_POST['fv_requested_version'],
	);
	// make sure license_key is valid.
	if ( ! fv_is_active_license_key( $query_args['license_key'] ) ) {
		$query_args['license_key'] = fv_get_any_license_key();
	}
	$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response          = fv_run_remote_query( $query );
	$fv_api            = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_vault_remote_request_update', 'fv_vault_remote_request_update' );
add_action( 'wp_ajax_nopriv_fv_vault_remote_request_update', 'fv_vault_remote_request_update' );

/**
 * Post a report_item form submission to the fv api (vault page).
 *
 * @return void
 */
function fv_vault_remote_report_item() {
	//commentdata = "Please update ".$_POST['fv_item_name']." to ".$_POST['versionNumber']." @FestingerUpdates";
	$query_base_url = FV_REST_API_URL . 'discourse-report';
	$query_args     = array(
		'license_key' => $_POST['fv_license_key'],
		'plugin_name' => $_POST['fv_item_name'],
		'title'       => $_POST['fv_item_name'],
		'postid'      => $_POST['fv_item_postid'],
		'comment'     => $_POST['fv_report_item_text'],
	);
	// make sure license_key is valid.
	if ( ! fv_is_active_license_key( $query_args['license_key'] ) ) {
		$query_args['license_key'] = fv_get_any_license_key();
	}
	$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response          = fv_run_remote_query( $query );
	$fv_api            = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_vault_remote_report_item', 'fv_vault_remote_report_item' );
add_action( 'wp_ajax_nopriv_fv_vault_remote_report_item', 'fv_vault_remote_report_item' );

/*
	By clicking download button
	Modal will pop up and fetch demo contents download buttons
	Based on licenses
*/
function fv_get_remote_additional_content_download_data() {

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
	    echo 'Unexpected Error! The query returned with an error.';
	}

	echo json_encode( json_decode( wp_remote_retrieve_body( $response ) ) );
}
add_action( 'wp_ajax_fv_get_remote_additional_content_download_data', 'fv_get_remote_additional_content_download_data' );
add_action( 'wp_ajax_nopriv_fv_get_remote_additional_content_download_data', 'fv_get_remote_additional_content_download_data' );

function fv_get_remote_product_additional_content_data() {

	$query_base_url = FV_REST_API_URL . 'first-server-demo-contents-data-get';
	$query_args     = array(
		'theme_plugin_id' => $_POST['product_hash'],
		'license_host'    => $_SERVER['HTTP_HOST'],
		'license_mode'    => 'first_server_return_demo_contents_fv',
	);
	$query          = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response       = fv_run_remote_query( $query );

	if ( is_wp_error( $response ) ) {
		echo "Unexpected Error! The query returned with an error.";
	}

	$fv_api = json_decode( wp_remote_retrieve_body( $response ) );

	echo json_encode( $fv_api );
}
add_action( 'wp_ajax_fv_get_remote_product_additional_content_data', 'fv_get_remote_product_additional_content_data' );
add_action( 'wp_ajax_nopriv_fv_get_remote_product_additional_content_data', 'fv_get_remote_product_additional_content_data' );

function fv_fs_plugin_download_ajax_dc() {

	$query_base_url = FV_REST_API_URL . 'demo-content-download';
	$query_args     = array(
	    'license_host'        => $_SERVER['HTTP_HOST'],
	    'license_mode'        => 'download_web_dc',
	    'license_v'           => '1.0.0',
	    'plugin_download_hash'=> $_POST['fv_product_hash'],
		'license_key'         => $_POST['fv_license_key'],
	    'download_type'       => $_POST['fv_download_type'],
	);
	// make sure license_key is valid.
	if ( ! fv_is_active_license_key( $query_args['license_key'] ) ) {
		$query_args['license_key'] = fv_get_any_license_key();
	}
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
