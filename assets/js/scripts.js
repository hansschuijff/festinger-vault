var productHashJson        = null;
var ajax_filter_data       = {};

// Variable to hold the timeout ID
var timeoutId;

// on document ready...
jQuery( fv_do_pages );

var linksData = {};

/**
 * Determines the current page and starts page script.
 */
function fv_do_pages() {
	// console.log( 'running fv_do_pages()' );

	switch ( plugin_ajax_object.fv_current_screen ) {

		case 'toplevel_page_festinger-vault':
			fv_do_vault_page();
			break;

		case plugin_ajax_object.fv_this_plugin_prefix + '_page_festinger-vault-activation':
			fv_do_activation_page();
			break;

		case plugin_ajax_object.fv_this_plugin_prefix + '_page_festinger-vault-updates':
			fv_do_plugin_updates_page();
			break;

		case plugin_ajax_object.fv_this_plugin_prefix + '_page_festinger-vault-theme-updates':
			fv_do_theme_updates_page();
			break;

		case plugin_ajax_object.fv_this_plugin_prefix + '_page_festinger-vault-theme-history':
			fv_do_history_page();
			break;

		case plugin_ajax_object.fv_this_plugin_prefix + '_page_festinger-vault-settings':
			fv_do_settings_page();
			break;

		default:
			// console.log('page not recognized: ', plugin_ajax_object.fv_current_screen );
			// Nothing to do.
			break;
	}

	// #toggle-event doesn't seem part of the plugin.
	jQuery("#toggle-event").bootstrapToggle( {
		on:  "",
		off: "",
	} );

}

/**
 * Script for the vaults page
 */
function fv_do_vault_page() {

	// console.log( 'running fv_do_vault_page()' );

	jQuery( '#history_table' ).DataTable( {
		"pageLength": 50,
		"order": [
			[ 6, "DESC" ]
		]
	} )

	jQuery('#filter_type'     ).on( 'change', fv_vault_show_filter_type_changed_value );
	jQuery('#reset_filter'    ).on( 'click',  fv_vault_on_click_reset_filters_button );
	jQuery('#mylist'          ).on( 'click',  fv_vault_show_tab_mylist_results );
	jQuery('#featured'        ).on( 'click',  fv_vault_show_tab_featured_results );
	jQuery('#popular'         ).on( 'click',  fv_vault_show_tab_popular_results );
	jQuery('#recent'          ).on( 'click',  fv_vault_show_tab_recent_results );
	jQuery('#filter_type'     ).on( 'change', fv_vault_show_filter_type_results );
	jQuery('#filter_allowence').on( 'change', fv_vault_show_filter_allowence_results );
	jQuery('#filter_category' ).on( 'change', fv_vault_show_filter_allowence_results );
	jQuery('#ajax_search'     ).on( 'keyup',  fv_vault_show_search_results );

	// Show spinner.
	fv_show_loading_animation();

	/**
	 * If s query-var present, then trigger search with that value.
	 *
	 * Note that this page is in fact only loaded once in full.
	 * After the first load, it will use events to refresh only the product grid.
	 *
	 * If that was not the case, this code would force it to always only
	 * show the result of this search string.
	 */
	if ( get_query_vars()['s'] ) {

		// Replace + in query var by space before using it as seach value.
		jQuery('#ajax_search').val( get_query_vars()['s'].replace(/\+/g, ' ') );

		// We need a custom event to pass the keyCode to the keyUp event.
		// the search string is only processed at certain keyCodes.
		var customEvent = jQuery.Event( 'keyup', { keyCode: 13 } );

		// Trigger the keyUp event so it will run the search
		jQuery('#ajax_search').trigger( customEvent );

	} else {

		// Load plugins and themes listing.
		fv_vault_load_products();
	}

	fv_vault_toggle_bulk_action_cart();

	// was run at windows.load() but document ready is same.
	fv_vault_refreshCartDisplay();
}

/**
 * Script for the Activation page
 */
function fv_do_activation_page() {
	// console.log( 'running fv_do_activation_page()' );

	jQuery( '#ajax-license-activation-form'     ).on( 'submit', fv_activation_form_submit );
	jQuery( '#ajax-license-refill-form'         ).on( 'submit', fv_activation_license_refill_form_submit );
	jQuery( '#ajax-license-refill-form2'        ).on( 'submit', fv_activation_license_refill_form_2_submit );
	jQuery( '.ajax-license-deactivation-form'   ).on( 'submit', fv_activation_deactivation_form_submit );
	jQuery( '.ajax-license-deactivation-form-2' ).on( 'submit', fv_activation_deactivation_form_2_submit );
}

/**
 * Script for the plugin updates page
 */
function fv_do_plugin_updates_page() {
	// console.log( 'running fv_do_plugin_updates_page()' );

	// plugin-updates page -> plugin -> auto update checkbox
	jQuery('.auto_plugin_update_switch')
		.on( 'change', fv_plugin_updates_toggle_plugin_auto_update  );

	// plugin-updates page -> FORCE UPDATE NOW button
	jQuery('#fv_force_update_plugins_button')
		.on( 'click', fv_plugin_updates_show_confirmation_force_update_now );

	// plugin-updates page -> Instant Update All button
	jQuery('#fv_instant_update_all_plugins_button')
		.on( 'click', fv_plugin_updates_show_confirmation_instant_update_all );

	// theme-updates page  -> FORCE UPDATE NOW NOT IN PLAN button
	// plugin-updates page -> FORCE UPDATE NOW NOT IN PLAN button
	jQuery('#manual_force_update_r')
		.on( 'click', fv_updates_show_alert_force_update_now_not_in_plan );

	// theme-updates page  -> Instant Update All NOT IN PLAN button
	// plugin-updates page -> Instant Update All NOT IN PLAN button
	jQuery('#manual_force_update_instant_r')
		.on( 'click', fv_updates_show_alert_instant_updates_all_not_in_plan );

	// theme-updates page  -> FORCE UPDATE NOW (NO UPDATES AVAILABLE) button
	// plugin-updates page -> FORCE UPDATE NOW (NO UPDATES AVAILABLE) button
	jQuery('#no_update_available')
		.on( 'click', fv_updates_show_alert_no_updates_available );

	// theme-updates page  -> Instant Update All (NO Instant UPDATES AVAILABLE) button
	// plugin-updates page -> Instant Update All (NO Instant UPDATES AVAILABLE) button
	jQuery('#no_instant_update_available')
		.on( 'click', fv_updates_show_alert_no_updates_available );
}

/**
 * Script for the theme updates page
 */
function fv_do_theme_updates_page() {
	// console.log( 'running fv_do_theme_updates_page()' );

	// theme-updates page -> theme -> auto update checkbox
	jQuery('.auto_theme_update_switch')
		.on( 'change', fv_theme_updates_toggle_theme_auto_update  );

	// theme-updates page -> FORCE UPDATE NOW button
	jQuery('#fv_force_update_themes_button')
		.on( 'click', fv_theme_updates_show_confirmation_force_update_now );

	// theme-updates page -> Instant Update All button
	jQuery('#fv_instant_update_all_themes_button')
		.on( 'click', fv_theme_updates_show_confirmation_instant_update_all );

	// theme-updates page  -> FORCE UPDATE NOW NOT IN PLAN button
	// plugin-updates page -> FORCE UPDATE NOW NOT IN PLAN button
	jQuery('#manual_force_update_r')
		.on( 'click', fv_updates_show_alert_force_update_now_not_in_plan );

	// theme-updates page  -> Instant Update All NOT IN PLAN button
	// plugin-updates page -> Instant Update All NOT IN PLAN button
	jQuery('#manual_force_update_instant_r')
		.on( 'click', fv_updates_show_alert_instant_updates_all_not_in_plan );

	// theme-updates page  -> FORCE UPDATE NOW (NO UPDATES AVAILABLE) button
	// plugin-updates page -> FORCE UPDATE NOW (NO UPDATES AVAILABLE) button
	jQuery('#no_update_available')
		.on( 'click', fv_updates_show_alert_no_updates_available );

	// theme-updates page  -> Instant Update All (NO Instant UPDATES AVAILABLE) button
	// plugin-updates page -> Instant Update All (NO Instant UPDATES AVAILABLE) button
	jQuery( '#no_instant_update_available'         )
		.on( 'click', fv_updates_show_alert_no_updates_available );
}

/**
 * Script for the histsory page
 */
function fv_do_history_page() {
	// console.log( 'running fv_do_history_page()' );
}

/**
 * Script for the setting page
 */
function fv_do_settings_page() {
	// console.log( 'running fv_do_settings_page()' );

	// settings page - white label form - submit button.
	jQuery('#white_label')
		.on( 'click', fv_settings_show_alert_white_label_not_available );
}

/**
 * Alert: Plan doesn't support white label functionality.
 *
 * @param {*} event
 */
function fv_settings_show_alert_white_label_not_available( event ) {
	// console.log( 'running fv_settings_show_alert_white_label_not_available()' );

	event.preventDefault();
	jQuery.alert( {
		title: 'Sorry!!!',
		content:
			'Your activated plan does not have white label feature. Please upgrade your license to enable this awesome feature. Click <a href="https://festingervault.com/get-started/" target="_blank">here</a> to upgrade. ',
	} );
}

/**
 * Show confirmation message before running Force Update NOW on the plugin updates page.
 *
 * @param {*} event
 */
function fv_plugin_updates_show_confirmation_force_update_now( event ) {
	// console.log( 'running fv_plugin_updates_show_confirmation_force_update_now()' );

	if ( ! confirm('Please confirm and auto update will run instantly!') ) {
		event.preventDefault();
	}
}

/**
 * Show confirmation message before running Instant update all on the plugin updates page.
 *
 * @param {*} event
 */
function fv_plugin_updates_show_confirmation_instant_update_all( event ) {
	// console.log( 'running fv_plugin_updates_show_confirmation_instant_update_all()' );

	if ( ! confirm('Please confirm to run update instantly!') ) {
		event.preventDefault();
	}
}

/**
 * Show confirmation message before running Force Update NOW.
 *
 * Page: Theme updates.
 *
 * @param {*} event
 */
function fv_theme_updates_show_confirmation_force_update_now( event ) {
	// console.log( 'running fv_theme_updates_show_confirmation_force_update_now()' );

	if ( ! confirm('Please confirm and auto update will run instantly!') ) {
		event.preventDefault();
	}
}

/**
 * Show confirmation message before running Instant update all.
 *
 * Page: Theme updates.
 *
 * @param {*} event
 */
function fv_theme_updates_show_confirmation_instant_update_all( event ) {
	// console.log( 'running fv_theme_updates_show_confirmation_instant_update_all()' );

	if ( ! confirm('Please confirm and instant update will run!') ) {
		event.preventDefault();
	}
}

/**
 * Show alert message Force UPDATE NOW not in plan.
 *
 * Page: Theme updates and Plugin updates.
 *
 * @param {*} event
 */
function fv_updates_show_alert_force_update_now_not_in_plan( event ) {
	// console.log( 'running fv_updates_show_alert_force_update_now_not_in_plan()' );

	event.preventDefault();
	jQuery.alert( {
		title: 'Sorry!!!',
		content:
			'Your activated plan does not have FORCE UPDATE feature.  Please upgrade your license to enable this awesome feature. Click <a href="https://festingervault.com/get-started/" target="_blank">here</a> to upgrade. ',
	} );
}

/**
 * Show alert message Instant update all not in plan.
 *
 * Page: Theme updates and Plugin updates.
 *
 * @param {*} event
 */
function fv_updates_show_alert_instant_updates_all_not_in_plan( event ) {
	// console.log( 'running fv_updates_show_alert_instant_updates_all_not_in_plan()' );

	event.preventDefault();
	jQuery.alert( {
		title: 'Sorry!!!',
		content:
			'Your activated plan does not have instant update feature. Please upgrade your license to enable this awesome feature. Click <a href="https://festingervault.com/get-started/" target="_blank">here</a> to upgrade. ',
	} );
}

/**
 * Show alert message No updates available on the plugin and theme updates pages.
 *
 * Page: Theme updates and Plugin updates.
 *
 * @param {*} event
 */
function fv_updates_show_alert_no_updates_available( event ) {
	// console.log( 'running fv_updates_show_alert_no_updates_available()' );

	event.preventDefault();
	jQuery.alert( {
		content: 'No update is available at this moment!',
	} );
}

/**
 * Click handler for auto-update switch.
 *
 * Page: Theme updates.
 */
function fv_theme_updates_toggle_theme_auto_update() {
	// console.log( 'running fv_theme_updates_toggle_theme_auto_update()' );

	var fv_theme_slug           = jQuery( this ).data('id');
	var fv_theme_auto_update_is_checked = true;

	if ( true == jQuery( this ).prop('checked') ) {
		fv_theme_auto_update_is_checked = true;
	} else {
		fv_theme_auto_update_is_checked = false;
	}

	jQuery.ajax( {
		data: {
			action: 'fv_toggle_theme_auto_update',
			fv_theme_slug:           fv_theme_slug,
			fv_theme_auto_update_is_checked: fv_theme_auto_update_is_checked,
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {
			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );

			if ( json.status == 'limitcrossed') {
				jQuery.alert( {
					title: 'Your auto update limit is crossed!',
					content:
						'You have already used all of your auto updates for this month. ' +
						'You can change auto update list again on next renewal. ' +
						'Your auto update limit is ' + json.plan_limit +
						'. This is not applicable for ONETIME license.',
				} );
			}
		},
	} );
}

/**
 * Click handler for auto-update switch.
 *
 * Page: Plugin updates.
 */
function fv_plugin_updates_toggle_plugin_auto_update() {
	// console.log( 'running fv_plugin_updates_toggle_plugin_auto_update()' );

	var fv_plugin_slug                   = jQuery( this ).data("id");
	var fv_plugin_auto_update_is_checked = false;

	if ( jQuery( this ).prop("checked") == true ) {
		fv_plugin_auto_update_is_checked = true;
	} else {
		fv_plugin_auto_update_is_checked = false;
	}

	jQuery.ajax( {
		data: {
			action: "fv_toggle_plugin_auto_update",
			fv_plugin_slug:                   fv_plugin_slug,
			fv_plugin_auto_update_is_checked: fv_plugin_auto_update_is_checked,
		},
		type: "POST",
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {
			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			if ( json == null ) {
				jQuery.alert( {
					title: "Alert!!",
					content:
						"Status is not updated please refresh the page and try again! ",
				} );
			} else {
				if ( json.status == "limitcrossed") {
					jQuery.alert( {
						title: "Your auto update limit is crossed!",
						content:
							"You have already used all of your auto updates for this month. You can change auto update list again on next renewal. Your auto update limit is " +
							json.plan_limit +
							". This is not applicable for ONETIME license.",
					} );
				}

				location.reload( true );
			}
		},
	} );
}

