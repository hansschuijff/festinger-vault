<?php
/**
 * Activate Plugin Update Checker.
 *
 * @package     FestingerVault
 * @since       4.0.1.h5
 * @author      Festinger Vault (refactored by Hans Schuijff)
 * @link        https://festingervault.com
 * @license     GPLv2 or later
 */

$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://update.festingervault.com/fv-updater/index.php?action=get_metadata&slug=festingervault',
	FV_PLUGIN__FILE__,
	'festingervault'
 );

