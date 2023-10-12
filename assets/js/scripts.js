var productHashJson        = null;
var ajax_filter_data       = {};

// Variable to hold the timeout ID
var timeoutId;

// on document ready...
jQuery( fv_do_pages );

paginationDataController( linksData );

var linksData = {
	// Place the "links" array from the JSON data here
};

function fv_do_pages() {
	console.log( 'running fv_do_pages()' );

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
			console.log('page not recognized: ', plugin_ajax_object.fv_current_screen );
			// Nothing to do.
			break;
	}

	// #toggle-event doesn't seem part of the plugin.
	jQuery("#toggle-event").bootstrapToggle( {
		on:  "",
		off: "",
	} );

}

function fv_do_vault_page() {

	console.log( 'running fv_do_vault_page()' );

	jQuery( '#history_table' ).DataTable( {
		"pageLength": 50,
		"order": [
			[ 6, "DESC" ]
		]
	} )

	// This id doesn't seem to be used anymore.
	// Perhaps it was and now it is redundant?
	jQuery('#ajax-plugin-search-form').ready( function( e ) {
		// Show spinner.
		fv_show_loading_animation();
		// Load plugins and themes listing.
		fv_load_vault_page_plugins_and_themes();
	});

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

	fv_vault_toggle_bulk_action_cart();

	// was run at windows.load() but document ready is same.
	refreshCartDisplay();
}

function fv_do_activation_page() {
	console.log( 'running fv_do_activation_page()' );

	jQuery( '#ajax-license-activation-form'     ).on( 'submit', fv_activation_form_submit );
	jQuery( '#ajax-license-refill-form'         ).on( 'submit', fv_activation_license_refill_form_submit );
	jQuery( '#ajax-license-refill-form2'        ).on( 'submit', fv_activation_license_refill_form_2_submit );
	jQuery( '.ajax-license-deactivation-form'   ).on( 'submit', fv_activation_deactivation_form_submit );
	jQuery( '.ajax-license-deactivation-form-2' ).on( 'submit', fv_activation_deactivation_form_2_submit );
}