/**
 * Show new selected value in filter type select.
 *
 * Page: Vault page.
 */
function fv_vault_show_filter_type_changed_value() {
	// console.log( 'running fv_vault_show_filter_type_changed_value()' );

	var filterValue = jQuery( this ).val();
	var row         = jQuery('.filter_type_cate_val');

	row.hide();
	row.each( function( i, el ) {
		if ( jQuery( el ).attr('data-type') == filterValue ) {
			jQuery( el ).show();
		}
	} );

	if ('all' == filterValue ) {
		row.show();
	}
}

/**
 * Resets filters, tabs and search field.
 *
 * Page: Vault page.
 */
function fv_vault_on_click_reset_filters_button() {
	// console.log( 'running fv_vault_on_click_reset_filters_button()' );

	var row         = jQuery(".filter_type_cate_val");

	row.show();

	// Make all filters non active.
	fv_vault_make_filter_tab_active('');
	fv_vault_reset_filters_active_state();
	fv_vault_reset_filters_value();

	// note jQuery( function ) is just another way of writing  jQuery(document).ready()
	// here that isn't needed, since the whole function is already run only after .ready.
	// jQuery( fv_vault_reset_filters_value );

	// load the unfiltered plugins and themes list.
	fv_vault_load_products();
}

/**
 * Rebuilds the product grid after the mylist button is clicked.
 *
 * Page: Vault page.
 */
function fv_vault_show_tab_mylist_results() {
	// console.log( 'running fv_vault_show_tab_mylist_results()' );

	var mylist = jQuery("#mylist").val();

	fv_vault_make_filter_tab_active('#mylist');

	fv_show_loading_animation();

	Object.assign(
		ajax_filter_data,
		{
			content_type: mylist
		}
	);

	fv_vault_load_products( ajax_filter_data );
}

/**
 * Rebuilds the product grid after the featured button is clicked.
 *
 * Page: Vault page.
 */
function fv_vault_show_tab_featured_results() {
	// console.log( 'running fv_vault_show_tab_featured_results()' );

	var featured = jQuery("#featured").val();


	fv_vault_make_filter_tab_active('#featured');

	fv_show_loading_animation();

	Object.assign(
		ajax_filter_data,
		{
			content_type: featured
		}
	);

	fv_vault_load_products( ajax_filter_data );
}

/**
 * Rebuilds the product grid after the popular button is clicked.
 *
 * Page: Vault page.
 */
function fv_vault_show_tab_popular_results() {
	// console.log( 'running fv_vault_show_tab_popular_results()' );

	var popular = jQuery("#popular").val();

	fv_vault_make_filter_tab_active('#popular');

	fv_show_loading_animation();

	Object.assign(
		ajax_filter_data,
		{
			content_type: popular
		 }
	 );

	fv_vault_load_products( ajax_filter_data );
}

/**
 * Rebuilds the product grid after the recent button is clicked.
 *
 * Page: Vault page.
 */
function fv_vault_show_tab_recent_results() {
	// console.log( 'running fv_vault_show_tab_recent_results()' );

	var recent = jQuery("#recent").val();

	fv_vault_make_filter_tab_active('#recent');

	fv_show_loading_animation();

	Object.assign(
		ajax_filter_data,
		{
			content_type: recent
		}
	);

	fv_vault_load_products( ajax_filter_data );
}

/**
 * Rebuilds the product grid for a new selection in filter type.
 *
 * Page: Vault page.
 */
function fv_vault_show_filter_type_results() {
	// console.log( 'running fv_vault_show_filter_type_results()' );

	var filter_type = jQuery("#filter_type").val();

	if ( filter_type == "all" ) {
		jQuery("#filter_type").removeClass("active_button");
	} else {
		jQuery("#filter_type").addClass("active_button");
	}

	Object.assign(
		ajax_filter_data,
		{
			filter_type: filter_type
		}
	);

	fv_show_loading_animation();

	fv_vault_load_products( ajax_filter_data );
}

/**
 * Rebuilds the product grid for a new selection in filter allowance.
 *
 * Page: Vault page.
 */
function fv_vault_show_filter_allowence_results() {
	// console.log( 'running fv_vault_show_filter_allowence_results()' );

	var filter_allowence = jQuery("#filter_allowence").val();

	if ( filter_allowence == "all") {
		jQuery("#filter_allowence").removeClass("active_button");
	} else {
		jQuery("#filter_allowence").addClass("active_button");
	}

	Object.assign(
		ajax_filter_data,
		{
			filter_item: filter_allowence
		}
	);

	fv_show_loading_animation();

	fv_vault_load_products( ajax_filter_data );
}

/**
 * Rebuilds the product grid for a new selection in filter category.
 *
 * Page: Vault page.
 */
function fv_vault_show_filter_category_results() {
	// console.log( 'running fv_vault_show_filter_category_results()' );

	var filter_category = jQuery("#filter_category").val();

	if ( filter_category == "all") {
		jQuery("#filter_category").removeClass("active_button");
	} else {
		jQuery("#filter_category").addClass("active_button");
	}

	Object.assign(
		ajax_filter_data,
		{
			filter_category: filter_category
		}
	);

	fv_show_loading_animation();

	fv_vault_load_products( ajax_filter_data );
}

/**
 * Rebuilds the product grid for new search field entry.
 *
 * Page: Vault page.
 */
function fv_vault_show_search_results( e ) {
	// console.log( 'running fv_vault_show_search_results()' );

	var ajax_search = jQuery('#ajax_search').val();

	if ( ajax_search.length >= 1 ) {
		jQuery('#ajax_search').addClass('active_button');
		jQuery('#ajax_search').removeClass('non_active_button');
	} else {
		jQuery('#ajax_search').addClass('non_active_button');
		jQuery('#ajax_search').removeClass('active_button');
	}

	// Clear the previous timeout
	clearTimeout( timeoutId );

	// Set a new timeout of 500 milliseconds
	timeoutId = setTimeout( function() {

		if ( 8  == e.keyCode              // 8  = backspace key
		||   13 == e.keyCode              // 13 = return key
		|| ajax_search.length >= 3 ) {

			Object.assign(
				ajax_filter_data,
				{
					search_data: ajax_search
				}
			);

			fv_show_loading_animation();

			fv_vault_load_products( ajax_filter_data );
		}
	}, 500 ); // half second
}

/**
 * Activation of a license.
 *
 * Page: Activation page.
 */
