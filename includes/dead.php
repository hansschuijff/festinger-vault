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