function fv_do_plugin_updates_page() {
	console.log( 'running fv_do_plugin_updates_page()' );

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

function fv_do_theme_updates_page() {
	console.log( 'running fv_do_theme_updates_page()' );

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

function fv_do_history_page() {
	console.log( 'running fv_do_history_page()' );
}

function fv_do_settings_page() {
	console.log( 'running fv_do_settings_page()' );

	// settings page - white label form - submit button.
	jQuery('#white_label')
		.on( 'click', fv_settings_show_alert_white_label_not_available );
}

function fv_settings_show_alert_white_label_not_available( event ) {
	console.log( 'running fv_settings_show_alert_white_label_not_available()' );

	event.preventDefault();
	jQuery.alert( {
		title: 'Sorry!!!',
		content:
			'Your activated plan does not have white label feature. Please upgrade your license to enable this awesome feature. Click <a href="https://festingervault.com/get-started/" target="_blank">here</a> to upgrade. ',
	} );
}

function fv_plugin_updates_show_confirmation_force_update_now( event ) {
	console.log( 'running fv_plugin_updates_show_confirmation_force_update_now()' );

	if ( ! confirm('Please confirm and auto update will run instantly!') ) {
		event.preventDefault();
	}
}

function fv_plugin_updates_show_confirmation_instant_update_all( event ) {
	console.log( 'running fv_plugin_updates_show_confirmation_instant_update_all()' );

	if ( ! confirm('Please confirm to run update instantly!') ) {
		event.preventDefault();
	}
}

function fv_theme_updates_show_confirmation_force_update_now( event ) {
	console.log( 'running fv_theme_updates_show_confirmation_force_update_now()' );

	if ( ! confirm('Please confirm and auto update will run instantly!') ) {
		event.preventDefault();
	}
}

function fv_theme_updates_show_confirmation_instant_update_all( event ) {
	console.log( 'running fv_theme_updates_show_confirmation_instant_update_all()' );

	if ( ! confirm('Please confirm and instant update will run!') ) {
		event.preventDefault();
	}
}

function fv_updates_show_alert_force_update_now_not_in_plan( event ) {
	console.log( 'running fv_updates_show_alert_force_update_now_not_in_plan()' );

	event.preventDefault();
	jQuery.alert( {
		title: 'Sorry!!!',
		content:
			'Your activated plan does not have FORCE UPDATE feature.  Please upgrade your license to enable this awesome feature. Click <a href="https://festingervault.com/get-started/" target="_blank">here</a> to upgrade. ',
	} );
}

function fv_updates_show_alert_instant_updates_all_not_in_plan( event ) {
	console.log( 'running fv_updates_show_alert_instant_updates_all_not_in_plan()' );

	event.preventDefault();
	jQuery.alert( {
		title: 'Sorry!!!',
		content:
			'Your activated plan does not have instant update feature. Please upgrade your license to enable this awesome feature. Click <a href="https://festingervault.com/get-started/" target="_blank">here</a> to upgrade. ',
	} );
}

function fv_updates_show_alert_no_updates_available( event ) {
	console.log( 'running fv_updates_show_alert_no_updates_available()' );

	event.preventDefault();
	jQuery.alert( {
		content: 'No update is available at this moment!',
	} );
}

function fv_theme_updates_toggle_theme_auto_update() {
	console.log( 'running fv_theme_updates_toggle_theme_auto_update()' );

	var fv_theme_slug           = jQuery( this ).data('id');
	var fv_theme_auto_update_is_checked = true;

	if ( true == jQuery( this ).prop('checked') ) {
		fv_theme_auto_update_is_checked = true;
	} else {
		fv_theme_auto_update_is_checked = false;
	}

	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: 'fv_toggle_theme_auto_update',
			fv_theme_slug:           fv_theme_slug,
			fv_theme_auto_update_is_checked: fv_theme_auto_update_is_checked,
		},
		type: 'POST',
		url:  ajax_url,
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

function fv_plugin_updates_toggle_plugin_auto_update() {
	console.log( 'running fv_plugin_updates_toggle_plugin_auto_update()' );

	var fv_plugin_slug                   = jQuery( this ).data("id");
	var fv_plugin_auto_update_is_checked = false;

	if ( jQuery( this ).prop("checked") == true ) {
		fv_plugin_auto_update_is_checked = true;
	} else {
		fv_plugin_auto_update_is_checked = false;
	}

	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: "fv_toggle_plugin_auto_update",
			fv_plugin_slug:                   fv_plugin_slug,
			fv_plugin_auto_update_is_checked: fv_plugin_auto_update_is_checked,
		},
		type: "POST",
		url: ajax_url,
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

function fv_vault_show_filter_type_changed_value() {
	console.log( 'running fv_vault_show_filter_type_changed_value()' );

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

function fv_vault_on_click_reset_filters_button() {
	console.log( 'running fv_vault_on_click_reset_filters_button()' );

	var filterValue = jQuery( this ).val();
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
	fv_load_vault_page_plugins_and_themes();
}

function fv_vault_show_tab_mylist_results() {
	console.log( 'running fv_vault_show_tab_mylist_results()' );

	var mylist = jQuery("#mylist").val();

	fv_vault_make_filter_tab_active('#mylist');

	fv_show_loading_animation();

	Object.assign(
		ajax_filter_data,
		{
			content_type: mylist
		}
	);

	fv_load_vault_page_plugins_and_themes( ajax_filter_data );
}

function fv_vault_show_tab_featured_results() {
	console.log( 'running fv_vault_show_tab_featured_results()' );

	var featured = jQuery("#featured").val();


	fv_vault_make_filter_tab_active('#featured');

	fv_show_loading_animation();

	Object.assign(
		ajax_filter_data,
		{
			content_type: featured
		}
	);

	fv_load_vault_page_plugins_and_themes( ajax_filter_data );
}

function fv_vault_show_tab_popular_results() {
	console.log( 'running fv_vault_show_tab_popular_results()' );

	var popular = jQuery("#popular").val();

	fv_vault_make_filter_tab_active('#popular');

	fv_show_loading_animation();

	Object.assign(
		ajax_filter_data,
		{
			content_type: popular
		 }
	 );

	fv_load_vault_page_plugins_and_themes( ajax_filter_data );
}

function fv_vault_show_tab_recent_results() {
	console.log( 'running fv_vault_show_tab_recent_results()' );

	var recent = jQuery("#recent").val();

	fv_vault_make_filter_tab_active('#recent');

	fv_show_loading_animation();

	Object.assign(
		ajax_filter_data,
		{
			content_type: recent
		}
	);

	fv_load_vault_page_plugins_and_themes( ajax_filter_data );
}

function fv_vault_show_filter_type_results() {
	console.log( 'running fv_vault_show_filter_type_results()' );

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

	fv_load_vault_page_plugins_and_themes( ajax_filter_data );
}

function fv_vault_show_filter_allowence_results() {
	console.log( 'running fv_vault_show_filter_allowence_results()' );

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

	fv_load_vault_page_plugins_and_themes( ajax_filter_data );
}

function fv_vault_show_filter_allowence_results() {
	console.log( 'running fv_vault_show_filter_allowence_results()' );

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

	fv_load_vault_page_plugins_and_themes( ajax_filter_data );
}

function fv_vault_show_search_results( e ) {
	console.log( 'running fv_vault_show_search_results()' );

	var ajax_search = jQuery("#ajax_search").val();

	if ( ajax_search.length >= 1 ) {
		jQuery("#ajax_search").addClass("active_button");
		jQuery("#ajax_search").removeClass("non_active_button");
	} else {
		jQuery("#ajax_search").addClass("non_active_button");
		jQuery("#ajax_search").removeClass("active_button");
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

			fv_load_vault_page_plugins_and_themes( ajax_filter_data );
		}
	}, 500 ); // half second
}

function fv_activation_form_submit( e ) {
	console.log( 'running fv_activation_form_submit()' );

	e.preventDefault();

	jQuery("#overlaybef").show();

	fv_show_loading_animation();

	var licenseKeyInput = jQuery("#licenseKeyInput").val();
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url        = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: "fv_activate_license_form_submit",
			licenseKeyInput: licenseKeyInput,
		},
		type: "POST",
		url: ajax_url,
		success: function( data ) {
			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );

			switch ( json.result ) {
				case 'valid':
					// license succesfully activated
					jQuery("#ajax-license-activation-form").hide();

					jQuery("#activation_result").addClass(
						"card text-center text-success"
					);
					jQuery("#activation_result").removeClass(
						"text-warning text-danger"
					);
					break;

				case 'invalid':
					jQuery("#activation_result").addClass(
						"card text-center text-warning"
					);
					jQuery("#activation_result").removeClass(
						"text-success text-danger"
					);
					break;

				case 'invalid':
					jQuery("#activation_result").addClass(
						"card text-center text-danger"
					);
					jQuery("#activation_result").removeClass(
						"text-success text-warning"
					);
					break;

				default:
					break;
			}

			jQuery("#activation_result").html( json.msg );

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

function fv_activation_license_refill_form_submit( e ) {
	console.log( 'running fv_activation_license_refill_form_submit()' );
	fv_do_license_refill_form_submit( '#license_key', '#refill_key' );
}

function fv_activation_license_refill_form_2_submit( e ) {
	console.log( 'running fv_activation_license_refill_form_2_submit()' );
	fv_do_license_refill_form_submit( '#license_key', '#refill_key' );
}

function fv_do_license_refill_form_submit( license_key_field_id, refill_key_field_id ) {
	console.log( 'running fv_do_license_refill_form_submit()' );

	e.preventDefault();
	jQuery('#overlaybef').show();
	fv_show_loading_animation();

	var license_key = jQuery( license_key_field_id ).val();
	var refill_key  = jQuery( refill_key_field_id  ).val();
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url    = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: 'fv_refill_license_form_submit',
			license_key: license_key,
			refill_key:  refill_key,
		},
		type: 'POST',
		url: ajax_url,
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

function fv_activation_deactivation_form_submit( e ) {
	console.log( 'running fv_activation_deactivation_form_submit()' );
	fv_do_deactivation_form_submit(
		'#license_key',
		'#license_d',
		'.deactivation_result'
	);
}

function fv_activation_deactivation_form_2_submit( e ) {
	console.log( 'running fv_activation_deactivation_form_2_submit()' );
	fv_do_deactivation_form_submit(
		'#license_key_2',
		'#license_d_2',
		'.deactivation_result2'
	);
}

function fv_do_deactivation_form_submit(
	license_key_field_id,
	license_domain_field_id,
	deactivation_result_field_class
	) {
	console.log( 'running fv_do_deactivation_form_submit()' );

	e.preventDefault();

	jQuery('#overlaybef').show();

	fv_show_loading_animation();

	var license_key = jQuery( license_key_field_id    ).val();
	var license_d   = jQuery( license_domain_field_id ).val();

	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url    = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: 'fv_deactivate_license_form_submit',
			license_key: license_key,
			license_d:   license_d,
		},
		type: 'POST',
		url: ajax_url,
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

function fv_vault_make_filter_tab_active( id ) {
	console.log( 'running fv_vault_make_filter_tab_active()' );

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

function fv_vault_reset_filters_active_state() {
	console.log( 'running fv_vault_reset_filters_active_state()' );

	let filters = [ "#filter_type", "#filter_allowence", "#filter_category", "#ajax_search" ];

	filters.forEach( filter => {
		jQuery( filter ).removeClass('active_button');
		jQuery( filter ).addClass('non_active_button');
	});
}

function grab_product_hash( d ) {
	console.log( 'running grab_product_hash()' );

	// progress bar
	jQuery(".progress").hide();

	// spinner/loading indicator...
	fv_show_loading_animation();

	var product_hash = d.getAttribute("data-id");

	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url     = plugin_ajax_object.ajax_url;

	jQuery
		.ajax( {
			data: { action: "fv_plugin_buttons_ajax", product_hash: product_hash },
			type: "POST",
			url: ajax_url,
			success: function( data ) {
				var data_s = data.slice( 0, -1 );
				var json = JSON.parse( data_s );

				if ( json.result == "failed") {
					setTimeout( function() {
						fv_hide_loading_animation();
					}, 500 );

					jQuery.alert( {
						content: json.msg,
					} );
				}
				if ( json.length == 0 ) {
					jQuery.alert( {
						content: "To enjoy this feature please activate your license.",
					} );
				} else {
					// show plugin download_confirmation
					collectort( json );
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
 * Renders and shows a modal popup for an update request in Vault.
 *
 * @param {*} d
 */
function grab_product_support_link( d ) {
	console.log( 'running grab_product_support_link()' );

	jQuery(".progress").hide();
	fv_show_loading_animation();

	var data_support_link   = d.getAttribute("data-support-link");
	var data_product_hash   = d.getAttribute("data-product-hash");
	var data_generated_slug = d.getAttribute("data-generated-slug");
	var data_generated_name = d.getAttribute("data-generated-name");
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url            = plugin_ajax_object.ajax_url;

	jQuery
		.ajax( {
			data: {
				action: "fv_plugin_support_link",
				data_support_link:   data_support_link,
				data_product_hash:   data_product_hash,
				data_generated_slug: data_generated_slug,
				data_generated_name: data_generated_name
			},
			type: "POST",
			url: ajax_url,
			success: function( data ) {

				var data_s = data.slice( 0, -1 );
				var json   = JSON.parse( data_s );

				if ( json.result == "success") {
					setTimeout( function() {
						fv_hide_loading_animation();
					}, 500 );

					button_data  = '<div class="row">';
					button_data +=     '<form class="submitupdaterequest" data-product-hash="'+json.data_product_hash+'" data-generated-slug="'+json.data_generated_slug+'" data-license="'+json.license_key+'">';
					button_data +=         '<div class="mb-3">';
					button_data +=             '<input type="text" class="form-control text-white" name="versionnumberrequest" placeholder="Enter the version number ( e.g. 2.3.2 )" onkeydown="if ( event.keyCode==13 ) {event.preventDefault();}">';
					button_data +=             '<input type="hidden" class="form-control text-white" name="versionnumberrequest_link" value="'+data_support_link+'">';
					button_data +=             '<input type="hidden" class="form-control text-white" name="data_generated_name" value="'+data_generated_name+'">';
					button_data +=             '<input type="hidden" class="form-control text-white" name="licenseKeyGet" value="'+json.license_key+'"> ';
					button_data +=         '</div><br/>';
					button_data +=         '<div class="d-grid gap-2 col-12 mx-auto"> ';
					button_data +=             '<button class="btn btn-secondary" onclick="handleFormSubmit( this )" type="button">Submit</button>  ';
					button_data +=         '</div>';
					button_data +=     '</form>';
					button_data += '</div>';

					jQuery(".modal-body").html( button_data );
					jQuery("#empModal").modal("show");
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
 * Renders and shows a modal popup to Report Item in Vault.
 *
 * @param {*} d
 */
function grab_product_report_link( d ) {
	console.log( 'running grab_product_report_link()' );

	jQuery(".progress").hide();
	fv_show_loading_animation();

	var data_support_link   = d.getAttribute("data-support-link");
	var data_product_hash   = d.getAttribute("data-product-hash");
	var data_generated_slug = d.getAttribute("data-generated-slug");
	var data_generated_name = d.getAttribute("data-generated-name");
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url            = plugin_ajax_object.ajax_url;

	jQuery
		.ajax( {
			data: {
				action: "fv_plugin_report_link",
				data_support_link:   data_support_link,
				data_product_hash:   data_product_hash,
				data_generated_slug: data_generated_slug,
				data_generated_name: data_generated_name
			},
			type: "POST",
			url: ajax_url,
			success: function( data ) {
				var data_s = data.slice( 0, -1 );
				var json = JSON.parse( data_s );

				if ( json.result == "success") {
					setTimeout( function() {
						fv_hide_loading_animation();
					}, 500 );

					button_data  =  '<div class="row">';
					button_data +=      '<form class="reportitemform" data-product-hash="' + json.data_product_hash + '" data-generated-slug="' + json.data_generated_slug + '" data-license="' + json.license_key + '">';
					button_data +=          '<div class="mb-3">' +
												'<textarea ' +
													'name="versionnumberrequest" ' +
													'placeholder="Fill in your report and it will be automatically posted on the community forums. You will receive your reported link afterwards." class="form-control text-white">' +
												'</textarea>' +
												'<input type="hidden" ' +
													'name="versionnumberrequest_link" ' +
													'value="' + data_support_link + '" class="form-control text-white" >' +
												'<input type="hidden" ' +
													'name="data_generated_name" ' +
													'value="' + data_generated_name + '" class="form-control text-white" >' +
												'<input type="hidden" ' +
													'name="licenseKeyGet" ' +
													'value="' + json.license_key + '" class="form-control text-white" >' +
											'</div><br/>' +
											'<div class="d-grid gap-2 col-12 mx-auto"> ' +
												'<button class="btn btn-secondary" onclick="handleFormSubmitReport( this )" type="button">' +
													'Submit' +
												'</button>  ' +
											'</div>';
					button_data +=      '</form>';
					button_data +=  '</div>';

					jQuery(".modal-body").html( button_data );
					jQuery("#empModal").modal("show");

				}

			},
		} )
		.done( function() {
			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );
		} );
}

function handleFormSubmit( button ) {
	console.log( 'running handleFormSubmit()' );

	// get the parent form of the clicked button
	var form                = jQuery( button ).closest("form");
	var formData            = form.serialize();
	var versionNumber       = form.find("input[name='versionnumberrequest']").val();
	var licenseKeyGet       = form.find("input[name='licenseKeyGet']").val();
	var data_generated_name = form.find("input[name='data_generated_name']").val();

	if ( versionNumber.trim() === "") {
		alert("Version number is required.");
		return;
	}

	jQuery("#empModal").modal("hide");

	fv_show_loading_animation();

	var getlinkData      = form.find("input[name='versionnumberrequest_link']").val();
	var parts            = getlinkData.split("/");
	var lastPart         = parts.pop();
	var lastNumericValue = parseInt( lastPart );
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url         = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: { action: "fv_discourse_post_new_version", versionNumber: versionNumber, lastNumericValue: lastNumericValue, licenseKeyGet: licenseKeyGet, data_generated_name: data_generated_name },
		type: "POST",
		url: ajax_url,
		success: function( data ) {
			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );

			if ( json.result == "success") {
				setTimeout( function() {
					fv_hide_loading_animation();
				}, 500 );

						jQuery("#empModal").modal("hide");

				jQuery.alert( {
					content: 'You request has been successfully posted. Visit <a target="_blank" href="'+getlinkData+'">this link</a> to see.',
				} );
			}

			if ( json.result == "failed") {
				setTimeout( function() {
					fv_hide_loading_animation();
				}, 500 );

						jQuery("#empModal").modal("hide");

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

function handleFormSubmitReport( button ) {
	console.log( 'running handleFormSubmitReport()' );

	// get the parent form of the clicked button
	var form                = jQuery( button ).closest("form");
	var formData            = form.serialize();
	var versionNumber       = form.find("textarea[name='versionnumberrequest']").val();
	var licenseKeyGet       = form.find("input[name='licenseKeyGet']").val();
	var data_generated_name = form.find("input[name='data_generated_name']").val();

	if ( versionNumber.trim() === "") {
		alert( "Version number is required." );
		return;
	}

	jQuery("#empModal").modal("hide");
	fv_show_loading_animation();

	var getlinkData      = form.find("input[name='versionnumberrequest_link']").val();
	var parts            = getlinkData.split("/");
	var lastPart         = parts.pop();
	var lastNumericValue = parseInt( lastPart );
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url         = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: "fv_discourse_post_new_report",
			versionNumber:       versionNumber,
			lastNumericValue:    lastNumericValue,
			licenseKeyGet:       licenseKeyGet,
			data_generated_name: data_generated_name
		},
		type: "POST",
		url:  ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			if ( json.result == "success") {

				setTimeout( function() {
					fv_hide_loading_animation();
				}, 500 );

				jQuery("#empModal").modal("hide");

				jQuery.alert( {
					content: 'You request has been successfully posted. Visit <a target="_blank" href="'+getlinkData+'">this link</a> to see.',
				} );
			}

			if ( json.result == "failed") {
				setTimeout( function() {
					fv_hide_loading_animation();
				}, 500 );

				jQuery("#empModal").modal("hide");

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
 * callback of the Download LATEST VERSION button of the download popup in Vault.

 * @param {*} d
 */
function grab_product_dowload_link( d ) {
	console.log( 'running grab_product_dowload_link()' );

	fv_show_loading_animation();

	let dd = $( d ).find( 'option:selected' );

	var plugin_download_hash = dd.data("id");
	var license_key          = dd.data("license");
	var data_key             = dd.data("key");

	if ( typeof license_key === 'undefined' || license_key === undefined ) {
		var plugin_download_hash = d.getAttribute("data-id");
		var license_key          = d.getAttribute("data-license");
		var data_key             = d.getAttribute("data-key");
	}

	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: "fv_plugin_download_ajax",
			plugin_download_hash: plugin_download_hash,
			license_key:          license_key,
			mfile:                data_key,
		},
		type: "POST",
		url:  ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json   = JSON.parse( data_s );

			if ( json.result == "success") {

				jQuery("#" + license_key + " #plan_limit_id").html( json.plan_limit );
				jQuery("#" + license_key + " #current_limit_id").html( json.download_current_limit );
				jQuery("#" + license_key + " #limit_available_id").html( json.download_available );
				location.href = json.link;
				jQuery("#empModal").modal("hide");

			} else {

				jQuery("#empModal").modal("hide");

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
 * Callback for the install button of the bulk install/download popup in the Vault page.
 *
 * @param {*} d
 */
function grab_product_install_bundle_link( d ) {
	console.log( 'running grab_product_install_bundle_link()' );

	jQuery(".progress").show();
	fv_show_loading_animation();
	updateProgressBarAuto( 50 );

	// Check if there is only one option in the select

	var plugin_download_hash = productHashJson;
	var license_key          = d.getAttribute("data-license");
	var data_key             = d.getAttribute("data-key");

	if ( typeof license_key === 'undefined' || license_key === undefined ) {
		var plugin_download_hash = d.getAttribute("data-id");
		var license_key          = d.getAttribute("data-license");
		var data_key             = d.getAttribute("data-key");
	}

	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: "fv_plugin_install_bulk_ajax",
			plugin_download_hash: plugin_download_hash,
			license_key:          license_key,
			mfile:                data_key,
		},
		type: "POST",
		url:  ajax_url,
		success: function( data ) {

				// updateProgressBarAuto( 100 );

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
						updateProgressBar( json.getfilesize );
						$.removeCookie( 'cartData' );
						refreshCartDisplay();
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
function grab_product_dowload_bundle_link( d ) {
	console.log( 'running grab_product_dowload_bundle_link()' );

	jQuery(".progress").show();
	fv_show_loading_animation();
	updateProgressBarAutoDLBefore( 50 );
		// Check if there is only one option in the select

	var plugin_download_hash = productHashJson;
	var license_key          = d.getAttribute("data-license");
	var data_key             = d.getAttribute("data-key");

	if ( typeof license_key === 'undefined' || license_key === undefined ) {
		var plugin_download_hash = d.getAttribute("data-id");
		var license_key          = d.getAttribute("data-license");
		var data_key             = d.getAttribute("data-key");
	}

	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: "fv_plugin_download_ajax_bundle",
			plugin_download_hash: plugin_download_hash,
			license_key:          license_key,
			mfile:                data_key,
		},
		type: "POST",
		url:  ajax_url,
		success: function( data ) {

			//updateProgressBar( 50 );

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

				updateProgressBarAutoDL( 100 );

				$.removeCookie( 'cartData' );
				refreshCartDisplay();

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

function updateProgressBarAuto( progress ) {
	console.log( 'running updateProgressBarAuto()' );

	var progressElement = $( '.progress' );
	var currentProgress = parseInt( progressElement.attr( 'aria-valuenow' ) );
	var targetProgress  = parseInt( progress );

	var increment = ( targetProgress - currentProgress ) / 100;

	function incrementProgress() {
		console.log( 'running incrementProgress()' );
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

function updateProgressBarAutoDLBefore( progress ) {
	console.log( 'running updateProgressBarAutoDLBefore()' );
	var progressElement = $( '.progress' );
	var currentProgress = parseInt( progressElement.attr( 'aria-valuenow' ) );
	var targetProgress  = parseInt( progress );
	var increment       = ( targetProgress - currentProgress ) / 100;

	function incrementProgress() {
		console.log( 'running incrementProgress()' );
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

function updateProgressBarAutoDL( progress ) {
	console.log( 'running updateProgressBarAutoDL()' );

	var progressElement = $( '.progress' );
	var currentProgress = parseInt( progressElement.attr( 'aria-valuenow' ) );
	var targetProgress  = parseInt( progress );
	var increment       = ( targetProgress - currentProgress ) / 100;

	function incrementProgress() {
		console.log( 'running incrementProgress()' );

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

function updateProgressBar( fileSize ) {
	console.log( 'running updateProgressBar()' );

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
function fv_show_bulk_action_confirmation_popup( json, type ) {
	console.log( 'running fv_show_bulk_action_confirmation_popup()' );

	switch ( type ) {
		case 'download':
			var typeBtnText   = 'Download Bundle';
			var typeBtnIcon   = 'fa fa-download';
			var typeBtnMethod = 'grab_product_dowload_bundle_link';
			break;

		case 'install':
			var typeBtnText   = 'Install Bundle';
			var typeBtnIcon   = 'fas fa-cloud-download-alt';
			var typeBtnMethod = 'grab_product_install_bundle_link';
			break;
		}

	var button_data =
		'<div class="row">';

	jQuery.each( json, function( index, item ) {

		var ind_item    = JSON.parse( item );
		productHashJson = ind_item.product_hash;

		button_data +=
			'<div class="col">' +
				'<div class="card bg-light" style="min-width:100%;"> ' +
					'<div class="card-header">' +
						ind_item.plan_name +
					'</div>' +
					'<ul class="list-group list-group-flush">' +
						'<li class="list-group-item">' +
							'Plan Type<b>: ' + ind_item.plan_type.toUpperCase() +
						'</b></li>' +
						'<li class="list-group-item">' +
							'Plan Limit: ' + ind_item.plan_limit +
						'</li>' +
						'<li class="list-group-item">' +
							'Available Limit: ' + ind_item.download_available +
						'</li>' +
					'</ul>' +
				'</div>' +
				'<div class="row">' +
					'<div class="text-white mt-2 mb-2">' +
						'Plugins Bundle' +
					'</div>' +
					'<ul class="text-white">';

					jQuery.each( ind_item.product_hash, function( index2, item2 ) {
						button_data += '<li>' + item2.product_name + '</li>';
					} );

		button_data +=
					'</ul>' +
				'</div>' +
				'<button id="option1" ' +
					'data-license="' + ind_item.license_key + '" ' +
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

	jQuery(".modal-body").html( button_data );
	jQuery("#empModal").modal("show");
	setTimeout( function() {
		fv_hide_loading_animation();
	}, 500 );
}

/**
 * Renders modal popup triggered by a download button in the Vault.
 *
 * @param {*} json
 */
function collectort( json ) {
	console.log( 'running collectort()' );

	var button_data = '<div class="row">';

	jQuery.each( json, function( index, item ) {

		var ind_item = JSON.parse( item );
		let count_versions = ( ind_item.other_available_versions.length );
		let whichevent = 'onChange';
		if ( count_versions == 1 ) {
			whichevent = 'onClick';
		}

		button_data +=
			'<div class="col">' +
				// Lincense information
				'<div class="card bg-light" style="min-width:100%;"> ' +
					'<div class="card-header">' +
						ind_item.plan_name +
					'</div>' +
					'<ul class="list-group list-group-flush">' +
						'<li class="list-group-item">' +
							'Plan Type<b>: '    + ind_item.plan_type.toUpperCase() + '</b>' +
						'</li>' +
						'<li class="list-group-item">' +
							'Plan Limit: '      + ind_item.plan_limit +
						'</li>' +
						'<li class="list-group-item">' +
							'Available Limit: ' + ind_item.download_available +
						'</li>' +
					'</ul>' +
				'</div>' +
				// Download button
				'<button id="option1" ' +
					'data-license="' + ind_item.license_key + '" ' +
					'data-id="'      + ind_item.product_hash + '" ' +
					'onclick="grab_product_dowload_link( this ); this.disabled=true;" ' +
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
										'<select ' + whichevent + '="grab_product_dowload_link( this ); this.disabled=true;" class="form-select text-white ' + ind_item.license_key + ind_item.product_hash + '" name="downloadOtherVerions">';

		if ( Array.isArray( ind_item.other_available_versions ) ) {
			jQuery.each( ind_item.other_available_versions.reverse(), function( index2, item2 ) {
				button_data +=              '<option ' +
												'value="' + item2.generated_version + '" ' +
												'data-key="' + item2.filename + '" ' +
												'data-license="' + ind_item.license_key + '" ' +
												'data-id="' + ind_item.product_hash + '" >' +
												'Version ' + item2.generated_version +
											'</option>';
			});
		}
		button_data +=                  '</select>' +
									'</div>' +
								'</td>' +
							'</tr>' +
						'</table>' +
					'</div>' +
				'</div>' +
			'</div>';
		} );

	button_data += '</div>';

	jQuery(".modal-body").html( button_data );
	jQuery("#empModal").modal("show");
	setTimeout( function() {
		fv_hide_loading_animation();
	}, 500 );
}

function grab_product_install_hash( d ) {
	console.log( 'running grab_product_install_hash()' );

	jQuery(".progress").hide();
	fv_show_loading_animation();

	var product_hash   = d.getAttribute("data-id");
	// var license_key    = dd.data("license");
	// var data_key       = dd.data("key");
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url       = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
			data: {
				action: "fv_plugin_install_button_modal_generate",
				product_hash: product_hash,
			},
			type: "POST",
			url: ajax_url,
			success: function( data ) {

				var data_s = data.slice( 0, -1 );
				var json   = JSON.parse( data_s );

				if ( json.result === "failed") {
					setTimeout( function() {
						fv_hide_loading_animation();
					}, 500 );
					jQuery.alert( {
						content: json.msg,
					} );
				}
				if ( json.length == 0 ) {
					jQuery.alert( {
						content: "To enjoy this feature please activate your license.",
					} );
				} else {
					// when api-call succes then show install & activate confirmation popup.
					setTimeout( function() {
						fv_hide_loading_animation();
					}, 500 );
					fv_vault_show_install_confirmation_popup( json );
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
 * Renders and shows a popup after a user clicked the install button on a plugin or theme in the vault page.
 *
 * @param {*} json
 */
function fv_vault_show_install_confirmation_popup( json ) {
	console.log( 'running fv_vault_show_install_confirmation_popup()' );

	let html = '<div class="row">';

	jQuery.each( json, function ( index, item ) {

		var ind_item               = JSON.parse( item );
		let count_versions         = ind_item.other_available_versions.length;
		let whichevent             = 'onChange';
		let fv_install_button_text = '';

		if ( count_versions == 1 ) {
			whichevent = 'onClick';
		}

  		switch ( true ) {
			// = active theme?
			case fv_is_product_slug_found(
					ind_item.product_slug,
					// note: plugin_ajax_object is the wp_localized data passed from php
					plugin_ajax_object.get_all_active_themes_js
				) :
				fv_install_button_text = 'Install latest version';
				break;

			// = active plugin?
			case fv_is_product_slug_found(
					ind_item.product_slug,
					// note: plugin_ajax_object is the wp_localized data passed from php
					plugin_ajax_object.get_all_active_plugins_js
				) :
				fv_install_button_text = 'Install latest version';
				break;

			// inactive theme?
			case fv_is_product_slug_found(
					ind_item.product_slug,
					// note: plugin_ajax_object is the wp_localized data passed from php
					plugin_ajax_object.get_all_inactive_themes_js
				) :
				fv_install_button_text = 'Already Installed Please Activate';
				break;

			// inactive plugin?
			case fv_is_product_slug_found(
					ind_item.product_slug,
					// note: plugin_ajax_object is the wp_localized data passed from php
					plugin_ajax_object.get_all_inactive_plugins_js
				) :
				fv_install_button_text = 'Already Installed Please Activate';
				break;

			default:

				switch ( true ) {
					case ( 'wordpress-themes' === ind_item.product_type ):
						fv_install_button_text = 'Install LATEST VERSION';
						break;

					case ( 'wordpress-plugins' === ind_item.product_type ):
						fv_install_button_text = 'Install & Activate LATEST VERSION';
						break;

					default:
						fv_install_button_text = 'Install latest version';
						break;
				}

				break;
		}

		html +=
			'<div class="col">' +
				// license information
				'<div class="card bg-light" style="min-width:100%;">' +
					'<div class="card-header">' +
						ind_item.plan_name +
					'</div>' +
					'<ul class="list-group list-group-flush">' +
						'<li class="list-group-item">' +
							'Plan Type<b>: ' + ind_item.plan_type.toUpperCase() +
						"</b></li>" +
						'<li class="list-group-item">' +
							'Plan Limit: ' + ind_item.plan_limit +
						"</li>" +
						'<li class="list-group-item">' +
							'Available Limit: ' + ind_item.download_available +
						'</li>' +
					'</ul>' +
				'</div>' +
				// installation button
				'<button id="option1" class="btn btn-sm btn-block card-btn" ' +
					'data-license="' + ind_item.license_key  + '" ' +
					'data-type="'    + ind_item.product_type + '" ' +
					'data-id="'      + ind_item.product_hash + '" ' +
					'href="#" ' +
					'onclick="grab_product_install_link( this ); this.disabled=true;"' +
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
											whichevent + '="grab_product_install_link( this ); this.disabled=true;" ' +
											'class="form-select text-white ' + ind_item.license_key + ind_item.product_hash + '" ' +
											'name="downloadOtherVerions">';

											if ( Array.isArray( ind_item.other_available_versions ) ) {
												jQuery.each( ind_item.other_available_versions.reverse(), function( index3, item3 ) {
													html +=
														'<option value="' + item3.generated_version     + '" ' +
															'data-license="' + ind_item.license_key    + '" ' +
															'data-type="'    + ind_item.product_type   + '" ' +
															'data-id="'      + ind_item.product_hash   + '" ' +
															'data-key="'     + item3.filename          + '" ' +
															'>' +
															'Version '       + item3.generated_version +
														'</option>';
												});
											}

		html +=                  		'</select>' +
									'</div>' +
								'</td>' +
							'</tr>' +
						'</table>' +
					'</div>' +
				'</div>' +
			'</div>';
	} );

	html += "</div>";

	jQuery(".modal-body").html( html );
	jQuery("#empModal").modal("show");

}

function fv_is_product_slug_found( needle, haystack ) {
	console.log( 'running fv_is_product_slug_found()' );
	return ( -1 !== jQuery.inArray( needle, JSON.parse( haystack ) ) );
}

function grab_product_install_link( d ) {
	console.log( 'running grab_product_install_link()' );

	fv_show_loading_animation();

	let ddd = $( d ).find( 'option:selected' );

	var plugin_download_hash = ddd.data("id");
	var license_key = ddd.data("license");
	var mfile = ddd.data("key");

	// show confirm popup

	var result = confirm("Are you sure you want to continue?");
	if ( ! result ) {
		fv_hide_loading_animation();
		jQuery("#empModal").modal("hide");
	} else {
		if ( typeof license_key === 'undefined' || license_key === undefined ) {
			var plugin_download_hash = d.getAttribute("data-id");
			var license_key = d.getAttribute("data-license");
			var mfile = d.getAttribute("data-key");
			conditionmatch = true;
		}

	// var plugin_download_hash = d.getAttribute("data-id");
	// var license_key          = d.getAttribute("data-license");
	// var mfile                = d.getAttribute("data-key");
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url                = plugin_ajax_object.ajax_url;

	jQuery
		.ajax( {
			data: {
				action: "fv_plugin_install_ajax",
				plugin_download_hash: plugin_download_hash,
				license_key:          license_key,
				mfile:                mfile,
			},
			type: "POST",
			url: ajax_url,
			success: function( data ) {
				var data_s = data.slice( 0, -1 );
				var json   = JSON.parse( data_s );

				if ( json.result == "success") {
					jQuery("#" + license_key + " #plan_limit_id").html( json.plan_limit );
					jQuery("#" + license_key + " #current_limit_id").html( json.download_current_limit );
					jQuery("#" + license_key + " #limit_available_id").html( json.download_available );

					if ( json.link == "theme") {
						jQuery.alert( {
							content:
								'Theme successfully installed. Click here to <a target="_blank" href="' +
								json.theme_preview +
								'">Preview theme</a>!',
						} );
					} else {
						location.href = json.activation;
					}
					jQuery("#empModal").modal("hide");
				} else {
					jQuery("#empModal").modal("hide");
					if ( json.result == "failed" && json.msg == "Daily limit crossed") {
						jQuery.alert( {
							title: "Sorry! Limit issue!",
							content:
								"Your daily Download Limit is crossed, you can download tomorrow again! Happy downloading.",
						} );
					} else {
						if ( json.msg ) {
							jQuery.alert( {
								title: "Alert!",
								content: json.msg,
							} );
						} else {
							jQuery.alert( {
								title: "Alert!",
								content: "Hello!Something went wrong, Please try again later!",
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

//var page = 1;
function fv_load_vault_page_plugins_and_themes( ajax_search = "", page = 1 ) {
	console.log( 'running fv_load_vault_page_plugins_and_themes()' );

	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url                  = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: 'fv_search_ajax_data',
			ajax_search: ajax_search,
			page:        page
		},
		type: 'POST',
		url: ajax_url,
		success: function( data ) {

			// fv_show_loading_animation();

			var data_s = data.slice( 0, -1 );  // removes the last char of string.
			var json   = JSON.parse( data_s ); // converts json in a js object.

			console.log( json );

			// port the parse result through pagination controller
			// json.links contains pagination urls etc.
			// json.data contains the plugin/theme data

			paginationDataController( ajax_search, json.links, page );

			jQuery('#list').pagination( {

				dataSource:    json.data, // plugin/theme data of the current page.
				pageSize:      40,
				showPrevious:  false,
				showNext:      false,
				showNavigator: false,

				callback: fv_vault_render_product_grid,
			} );
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
	console.log( 'running fv_vault_render_product_grid()' );

	// note: plugin_ajax_object is the wp_localized data passed from php

	jQuery( '.paginationjs-page' )
		.addClass( 'disabled disablendhide' )
		.attr( 'disabled', true );

	var wrapper          = jQuery('#list .wrapper').empty();
	var fv_item_count    = 1;

	// Nothing found.
	if ( products.length == 0 ) {
		wrapper.html(
			'<div class=mt-4 mb-4"" style="color:#fff; font-size:20px; text-align:center;">' +
				'Sorry, No plugins or themes are found!' +
			'</div>'
		);

		// Hide pagagination.
		jQuery('.paginationjs').hide();
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
	jQuery('.paginationjs').show();

	// Determine number of grid columns.
	var fv_items_per_row = fv_get_items_per_row( '.wrapper' );

	// iterate though the found products.
	var product_grid_html = '<div class="row mb-3 ">';

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

	product_grid_html += "</div>"; // close row
	product_grid_html += "</div>"; // close grid

	// insert grid in page.
	wrapper.append( product_grid_html );
}

function paginationDataController( ajax_search, links, page ) {
	console.log( 'running paginationDataController()' );

	var pagination = $( '#pagination' );
	pagination.empty(); // Clear previous pagination links

	$.each( links, function( index, link ) {
		var listItem = $( '<li class="page-item"></li>' );
		var linkElement = $( '<a class="page-link"></a>' );

		if ( link.url ) {
			linkElement.attr( 'href', link.url );
			linkElement.on( 'click', function( event ) {
				event.preventDefault();
				var page = getPageFromUrl( link.url );
				handlePageClick( ajax_search, page );
			} );

			// Add "active" class to the selected page link
			if ( page === getPageFromUrl( link.url ) ) {
				linkElement.addClass( 'active' );
			}

		} else {
			linkElement.addClass( 'disabled' );
		}

	// Decode HTML entities
		var decodedLabel = $( '<div>' ).html( link.label ).text();
		linkElement.html( decodedLabel );

		listItem.append( linkElement );
		pagination.append( listItem );
	} );
}

function getPageFromUrl( url ) {
	console.log( 'running getPageFromUrl()' );
	var regex = /[?&]page=( \d+ )/;
	var match = regex.exec( url );
	return match ? parseInt( match[1] ) : null;
}

function handlePageClick( ajax_search, page ) {
	console.log( 'running handlePageClick()' );

	// Replace this with your logic to handle the page click
	fv_load_vault_page_plugins_and_themes( ajax_search, page );
}

function showToast() {
	console.log( 'running showToast()' );
	var toast = document.querySelector( '.toast' );
	var toastEl = new bootstrap.Toast( toast );
	toastEl.show();
}

function fv_add_item_to_cart( d ) {
	console.log( 'running fv_add_item_to_cart()' );

	var productId   = d.getAttribute("data-id");
	var productName = d.getAttribute("data-itemname");

	// Get the cart items from the cookie or create a new empty object
	var cartData  = getCartData();
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
			var cartItems = document.getElementById("cart-items");
			var newItem = document.createElement("div");

			newItem.innerHTML =
				productName +
				'<button type="button" class="btn btn-danger btn-sm float-right" ' +
					'onclick="remove_from_cart( this )" ' +
					'data-id="' + productId + '">' +
					'Remove' +
				'</button>';

			cartItems.appendChild( newItem );
		} else {
		}

		showToast();

	} else {
		alert( 'Sorry, Maximum limit is 10 items.' )
	}

	// Save the updated cart data to the cookie
	setCartData( cartData );

	refreshCartDisplay();
};

/**
 * Remove a bulk action item from cart.
 */
function remove_from_cart( d ) {
	console.log( 'running remove_from_cart()' );

	var productId = d.getAttribute("data-id");

	// Get the cart items from the cookie or create a new empty object
	var cartData  = getCartData();

	// Remove the selected item from the cart
	delete cartData[ productId ];

	// Remove the item from the cart dropdown
	var cartItems    = document.getElementById("cart-items");
	var itemToRemove = d.parentNode;

	cartItems.removeChild( itemToRemove );

	// Save the updated cart data to the cookie
	setCartData( cartData );
};

/**
 * Get bulk items in cart from cookie
 */
function getCartData() {
	console.log( 'running getCartData()' );

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
function setCartData( cartData ) {
	console.log( 'running setCartData()' );

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
function refreshCartDisplay() {
	console.log( 'running refreshCartDisplay()' );

	var cartData      = getCartData();
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
	 * Handle click on the Vault Page -> Bulk -> Clear All button.
	 */
	jQuery( '.cart-dropdown #clearall-button' )
		.off( 'click' )
		.on( 'click',
			function() {
				$.removeCookie( 'cartData' );
				refreshCartDisplay();
			}
		);

	/**
	 * Handle click on the Vault Page -> Bulk -> Download All button.
	 */
	jQuery( '.cart-dropdown #download-button' )
		.off( 'click' )
		.on( 'click', fv_vault_page_handle_bulk_download_all_button );

	/**
	 * Callback for the Vault Page -> Bulk -> Download All button.
	 */
	function fv_vault_page_handle_bulk_download_all_button() {
		console.log( 'running fv_vault_page_handle_bulk_download_all_button()' );

		jQuery(".progress").hide();

		var progressBar = $( '.progress' );

		progressBar.attr( 'aria-valuenow', 0 );
		progressBar.find( '.progress-bar' ).css( 'width', 0 + '%' );

		// collect the bulk plugins/themes from the card.
		// note: carItemsList is filled in refreshCartDisplay()

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

		// note: plugin_ajax_object is the wp_localized data passed from php
		var ajax_url         = plugin_ajax_object.ajax_url;

		jQuery.ajax( {
			data: {
				action: "fv_get_bulk_items_data_from_api",
				product_hash: cartItems
			},
			type: "POST",
			url: ajax_url,
			success: function( data ) {

				var data_s = data.slice( 0, -1 );
				var json   = JSON.parse( data_s );

				if ( json.result == "failed" ) {

					setTimeout( function() {
						fv_hide_loading_animation();
					}, 500 );

					jQuery.alert( {
						content: json.msg,
					} );
				}
				if ( json.length == 0 ) {

					jQuery.alert( {
						content: "To enjoy this feature please activate your license.",
					} );

				} else {

					// If all went well, build and show the confirmation popup.
					fv_show_bulk_action_confirmation_popup( json, 'download' );
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
	 * Handle click on the Vault Page -> Bulk -> Install All button.
	 */
	jQuery( '.cart-dropdown #install-button' )
		.off( 'click' )
		.on( 'click', fv_vault_page_handle_bulk_install_all_button );

	/**
	 * Callback for the Vault Page -> Bulk -> Install All button.
	 */
	function fv_vault_page_handle_bulk_install_all_button() {
		console.log( 'running fv_vault_page_handle_bulk_install_all_button()' );
		jQuery(".progress").hide();

		var progressBar = $( '.progress' );

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
		// note: plugin_ajax_object is the wp_localized data passed from php
		var ajax_url         = plugin_ajax_object.ajax_url;

		jQuery.ajax( {
			data: {
				action: "fv_get_bulk_items_data_from_api",
				product_hash: cartItems
			},
			type: "POST",
			url: ajax_url,
			success: function( data ) {
				var data_s = data.slice( 0, -1 );
				var json = JSON.parse( data_s );

				if ( json.result == "failed") {
					setTimeout( function() {
						fv_hide_loading_animation();
					}, 500 );

					jQuery.alert( {
						content: json.msg,
					} );
				}
				if ( json.length == 0 ) {
					jQuery.alert( {
						content: "To enjoy this feature please activate your license.",
					} );
				} else {
					fv_show_bulk_action_confirmation_popup( json, 'install' );
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
	 * Handle click on the Vault Page -> Bulk -> Cart-item -> Remove button.
	 */
	jQuery( '.cart-dropdown .remove-item' )
		.off( 'click' )
		.on( 'click', fv_vault_page_handle_bulk_action_item_remove_button );

	/**
	 * Callback for the Vault Page -> Bulk -> Cart-item -> Remove button.
	 */
	function fv_vault_page_handle_bulk_action_item_remove_button() {
		console.log( 'running fv_vault_page_handle_bulk_action_item_remove_button()' );
		var itemId   = jQuery( this ).parent().attr( 'data-id' );
		// Read cookie for cart data
		var cartData = getCartData();
		// remove item from bulk actions.
		delete cartData[itemId];
		// Save the updated cart data to the cookie
		setCartData( cartData );
		// refresh cart.
		refreshCartDisplay();
	}

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
	console.log( 'running fv_vault_toggle_bulk_action_cart()' );

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

	refreshCartDisplay();
}

function grab_dc_product_dcontents( d ) {
	console.log( 'running grab_dc_product_dcontents()' );

	fv_show_loading_animation()　
	jQuery(".progress").hide();

	var product_hash = ( d.getAttribute("data-id") );
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url     = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: 'fv_fs_plugin_dc_contents_ajax',
			product_hash: product_hash
		},
		type: 'POST',
		url: ajax_url,
		success: function( data ) {
			console.log( data );
			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );
			console.log( json );

			if ( json.length === 0 ) {
				jQuery( '#empModal' ).modal( 'hide' );

				jQuery.alert( {
					content: 'No contents found.',
				} );
			} else {
				var button_data =
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

				var getIdFromContent = null;
				jQuery.each( json, function( index, item ) {

					if ( item ) {
						var ind_item             = ( item );
						const dateTimeStringFvDc = ind_item.created_at;
						const datePartFvDC       = dateTimeStringFvDc.split(" ")[0];
						const inputStringfvdc    = ind_item.content_type;

						// Step 1: Remove the hyphen and split the string into an array
						const wordsArrayfvdc = inputStringfvdc.split("-");

						// Step 2: Capitalize each word in the array
						const capitalizedWordsArrayfvdc = wordsArrayfvdc.map( ( word ) => {
							return word.charAt( 0 ).toUpperCase() + word.slice( 1 );
						} );

						// Step 3: Join the words back together to form the final string
						const resultStringFvDc = capitalizedWordsArrayfvdc.join("");

						if ( getIdFromContent == null ) {
							getIdFromContent = ind_item.id;
						}

						button_data +=
							'<tr class="text-white">' +
								'<td class="text-white">' +
									ind_item.content_name +
								'</td>' +
								'<td class="text-white">' +
									resultStringFvDc +
								'</td>' +
								'<td class="text-white">' +
									datePartFvDC +
								'</td>' +
								'<td class="text-white">';
									'<button class="btn btn-success btn-xs text-white download_dc_fv" style="padding: 1px 7px 1px 0px; font-size: 12px;"' +
										'id="dc_option" ' +
										'data-dltype="single" ' +
										'data-id="' + ind_item.id + '" ' +
										'onclick="grab_dc_product_hash( this );" ' +
										'onclick="grab_product_dowload_link_dc( this ); this.disabled=true;">' +
										'Download' +
									'</button>' +
								'</td>' +
							'</tr>';
					}
				} );

				button_data +=
						'</body>' +
					'</table>' +
					'<table class="table" style="color:#fff;margin-top:20px;">' +
						'<tr>' +
							'<td colspan>' +
								'<button class="btn btn-success btn-xs text-white download_dc_fv" style="width:100%;"' +
									'onclick="grab_dc_product_hash( this );" data-dltype="all_dl"' +
									'data-id="' + getIdFromContent + '">' +
									'Download all' +
								'</button>' +
							'</td>' +
						'</tr>' +
					'</table>';

				jQuery( '.modal-body' ).html( button_data );
				jQuery( '#empModal'   ).modal( 'show' );

				$( '.data_table998fv' ).DataTable( {
					"pageLength": 10
				} );
			}

			setTimeout( function() {
				fv_hide_loading_animation();
			}, 500 );
		}
	} ).done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

//demo contents product button web
function grab_dc_product_hash( d ) {
	console.log( 'running grab_dc_product_hash()' );

	fv_show_loading_animation()　

	var product_hash = ( d.getAttribute("data-id") );
	var data_dltype = ( d.getAttribute("data-dltype") );
	var product_mfile_id = '';

	jQuery(".cs-fp-follow-button").on( 'click',);

	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {action: 'fv_fs_plugin_dc_buttons_ajax', product_hash:product_hash, data_dltype:data_dltype},
		type: 'POST',
		url: ajax_url,
		success: function( data ) {
			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );
			console.log( json );
			if ( data_s == null || data_s.length == 5 ) {
				jQuery.alert( {
					content: 'No downloadable file is available for this item. Please try again later',
				} );
			} else {
				if ( json.result == 'invalid' ) {
					setTimeout( function() {
						fv_hide_loading_animation();
					}, 500 );

					if ( json.msg == 'Please login to download.' ) {
						window.location="/get-started/";
					} else {
						jQuery.alert( {
							content: json.msg,
						} );
					}
				}

				if ( data_s.length == 0 ) {
					jQuery.alert( {
						content: 'To enjoy this feature please activate your license.',
					} );
				} else {
					collectortdc( json );
				}
			}
		}
	} ).done( function() {
		setTimeout( function() {
			fv_hide_loading_animation();
		}, 500 );
	} );
}

function collectortdc( json ) {
	console.log( 'running collectortdc()' );

	var button_data = '<div class="row">';

	jQuery.each( json, function( index, item ) {
		if ( item ) {
				var ind_item = JSON.parse( item );
				button_data +=
					'<div class="col-md-6">' +
						'<div class="card mb-2 bg-light" style="min-width:100%;">' +
							'<div class="card-header">' +
								ind_item.plan_name +
							'</div>' +
							'<ul class="list-group list-group-flush">' +
								'<li class="list-group-item">Plan Type<b>: '    +
									ind_item.plan_type.toUpperCase() +
								'</b></li>' +
								'<li class="list-group-item">Plan Limit: '      +
									ind_item.plan_limit +
								'</li>' +
								'<li class="list-group-item">Available Limit: ' +
									ind_item.download_available +
								'</li>' +
							'</ul>' +
							'<div class="card-footer">' +
								'<button style="width:100%;" class="btn btn-sm btn-block card-btn" ' +
									'id="dc_option" ' +
									'onclick="grab_product_dowload_link_dc( this ); this.disabled=true;"' +
									'data-license="' + ind_item.license_key  + '" ' +
									'data-id="' +      ind_item.product_hash + '" ' +
									'data-dltype="' +  ind_item.dl_type      + '">' +
									'<i class="fa fa-arrow-down"></i>' +
									'Download from ' + ind_item.plan_type.toUpperCase() + ' plan' +
								'</button>' +
							'</div>' +
						'</div>' +
					'</div>';
			}
	} );
	button_data += '</div>';

	jQuery( '.modal-body' ).html( button_data );
	jQuery( '#empModal' ).modal( 'show' );
	setTimeout( function() {
		fv_hide_loading_animation();
	}, 500 );

}

function getFileNameFromURL( url ) {
	console.log( 'running getFileNameFromURL()' );

	// Get the part of the URL after the last '/'
	var filenameWithParams    = url.substring( url.lastIndexOf( '/' ) + 1 );
	// Remove query parameters from the filename ( everything after the '?' )
	var filenameWithoutParams = filenameWithParams.split( '?' )[0];
	// Extract the file name and extension
	var parts                 = filenameWithoutParams.split( '.' );
	var extension             = parts.pop();
	var filename              = parts.join( '.' );

	filenameFinal = filename + extension;

	console.log( filenameFinal );

	return filenameFinal;

	//return { filename: filename, extension: extension };
}

function downloadFileDC( url ) {
	console.log( 'running downloadFileDC()' );

	var xhr = new XMLHttpRequest();

	xhr.open( 'GET', url, true );
	xhr.responseType = 'blob';
	xhr.onload       = function() {
		if ( xhr.status === 200 ) {
			var blob        = xhr.response;
			var filename    = getFileNameFromURL( url ); // Use the getFileNameFromURL function to extract the filename
			var a           = document.createElement( 'a' );
			a.href          = window.URL.createObjectURL( blob );
			a.download      = filename;
			a.style.display = 'none';
			document.body.appendChild( a );
			a.click();
			window.URL.revokeObjectURL( a.href );
		}
	};

	xhr.send();
}

function grab_product_dowload_link_dc( d ) {
	console.log( 'running grab_product_dowload_link_dc()' );

	fv_show_loading_animation()　

	var plugin_download_hash = ( d.getAttribute("data-id") );
	var license_key          = ( d.getAttribute("data-license") );
	var download_type        = ( d.getAttribute("data-dltype") );
	// note: plugin_ajax_object is the wp_localized data passed from php
	var ajax_url             = plugin_ajax_object.ajax_url;

	jQuery.ajax( {
		data: {
			action: 'fv_fs_plugin_download_ajax_dc',
			plugin_download_hash: plugin_download_hash,
			license_key:          license_key,
			download_type:        download_type
		},
		type: 'POST',
		url: ajax_url,
		success: function( data ) {

			var data_s = data.slice( 0, -1 );
			var json = JSON.parse( data_s );

			console.log( json );

			if ( json.result == 'success' ) {

				jQuery( '#'+license_key+' #plan_limit_id' ).html(      json.plan_limit  );
				jQuery( '#'+license_key+' #current_limit_id' ).html(   json.download_current_limit  );
				jQuery( '#'+license_key+' #limit_available_id' ).html( json.download_available );

				if ( download_type == 'single' ) {
					downloadFileDC( json.link );
				} else {
					location.href = json.link;
				}
				jQuery( '#empModal' ).modal( 'hide' );
			} else {
				jQuery( '#empModal' ).modal( 'hide' );
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
	console.log( 'running fv_vault_reset_filters_value()' );
	jQuery("#ajax_search").val("");
	jQuery("#filter_allowence").val("all");
	jQuery("#filter_type").val("all");
	jQuery("#filter_category").val("all");
}

function fv_show_loading_animation() {
	console.log( 'running fv_show_loading_animation()' );
	jQuery("#overlay").fadeIn( 300 );
}

function fv_hide_loading_animation() {
	console.log( 'running fv_hide_loading_animation()' );
	jQuery("#overlay").fadeOut( 300 );
}

/**
 * Get number of columns depending on wrapper width.
 *
 * @param {string} wrapper css selector for the wrapper
 * @returns
 */
function fv_get_items_per_row( wrapper ) {
	console.log( 'running fv_get_items_per_row()' );

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
	console.log( 'running fv_is_active_theme()' );
	return fv_is_product_slug_found( slug, plugin_ajax_object.get_all_active_themes_js );
}
function fv_is_active_plugin( slug ) {
	console.log( 'running fv_is_active_plugin()' );
	return fv_is_product_slug_found( slug, plugin_ajax_object.get_all_active_plugins_js );
}
function fv_is_disabled_theme( slug ) {
	console.log( 'running fv_is_disabled_theme()' );
	return fv_is_product_slug_found( slug, plugin_ajax_object.get_all_inactive_themes_js );
}
function fv_is_disabled_plugin( slug ) {
	console.log( 'running fv_is_disabled_plugin()' );
	return fv_is_product_slug_found( slug, plugin_ajax_object.get_all_inactive_plugins_js );
}

function fv_get_allowence_img_html( value, dl_linkxyzzu ) {
	console.log( 'running fv_get_allowence_img_html()' );

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
	console.log( 'running fv_get_install_button_html_for_vault_product_card()' );

	// install button
	let html =
		'<button ' +
			'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn"' +
			'href="#" '   +
			'data-id="'   + f.unique_rand_md5 + '" ' +
			'onclick="grab_product_install_hash( this );" ' +
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
					'onclick="grab_product_install_hash( this );" ' +
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
					'onclick="grab_product_install_hash( this );" ' +
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
						'onclick="grab_product_install_hash( this );" ' +
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
					'onclick="grab_product_install_hash( this );" ' +
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
					'onclick="grab_product_install_hash( this );" ' +
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
					'onclick="grab_product_install_hash(this);"  ' +
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
	console.log( 'running fv_get_download_button_html_for_vault_product_card()' );

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
				'onclick="grab_product_hash( this );" ' +
				'><i class="fas fa-download"></i>' +
				'Download' +
			'</button>' +
		'</div>';

	return html;
}

function fv_get_sales_page_button_html_for_vault_product_card( f ) {
	console.log( 'running fv_get_sales_page_button_html_for_vault_product_card()' );

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
	console.log( 'running fv_get_request_update_button_html_for_vault_product_card()' );

	let html =
		'<div class="col-6 mt-1">' +
			'<button id="requestupdate" ' +
				'data-support-link="'   + f.support_link + '" ' +
				'data-product-hash="'   + f.unique_rand_md5 + '" ' +
				'data-generated-slug="' + f.new_generated_slug + '" ' +
				'data-generated-name="' + f.title + '" ' +
				'href="#" ' +
				'onclick="grab_product_support_link( this );" ' +
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
	console.log( 'running fv_get_support_button_html_for_vault_product_card()' );

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

function fv_get_report_item_button_html_for_vault_product_card( f, fv_white_label_is_active ) {
	console.log( 'running fv_get_report_item_button_html_for_vault_product_card()' );

	let html =
		'<div class="col-6 mt-1">' +
			'<button id="reportitem" ' +
				'data-support-link="'   + f.support_link + '" ' +
				'data-product-hash="'   + f.unique_rand_md5 + '" ' +
				'data-generated-slug="' + f.new_generated_slug + '" ' +
				'data-generated-name="' + f.title + '" ' +
				'href="#" ' +
				'onclick="grab_product_report_link( this );" ' +
				'class="btn btn-sm btn-block card-btn "' +
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
	console.log( 'running fv_get_add_to_bulk_button_html_for_vault_product_card()' );

	let html =
		'<div class="col-6 mt-1 mb-1">' +
			'<button type="button" style="font-size:12.6px;" ' +
				'data-id="' + f.unique_rand_md5 + '" ' +
				'data-itemname="' + f.title + '" ' +
				'onclick="fv_add_item_to_cart( this )" ' +
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
	console.log( 'running fv_get_virus_scan_button_html_for_vault_product_card()' );

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
	console.log( 'running fv_get_additional_content_button_html_for_vault_product_card()' );

	// install button
	let html =
		'<div class="col-12 mt-1">' +
			'<button id="optiondc" ' +
				'data-id="' + f.unique_rand_md5 + '" ' +
				'data-itemname="' + f.title + '" href="#" ' +
				'grab_dc_product_hash ' +
				'onclick="grab_dc_product_dcontents( this );" ' +
				'class="btn ' + fv_button_disabled + ' btn-sm btn-block card-btn">' +
				'<i class="fas fa-download"></i>' +
				'Additional Content' +
			'</button>' +
		'</div>';

	return html;
}

function fv_has_membership_allowance( f ) {
	console.log( 'running fv_has_membership_allowance()' );

	return ( null      !== f.membershipallowance
		&& 'undefined' !== typeof f.membershipallowance
		&& ''          !== f.membershipallowance );
}

function fv_get_vault_product_card_html( f ) {
	console.log( 'running fv_get_vault_product_card_html()' );

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
	fv_report_item_button_html        = fv_get_report_item_button_html_for_vault_product_card( f, fv_white_label_is_active );
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
