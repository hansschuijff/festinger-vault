<?php
/**
 * Code to be suspected of no longer being used.
 *
 * @package     FestingerVault
 * @since       4.0.1.h3
 * @author      Festinger Vault (refactored by Hans Schuijff)
 * @link        https://festingervault.com
 * @license     GPLv2 or later
 */

/**
 * Renders a list of installed themes that are also in Festinger Vault
 *
 * Doesn't seem to be called anymore, so dead code.
 *
 * @return void
 */
function activeThemesVersions() {

	if ( ! fv_has_any_license() ) {
		return;
	}

	$req_themes       = fv_get_installed_themes_for_api_request();
	$fv_api           = fv_get_remote_themes( $req_themes );
	$fv_themes_slugs  = fv_get_theme_slugs_from_api_response( $fv_api->themes );

	foreach( fv_get_themes() as $theme ) {
		if ( ! in_array( fv_get_wp_theme_slug( $theme ), $fv_themes_slugs ) ) {
			continue;
		}
		fv_print_theme_active_version_markup( $theme );
	}
}

/**
 * Builds an array of theme slugs from the themes as returned by the fv_api.
 *
 * @param stdClass $themes The themes from the response object of an fv api-call.
 * @return array
 */
function fv_get_theme_slugs_from_api_response( stdClass $themes ) : array {
	$slugs  = array();
	foreach( $themes as $theme ) {
		$slugs[] = $theme->slug;
	}
	return $slugs;
}

/**
 * Renders and prints a table row for a given theme.
 *
 * Note this markup is probably part of a previous version of FV and is not used anymore.
 * Probably dead code.
 *
 * @param WP_Theme $theme
 * @return void
 */
function fv_print_theme_active_version_markup( WP_Theme $theme ) : void {
	?>
	<tr>
		<td class='plugin_update_width_30'>
			<?php echo $theme->name; ?> <br/>
			<?php echo ( is_active_theme( $theme->Name ) ) ? "<span class='badge bg-info'>Active</span>" : ''; ?>
		</td>
		<td class='plugin_update_width_60'>
			<?php echo substr( $theme->Description, 0, 180 ) . '...'; ?>
		</td>
		<td>
			<?php echo $theme->Version ?>
		</td>
		<td>2.0</td>
		<td>
			<center>
				<input type='checkbox' checked data-toggle='toggle' data-size='xs'>
			</center>
		</td>
	</tr>
	<?php
}

/**
 * Renders a list of plugins that are both in Festinger Vault and installed.
 *
 * Doesn't seem to be called anywhere.
 *
 * @return void
 */
function activePluginsVersions() {

	if ( ! fv_has_any_license() ) {
		return;
	}

	$req_plugins       = fv_get_installed_plugins_for_api_request();
	$active_plugins    = get_option( 'active_plugins' );
	$fv_api            = fv_get_remote_plugins( $req_plugins );
	$fv_plugins_slugs  = fv_get_plugin_slugs_from_api_response( $fv_api->plugins );

	foreach( fv_get_plugins() as $basename -> $plugin_data ) {

		if ( ! in_array(   fv_get_slug( $basename ), $fv_plugins_slugs ) ) {
			continue;
		}

		// Mark active plugin
		$plugin_data['is_active'] = in_array( $basename, $active_plugins );

		fv_print_plugin_active_version_markup( $basename, $plugin_data );
	}

}

/**
 * Make an array of plugin slugs from the plugins returned by the fv_api
 *
 * @param stdClass $fv_api_plugins stdClas containing the plugins from the fv_api response object.
 * @return void
 */
function fv_get_plugin_slugs_from_api_response( stdClass $fv_api_plugins ) {
	$slugs = [];
	foreach( $fv_api_plugins as $plugin ) {
		$slugs[] = $fv_api_plugins->slug;
	}
	return $slugs;
}

/**
 * Echo a table row for a given plugin.
 *
 * @param string $basename Plugin basename.)
 * @param array $plugin    Plugin data + is_active marker.
 * @return void
 */