function fv_activation_form_submit( e ) {
	// console.log( 'running fv_activation_form_submit()' );

	e.preventDefault();

	jQuery('#overlaybef').show();

	fv_show_loading_animation();

	var licenseKeyInput = jQuery('#licenseKeyInput').val();

	jQuery.ajax( {
		data: {
			action: 'fv_activate_license_form_submit',
			licenseKeyInput: licenseKeyInput,
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {
			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );

			switch ( json.result ) {
				case 'valid':
					// license succesfully activated
					jQuery('#ajax-license-activation-form').hide();

					jQuery('#activation_result').addClass(
						'card text-center text-success'
					);
					jQuery('#activation_result').removeClass(
						'text-warning text-danger'
					);
					break;

				case 'invalid':
					jQuery('#activation_result').addClass(
						'card text-center text-warning'
					);
					jQuery('#activation_result').removeClass(
						'text-success text-danger'
					);
					break;

				case 'invalid':
					jQuery('#activation_result').addClass(
						'card text-center text-danger'
					);
					jQuery('#activation_result').removeClass(
						'text-success text-warning'
					);
					break;

				default:
					break;
			}

			jQuery('#activation_result').html( json.msg );

			setTimeout( function() {
				location.reload();
			}, 3000 );
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Handle license 1 refill form submission.
 *
 * Page: Activation page.
 *
 * @param {*} e
 */
function fv_activation_license_refill_form_submit( e ) {
	// console.log( 'running fv_activation_license_refill_form_submit()' );
	fv_do_license_refill_form_submit( '#license_key', '#refill_key' );
}

/**
 * Handle license 2 refill form submission.
 *
 * Page: Activation page.
 *
 * @param {*} e
 */
function fv_activation_license_refill_form_2_submit( e ) {
	// console.log( 'running fv_activation_license_refill_form_2_submit()' );
	fv_do_license_refill_form_submit( '#license_key', '#refill_key' );
}

/**
 * Processing a license refill form submission (License 1+2)
 *
 * Page: Activation page.
 *
 * @param {string} license_key_field_id
 * @param {string} refill_key_field_id
 */
function fv_do_license_refill_form_submit( license_key_field_id, refill_key_field_id ) {
	// console.log( 'running fv_do_license_refill_form_submit()' );

	e.preventDefault();
	jQuery('#overlaybef').show();
	fv_show_loading_animation();

	var license_key = jQuery( license_key_field_id ).val();
	var refill_key  = jQuery( refill_key_field_id  ).val();

	jQuery.ajax( {
		data: {
			action: 'fv_refill_license_form_submit',
			license_key: license_key,
			refill_key:  refill_key,
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			if ( json.result == 'success') {
				jQuery('#credit_refill_msg').removeClass('text-danger mb-3');
				jQuery('#credit_refill_msg').addClass('text-success mb-3');
				jQuery('.refill_button').hide();
				jQuery('.refresh_button').show();
			} else {
				jQuery('#credit_refill_msg').removeClass('text-success mb-3');
				jQuery('#credit_refill_msg').addClass('text-danger mb-3');
			}
			jQuery('#credit_refill_msg').html( json.msg );
			jQuery( refill_key_field_id ).val('');
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Handle deactivation of license 1 (form submission).
 *
 * Page: Activation page.
 *
 * @param {*} e
 */
function fv_activation_deactivation_form_submit( e ) {
	// console.log( 'running fv_activation_deactivation_form_submit()' );
	fv_do_deactivation_form_submit(
		'#license_key',
		'#license_d',
		'.deactivation_result'
	);
}

/**
 * Handle deactivation of license 2 (form submission).
 *
 * Page: Activation page.
 *
 * @param {*} e
 */
function fv_activation_deactivation_form_2_submit( e ) {
	// console.log( 'running fv_activation_deactivation_form_2_submit()' );
	fv_do_deactivation_form_submit(
		'#license_key_2',
		'#license_d_2',
		'.deactivation_result2'
	);
}

/**
 * Processing a license deactivation form submission (License 1+2)
 *
 * Page: Activation page.
 *
 * @param {string} license_key_field_id CSS id-selector of the license key input field.
 * @param {string} license_domain_field_id CSS id-selector of the license domain id input field.
 * @param {string} deactivation_result_field_class CSS class-selector of the deactivation result.
 */
function fv_do_deactivation_form_submit(
	license_key_field_id,
	license_domain_field_id,
	deactivation_result_field_class
	) {
	// console.log( 'running fv_do_deactivation_form_submit()' );

	e.preventDefault();

	jQuery('#overlaybef').show();

	fv_show_loading_animation();

	var license_key = jQuery( license_key_field_id    ).val();
	var license_d   = jQuery( license_domain_field_id ).val();

	jQuery.ajax( {
		data: {
			action: 'fv_deactivate_license_form_submit',
			license_key: license_key,
			license_d:   license_d,
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {
			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );

			if ( json.result == 'failed') {
				jQuery( deactivation_result_field_class ).addClass(
					'card text-center text-danger'
				);
				jQuery( deactivation_result_field_class ).removeClass(
					'text-success text-warning'
				);
			} else if ( json.result == 'notfound') {
				jQuery( deactivation_result_field_class ).addClass(
					'card text-center text-warning'
				);
				jQuery( deactivation_result_field_class ).removeClass(
					'text-success text-danger'
				);
			} else if ( json.result == 'success') {
				jQuery('#ajax-license-activation-form').hide();
				jQuery( deactivation_result_field_class ).addClass(
					'card text-center text-success'
				);
				jQuery( deactivation_result_field_class ).removeClass(
					'text-warning text-danger'
				);
			}
			jQuery( deactivation_result_field_class ).html( json.msg );
			setTimeout( function() {
				location.reload();
			}, 5000 );
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Marks tab buttons to make one active and the rest inactive (Vault page).
 *
 * Page: Vault page.
 *
 * @param {string} id CSS id-selector of the active tab button.
 */
function fv_vault_make_filter_tab_active( id ) {
	// console.log( 'running fv_vault_make_filter_tab_active()' );

	let tabs    = [ '#popular', '#recent', '#featured', '#mylist' ];

	tabs.forEach( tab => {
		if ( tab === id ) {
			jQuery( tab ).removeClass('non_active_button');
			jQuery( tab ).addClass('active_button');
			return;
		}
		jQuery( tab ).removeClass('active_button');
		jQuery( tab ).addClass('non_active_button');
	});
}

/**
 * Marks all tab buttons as inactive (Vault page).
 *
 * Page: Vault page.
 */
function fv_vault_reset_filters_active_state() {
	// console.log( 'running fv_vault_reset_filters_active_state()' );

	let filters = [ "#filter_type", "#filter_allowence", "#filter_category", "#ajax_search" ];

	filters.forEach( filter => {
		jQuery( filter ).removeClass('active_button');
		jQuery( filter ).addClass('non_active_button');
	});
}

/**
 * Shows a modal popup for installing an item in Vault.
 *
 * @param {object} button Button that called the function.
 */
function fv_vault_on_click_install_product_button( button ) {
	// console.log( 'running fv_vault_on_click_install_product_button()' );

	jQuery('.progress').hide();
	fv_show_loading_animation();

	var product_hash   = button.getAttribute('data-id');

	jQuery.ajax( {
		data: {
			action: 'fv_get_remote_product_download_data',
			product_hash: product_hash,
		},
		type: 'POST',
		url: plugin_ajax_object.ajax_url,
		success: function( data ) {

			let data_s = data.slice( 0, -1 );
			let json   = JSON.parse( data_s );

			/**
			 * This api action results the result in array,
			 * with (expected) only one entry.
			 * Each entry is json decoded, so we should parse,
			 * since otherwise we cannot check for result.
			 */
			if ( Array.isArray( json ) && json.length >= 1 ) {
				json   = JSON.parse( json[0] );
			}
			// console.log( 'json = ', json );

			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );

			switch ( true ) {
				case ( 0 == json.length ):
					jQuery.alert( {
						content: 'To enjoy this feature please activate your license.',
					} );
					break;

				case ( 'failed' === json.result ):
					jQuery.alert( {
						content: json.msg,
					} );
					break;

				default:
					fv_vault_show_modal_product_install_form( json );
					break;
			}
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Render and show a popup after a user clicked the install button on a plugin or theme in the vault page.
 *
 * @param {*} json
 */
function fv_vault_show_modal_product_install_form( product ) {
	// console.log( 'running fv_vault_show_modal_product_install_form()' );
	let html = '<div class="row">';

	let count_versions         = product.other_available_versions.length;
	let fv_install_button_text = '';

	// if only one version, the select can't be changed, so onClick.
	let fv_on_event = 'onChange';
	if ( count_versions == 1 ) {
		fv_on_event = 'onClick';
	}

	fv_install_button_text = fv_vault_get_install_product_button_text( product.product_slug, product.product_type );

	// Render html for the confirmation popup.
	html +=
		'<div class="col">' +
			// license information
			'<div class="card bg-light" style="min-width:100%;">' +
				'<div class="card-header">' +
					product.plan_name +
				'</div>' +
				'<ul class="list-group list-group-flush">' +
					'<li class="list-group-item">' +
						'Plan Type<b>: ' + product.plan_type.toUpperCase() +
					"</b></li>" +
					'<li class="list-group-item">' +
						'Plan Limit: ' + product.plan_limit +
					"</li>" +
					'<li class="list-group-item">' +
						'Available Limit: ' + product.download_available +
					'</li>' +
					fv_perhaps_add_version_installed( product ) +
				'</ul>' +
			'</div>' +
			// installation button
			'<button id="option1" class="btn btn-sm btn-block card-btn" ' +
				'data-license="' + product.license_key  + '" ' +
				'data-type="'    + product.product_type + '" ' +
				'data-id="'      + product.product_hash + '" ' +
				'href="#" ' +
				'onclick="fv_vault_on_submit_product_install_form( this ); this.disabled=true;"' +
				'>' +
				'<i class="fa fa-arrow-down"></i>' +
				fv_install_button_text +
			'</button>' +
			// version selection
			'<div class="row" style="margin-top:40px;">' +
				'<div class="col">' +
					'<table class="table table-bordered" style="color:#fff;">' +
						'<tr>' +
							'<td>' +
								'<div class="input-group text-white">' +
									'<label class="input-group-text" for="inputGroupSelect01">' +
										'Please choose your preferred version. Once selected, it will be installed and activated automatically' +
									'</label>' +
									// other available versions.
									'<select ' +
										fv_on_event + '="fv_vault_on_submit_product_install_form( this ); this.disabled=true;" ' +
										'class="form-select text-white ' + product.license_key + product.product_hash + '" ' +
										'name="downloadOtherVerions"' +
									'>' +
										fv_vault_get_other_versions_options_html( product ) +
									'</select>' +
								'</div>' +
							'</td>' +
						'</tr>' +
					'</table>' +
				'</div>' +
			'</div>' +
		'</div>';

	html += "</div>";

	// fill and show install confirmation modal
	jQuery('.modal-body').html( html );
	jQuery('#empModal' ).modal('show');

}

function fv_is_product_slug_found( needle, haystack ) {
	// console.log( 'running fv_is_product_slug_found()' );
	return ( -1 !== jQuery.inArray( needle, JSON.parse( haystack ) ) );
}

/**
 * Handles submissions of the product install form (install confirmation modal).
 *
 * @param {object} submit_el Submit button or Select element.
 * @returns
 */
function fv_vault_on_submit_product_install_form( submit_el ) {
	// console.log( 'running fv_vault_on_submit_product_install_form()' );

	fv_show_loading_animation();

	// Submit element can be a submit button or a select element.
	switch ( submit_el.type ) {

		// User clicked the submit button
		case 'submit':
			var fv_product_hash = submit_el.getAttribute('data-id');
			var fv_license_key  = submit_el.getAttribute('data-license');
			var fv_mfile        = submit_el.getAttribute('data-key');
			break;

		// User changed the select element
		case 'select-one':
			let selected_option = $( submit_el ).find( 'option:selected' );
			var fv_product_hash = selected_option.data('id');
			var fv_license_key  = selected_option.data('license');
			var fv_mfile        = selected_option.data('key');
			break;

		default:
			fv_hide_loading_animation();
			return;
	}

	/**
	 * Show "are you sure?" confirmation.
	 */
	if ( ! fv_are_you_sure() ) {
		fv_hide_loading_animation();
		jQuery('#empModal').modal('hide');
		return;
	}

	jQuery('#empModal').modal('hide');

	jQuery.ajax( {
		data: {
			// downloads and installs the selected plugin or theme.
			action: 'fv_vault_product_install',
			fv_product_hash: fv_product_hash,
			fv_license_key:  fv_license_key,
			fv_mfile:        fv_mfile,
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			if ( json.result == 'success') {
				jQuery('#' + fv_license_key + ' #plan_limit_id'      ).html( json.plan_limit );
				jQuery('#' + fv_license_key + ' #current_limit_id'   ).html( json.download_current_limit );
				jQuery('#' + fv_license_key + ' #limit_available_id' ).html( json.download_available );

				jQuery('#empModal').modal('hide');

				if ( json.link == 'theme') {
					jQuery.alert( {
						content:
							'Theme successfully installed. Click here to ' +
							'<a target="_blank" href="' + json.theme_preview + '">' +
								'Preview theme' +
							'</a>!',
					} );
				} else {
					location.href = json.activation;
				}
			} else {
				jQuery('#empModal').modal('hide');
				if ( json.result == 'failed' && json.msg == 'Daily limit crossed') {
					jQuery.alert( {
						title: 'Sorry! Limit issue!',
						content:
							'Your daily Download Limit is crossed, you can download tomorrow again! Happy downloading.',
					} );
				} else {
					if ( json.msg ) {
						jQuery.alert( {
							title: 'Alert!',
							content: json.msg,
						} );
					} else {
						jQuery.alert( {
							title: 'Alert!',
							content: 'Hello!Something went wrong, Please try again later!',
						} );
					}
				}
			}
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Ask extra confirmation of intent.
 *
 * @returns {bool} true if confirmed.
 */
function fv_are_you_sure() {
	return confirm('Are you sure you want to continue?');
}

/**
 * Shows a modal popup for downloading an item in Vault.
 *
 * @param {object} button Button that called the function.
 */
function fv_vault_on_click_download_product_button( button ) {
	// console.log( 'running fv_vault_on_click_download_product_button()' );

	// progress bar
	jQuery(".progress").hide();

	// spinner/loading indicator...
	fv_show_loading_animation();

	let product_hash = button.getAttribute("data-id");

	jQuery.ajax( {
		data: {
			action: "fv_get_remote_product_download_data",
			product_hash: product_hash
		},
		type: "POST",
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			let data_s = data.slice( 0, -1 );
			let json = JSON.parse( data_s );

			/**
			 * If API returned result in array, then it should have only one entry.
			 * Each entry is json decoded, so we should parse,
			 * since otherwise we cannot check for result.
			 */
			if ( Array.isArray( json ) && json.length >= 1 ) {
				json   = JSON.parse( json[0] );
			}

			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );
			switch ( true ) {
				case ( 0 == json.length ):
					jQuery.alert( {
						content: 'To enjoy this feature please activate your license.',
					} );
					break;

				case ( 'failed' === json.result ):
					jQuery.alert( {
						content: json.msg,
					} );
					break;

				default:
					fv_vault_show_modal_product_download_form( json );
					break;
			}
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Renders modal popup triggered by a download button in the Vault.
 *
 * @param {*} json
 */
function fv_vault_show_modal_product_download_form( json ) {
	// console.log( 'running fv_vault_show_modal_product_download_form()' );

	var form_html = '<div class="row">';

	form_html += fv_vault_get_single_product_download_form_html( json );

	form_html += '</div>';

	jQuery('.modal-body').html( form_html );
	jQuery('#empModal'  ).modal('show');
	setTimeout( function() {
		fv_hide_loading_animation();
	}, 500 );
}

function fv_vault_get_single_product_download_form_html( product ) {

	let count_versions = product.other_available_versions.length;
	/**
	 * React to click event if only one version in dropdown list,
	 * Otherwise react to value change.
	 */
	let fv_on_event = 'onChange';
	if ( count_versions == 1 ) {
		fv_on_event = 'onClick';
	}

	return '' +
		'<div class="col">' +
			// Lincense information
			'<div class="card bg-light" style="min-width:100%;"> ' +
				'<div class="card-header">' +
				product.plan_name +
				'</div>' +
				'<ul class="list-group list-group-flush">' +
					'<li class="list-group-item">' +
						'Plan Type<b>: '    + product.plan_type.toUpperCase() + '</b>' +
					'</li>' +
					'<li class="list-group-item">' +
						'Plan Limit: '      + product.plan_limit +
					'</li>' +
					'<li class="list-group-item">' +
						'Available Limit: ' + product.download_available +
					'</li>' +
					fv_perhaps_add_version_installed( product ) +
				'</ul>' +
			'</div>' +
			// Download button
			'<button id="option1" ' +
				'data-license="' + product.license_key + '" ' +
				'data-id="'      + product.product_hash + '" ' +
				'onclick="fv_vault_on_submit_product_download_form( this ); this.disabled=true;" ' +
				'class="btn btn-sm btn-block card-btn"><i class="fa fa-download"></i>' +
				'Download LATEST VERSION' +
			'</button>' +
			// Available versions.
			'<div class="row" style="margin-top:40px;">' +
				'<div class="col">' +
					'<table class="table table-bordered" style="color:#fff;">' +
						'<tr>' +
							'<td>' +
								'<div class="input-group text-white">' +
									'<label class="input-group-text" for="inputGroupSelect01">Please choose your preferred version. Once selected, it will be installed and activated automatically</label>' +
									'<select ' +
										fv_on_event + '="fv_vault_on_submit_product_download_form( this ); this.disabled=true;" ' +
										'class="form-select text-white ' + product.license_key + product.product_hash + '" ' +
										'name="downloadOtherVerions"' +
									'>' +
										fv_vault_get_other_versions_options_html( product ) +
									'</select>' +
								'</div>' +
							'</td>' +
						'</tr>' +
					'</table>' +
				'</div>' +
			'</div>' +
		'</div>';
}

function fv_perhaps_add_version_installed( product ) {
	if ( product.version_installed ) {
		return '' +
		'<li class="list-group-item">' +
			'Installed Version: ' + product.version_installed +
		'</li>';
	}
	return '';
}

function fv_vault_get_other_versions_options_html( product ) {
	// console.log( 'running fv_vault_get_other_versions_options_html()' );
	// console.log( 'product = ', product );
	var product_versions_html = '';

	if ( ! Array.isArray( product.other_available_versions ) ) {
		// console.log( 'not an array: product.other_available_versions ')
		return '';
	}

	jQuery.each( product.other_available_versions.reverse(), function( index, other_version ) {
		product_versions_html +=
			'<option ' +
				'value="'        + other_version.generated_version + '" ' +
				'data-type="'    + product.product_type            + '" ' +
				'data-key="'     + other_version.filename          + '" ' +
				'data-license="' + product.license_key             + '" ' +
				'data-id="'      + product.product_hash            + '" ' +
			'>' +
				'Version '       + other_version.generated_version +
			'</option>';
	});

	return product_versions_html
}

/**
 * Handles submissions of the product download form (download confirmation modal).
 *
 * @param {object} submit_el Submit button or Select element.
 * @returns
 */
function fv_vault_on_submit_product_download_form( submit_el ) {
	// console.log( 'running fv_vault_on_submit_product_download_form()' );

	fv_show_loading_animation();


	// Submit element can be a submit button or a select element.
	switch ( submit_el.type ) {

		// User clicked the submit button
		case 'submit':
			var fv_product_hash  = submit_el.getAttribute('data-id');
			var fv_license_key   = submit_el.getAttribute('data-license');
			// not an attribute of button, so fv_mfile will be null.
			var fv_mfile         = submit_el.getAttribute('data-key');
			break;

		// User changed the select element
		case 'select-one':
			let selected_option  = $( submit_el ).find( 'option:selected' );
			var fv_product_hash  = selected_option.data('id');
			var fv_license_key   = selected_option.data('license');
			var fv_mfile         = selected_option.data('key');
			break;

		default:
			fv_hide_loading_animation();
			return;
	}

	jQuery('#empModal').modal('hide');

	jQuery.ajax( {
		data: {
			action: "fv_plugin_download_ajax",
			fv_product_hash: fv_product_hash,
			fv_license_key:  fv_license_key,
			fv_mfile:        fv_mfile,
		},
		type: "POST",
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );
			// console.log( 'json = ', json );

			// hide modal.
			jQuery("#empModal").modal("hide");

			if ( json.result == "success") {
				// set license budgets.
				jQuery("#" + fv_license_key + " #plan_limit_id"     ).html( json.plan_limit );
				jQuery("#" + fv_license_key + " #current_limit_id"  ).html( json.download_current_limit );
				jQuery("#" + fv_license_key + " #limit_available_id").html( json.download_available );
				// redirect to the download link.
				location.href = json.link;
			} else {

				if ( json.result == "failed" && json.msg == "Daily limit crossed") {

					if ( json.plan_type == "onetime") {
						jQuery.alert( {
							title: "Sorry! Limit issue!",
							content:
								"Your Download Limit is over, For onetime license please refill to enjoy downloading again! Happy downloading.",
						} );
					} else {
						jQuery.alert( {
							title: "Sorry! Limit issue!",
							content:
								"Your daily Download Limit is crossed, you can download tomorrow again! Happy downloading.",
						} );
					}

				} else {
					if ( json.msg ) {
						jQuery.alert( {
							title: "Alert!",
							content: json.msg,
						} );
					} else {
						jQuery.alert( {
							title: "Alert!",
							content: "Something went wrong, Please try again later!",
						} );
					}
				}
			}
		},
	} )

	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Shows a modal popup for an reporting an item in Vault.
 *
 * @param {object} button Button that called the function.
 */
function fv_vault_on_click_report_item_button( button ) {
	// console.log( 'running fv_vault_on_click_report_item_button()' );

	jQuery('.progress').hide();
	fv_show_loading_animation();

	// get relevant data from buttons attributes.
	let fv_item_support_link   = button.getAttribute('data-support-link');
	let fv_item_slug           = button.getAttribute('data-product-hash');
	let fv_item_hash           = button.getAttribute('data-generated-slug');
	let fv_item_name           = button.getAttribute('data-generated-name');

	jQuery
		.ajax( {
			data: {
				action: 'fv_vault_item_report_has_license',
				fv_item_support_link: fv_item_support_link,
				fv_item_slug:         fv_item_slug,
				fv_item_hash:         fv_item_hash,
				fv_item_name:         fv_item_name
			},
			type: 'POST',
			url:  plugin_ajax_object.ajax_url,
			success: fv_vault_show_modal_report_item_form,
		} )
		.done( function() {
			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );
		} );
}

/**
 * Renders and inserts a (modal) form for reporting an item in Vault.
 *
 * Page: Vault page
 *
 * @param {object} data Data object that was returned by ajax action 'fv_vault_item_report_has_license'.
 */
function fv_vault_show_modal_report_item_form( data ) {
	// console.log( 'running fv_vault_show_modal_report_item_form()' );
	// console.log( data );

	let report_item_form_html = '';
	let data_s                = data.slice( 0, -1 );
	let json                  = JSON.parse( data_s );

	if ( json.result !== 'success') {
		return;
	}

	setTimeout( function() {
		fv_hide_loading_animation();
	}, 500 );

	report_item_form_html  =
		// Report item form
		'<div class="row">' +
			'<form class="reportitemform" ' +
				'data-product-hash="'   + json.fv_item_hash   + '" ' +
				'data-generated-slug="' + json.fv_item_slug   + '" ' +
				'data-license="'        + json.fv_license_key + '"'  +
			'>' +
				'<div class="mb-3">' +
					// Report item text
					'<textarea ' +
						'name="fv_report_item_text" ' +
						'placeholder="Fill in your report and it will be automatically posted on the community forums. You will receive your reported link afterwards." ' +
						'class="form-control text-white">' +
					'</textarea>' +
					// Link to product (community) support page
					'<input type="hidden" ' +
						'name="fv_item_support_page_url" ' +
						'value="' + json.fv_item_support_link + '" ' +
						'class="form-control text-white" ' +
					'>' +
					// Product name
					'<input type="hidden" ' +
						'name="fv_item_name" ' +
						'value="' + json.fv_item_name + '" ' +
						'class="form-control text-white" ' +
					'>' +
					// License key
					'<input type="hidden" ' +
						'name="fv_license_key" ' +
						'value="' + json.fv_license_key + '" ' +
						'class="form-control text-white" ' +
					'>' +
				'</div><br/>' +
				// form submit button
				'<div class="d-grid gap-2 col-12 mx-auto"> ' +
					'<button ' +
						'class="btn btn-secondary"' +
						'onclick="fv_vault_on_submit_report_item_form( this )" ' +
						'type="button"' +
					'>' +
						'Submit' +
					'</button>  ' +
				'</div>' +
			'</form>' +
		'</div>';

	jQuery('.modal-body').html( report_item_form_html );
	jQuery('#empModal').modal('show');
}

/**
 * Handles submissions of the report item form.
 *
 * @param {object} button Submit button or report item form.
 * @returns
 */
function fv_vault_on_submit_report_item_form( button ) {
	// console.log( 'running fv_vault_on_submit_report_item_form()' );

	// get the parent form of the clicked button
	let form                     = jQuery( button ).closest("form");
	let fv_license_key           = form.find('input[name="fv_license_key"]'          ).val();
	var fv_item_support_page_url = form.find('input[name="fv_item_support_page_url"]').val();
	let fv_item_name             = form.find('input[name="fv_item_name"]'            ).val();
	let fv_report_item_text      = form.find('textarea[name="fv_report_item_text"]'  ).val();
	let fv_item_postid           = fv_get_postid_from_item_support_page_url( fv_item_support_page_url );

	// form validations...

	if ( fv_report_item_text.trim() === '') {
		alert( 'Please enter your report before submitting.' );
		return;
	}

	// hide form and show spinner.

	jQuery('#empModal').modal('hide');

	fv_show_loading_animation();

	// Remote post the item report to the fv_api.

	jQuery.ajax( {
		data: {
			action: 'fv_vault_remote_report_item',
			fv_report_item_text: fv_report_item_text,
			fv_item_postid:      fv_item_postid,
			fv_license_key:      fv_license_key,
			fv_item_name:        fv_item_name
		},
		type: "POST",
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			// console.log( 'fv_vault_on_submit_report_item_form', 'json = ', json );
			// hide loading animation and form.

			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );

			// show success alert
			if ( json.result == "success") {
				jQuery.alert( {
					content: 'Your request has been successfully posted. ' +
						'Visit <a target="_blank" href="' + fv_item_support_page_url + '">this link</a> to see.',
				} );
			}

			// show failure alert.
			if ( json.result == "failed") {
				jQuery.alert( {
					content: json.msg,
				} );
			}
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Shows a modal popup for an update request in Vault.
 *
 * @param {*} button
 */
function fv_vault_on_click_update_request_button( button ) {
	// console.log( 'running fv_vault_on_click_update_request_button()' );

	jQuery('.progress').hide();

	fv_show_loading_animation();

	let fv_item_support_link = button.getAttribute('data-support-link');
	let fv_item_hash         = button.getAttribute('data-product-hash');
	let fv_item_slug         = button.getAttribute('data-generated-slug');
	let fv_item_name         = button.getAttribute('data-generated-name');

	jQuery
		.ajax( {
			data: {
				action: 'fv_vault_update_request_has_license',
				fv_item_support_link:   fv_item_support_link,
				fv_item_hash:           fv_item_hash,
				fv_item_slug:           fv_item_slug,
				fv_item_name:           fv_item_name
			},
			type: 'POST',
			url:  plugin_ajax_object.ajax_url,
			success: fv_vault_show_modal_update_request_form,
		} )
		.done( function() {
			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );
		} );
}

/**
 * Renders and inserts a (modal) form for reporting an item in Vault.
 *
 * Page: Vault page
 *
 * @param {object} data Data object that was passed to ajax call.
 */
function fv_vault_show_modal_update_request_form( data ) {
	// console.log( 'running fv_vault_show_modal_update_request_form()' );

	let html = '';
	let data_s                   = data.slice( 0, -1 ); // trim last char.
	let json                     = JSON.parse( data_s );

	// console.log( 'fv_vault_show_modal_update_request_form', 'json = ', json );

	if ( 'success' !== json.result ) {
		return;
	}

	setTimeout( function() {
		fv_hide_loading_animation();
	}, 500 );

	html =
		'<div class="row">' +
			'<form ' +
				'class="submitupdaterequest" ' +
				'data-product-hash="'   + json.data_product_hash   + '" ' +
				'data-generated-slug="' + json.data_generated_slug + '" ' +
				'data-license="'        + json.license_key         + '"' +
			'>' +
				'<div class="mb-3">' +
					// version number request link
					'<input ' +
						'class="form-control text-white" ' +
						'type="hidden" ' +
						'name="fv_item_support_page_url" ' +
						'value="' + json.data_support_link   + '"' +
					'>' +
					// product generated nam
					'<input ' +
						'class="form-control text-white" ' +
						'type="hidden" ' +
						'name="fv_item_name" ' +
						'value="' + json.fv_item_name + '"' +
					'>' +
					// license key
					'<input ' +
						'class="form-control text-white" ' +
						'type="hidden" ' +
						'name="fv_license_key" ' +
						'value="' + json.license_key    + '"' +
					'>' +
					// version number entry field
					'<input ' +
						'class="form-control text-white" ' +
						'type="text" ' +
						'name="fv_requested_version_number" ' +
						'placeholder="Enter the version number ( e.g. 2.3.2 )" ' +
						'onkeydown="if ( event.keyCode==13 ) {event.preventDefault();}"' +
					'>' +
				'</div><br/>' +
				'<div class="d-grid gap-2 col-12 mx-auto"> ' +
					// submit button
					'<button ' +
						'class="btn btn-secondary" ' +
						'type="button"' +
						'onclick="fv_vault_on_submit_update_request_form( this )" ' +
					'>' +
						'Submit' +
					'</button>  ' +
				'</div>' +
			'</form>' +
		'</div>';

	jQuery('.modal-body').html( html );
	jQuery('#empModal'  ).modal('show');
}

// Handle update request form submit
function fv_vault_on_submit_update_request_form( button ) {
	// console.log( 'running fv_vault_on_submit_update_request_form()' );

	// get the parent form of the clicked button
	let form                        = jQuery( button ).closest('form');
	let fv_requested_version_number = form.find('input[name="fv_requested_version_number"]').val();
	let fv_license_key              = form.find('input[name="fv_license_key"]'             ).val();
	let fv_item_name                = form.find('input[name="fv_item_name"]'               ).val();
	var fv_item_support_page_url    = form.find('input[name="fv_item_support_page_url"]'   ).val();
	let fv_item_postid              = fv_get_postid_from_item_support_page_url( fv_item_support_page_url );

	// form validations...

	if ( fv_requested_version_number.trim() === '') {
		alert('Version number is required.');
		return;
	}

	// hide form and show spinner.

	jQuery('#empModal').modal('hide');

	fv_show_loading_animation();

	// post the update request to the api (via ajax) an process result.

	jQuery.ajax( {
		data: {
			action: 'fv_vault_remote_request_update',
			fv_version_number: fv_requested_version_number,
			fv_item_postid:    fv_item_postid,
			fv_license_key:    fv_license_key,
			fv_item_name:      fv_item_name
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );

			// hide spinner

			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );

			// success alert
			if ( json.result == 'success') {
				jQuery.alert( {
					content: 'You request has been successfully posted. ' +
						'Visit <a target="_blank" href="' + fv_item_support_page_url + '">this link</a> to see.',
				} );
			}

			// failure alert
			if ( json.result == "failed") {
				jQuery.alert( {
					content: json.msg,
				} );
			}
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Callback for the install button of the bulk install/download popup in the Vault page.
 *
 * @param {*} d
 */
function fv_vault_on_submit_bulk_install_confirmation( d ) {
	// console.log( 'running fv_vault_on_submit_bulk_install_confirmation()' );

	jQuery(".progress").show();
	fv_show_loading_animation();
	fv_vault_updateProgressBarAuto( 50 );

	// Check if there is only one option in the select

	var plugin_download_hash = productHashJson;
	var license_key          = d.getAttribute("data-license");
	var data_key             = d.getAttribute("data-key");

	if ( typeof license_key === 'undefined' || license_key === undefined ) {
		var plugin_download_hash = d.getAttribute("data-id");
		var license_key          = d.getAttribute("data-license");
		var data_key             = d.getAttribute("data-key");
	}

	jQuery.ajax( {
		data: {
			action: "fv_plugin_install_bulk_ajax",
			plugin_download_hash: plugin_download_hash,
			license_key:          license_key,
			mfile:                data_key,
		},
		type: "POST",
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

				// fv_vault_updateProgressBarAuto( 100 );

			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );

			if ( json.result == "success") {
				jQuery("#" + license_key + " #plan_limit_id").html( json.plan_limit );
				jQuery("#" + license_key + " #current_limit_id").html( json.download_current_limit );
				jQuery("#" + license_key + " #limit_available_id").html( json.download_available );

				//location.href = json.link;

				//
				// jQuery("#empModal").modal("hide");
			} else {

				if ( typeof json.result !== 'undefined' ) {
					if ( json.msg ) {
						jQuery.alert( {
							title:   "Alert!",
							content: json.msg,
						} );

					} else {
						jQuery.alert( {
							title:   "Alert!",
							content: "Something went wrong, Please try again later!",
						} );
					}

					jQuery("#empModal").modal("hide");
					fv_hide_loading_animation();
				}

				//jQuery("#empModal").modal("hide");
				if ( json.config.result == "failed" && json.config.msg == "Daily limit crossed") {
					if ( json.config.plan_type == "onetime") {
						jQuery.alert( {
							title:   "Sorry! Limit issue!",
							content: "Your Download Limit is over, For onetime license please refill to enjoy downloading again! Happy downloading.",
						} );
					} else {
						jQuery.alert( {
							title:   "Sorry! Limit issue!",
							content: "Your daily Download Limit is crossed, you can download tomorrow again! Happy downloading.",
						} );
					}
				} else {
					if ( json.config.msg ) {
						jQuery.alert( {
							title: "Alert!",
							content: json.config.msg,
						} );
					} else {
						// jQuery.alert( {
						//   title: "Alert!",
						//   content: "Items has been installed successfully.",
						// } );
						fv_vault_updateProgressBar( json.getfilesize );
						$.removeCookie( 'cartData' );
						fv_vault_refreshCartDisplay();
					}
				}
			}
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Callback of the download button of the download popup in the Vault page.
 *
 * @param {*} d
 */
function fv_vault_on_submit_bulk_download_confirmation( d ) {
	// console.log( 'running fv_vault_on_submit_bulk_download_confirmation()' );

	jQuery(".progress").show();
	fv_show_loading_animation();
	fv_vault_updateProgressBarAutoDLBefore( 50 );
		// Check if there is only one option in the select

	var plugin_download_hash = productHashJson;
	var license_key          = d.getAttribute("data-license");
	var data_key             = d.getAttribute("data-key");

	if ( typeof license_key === 'undefined' || license_key === undefined ) {
		var plugin_download_hash = d.getAttribute("data-id");
		var license_key          = d.getAttribute("data-license");
		var data_key             = d.getAttribute("data-key");
	}

	jQuery.ajax( {
		data: {
			action: "fv_plugin_download_ajax_bundle",
			plugin_download_hash: plugin_download_hash,
			license_key:          license_key,
			mfile:                data_key,
		},
		type: "POST",
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			//fv_vault_updateProgressBar( 50 );

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			if ( typeof json.result !== 'undefined' ) {
				if ( json.msg ) {
						jQuery.alert( {
							title: "Alert!",
							content: json.msg,
						} );

				} else {
					jQuery.alert( {
						title: "Alert!",
						content: "Something went wrong, Please try again later!",
					} );
				}
				jQuery("#empModal").modal("hide");
				fv_hide_loading_animation();
			}

			if ( typeof json.config.result != 'undefined' && json.config.result == "success") {

				// sets the credits in the license panel in the header of the vault page.

				jQuery("#" + license_key + " #plan_limit_id").html( json.config.plan_limit );
				jQuery("#" + license_key + " #current_limit_id").html( json.config.download_current_limit );
				jQuery("#" + license_key + " #limit_available_id").html( json.config.download_available );

				location.href = json.links;

				fv_vault_updateProgressBarAutoDL( 100 );

				$.removeCookie( 'cartData' );
				fv_vault_refreshCartDisplay();

			//
			// jQuery("#empModal").modal("hide");
			} else {
				jQuery("#empModal").modal("hide");
				if ( json.config.result == "failed" && json.config.msg == "Daily limit crossed") {
					if ( json.config.plan_type == "onetime") {
						jQuery.alert( {
							title: "Sorry! Limit issue!",
							content:
								"Your Download Limit is over, For onetime license please refill to enjoy downloading again! Happy downloading.",
						} );
					} else {
						jQuery.alert( {
							title: "Sorry! Limit issue!",
							content:
								"Your daily Download Limit is crossed, you can download tomorrow again! Happy downloading.",
						} );
					}
				} else {
					if ( json.config.msg ) {
						jQuery.alert( {
							title: "Alert!",
							content: json.config.msg,
						} );

					} else {
						jQuery.alert( {
							title: "Alert!",
							content: "Something went wrong, Please try again later!",
						} );
					}
				}
			}
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

// progressbar start

function fv_vault_updateProgressBarAuto( progress ) {
	// console.log( 'running fv_vault_updateProgressBarAuto()' );

	var progressElement = $( '.progress' );
	var currentProgress = parseInt( progressElement.attr( 'aria-valuenow' ) );
	var targetProgress  = parseInt( progress );

	var increment = ( targetProgress - currentProgress ) / 100;

	function incrementProgress() {
		// console.log( 'running incrementProgress()' );
		currentProgress += increment;
		progressElement.attr( 'aria-valuenow', currentProgress );
		progressElement.find( '.progress-bar' ).css( 'width', currentProgress + '%' );

		if ( currentProgress >= targetProgress ) {
			return;
		}

		setTimeout( incrementProgress, 100 );
	}

	incrementProgress();
}

function fv_vault_updateProgressBarAutoDLBefore( progress ) {
	// console.log( 'running fv_vault_updateProgressBarAutoDLBefore()' );
	var progressElement = $( '.progress' );
	var currentProgress = parseInt( progressElement.attr( 'aria-valuenow' ) );
	var targetProgress  = parseInt( progress );
	var increment       = ( targetProgress - currentProgress ) / 100;

	function incrementProgress() {
		// console.log( 'running incrementProgress()' );
		currentProgress += increment;
		progressElement.attr( 'aria-valuenow', currentProgress );
		progressElement.find( '.progress-bar' ).css( 'width', currentProgress + '%' );

		if ( currentProgress >= targetProgress ) {
			return;
		}

		setTimeout( incrementProgress, 60 );
	}

	incrementProgress();
}

function fv_vault_updateProgressBarAutoDL( progress ) {
	// console.log( 'running fv_vault_updateProgressBarAutoDL()' );

	var progressElement = $( '.progress' );
	var currentProgress = parseInt( progressElement.attr( 'aria-valuenow' ) );
	var targetProgress  = parseInt( progress );
	var increment       = ( targetProgress - currentProgress ) / 100;

	function incrementProgress() {
		// console.log( 'running incrementProgress()' );

		currentProgress += increment;
		progressElement.attr( 'aria-valuenow', currentProgress );
		progressElement.find( '.progress-bar' ).css( 'width', currentProgress + '%' );

		if ( currentProgress >= 100  ) {
			setTimeout( function() {
					jQuery( "#empModal" ).modal( "hide" );
				},
				1000
			); // Delay of 1000 milliseconds ( 1 second )
		}

		if ( currentProgress >= targetProgress ) {
			return;
		}

		setTimeout( incrementProgress, 5 );
	}

	incrementProgress();
}

function fv_vault_updateProgressBar( fileSize ) {
	// console.log( 'running fv_vault_updateProgressBar()' );

	var progressBar     = $( '.progress' );
	var currentProgress = parseInt( progressBar.attr( 'aria-valuenow' ) );
	var targetProgress  = 100 - currentProgress; // The target progress is 100% ( full progress bar )

	// Calculate the estimated increment per chunk
	var chunkSize   = 1024 * 80; // 10 KB ( adjust as needed )
	var totalChunks = Math.ceil( fileSize / chunkSize );
	var increment   = targetProgress / totalChunks;

	// Simulate download progress
	var currentChunk = 0;
	var interval     = setInterval(
		function() {
			currentChunk++;
			currentProgress += increment;
			// progressBar.css( 'width', currentProgress + '%' ).attr( 'aria-valuenow', currentProgress.toFixed( 2 ) );
			progressBar.attr( 'aria-valuenow', currentProgress );
			progressBar.find( '.progress-bar' ).css( 'width', currentProgress + '%' );

			// Check if the target progress is reached
			if ( currentChunk >= totalChunks ) {
				clearInterval( interval );
				setTimeout(
					function() {
						jQuery("#empModal").modal("hide");
					},
					// Delay of 1000 milliseconds ( 1 second )
					1000
				);
			}

		},
		// Adjust the interval in milliseconds ( e.g., 100 for smoother animation )
		100
	);
}

//progressbar end

/**
 * Renders and shows a bulk download/install confirmation popup,
 * based on fv_api data.
 *
 * @param {*} json json parsed body of a fp_api call.
 * @param {*} type 'download' or 'install'
 */
function fv_vault_show_bulk_action_confirmation_popup( json, type ) {
	// console.log( 'running fv_vault_show_bulk_action_confirmation_popup()' );

	switch ( type ) {
		case 'download':
			var typeBtnText   = 'Download Bundle';
			var typeBtnIcon   = 'fa fa-download';
			var typeBtnMethod = 'fv_vault_on_submit_bulk_download_confirmation';
			break;

		case 'install':
			var typeBtnText   = 'Install Bundle';
			var typeBtnIcon   = 'fas fa-cloud-download-alt';
			var typeBtnMethod = 'fv_vault_on_submit_bulk_install_confirmation';
			break;
		}

	var button_data =
		'<div class="row">';

	jQuery.each( json, function( index, json_item ) {

		var item        = JSON.parse( json_item );
		productHashJson = item.product_hash;

		button_data +=
			'<div class="col">' +
				'<div class="card bg-light" style="min-width:100%;"> ' +
					'<div class="card-header">' +
						item.plan_name +
					'</div>' +
					'<ul class="list-group list-group-flush">' +
						'<li class="list-group-item">' +
							'Plan Type<b>: ' + item.plan_type.toUpperCase() +
						'</b></li>' +
						'<li class="list-group-item">' +
							'Plan Limit: ' + item.plan_limit +
						'</li>' +
						'<li class="list-group-item">' +
							'Available Limit: ' + item.download_available +
						'</li>' +
					'</ul>' +
				'</div>' +
				'<div class="row">' +
					'<div class="text-white mt-2 mb-2">' +
						'Plugins Bundle' +
					'</div>' +
					'<ul class="text-white">';

					jQuery.each( item.product_hash, function( index2, item2 ) {
						button_data += '<li>' + item2.product_name + '</li>';
					} );

		button_data +=
					'</ul>' +
				'</div>' +
				'<button id="option1" ' +
					'data-license="' + item.license_key + '" ' +
					'data-id="' + productHashJson + '" ' +
					'onclick="' + typeBtnMethod + '( this ); this.disabled=true;" ' +
					'class="btn btn-sm btn-block card-btn">' +
					'<i class="' + typeBtnIcon + '"></i>' +
					typeBtnText +
				'</button> ' +
			'</div>';
	} );
	button_data +=
		'</div>';

	jQuery('.modal-body').html( button_data );
	jQuery('#empModal').modal('show');
	setTimeout( function() {
		fv_hide_loading_animation();
	}, 500 );
}

jQuery.date = function( orginaldate ) {
	var date = new Date( orginaldate );
	var dates = new Date( orginaldate );
	var day = date.getDate();
	var month = date.getMonth() + 1;
	var year = date.getFullYear();
	if ( day < 10 ) {
		day = "0" + day;
	}
	if ( month < 10 ) {
		month = "0" + month;
	}
	var months = [
		"January",
		"February",
		"March",
		"April",
		"May",
		"June",
		"July",
		"August",
		"September",
		"October",
		"November",
		"December",
	];
	final_month = months[dates.getMonth()];
	var date = final_month + " " + day + ", " + year;

	return date;
};

/**
 * Loads a grid of plugins and themes for the vault page.
 *
 * @param {object} ajax_search
 * @param {number} page
 */
function fv_vault_load_products( ajax_search = {}, page = 1 ) {
	// console.log( 'running fv_vault_load_products()' );
	// console.log( 'ajax_search', ajax_search );

	jQuery.ajax( {
		data: {
			action: 'fv_search_ajax_data',
			ajax_search: ajax_search,
			page:        page
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			fv_show_loading_animation();

			var data_s = data.slice( 0, -1 );  // removes the last char of string.
			var json   = JSON.parse( data_s ); // converts json in a js object.
			// console.log( json );

			/**
			 * Renders the grid with product cards.
			 * json.data contains the plugin/theme data
			 */
			fv_vault_render_product_grid( json.data );

			/**
			 * ajax_search contains the search and filter criteria.
			 * json.links contains pagination data.
			 * page contains pagenumber
			 */
			fv_render_pagination( ajax_search, json.links, page );
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 *
 * @param {array} products Array of found Products (plugins/themes)
 * @param {object} pagination Pagination data
 * @returns
 */
function fv_vault_render_product_grid( products, pagination ) {  // render current page.
	// console.log( 'running fv_vault_render_product_grid()' );

	// note: plugin_ajax_object is the wp_localized data passed from php

	jQuery( '#pagination' )
		.addClass( 'disabled disablendhide' )
		.attr( 'disabled', true );

	var wrapper          = jQuery('#list .wrapper').empty();
	var fv_item_count    = 1;

	// Nothing found.
	if ( products.length == 0 ) {
		wrapper.html(
			'<div class="mt-4 mb-4" style="color:#fff; font-size:20px; text-align:center;">' +
				'Sorry, No plugins or themes are found!' +
			'</div>'
		);

		// Hide pagagination.
		jQuery('#pagination').hide();
		return;
	}

	wrapper.append( '<div class="row">' );

	// There are products to show

	jQuery('html, body').animate(
		{
			scrollTop: 0,
		},
		100
	);

	// show pagination.
	jQuery('#pagination').show();

	// Determine number of grid columns.
	var fv_items_per_row = fv_get_items_per_row( '.wrapper' );

	// iterate though the found products.
	var product_grid_html = '<div class="row mb-3">';

	jQuery.each( products, function( i, f ) {

		// If row is full, start new row.
		if ( fv_item_count > 1
		&& 0 === ( fv_item_count - 1 ) % fv_items_per_row ) {
			product_grid_html +=
				'</div>' +               // close row
					'<div class="row">'; // start new row.
		}

		// add product card to row.
		product_grid_html += fv_get_vault_product_card_html( f );

		fv_item_count++;
	} );

	product_grid_html += '</div>'; // close row
	product_grid_html += '</div>'; // close grid

	// insert grid in page.
	wrapper.append( product_grid_html );
}

/**
 *	Render the pagination html on the vault page.
 *
 * @param {object} ajax_search Object with selected filters and search string.
 * @param {array} links Pagination data.
 * @param {int} page Current pagenumber.
 */
function fv_render_pagination( ajax_search, links, page ) {
	// console.log( 'running fv_render_pagination()' );

	var pagination = $( '#pagination' );

	pagination.empty(); // Clear previous pagination links

	$.each( links, function( index, link ) {

		// create basic elements.
		var linkElement = $( '<a class="page-link"></a>' );
		var listItem    = $( '<li class="page-item"></li>' );

		if ( link.url ) {

			// add href.
			linkElement.attr( 'href', link.url );

			// add click event handler.
			linkElement.on( 'click', function( event ) {
				event.preventDefault();
				var page = fv_get_page_from_url( link.url );
				fv_vault_load_products( ajax_search, page );
			} );

			// Add 'active' class to the selected page link
			if ( page === fv_get_page_from_url( link.url ) ) {
				linkElement.addClass( 'active' );
			}

		} else {
			// no link provided, so disable.
			linkElement.addClass( 'disabled' );
		}

		// $( '<div>' ) creates a div-element.
		// .html( link.label ) fills it with the html link.label.
		// .text() returns the text-content.
		var decodedLabel = $( '<div>' ).html( link.label ).text();

		linkElement.html( decodedLabel );
		listItem.append( linkElement );
		pagination.append( listItem );
	} );
}

function fv_get_page_from_url( url ) {
	// console.log( 'running fv_get_page_from_url()' );
	var regex = /[?&]page=( \d+ )/;
	var match = regex.exec( url );
	return match ? parseInt( match[1] ) : null;
}

function fv_vault_showToast() {
	// console.log( 'running fv_vault_showToast()' );
	var toast = document.querySelector( '.toast' );
	var toastEl = new bootstrap.Toast( toast );
	toastEl.show();
}

function fv_vault_add_item_to_cart( d ) {
	// console.log( 'running fv_vault_add_item_to_cart()' );

	var productId   = d.getAttribute('data-id');
	var productName = d.getAttribute('data-itemname');

	// Get the cart items from the cookie or create a new empty object
	var cartData  = fv_vault_getCartData();
	var cartCount = Object.keys( cartData ).length;

	jQuery( '.dropdown-menu.cart-dropdown' )
		.css( 'display', 'block' )
		.css( 'position', 'absolute' );

	// MAX 10 items allowed for bulk actions.

	if ( cartCount < 10 ) {
		// Check if the product is already in the cart
		if ( typeof cartData[ productId ] === 'undefined' ) {

			cartData[ productId ] = {
				'name': productName,
				'qty': 1
			};

			// Add the new item to the cart dropdown
			var cartItems = document.getElementById('cart-items');
			var newItem = document.createElement('div');

			newItem.innerHTML =
				productName +
				'<button type="button" class="btn btn-danger btn-sm float-right" ' +
					'onclick="fv_vault_remove_from_cart( this )" ' +
					'data-id="' + productId + '">' +
					'Remove' +
				'</button>';

			cartItems.appendChild( newItem );
		} else {
		}

		fv_vault_showToast();

	} else {
		alert( 'Sorry, Maximum limit is 10 items.' )
	}

	// Save the updated cart data to the cookie
	fv_vault_setCartData( cartData );

	fv_vault_refreshCartDisplay();
};

/**
 * Remove a bulk action item from cart.
 */
function fv_vault_remove_from_cart( d ) {
	// console.log( 'running fv_vault_remove_from_cart()' );

	var productId = d.getAttribute('data-id');

	// Get the cart items from the cookie or create a new empty object
	var cartData  = fv_vault_getCartData();

	// Remove the selected item from the cart
	delete cartData[ productId ];

	// Remove the item from the cart dropdown
	var cartItems    = document.getElementById('cart-items');
	var itemToRemove = d.parentNode;

	cartItems.removeChild( itemToRemove );

	// Save the updated cart data to the cookie
	fv_vault_setCartData( cartData );
};

/**
 * Get bulk items in cart from cookie
 */
function fv_vault_getCartData() {
	// console.log( 'running fv_vault_getCartData()' );

	// read cookie
	var cartData = $.cookie( 'cartData' );

	if ( typeof cartData !== 'undefined' ) {

		// process cookie content
		cartData    = JSON.parse( cartData );

		// remove Cookie when expired
		var expires = new Date( cartData.expires );
		var now     = new Date();
		if ( now > expires ) {
			$.removeCookie( 'cartData' );
			cartData = {};
		}

	} else {

		// cookie not found
		cartData = {};
	}

	return cartData;
}

/**
 * Save bulk items in cart in cookie
 */
function fv_vault_setCartData( cartData ) {
	// console.log( 'running fv_vault_setCartData()' );

	var expires = new Date();

	// Set expiration time to 10 minutes
	expires.setTime( expires.getTime() + ( 10 * 60 * 1000 ) );

	// Save cookie
	$.cookie(
		'cartData',
		JSON.stringify( cartData ),
		{
			expires: expires
		}
	);
}

/**
 * Refresh cart with items-data from cookie.
 */
function fv_vault_refreshCartDisplay() {
	// console.log( 'running fv_vault_refreshCartDisplay()' );

	var cartData      = fv_vault_getCartData();
	var cartCount     = Object.keys( cartData ).length;
	var cartItemsList = jQuery( '.cart-dropdown .cart-items' );

	// Clear cart items list
	cartItemsList.empty();

	// Add each item to the cart items list
	for ( var itemId in cartData ) {

		var itemName = cartData[itemId].name;
		var cartItem =
			'<li ' +
				'data-id="'   + itemId   + '" ' +
				'data-name="' + itemName + '">' +
				itemName +
				'<button class="btn btn-sm btn-danger remove-item float-end">' +
					'Remove' +
				'</button>' +
			'</li>';

		cartItemsList.append( cartItem );
	}

	// Update cart count
	jQuery( '#cart-dropdown .cart-count' ).text( '( ' + cartCount + ' )' );

	// Get a reference to the dropdown menu element
	const dropdownMenu = document.querySelector( '.dropdown-menu' );

	// Get a reference to the button inside the dropdown menu
	const button       = dropdownMenu.querySelector( 'button' );

	// Add a click event listener to the button
	button.addEventListener( 'click', ( event ) => {
		// Prevent the default behavior of the button click
		event.preventDefault();
		// Stop the event propagation to prevent the dropdown menu from closing
		event.stopPropagation();
	});

	/**
	 * Handle click on the Vault Page -> Bulk -> Cart-item -> Remove button.
	 */
	jQuery( '.cart-dropdown .remove-item' )
		.off( 'click' )
		.on( 'click', fv_vault_page_handle_bulk_action_item_remove_button );

	/**
	 * Handle click on the Vault Page -> Bulk -> Download All button.
	 */
	jQuery( '.cart-dropdown #download-button' )
		.off( 'click' )
		.on( 'click', fv_vault_page_handle_bulk_download_all_button );

	/**
	 * Handle click on the Vault Page -> Bulk -> Install All button.
	 */
	jQuery( '.cart-dropdown #install-button' )
		.off( 'click' )
		.on( 'click', fv_vault_page_handle_bulk_install_all_button );

	/**
	 * Handle click on the Vault Page -> Bulk -> Clear All button.
	 */
	jQuery( '.cart-dropdown #clearall-button' )
		.off( 'click' )
		.on( 'click',
			function() {
				$.removeCookie( 'cartData' );
				fv_vault_refreshCartDisplay();
			}
		);

	// Show-hide buttons in bulk-action Cart
	if (  cartCount > 0  ) {
		// one or more items for bulk action
		$( '#download-button'     ).show();
		$( '#install-button'      ).show();
		$( '#clearall-button'     ).show();
		$( '.cart-items-notfound' ).hide();

	} else {
		// no items for bulk action
		$( '#download-button'     ).hide();
		$( '#install-button'      ).hide();
		$( '#clearall-button'     ).hide();
		$( '.cart-items-notfound' ).show();
	}
}

function fv_vault_toggle_bulk_action_cart() {
	// console.log( 'running fv_vault_toggle_bulk_action_cart()' );

	// Get the cart-get-dropdown and get-cart-dropdownsub elements
	var cartGetDropdown    = $( '.cart-get-dropdown'    ); // Bulk-button
	var getCartDropdownsub = $( '.get-cart-dropdownsub' ); // Dropdown of the Bulk-button
	var addToBulkBtn       = $( '.add_to_bulk_btn'      ); // A 'Add to bulk'-button

	// Show/hide the dropdown when the cart-get-dropdown is clicked
	cartGetDropdown.on( 'click', function() {
		getCartDropdownsub.toggle();
	} );

	// Hide the dropdown when the user clicks anywhere on the webpage
	// except the Bulk button
	// Or the Add to bulk button
	jQuery( document )
		.on( 'click',
		function( event ) {
			if ( ! cartGetDropdown.is( event.target )
			&&     cartGetDropdown.has( event.target ).length === 0
			&&   ! $( event.target ).hasClass( 'add_to_bulk_btn' ) ) {
				getCartDropdownsub.hide();
			}
		}
	);

	fv_vault_refreshCartDisplay();
}

function fv_vault_on_click_product_additional_content_button( d ) {
	// console.log( 'running fv_vault_on_click_product_additional_content_button()' );

	fv_show_loading_animation();

	jQuery(".progress").hide();

	var product_hash = ( d.getAttribute("data-id") );

	jQuery.ajax( {
		data: {
			action: 'fv_get_remote_product_additional_content_data',
			product_hash: product_hash
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			let data_s = data.slice( 0, -1 );
			let json   = JSON.parse( data_s );

			// console.log( json );
			jQuery( '#empModal' ).modal( 'hide' );

			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );

			if ( json.length === 0 ) {
				jQuery.alert( {
					content: 'No additional content found.',
				} );
				return;
			} else {

				let html = fv_vault_get_product_additional_content_list_html( json );

				jQuery( '.modal-body' ).html( html );
				jQuery( '#empModal'   ).modal( 'show' );

				$( '.data_table998fv' ).DataTable( {
					'pageLength': 10
				} );
			}

		}
	} ).done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

function fv_vault_get_product_additional_content_list_html( content_items ) {
	// console.log('running fv_vault_get_product_additional_content_list_html()');

	let first_item_id = null;
	let html          =
		'<table class="table table-bordered data_table998fv" style="color:#fff; border:1px solid #fff;">' +
			'<thead>' +
				'<tr>' +
					'<th class="text-white">File name</th>' +
					'<th class="text-white">Type</th>' +
					'<th class="text-white">Date</th>' +
					'<th class="text-white">Action</th>' +
				'</tr>' +
			'</thead>' +
			'<tbody>';

	jQuery.each( content_items, function( index, item ) {

		if ( ! item ) {
			return;
		}

		// timestamp formated like 2023-08-03 14:00:49
		const fv_created_at_date = item.created_at.split(' ')[0];
		const fv_content_type    = fv_kebab_case_to_pascal_case( item.content_type );

		if ( first_item_id == null ) {
			first_item_id = item.id;
		}

		html += '<tr class="text-white">' +
					// File name
					'<td class="text-white">' +
						item.content_name +
					'</td>' +
					// Type
					'<td class="text-white">' +
						fv_content_type +
					'</td>' +
					// Date
					'<td class="text-white">' +
						fv_created_at_date +
					'</td>' +
					// Action
					'<td class="text-white">' +
						'<button class="btn btn-success btn-xs text-white download_dc_fv" style="padding: 1px 7px 1px 0px; font-size: 12px;"' +
							'id="dc_option" ' +
							'data-dltype="single" ' +
							'data-id="' + item.id + '" ' +
							'onclick="fv_vault_on_click_download_additional_content( this ); this.disabled=true;"' +
						'>' +
							'Download' +
						'</button>' +
					'</td>' +
				'</tr>';
	} );

	html += '</body>' +
		'</table>' +
		'<table class="table" style="color:#fff;margin-top:20px;">' +
			'<tr>' +
				'<td colspan>' +
					'<button class="btn btn-success btn-xs text-white download_dc_fv" style="width:100%;"' +
						'onclick="fv_vault_on_click_download_all_additional_content( this );" ' +
						'data-dltype="all_dl"' +
						'data-id="' + first_item_id + '">' +
						'Download all' +
					'</button>' +
				'</td>' +
			'</tr>' +
		'</table>';

	return html;
}

/**
 * change slug to title case sentence.
 *
 * Input string is expected to be formated in kebab case,
 * which means all lowercase en with hyphens between words.
 *
 * Title case means each word is capitalizes, and there is a space between each word.
 *
 * @param {*} str string lowercase with hyphen ('-') between each word.
 */
function fv_kebab_case_to_title_case( str ) {

	// Remove the hyphen and split str into an array
	const words = str.split('-');

	/**
	 * Capitalize each word.
	 *
	 * note: charAt(0) selects onlyfirst char of word.
	 *       slice(1)  selects word except the first char.
	 */
	const capitalized_words = words.map( ( word ) => {
		return word.charAt( 0 ).toUpperCase() + word.slice( 1 );
	} );

	// Rebuild the string with spaces instead of hyphens.
	return capitalized_words.join(' ');
}

/**
 * Change slug to Pascal case.
 *
 * Input string is expected to be formated in kebab case,
 * which means all lowercase en with hyphens between words.
 *
 * Pascal case means each word is capitalizes, and no seperator between words.
 *
 * @param {*} str string lowercase with hyphen ('-') between each word.
 */
function fv_kebab_case_to_pascal_case( str ) {

	// Remove the hyphen and split str into an array
	const words = str.split('-');

	/**
	 * Capitalize each word.
	 *
	 * note: charAt(0) selects onlyfirst char of word.
	 *       slice(1)  selects word except the first char.
	 */
	const capitalized_words = words.map( ( word ) => {
		return word.charAt( 0 ).toUpperCase() + word.slice( 1 );
	} );

	// Rebuild the string with spaces instead of hyphens.
	return capitalized_words.join('');
}

function fv_vault_on_click_download_all_additional_content( button ) {
	// console.log( 'running fv_vault_on_click_download_all_additional_content()' );

	fv_show_loading_animation();

	// 'single' of 'all_dl'
	var data_dltype  = button.getAttribute('data-dltype');
	// additional content post_id
	var product_hash = button.getAttribute('data-id');

	jQuery('.cs-fp-follow-button').on( 'click',);

	jQuery.ajax( {
		data: {
			action: 'fv_get_remote_additional_content_download_data',
			product_hash: product_hash,
			data_dltype:  data_dltype
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			/**
			 * This api action results the result in array,
			 * with (expected) only one entry.
			 * Each entry is json decoded, so we should parse,
			 * since otherwise we cannot check for result.
			 */
			if ( Array.isArray( json ) && json.length >= 1 ) {
				json   = JSON.parse( json[0] );
			}
			// console.log( 'json = ', json );

			switch ( true ) {
				case ( data_s == null || data_s.length == 5 ):
					jQuery.alert( {
						content: 'No downloadable file is available for this item. Please try again later',
					} );
					break;

				case ( data_s.length == 0 ):
					jQuery.alert( {
						content: 'To enjoy this feature please activate your license.',
					} );
					break;

				case ( 'invalid' === json.result ):

					setTimeout( function() {
						fv_hide_loading_animation();
					}, 500 );

					if ( json.msg == 'Please login to download.' ) {
						window.location='/get-started/';
					} else {
						jQuery.alert( {
							content: json.msg,
						} );
					}
					break;

				default:
					fv_vault_show_download_all_additional_content_confirmation( json );
					break;
			}
		}
	} ).done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

function fv_vault_show_download_all_additional_content_confirmation( item ) {
	// console.log( 'running fv_vault_show_download_all_additional_content_confirmation()' );

	let html = '<div class="row">';

	if ( ! item ) {
		return;
	}

	html +=
		'<div class="col-md-6">' +
			'<div class="card mb-2 bg-light" style="min-width:100%;">' +
				'<div class="card-header">' +
					item.plan_name +
				'</div>' +
				'<ul class="list-group list-group-flush">' +
					'<li class="list-group-item">Plan Type<b>: '    +
						item.plan_type.toUpperCase() +
					'</b></li>' +
					'<li class="list-group-item">Plan Limit: '      +
						item.plan_limit +
					'</li>' +
					'<li class="list-group-item">Available Limit: ' +
						item.download_available +
					'</li>' +
				'</ul>' +
				'<div class="card-footer">' +
					'<button style="width:100%;" class="btn btn-sm btn-block card-btn" ' +
						'id="dc_option" ' +
						'onclick="fv_vault_on_click_download_additional_content( this ); this.disabled=true;"' +
						'data-license="' + item.license_key  + '" ' +
						'data-id="' +      item.product_hash + '" ' +
						'data-dltype="' +  item.dl_type      + '">' +
						'<i class="fa fa-arrow-down"></i>' +
						'Download from ' + item.plan_type.toUpperCase() + ' plan' +
					'</button>' +
				'</div>' +
			'</div>' +
		'</div>';

	html += '</div>';

	jQuery( '.modal-body' ).html( html );
	jQuery( '#empModal' ).modal( 'show' );
	setTimeout( function() {
		fv_hide_loading_animation();
	}, 500 );

}

function fv_get_filename_from_url( url ) {
	// console.log( 'running fv_get_filename_from_url()' );

	// Get the part of the URL after the last '/'
	var filenameWithParams    = url.substring( url.lastIndexOf( '/' ) + 1 );
	// Remove query parameters from the filename ( everything after the '?' )
	var filenameWithoutParams = filenameWithParams.split( '?' )[0];
	// Extract the file name and extension
	var parts                 = filenameWithoutParams.split( '.' );
	var extension             = parts.pop();
	var filename              = parts.join( '.' );

	filenameFinal = filename + extension;

	// console.log( filenameFinal );

	return filenameFinal;

	//return { filename: filename, extension: extension };
}

function fv_download_file_from_url( url ) {
	// console.log( 'running fv_download_file_from_url()' );

	var xhr = new XMLHttpRequest();

	xhr.open( 'GET', url, true );
	xhr.responseType = 'blob';
	xhr.onload       = function() {
		if ( xhr.status !== 200 ) {
			return;
		}
		// creates an a-tag and self-clicks that to download.
		var blob        = xhr.response;
		var filename    = fv_get_filename_from_url( url ); // Use the fv_get_filename_from_url function to extract the filename
		var a           = document.createElement( 'a' );
		a.href          = window.URL.createObjectURL( blob );
		a.download      = filename;
		a.style.display = 'none';
		document.body.appendChild( a );
		a.click();
		window.URL.revokeObjectURL( a.href );
	};

	xhr.send();
}

/**
 * Handles submissions of the product download form dc (download confirmation modal).
 *
 * @param {object} submit_el Submit button or Select element.
 * @returns
 */
function fv_vault_on_click_download_additional_content( submit_el ) {
	// console.log( 'running fv_vault_on_click_download_additional_content()' );

	fv_show_loading_animation()　

	// Submit element can be a submit button or a select element.
	switch ( submit_el.type ) {

		// User clicked the submit button
		case 'submit':
			var fv_product_hash  = submit_el.getAttribute('data-id');
			var fv_license_key   = submit_el.getAttribute('data-license');
			var fv_download_type = submit_el.getAttribute("data-dltype");
			break;

		// User changed the select element
		case 'select-one':
			let selected_option  = $( submit_el ).find( 'option:selected' );
			var fv_product_hash  = selected_option.data('id');
			var fv_license_key   = selected_option.data('license');
			var fv_download_type = selected_option.data('dltype');
			break;

		default:
			fv_hide_loading_animation();
			return;
	}

	jQuery.ajax( {
		data: {
			action: 'fv_fs_plugin_download_ajax_dc',
			fv_product_hash:  fv_product_hash,
			fv_license_key:   fv_license_key,
			fv_download_type: fv_download_type
		},
		type: 'POST',
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			// console.log( json );

			jQuery('#empModal').modal('hide');

			if ( json.result == 'success' ) {

				jQuery( '#' + fv_license_key + ' #plan_limit_id'      ).html( json.plan_limit  );
				jQuery( '#' + fv_license_key + ' #current_limit_id'   ).html( json.download_current_limit  );
				jQuery( '#' + fv_license_key + ' #limit_available_id' ).html( json.download_available );

				if ( fv_download_type == 'single' ) {
					fv_download_file_from_url( json.link );
				} else {
					location.href = json.link;
				}
			} else {
				if ( json.msg ) {
					jQuery.alert( {
						title: 'Alert!',
						content: json.msg,
					} );
				} else {
					jQuery.alert( {
						title: 'Alert!',
						content: 'Something went wrong, Please try again later!',
					} );
				}
			}
		}
	} ).done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

function fv_vault_reset_filters_value() {
	// console.log( 'running fv_vault_reset_filters_value()' );
	jQuery("#ajax_search").val("");
	jQuery("#filter_allowence").val("all");
	jQuery("#filter_type").val("all");
	jQuery("#filter_category").val("all");
	ajax_filter_data = {};
}

function fv_show_loading_animation() {
	// console.log( 'running fv_show_loading_animation()' );
	jQuery("#overlay").fadeIn( 300 );
}

function fv_hide_loading_animation() {
	// console.log( 'running fv_hide_loading_animation()' );
	jQuery("#overlay").fadeOut( 300 );
}

/**
 * Get number of columns depending on wrapper width.
 *
 * @param {string} wrapper css selector for the wrapper
 * @returns
 */
function fv_get_items_per_row( wrapper ) {
	// console.log( 'running fv_get_items_per_row()' );

	var get_list_wrapper_display_size = jQuery( wrapper ).width();

	switch ( true ) {
		case ( get_list_wrapper_display_size  > 900
			&& get_list_wrapper_display_size <= 2000 ):
			return 4;
			break;

		case ( get_list_wrapper_display_size  > 2000
			&& get_list_wrapper_display_size <= 4000 ):
			return 6;
			break;

		case ( get_list_wrapper_display_size > 4000 ):
			return 8;
			break;

		default:
			return 2
			break;
	}

}

function fv_is_active_theme( slug ) {
	// console.log( 'running fv_is_active_theme()' );
	return fv_is_product_slug_found( slug, plugin_ajax_object.get_all_active_themes_js );
}
function fv_is_active_plugin( slug ) {
	// console.log( 'running fv_is_active_plugin()' );
	return fv_is_product_slug_found( slug, plugin_ajax_object.get_all_active_plugins_js );
}
function fv_is_disabled_theme( slug ) {
	// console.log( 'running fv_is_disabled_theme()' );
	return fv_is_product_slug_found( slug, plugin_ajax_object.get_all_inactive_themes_js );
}
function fv_is_disabled_plugin( slug ) {
	// console.log( 'running fv_is_disabled_plugin()' );
	return fv_is_product_slug_found( slug, plugin_ajax_object.get_all_inactive_plugins_js );
}

function fv_get_allowence_img_html( value, dl_linkxyzzu ) {
	// console.log( 'running fv_get_allowence_img_html()' );

	let html = '';

	switch ( value ) {
		case 'bronze':
			html =
				'<a ' +
					'href="' + dl_linkxyzzu + '" ' +
					'target="_blank">' +
					'<img style="height: 20px; float: right;" ' +
						'src="https://festingervault.com/wp-content/uploads/2021/08/Orange-Quest-Medal.png" ' +
						'title="Bronze Download">' +
				'</a>';
			break;

		case 'silver':
			html =
				'<a href="' + dl_linkxyzzu + '" ' +
					'target="_blank">' +
					'<img style="height:20px; float:right;" ' +
						'src="https://festingervault.com/wp-content/uploads/2021/08/Silver-Quest-Medal.png" ' +
						'title="Silver Download">' +
				'</a>';
			break;

		case 'gold':
			html =
				'<a href="' + dl_linkxyzzu + '" ' +
					'target="_blank">' +
					'<img style="height:20px; float:right;" ' +
						'src="https://festingervault.com/wp-content/uploads/2021/08/Gold-Quest-Medal.png" ' +
						'title="Gold Download">' +
				'</a>';
			break;

		default:
			break;
	}

	return html;
}

function fv_get_install_button_html_for_vault_product_card( f, fv_button_disabled ) {
	// console.log( 'running fv_get_install_button_html_for_vault_product_card()' );

	// install button
	let html =
		'<button ' +
			'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn"' +
			'href="#" '   +
			'data-id="'   + f.unique_rand_md5 + '" ' +
			'onclick="fv_vault_on_click_install_product_button( this );" ' +
			'><i class="fas fa-cloud-download-alt"></i>' +
			'Install' +
		'</button>';

	// Request download button.
	if ( 'wordpress-requests' === f.type_slug ) {

		html =
			'<a ' +
				'class="btn btn-sm btn-block card-btn" style="font-size: 12.6px; padding: 13px;" target="_blank" ' +
				'href="' + f.href + '"' +
				'>' +
				'<i class="fas fa-external-link-alt"></i>' +
				'Request Download' +
			'</a>';

		return html;
	}

	switch ( true ) {
		case fv_is_active_theme( f.new_generated_slug ):
			html =
				'<button ' +
					'href="#" ' +
					'data-id="' + f.unique_rand_md5 + '" ' +
					'onclick="fv_vault_on_click_install_product_button( this );" ' +
					'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn"' +
					'>' +
					'<i class="fas fa-cloud-download-alt"></i>' +
					'Change Version' +
				'</button>';
			break;

		case fv_is_active_plugin( f.new_generated_slug ):
			html =
				'<button ' +
					'href="#" ' +
					'data-id="'   + f.unique_rand_md5  + '" ' +
					'onclick="fv_vault_on_click_install_product_button( this );" ' +
					'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn" ' +
					'> ' +
					'<i class="fas fa-cloud-download-alt"></i>' +
					'Change Version' +
				'</button>';
			break;

		case fv_is_disabled_theme( f.new_generated_slug ):
			if ( 'wordpress-themes' === f.type_slug ) {
				html =
					'<button ' +
						'href="#" ' +
						'data-id="' + f.unique_rand_md5 + '" ' +
						'onclick="fv_vault_on_click_install_product_button( this );" ' +
						'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn" ' +
						'>' +
						'<i class="fas fa-cloud-download-alt"></i>' +
						'Change Version' +
					'</button>';
				break;
			}
			html =
				'<button ' +
					'data-id="' + f.unique_rand_md5 + '" ' +
					'onclick="fv_vault_on_click_install_product_button( this );" ' +
					'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn"' +
					'><i class="fa fa-arrow-down"></i>' +
					'Activate' +
				'</button>';
			break;

		case fv_is_disabled_plugin( f.new_generated_slug ):
			html =
				'<button ' +
					'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn"' +
					'data-id="'   + f.unique_rand_md5  + '" ' +
					'onclick="fv_vault_on_click_install_product_button( this );" ' +
					'><i class="fa fa-arrow-down"></i>' +
					'Activate' +
				'</button>';
			break;

		case fv_is_active_theme( f.new_generated_slug ):
			html =
				'<button ' +
					'class="btn ' + disable_the_button + ' btn-sm btn-block card-btn"' +
					'data-id="'   + f.unique_rand_md5  + '" ' +
					'href="#" '   +
					'onclick="fv_vault_on_click_install_product_button(this);"  ' +
					'><i class="fas fa-cloud-download-alt"></i>' +
					'Change Version' +
					'</button>';

			break;

		default:
			break;
	}

	if ( 'elementor-template-kits' === f.type_slug ) {
		html =
			'<button ' +
				'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn" ' +
				'disabled><i class="fa fa-arrow-down"></i>' +
				'Install' +
			'</button>';
	}

	return html;
}

function fv_get_download_button_html_for_vault_product_card( f, fv_button_disabled ) {
	// console.log( 'running fv_get_download_button_html_for_vault_product_card()' );

	let html = '';

	if ( 'wordpress-requests' === f.type_slug ) {
		return '';
	}

	html =
		'<div class="col-6 mb-1">' +
			'<button id="option1" ' +
				'href="#" ' +
				'class="btn '     + fv_button_disabled + ' btn-sm btn-block card-btn"' +
				'data-id="'       + f.unique_rand_md5  + '" ' +
				'data-itemname="' + f.title + '" ' +
				'onclick="fv_vault_on_click_download_product_button( this );" ' +
				'><i class="fas fa-download"></i>' +
				'Download' +
			'</button>' +
		'</div>';

	return html;
}

function fv_get_sales_page_button_html_for_vault_product_card( f ) {
	// console.log( 'running fv_get_sales_page_button_html_for_vault_product_card()' );

	let html = '';

	if ( 'wordpress-requests' === f.type_slug ) {
		return '';
	}

	html =
		'<div class="col-6 mt-1 mb-1">' +
			'<a target="_blank" rel="noreferrer" style="font-size:12.6px;" ' +
				'class="btn btn-sm btn-block card-btn"' +
				'href="' + f.preview + '" ' +
				'>' +
				'<i class="fas fa-eye"></i>' +
				'Sales Page' +
			'</a>' +
		'</div>';

	return html;
}

function fv_get_request_update_button_html_for_vault_product_card( f, fv_button_disabled, fv_white_label_is_active ) {
	// console.log( 'running fv_get_request_update_button_html_for_vault_product_card()' );

	let html =
		'<div class="col-6 mt-1">' +
			'<button id="requestupdate" ' +
				'data-support-link="'   + f.support_link + '" ' +
				'data-product-hash="'   + f.unique_rand_md5 + '" ' +
				'data-generated-slug="' + f.new_generated_slug + '" ' +
				'data-generated-name="' + f.title + '" ' +
				'href="#" ' +
				'onclick="fv_vault_on_click_update_request_button( this );" ' +
				'class="btn btn-sm btn-block card-btn ' + fv_button_disabled + '" ' +
				'><i class="fas fa-sync"></i>' +
				'Request Update' +
			'</button>' +
		'</div>';

	if ( fv_white_label_is_active ) {

		html =
			'<div class="col-6 mt-1 mb-1">' +
				'<a style="font-size:12.6px;" href="#" class="btn btn-sm btn-block card-btn disabled">' +
					'<i class="fas fa-sync"></i>' +
					'Request Update' +
				'</a>' +
			'</div>';

	}

	return html;
}

function fv_get_support_button_html_for_vault_product_card( f, fv_white_label_is_active ) {
	// console.log( 'running fv_get_support_button_html_for_vault_product_card()' );

	let html =
		'<div class="col-6 mt-1 mb-1">' +
			'<a target="_blank" style="font-size:12.6px;" ' +
				'href="' + f.support_link + '" ' +
				'class="btn btn-sm btn-block card-btn"' +
				'> <i class="fas fa-life-ring"></i>' +
				'Support' +
			'</a>' +
		'</div>';

	if (  fv_white_label_is_active ) {

		html =
			'<div class="col-6 mt-1 mb-1">' +
				'<a style="font-size:12.6px;" href="#" class="btn btn-sm btn-block card-btn disabled">' +
					'<i class="fas fa-life-ring"></i>' +
					'Support' +
				'</a>' +
			'</div>';

	}

	return html;
}

function fv_get_report_item_button_html_for_vault_product_card( f, fv_button_disabled, fv_white_label_is_active ) {
	// console.log( 'running fv_get_report_item_button_html_for_vault_product_card()' );

	let html =
		'<div class="col-6 mt-1">' +
			'<button id="reportitem" ' +
				'data-support-link="'   + f.support_link + '" ' +
				'data-product-hash="'   + f.unique_rand_md5 + '" ' +
				'data-generated-slug="' + f.new_generated_slug + '" ' +
				'data-generated-name="' + f.title + '" ' +
				'href="#" ' +
				'onclick="fv_vault_on_click_report_item_button( this );" ' +
				'class="btn btn-sm btn-block card-btn ' + fv_button_disabled + '" ' +
				'><i class="fas fa-flag"></i>' +
				'Report Item' +
			'</button>' +
		'</div>';

	if (  fv_white_label_is_active ) {

		html =
			'<div class="col-6 mt-1 mb-1">' +
				'<a style="font-size:12.6px;" href="#" class="btn btn-sm btn-block card-btn disabled">' +
					'<i class="fas fa-flag"></i>'+
					'Report Item' +
				'</a>' +
			'</div>';

	}

	return html;
}

function fv_get_add_to_bulk_button_html_for_vault_product_card( f, fv_button_disabled ) {
	// console.log( 'running fv_get_add_to_bulk_button_html_for_vault_product_card()' );

	let html =
		'<div class="col-6 mt-1 mb-1">' +
			'<button type="button" style="font-size:12.6px;" ' +
				'data-id="' + f.unique_rand_md5 + '" ' +
				'data-itemname="' + f.title + '" ' +
				'onclick="fv_vault_add_item_to_cart( this )" ' +
				'class="btn btn-sm btn-block card-btn mt-2 add_to_bulk_btn ' + fv_button_disabled + '"' +
				'> <i class="fas fa-angle-down"></i>' +
				'Add to bulk' +
			'</button>' +
		'</div>';

	if ( 'elementor-template-kits' === f.type_slug ) {
		html =
			'<div class="col-6 mt-1 mb-1">' +
				'<button type="button" style="font-size:12.6px;" class="btn btn-sm btn-block card-btn mt-2 add_to_bulk_btn" disabled>' +
					'<i class="fas fa-angle-down"></i>' +
					'Add to bulk' +
				'</button>' +
			'</div>';
	}

	return html;
}

function fv_get_virus_scan_button_html_for_vault_product_card( f ) {
	// console.log( 'running fv_get_virus_scan_button_html_for_vault_product_card()' );

	let html =
		'<div class="col-6 mt-1 mb-1">' +
			'<a target="_blank" rel="noreferrer" style="font-size:12.6px;" ' +
				'href="' + f.virusscanurl + '" ' +
				'class="btn btn-sm btn-block card-btn mt-2"' +
				'><i class="fas fa-virus-slash"></i>' +
				'Virustotal Scan' +
			'</a>' +
		'</div>';

	if ( f.virusscanurl == null || f.virusscanurl == '' ) {
		html =
			'<div class="col-6 mt-1 mb-1">' +
				'<button type="button" style="font-size:12.6px;" class="btn btn-sm btn-block card-btn mt-2 add_to_bulk_btn" disabled>' +
					'<i class="fas fa-virus-slash"></i>' +
					'Virustotal Scan' +
				'</button>' +
			'</div>';
	}

	return html;
}

function fv_get_additional_content_button_html_for_vault_product_card( f, fv_button_disabled ) {
	// console.log( 'running fv_get_additional_content_button_html_for_vault_product_card()' );

	// install button
	let html =
		'<div class="col-12 mt-1">' +
			'<button id="optiondc" ' +
				'data-id="' + f.unique_rand_md5 + '" ' +
				'data-itemname="' + f.title + '" href="#" ' +
				'onclick="fv_vault_on_click_product_additional_content_button( this );" ' +
				'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn">' +
				'<i class="fas fa-download"></i>' +
				'Additional Content' +
			'</button>' +
		'</div>';

	return html;
}

function fv_has_membership_allowance( f ) {
	// console.log( 'running fv_has_membership_allowance()' );

	return ( null      !== f.membershipallowance
		&& 'undefined' !== typeof f.membershipallowance
		&& ''          !== f.membershipallowance );
}

function fv_get_vault_product_card_html( f ) {
	// console.log( 'running fv_get_vault_product_card_html()' );

	let fv_install_button_html;
	let fv_download_button_html;
	let fv_sales_page_button_html;
	let fv_support_button_html;
	let fv_request_update_button_html;
	let fv_report_item_button_html;
	let fv_add_to_bulk_button_html;
	let fv_virus_scan_button_html;
	let fv_additional_content_button_html;

	let fv_is_featured_product;
	let fv_has_featured_image;
	let fv_button_disabled;
	let fv_allowence_image;
	let html;
	let fv_featured_product_marker;
	let json22;
	let values;
	let dl_linkxyzzu;
	let fv_white_label_is_active;
	let fv_has_download_credits;

	fv_is_featured_product     = ( 1 == f.featured );
	fv_has_featured_image      = ( null != f.image );
	fv_button_disabled         = '';
	fv_allowence_image         = '';
	fv_featured_product_marker = '';
	html                       = '';


	fv_white_label_is_active   = ( 1 == plugin_ajax_object.fv_white_label_is_active ) ? true : false;
	fv_has_download_credits    = ( 1 == plugin_ajax_object.fv_has_download_credits  ) ? true : false;

	/**
	 * Disable some buttons based on credits or filters
	 */

	// No download credits
	if ( ! fv_has_download_credits ) {
		fv_button_disabled = 'disabled';
	}

	// current type or category selection = whishlist
	if ( 'wishlist' === f.category_slug
	||   'wishlist' === f.type_slug     ) {
		fv_button_disabled = 'disabled';
	}

	/**
	 * If whitelabel is enabled, then remove links to festingervault.com
	 */
	if ( fv_white_label_is_active ) {
		f.href = '#';
	}

	// Mark featured products.
	if ( fv_is_featured_product ) {
		fv_featured_product_marker =
			'<div style="position: absolute;top:0; margin-top: -28px; background: #4d378e; padding: 4px 12px; border-top-left-radius: 12px; border-top-right-radius: 12px; font-size: 12px; letter-spacing: .5px; color: #fff; font-weight:400">' +
				'Featured'+
			'</div>  ';
	}

	if ( ! fv_has_featured_image ) {
		f.image = plugin_ajax_object.fv_default_product_img_url;
	}

	fv_install_button_html            = fv_get_install_button_html_for_vault_product_card( f, fv_button_disabled );
	fv_download_button_html           = fv_get_download_button_html_for_vault_product_card( f, fv_button_disabled );
	fv_sales_page_button_html         = fv_get_sales_page_button_html_for_vault_product_card( f );
	fv_request_update_button_html     = fv_get_request_update_button_html_for_vault_product_card( f, fv_button_disabled, fv_white_label_is_active );
	fv_support_button_html            = fv_get_support_button_html_for_vault_product_card( f, fv_white_label_is_active );
	fv_report_item_button_html        = fv_get_report_item_button_html_for_vault_product_card( f, fv_button_disabled, fv_white_label_is_active );
	fv_add_to_bulk_button_html        = fv_get_add_to_bulk_button_html_for_vault_product_card( f, fv_button_disabled );
	fv_virus_scan_button_html         = fv_get_virus_scan_button_html_for_vault_product_card( f );
	fv_additional_content_button_html = fv_get_additional_content_button_html_for_vault_product_card( f, fv_button_disabled );

	if ( fv_has_membership_allowance( f ) ) {

		json22       = JSON.parse( f.membershipallowance );
		values       = Object.values( json22 );
		dl_linkxyzzu = '#';

		if ( ! fv_white_label_is_active ) {
			dl_linkxyzzu = 'https://community.festingervault.com/t/gold-silver-and-bronze-downloads/35448';
		}

		$.each( values, function( index, value ) {
			fv_allowence_image = fv_get_allowence_img_html( value, dl_linkxyzzu );
		} );
	}

	// build the product card markup.

	html =
		// product card
		'<div class="col margin-bottom-xs my-4 rounded-lg">' +
			'<div style="max-width:350px;" class="card h-100 border-8 hover-elevate light-blue light-border"> ' +

				// featured image.
				'<a href="' + f.href + '" style="text-decoration:none;" target="_blank">' +
					'<div class="p-2">' +
						fv_featured_product_marker +
						'<img ' +
							'src="' + f.image + '" ' +
							'alt="' + f.title + '" ' +
							'class="card-img-top card-rounded-img" ' +
							'style="height: 155px;"' +
							'>' +
					'</div>' +
				'</a>' +

				// card body
				'<div class="card-body light-blue" style=" color:#f4f5f6; padding:0px;">' +
					'<div  style="border-bottom:solid 1px #4d378e;">' +
					'</div>' +

					// product details
					'<div class="light-border-bottom" style="padding:16px 10px;"> ' +

						// product name
						'<a href="' + f.href + '" style="text-decoration:none;" target="_blank">' +
							'<h5 class="card-title  cut-the-text" style="font-size: 1.125rem; color:#f4f5f6;font-weight:700;margin-bottom:3px;">' +
								( ( f.title.length > 60 )
									? ( f.title.substring( 0, 60 ) + "..." )
									: f.title ) +
							"</h5> " +
						'</a>' +

						// product category + allowence image (bronze, silver, gold)
						'<p class="card-text" style="color: #cfcfcf; font-size: .75rem; font-weight: 700; letter-spacing: .025rem; text-transform: uppercase;"> ' +
							f.category_slug
								.replace( "-", " " )
								.toUpperCase()
								.replace( "-", " " ) +
							fv_allowence_image +
						'</p>' +
					'</div>' +

					// product meta
					'<div class="card-title d-flex justify-content-between " style="font-size: 12px; padding-top: 10px; padding-left: 10px; padding-right: 10px;"> ' +
						// date last modified
						'<div class=""> ' +
							jQuery.date( f.modified ) +
						'</div>' +
						// product downloads
						'<div class=""><i class="fas fa-chart-line"></i> ' +
							f.hits +
						'</div>' +
					'</div>' +
				'</div>' +
				// action buttons...
				'<div class="" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; background:  #201943 !important; border-top: 1px solid #4d378e; padding: 12px 10px;">' +
					'<div class="row">' +
						'<div class="col-6 mb-1">' +
							fv_install_button_html +
						"</div>" +
						fv_download_button_html +
						" " +
						fv_sales_page_button_html +
						" " +
						fv_support_button_html +
						" " +
						fv_request_update_button_html +
						" " +
						fv_report_item_button_html +
						" " +
						fv_add_to_bulk_button_html +
						" " +
						fv_virus_scan_button_html +
						" " +
						fv_additional_content_button_html +
					'</div>' +
				'</div>' +
			'</div>' +
		'</div>';

	return html;
}

/**
 * Callback for the Vault Page -> Bulk -> Download All button.
 */
function fv_vault_page_handle_bulk_download_all_button() {
	// console.log( 'running fv_vault_page_handle_bulk_download_all_button()' );

	jQuery(".progress").hide();

	var progressBar   = $( '.progress' );
	var cartItemsList = jQuery( '.cart-dropdown .cart-items' );

	progressBar.attr( 'aria-valuenow', 0 );
	progressBar.find( '.progress-bar' ).css( 'width', 0 + '%' );

	/**
	 * Collect the bulk plugins/themes from the card.
	 *
	 * Note: carItemsList is filled in fv_vault_refreshCartDisplay()
	 */

	var cartItems = [];

	cartItemsList.children().each( function() {

			var itemId   = jQuery( this ).attr( 'data-id' );
			var itemName = jQuery( this ).attr( 'data-name' );

			cartItems.push( {
				'id':   itemId,
				'name': itemName
			} );
		}
	);

	// Send AJAX request to download items
	var cartItemListForS = JSON.stringify( cartItems );

	jQuery.ajax( {
		data: {
			action: "fv_get_bulk_items_data_from_api",
			product_hash: cartItems
		},
		type: "POST",
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );

			if ( json.length == 0 ) {
				jQuery.alert( {
					content: "To enjoy this feature please activate your license.",
				} );
				return;
			}

			if ( json.result == "failed" ) {
				jQuery.alert( {
					content: json.msg,
				} );
				return;
			}

			// If all went well, build and show the confirmation popup.
			fv_vault_show_bulk_action_confirmation_popup( json, 'download' );
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

/**
 * Callback for the Vault Page -> Bulk -> Install All button.
 */
function fv_vault_page_handle_bulk_install_all_button() {
	// console.log( 'running fv_vault_page_handle_bulk_install_all_button()' );

	jQuery(".progress").hide();

	var progressBar   = $( '.progress' );
	var cartItemsList = jQuery( '.cart-dropdown .cart-items' );

	progressBar.attr( 'aria-valuenow', 0 );
	progressBar.find( '.progress-bar' ).css( 'width', 0 + '%' );

	var cartItems = [];
	cartItemsList.children().each( function() {
		var itemId = jQuery( this ).attr( 'data-id' );
		var itemName = jQuery( this ).attr( 'data-name' );
		cartItems.push( {
			'id': itemId,
			'name': itemName
		} );
	} );

	// Send AJAX request to install items

	var cartItemListForS = JSON.stringify( cartItems );

	jQuery.ajax( {
		data: {
			action: "fv_get_bulk_items_data_from_api",
			product_hash: cartItems
		},
		type: "POST",
		url:  plugin_ajax_object.ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );

			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );

			if ( json.length == 0 ) {
				jQuery.alert( {
					content: "To enjoy this feature please activate your license.",
				} );
				return;
			}
			if ( json.result == "failed") {
				jQuery.alert( {
					content: json.msg,
				} );
				return;
			}

			fv_vault_show_bulk_action_confirmation_popup( json, 'install' );
		},
	} )
	.done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );

}

/**
 * Callback for the Vault Page -> Bulk -> Cart-item -> Remove button.
 */
function fv_vault_page_handle_bulk_action_item_remove_button() {
	// console.log( 'running fv_vault_page_handle_bulk_action_item_remove_button()' );
	var itemId   = jQuery( this ).parent().attr( 'data-id' );
	// Read cookie for cart data
	var cartData = fv_vault_getCartData();
	// remove item from bulk actions.
	delete cartData[itemId];
	// Save the updated cart data to the cookie
	fv_vault_setCartData( cartData );
	// refresh cart.
	fv_vault_refreshCartDisplay();
}

/**
 * Get product post-id from item support page url.
 *
 * item support page url looks like...
 * https://community.festingervault.com/t/item-slug/1234
 *
 * It ends with an integer product id (here: 1234).
 *
 * @param {string} url
 * @returns {int} post_id
 */
function fv_get_postid_from_item_support_page_url( url ) {
	let chunks     = url.split("/");
	let last_chunk = chunks.pop();
	return parseInt( last_chunk );
}

/**
 * Determine the text of the button on the install confirmation modal in the vault.
 *
 * @param {string} slug Product slug
 * @param {string} type Product type
 * @returns string Install button text
 */
function fv_vault_get_install_product_button_text( slug, type ) {

	switch ( true ) {

		// = active theme?
		case fv_is_active_theme( slug ):
			return 'Install latest version';

		// = active plugin?
		case fv_is_active_plugin( slug ):
			return  'Install latest version';

		// inactive theme?
		case fv_is_disabled_theme( slug ):
			return  'Already Installed Please Activate';

		// inactive plugin?
		case fv_is_disabled_plugin( slug ):
			return 'Already Installed Please Activate';

		default:

			switch ( true ) {

				case ( 'wordpress-themes' === type ):
					return 'Install LATEST VERSION';

				case ( 'wordpress-plugins' === type ):
					return 'Install & Activate LATEST VERSION';

				default:
					return 'Install latest version';

			}
	}

}

/**
 * Gets an associative array of query vars from a url.
 *
 * @param {string} url Default is curren page url.
 * @returns array
 */
function get_query_vars( url = null ) {

	if ( ! url ) {
		// Current page url.
		url = window.location.href;
	}

	// breaks the query-vars from url and puts them in an array.
	let hashes = url.slice( url.indexOf('?') + 1 ).split('&');

	let vars   = [];
	let hash   = [];

	for( var i = 0; i < hashes.length; i++) {
		// split each query-var in an key->value pair.
        hash = hashes[i].split('=');
		// Build associated array in vars.
		vars.push(hash[0]); // key
		vars[ hash[0] ] = hash[1]; // value
    }
    return vars;
}