function fv_print_plugin_active_version_markup( string $basename, array $plugin ) : void {
	?>
	<tr>
		<!-- plugin name -->
		<td class='plugin_update_width_30'>
			<?php echo $plugin['Name'] ?>
			<br/>
			<span class='badge bg-success'>
				<?php echo ( $plugin['is_active'] ) ? 'Active' : 'Inactive'; ?>
			</span>
		</td>
		<!-- plugin short description -->
		<td class='plugin_update_width_60'>
			<?php echo substr( $plugin['Description'], 0, 180 ) . '...'; ?>
			<br/>
			Slug: <?php echo fv_get_slug( $basename ); ?>
		</td>
		<!-- plugin version -->
		<td>
			<?php echo $plugin['Version'] ?>
		</td>
		<td>
			<?php echo fv_esc_version( $plugin['Version'] ); ?>
		</td>
		<!-- auto-update check -->
		<td>
			<center>
				<input type='checkbox' checked data-toggle='toggle' data-size='xs'>
			</center>
		</td>
	</tr>
	<?php
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
 * Get data about installed themes and plugins.
 *
 * @param string $request_list Type of data requested.
 * @return string Json encoded array with slugs of themes and/or plugins as requested.
 */
function get_plugin_theme_data( string $request_list = 'all' ): string {

	switch ( $request_list ) {
		case 'active_plugins':
			return json_encode( fv_get_active_plugins_slugs() );
			break;

		case 'inactive_plugins':
			return json_encode( fv_get_inactive_plugins_slugs() );
			break;

		case 'active_themes':
			return json_encode( fv_get_active_themes_slugs() );
			break;

		case 'inactive_themes':
			return json_encode( fv_get_inactive_themes_slugs() );
			break;

		case 'all_plugins_themes':
			return  json_encode( array(
				'plugins' => fv_get_plugins_slugs(),
				'themes'  => fv_get_themes_slugs(),
			));
			break;

		default:
			return  json_encode( array(
				'active_plugins'   => fv_get_active_plugins_slugs(),
				'inactive_plugins' => fv_get_inactive_plugins_slugs(),
				'active_themes'    => fv_get_active_themes_slugs(),
				'inactive_themes'  => fv_get_inactive_themes_slugs(),
			));
			break;
	}
}

/**
 * This function collects data for sending to the api,
 * but only 'all_plugins_themes' was called and that
 * just called another function that performed a query
 * without returning data.
 *
 * Just cut the middleman and wonder why the query is run.
 * Now this function is dead code
 *
 * @param string $request_list
 * @return void
 */
function get_plugin_theme_data_details( $request_list = 'all' ) {
	switch ( $request_list ) {
		case 'active_plugins':
			return json_encode( fv_get_active_plugins_data() );
			break;

		case 'active_plugins':
			return json_encode( fv_get_inactive_plugins_data() );
			break;

		case 'active_themes':
			return json_encode( fv_get_active_themes_data() );
			break;

		case 'inactive_themes':
			return json_encode( fv_get_inactive_themes_data() );
			break;

		case 'all_plugins_themes':
			fv_run_remote_licensed_info_list( fv_get_plugins_data(), fv_get_themes_data() );
			break;

		default:
			return json_encode( array (
				'active_plugins'   => fv_get_active_plugins_data(),
				'inactive_plugins' => fv_get_inactive_plugins_data(),
				'active_themes'    => fv_get_active_themes_data(),
				'inactive_themes'  => fv_get_inactive_themes_data(),
			));
			break;
	}
}

/**
 * Doesn't seem to be called anywhere
 *
 * @return void
 */
function festinger_vault_get_multi_purpose_data() {

	$query_base_url = FV_REST_API_URL . 'get-multi-purpose-data';
	$query_args      = array(
		'license_key'   => fv_get_license_key(),
		'license_key_2' => fv_get_license_key_2(),
		'license_d'     => fv_get_license_domain_id(),
		'license_d_2'   => fv_get_license_domain_id_2(),
		'license_pp'    => $_SERVER['REMOTE_ADDR'],
		'license_host'  => $_SERVER['HTTP_HOST'],
		'license_mode'  => 'get_multi_purpose_data_status',
		'license_v'     => FV_API_PLUGIN_VERSION,
	);
	$query             = esc_url_raw( add_query_arg( $query_args, $query_base_url ) );
	$response          = fv_run_remote_query( $query );
	$license_histories = json_decode( wp_remote_retrieve_body( $response ) );

	return $license_histories;
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

function fv_auto_update_download_instant_bak( $should_update = 'all', $single_update_data = array() ) {

	if ( ! fv_has_any_license() ) {
		return;
	}

	$total_download_size        = 10;

	if ( 'plugin' === $should_update
	&& ! empty( $single_update_data )
	&&   count( $single_update_data ) > 0 ) {
		$fv_plugins[] = array(
			'slug'    => ! empty( $single_update_data['slug'] )    ? $single_update_data['slug']    : '',
			'version' => ! empty( $single_update_data['version'] ) ? $single_update_data['version'] : '',
			'dl_link' => ''
		);
	} else {
		$fv_plugins = fv_get_installed_plugins_for_api_request();
	}

	if ( 'theme' === $should_update
	&& ! empty( $single_update_data )
	&&   count( $single_update_data ) > 0 ) {
		$fv_themes[] = array(
			'slug'    => ! empty( $single_update_data['slug'] )    ? $single_update_data['slug']    : '',
			'version' => ! empty( $single_update_data['version'] ) ? $single_update_data['version'] : '',
			'dl_link' => ''
		);

	} else {
		$fv_themes = fv_get_auto_update_themes_for_api_request();
	}

	// Get matching plugins and themes remote using FV Api.
	$license_histories = fv_get_remote_matches(
		plugins: $fv_plugins,
		themes:  $fv_themes,
		context: 'update'
	);

	// Include wp filesystem for actual file manipulation functions.
	require_once( ABSPATH . '/wp-admin/includes/file.php' );
	WP_Filesystem();

	if ( 'all'   === $should_update
	||   'theme' === $should_update ) {

		if ( ! empty( $license_histories->themes ) ) {

			$fv_themes_with_stylesheet=[];

			foreach( fv_get_themes() as $theme ) {
				$get_theme_slug = fv_get_theme_slug( $theme );
				if ( empty( $get_theme_slug ) ) {
					$get_theme_slug = $theme->get( 'TextDomain' );
				}
				$fv_themes_with_stylesheet[]=[
					'stylesheet'     => $theme->get_stylesheet(),
					'slug'           => $get_theme_slug,
					'version'        => $theme->Version
				];
			}

			foreach ( $license_histories->themes as $fv_update ) {

				foreach( $fv_themes_with_stylesheet as $single_th ) {
					if ( $single_th['slug'] == $fv_update->slug
					&& version_compare( $fv_update->version, $single_th['version'], '>' ) ) {

						//start of update

						if ( ! empty( $single_update_data ) ) {
							if ( count( $single_update_data ) > 0 && $single_update_data['slug'] == $fv_update->slug ) {

								$pathInfo=pathinfo( $fv_update->slug );
								$fileName=$pathInfo['filename'].'.zip';

								$total_download_size += fv_download( url: $fv_update->dl_link, to_file: fv_get_upload_dir('themes') . $fileName );

								$determine_theme_dir = fv_get_theme_dir( $fv_update->slug, $fv_themes_with_stylesheet );
								$get_all_themes      = scandir( fv_get_upload_dir('themes-backups') );
								foreach( $get_all_themes as $single_theme ) {
									if ( strpos( $single_theme, $fv_update->slug ) !== false ) {
										fv_delete_directory( fv_get_upload_dir('themes-backups') . $single_theme );
									}
								}

								$fv_theme_dir             = trailingslashit( get_theme_root() ) . $determine_theme_dir;
								$fv_theme_backup_dir = fv_get_upload_dir('themes-backups') . $determine_theme_dir;

								if ( is_dir( $fv_theme_dir ) ) {
									fv_copy_recursive( $fv_theme_dir, $fv_theme_backup_dir ); // copy old version as backup
								}

								$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
								if ( $ext=='zip' ) {
									$basename = pathinfo( $fileName,  PATHINFO_BASENAME );
									fv_unzip_file_to( file: fv_get_upload_dir('themes') . $basename, to: get_theme_root() );

									fv_run_remote_update_request_load( $fv_update->slug, $fv_update->version );
								}
								//end of update
							}
						} else {
							// if ( get_option( 'fv_themes_auto_update_list' ) == true
							// && in_array( $fv_update->slug, get_option( 'fv_themes_auto_update_list' ) ) ) {
							$pathInfo=pathinfo( $fv_update->slug );
							$fileName=$pathInfo['filename'].'.zip';

							$total_download_size += fv_download( url: $fv_update->dl_link, to_file: fv_get_upload_dir('themes') . $fileName );

							$determine_theme_dir = get_array_value_with_slug( $fv_update->slug, $fv_themes_with_stylesheet )['stylesheet'];
							$get_all_themes = scandir( fv_get_upload_dir('themes-backups') );
							foreach( $get_all_themes as $single_theme ) {
								if ( strpos( $single_theme, $fv_update->slug ) !== false ) {
									fv_delete_directory( fv_get_upload_dir('themes-backups') . $single_theme );
								}
							}

							$fv_theme_dir             = trailingslashit( get_theme_root() ) . $determine_theme_dir;
							$fv_theme_backup_dir = fv_get_upload_dir('themes-backups') . $determine_theme_dir;

							if ( is_dir( $fv_theme_dir ) ) {
								fv_copy_recursive( $fv_theme_dir, $fv_theme_backup_dir ); // copy old version as backup
							}

							$ext = pathinfo( $fileName, PATHINFO_EXTENSION );

							if ( $ext == 'zip' ) {

								$basename=pathinfo( $fileName,  PATHINFO_BASENAME );
								fv_unzip_file_to( file: fv_get_upload_dir('themes') . $basename, to: get_theme_root() );

								fv_run_remote_update_request_load( $fv_update->slug, $fv_update->version );
							}
						}
					}
				}
			}
		}
	}


	if ( 'all'    === $should_update
	||   'plugin' === $should_update ) {
		$all_plugins = fv_get_plugins();

		if ( ! empty( $license_histories->plugins ) ) {
			$get_plugin_directory=[];

			if ( ! empty( $all_plugins ) ) {

				foreach ( $all_plugins as $plugin_slug => $values ) {
					$version = fv_esc_version( $values['Version'] );
					$slug    = fv_get_slug( $plugin_slug );
					$get_plugin_directory[] = [
												'stylesheet' 	 => explode( '/', $plugin_slug )[0],
												'slug'	 => $slug,
												'version'=> $version
												];
				}
			}

			foreach ( $license_histories->plugins as $fv_update ) {
				foreach( $get_plugin_directory as $single_pl ) {
					if ( $single_pl['slug'] == $fv_update->slug
					&& version_compare( $fv_update->version, $single_pl['version'], '>' ) ) {

						// y

						if ( ! empty( $single_update_data ) ) {
							if ( count( $single_update_data ) > 0 && $single_update_data['slug'] == $fv_update->slug ) {

							// start

								$pathInfo=pathinfo( $fv_update->slug );
								$fileName=$pathInfo['filename'].'.zip';

								$total_download_size += fv_download( url: $fv_update->dl_link, to_file: fv_get_upload_dir('themes') . $fileName );

								$determine_plugin_dir            = get_array_value_with_slug( $fv_update->slug, $get_plugin_directory )['stylesheet'];
								$original_plugin_dir             = trailingslashit( WP_PLUGIN_DIR ) . $determine_plugin_dir;
								$fv_plugin_zip_upload_dir_backup = fv_get_upload_dir('plugins-backups') . $determine_plugin_dir;

								if ( is_dir( $original_plugin_dir ) ) {
									fv_copy_recursive( $original_plugin_dir, $fv_plugin_zip_upload_dir_backup ); // copy old version as backup
								}

								$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
								if ( $ext == 'zip' ) {
									$basename=pathinfo( $fileName,  PATHINFO_BASENAME );
									if ( is_dir( $original_plugin_dir ) ) {
									}
									fv_unzip_file_to( file: fv_get_upload_dir('plugins') . $basename, to: WP_PLUGIN_DIR );

									fv_run_remote_update_request_load( $fv_update->slug, $fv_update->version );
								}
								//end of plugin update
							//end
							}
						} else {
							// if ( get_option( 'fv_plugins_auto_update_list' ) == true
							// && in_array( $fv_update->slug, get_option( 'fv_plugins_auto_update_list' ) ) ) {

								//start
								$fileName = pathinfo( $fv_update->slug )['filename'] . '.zip';

								$total_download_size += fv_download( url: $fv_update->dl_link, to_file: fv_get_upload_dir('themes') . $fileName );

								$determine_plugin_dir = get_array_value_with_slug( $fv_update->slug, $get_plugin_directory )['stylesheet'];
								$original_plugin_dir = trailingslashit( WP_PLUGIN_DIR ) . $determine_plugin_dir;
								$fv_plugin_zip_upload_dir_backup = fv_get_upload_dir('backup-backup') . $determine_plugin_dir;

								if ( is_dir( $original_plugin_dir ) ) {
									fv_copy_recursive( $original_plugin_dir, $fv_plugin_zip_upload_dir_backup ); // copy old version as backup
								}

								$ext = pathinfo( $fileName, PATHINFO_EXTENSION );

								if ( $ext=='zip' ) {

									$basename=pathinfo( $fileName,  PATHINFO_BASENAME );
									if ( is_dir( $original_plugin_dir ) ) {
									}
									fv_unzip_file_to( file: fv_get_upload_dir('plugins') . $basename, to: WP_PLUGIN_DIR );

									fv_run_remote_update_request_load( $fv_update->slug, $fv_update->version );
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

	if ( isset( $license_histories ) ) {
		fv_run_remote_request_data( array(
			'ld_tm'          => $license_histories->ld_tm,
			'ld_type'        => 'up_dl_plugs_thms',
			'l_dat'          => fv_get_license_key(),
			'ld_dat'         => $_SERVER['HTTP_HOST'],
			'rm_ip'          => $_SERVER['REMOTE_ADDR'],
			'status'         => 'executed',
			'req_time'       => time(),
			'res'            => '1',
			'dsz'            => $total_download_size,
			'themes_plugins' => array(
				'themes'  => isset( $license_histories->themes )  ? $license_histories->themes  : array(),
				'plugins' => isset( $license_histories->plugins ) ? $license_histories->plugins : array()
			)
		));
	}

}
