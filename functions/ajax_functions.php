<?php 

add_action('wp_ajax_fv_themes_autoupdate_switch', 'fv_themes_autoupdate_switch');
add_action('wp_ajax_nopriv_fv_themes_autoupdate_switch', 'fv_themes_autoupdate_switch');
function fv_themes_autoupdate_switch(){
	$theme_slug_capture = $_POST['theme_slug_capture'];
	$is_switch_yes = $_POST['theme_switch_is_checked'];
	if(!empty($theme_slug_capture) && ($is_switch_yes == true || $is_switch_yes == false)){
		if(get_option('fv_themes_auto_update_list') == true || empty(get_option('fv_themes_auto_update_list'))){
			$auto_update_theme_list = get_option('fv_themes_auto_update_list');

				if($is_switch_yes == true || $is_switch_yes == 'true'){
					$plugin_switch_status = 1;
				}

				if($is_switch_yes == false || $is_switch_yes == 'false'){

					$plugin_switch_status = 0;
				}
				
				$_ls_domain_sp_id_vf ='';
				$_data_ls_key_no_id_vf='';

				$_ls_domain_sp_id_vf_2 ='';
				$_data_ls_key_no_id_vf_2='';

				if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
					$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
					$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
			    }

				if(get_option('_data_ls_key_no_id_vf_2') && get_option('_ls_domain_sp_id_vf_2')){
					$_ls_domain_sp_id_vf_2 = get_option( '_ls_domain_sp_id_vf_2' );
					$_data_ls_key_no_id_vf_2 = get_option( '_data_ls_key_no_id_vf_2' );
			    }

					$api_params = array(
					    'license_key' => $_data_ls_key_no_id_vf,
					    'license_key_2' => $_data_ls_key_no_id_vf_2,
					    'license_d' => $_ls_domain_sp_id_vf,
					    'license_d_2' => $_ls_domain_sp_id_vf_2,
					    'captured_slug' => $theme_slug_capture,
					    'slug_type' => 'theme',
					    'action_status'=>$plugin_switch_status,
					    'license_pp' => $_SERVER['REMOTE_ADDR'],
					    'license_host'=> $_SERVER['HTTP_HOST'],
					    'license_mode'=> 'captured_slug_st',
					    'license_v'=>FV_PLUGIN_VERSION,
					);

					$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'recurring-slug-cap'));
					$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
					
					if (is_wp_error($response)){
						$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
						if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';							
						}
					}

					$slug_res = json_decode(wp_remote_retrieve_body($response));

					if(get_option('fv_themes_auto_update_list') != true){
						add_option('fv_themes_auto_update_list', []);
					}
					
					if($slug_res->status == 'na'){



						if (($key = array_search($theme_slug_capture, $auto_update_theme_list)) !== false) {
						    if($is_switch_yes == false || $is_switch_yes == 'false'){
						    	unset($auto_update_theme_list[$key]);
						    	update_option('fv_themes_auto_update_list', $auto_update_theme_list);
						    }
						}else{
						    if($is_switch_yes == true || $is_switch_yes == 'true'){
								array_push($auto_update_theme_list, $theme_slug_capture);
								update_option('fv_themes_auto_update_list', $auto_update_theme_list);
							}
						}


					}elseif($slug_res->status == 'inserted'){



						if (($key = array_search($theme_slug_capture, $auto_update_theme_list)) !== false) {
						    if($is_switch_yes == false || $is_switch_yes == 'false'){
						    	unset($auto_update_theme_list[$key]);
						    	update_option('fv_themes_auto_update_list', $auto_update_theme_list);
						    }
						}else{
						    if($is_switch_yes == true || $is_switch_yes == 'true'){
								array_push($auto_update_theme_list, $theme_slug_capture);
								update_option('fv_themes_auto_update_list', $auto_update_theme_list);
							}
						}


					}elseif($slug_res->status == 'updated'){



						if (($key = array_search($theme_slug_capture, $auto_update_theme_list)) !== false) {
						    if($is_switch_yes == false || $is_switch_yes == 'false'){
						    	unset($auto_update_theme_list[$key]);
						    	update_option('fv_themes_auto_update_list', $auto_update_theme_list);
						    }
						}else{
						    if($is_switch_yes == true || $is_switch_yes == 'true'){
								array_push($auto_update_theme_list, $theme_slug_capture);
								update_option('fv_themes_auto_update_list', $auto_update_theme_list);
							}
						}


					}

		}
	}
	echo json_encode($slug_res);
}


add_action('wp_ajax_fv_plugin_autoupdate_switch', 'fv_plugin_autoupdate_switch');
add_action('wp_ajax_nopriv_fv_plugin_autoupdate_switch', 'fv_plugin_autoupdate_switch');
function fv_plugin_autoupdate_switch(){
	$plugin_slug_capture = $_POST['plugin_slug_capture'];
	$is_plugin_switch_yes = $_POST['plugin_switch_is_checked'];
	if(!empty($plugin_slug_capture) && ($is_plugin_switch_yes == true || $is_plugin_switch_yes == false)){
		if(get_option('fv_plugin_auto_update_list') == true || empty(get_option('fv_plugin_auto_update_list'))){


			$auto_update_plugin_list = get_option('fv_plugin_auto_update_list');


				if(get_option('fv_plugin_auto_update_list') != true){
					add_option('fv_plugin_auto_update_list', []);
				}

				if($is_plugin_switch_yes == true || $is_plugin_switch_yes == 'true'){
					$plugin_switch_status = 1;
				}

				if($is_plugin_switch_yes == false || $is_plugin_switch_yes == 'false'){

					$plugin_switch_status = 0;
				}

				
				$_ls_domain_sp_id_vf ='';
				$_data_ls_key_no_id_vf='';

				$_ls_domain_sp_id_vf_2 ='';
				$_data_ls_key_no_id_vf_2='';

				if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
					$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
					$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
			    }

				if(get_option('_data_ls_key_no_id_vf_2') && get_option('_ls_domain_sp_id_vf_2')){
					$_ls_domain_sp_id_vf_2 = get_option( '_ls_domain_sp_id_vf_2' );
					$_data_ls_key_no_id_vf_2 = get_option( '_data_ls_key_no_id_vf_2' );
			    }

					$api_params = array(
					    'license_key' => $_data_ls_key_no_id_vf,
					    'license_key_2' => $_data_ls_key_no_id_vf_2,
					    'license_d' => $_ls_domain_sp_id_vf,
					    'license_d_2' => $_ls_domain_sp_id_vf_2,
					    'captured_slug' => $plugin_slug_capture,
					    'slug_type' => 'plugin',
					    'action_status'=>$plugin_switch_status,
					    'license_pp' => $_SERVER['REMOTE_ADDR'],
					    'license_host'=> $_SERVER['HTTP_HOST'],
					    'license_mode'=> 'captured_slug_st',
					    'license_v'=>FV_PLUGIN_VERSION,
					);

					$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'recurring-slug-cap'));
					
			
				    $response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
					
					if (is_wp_error($response)){
						$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
						if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';							
						}
					}


					$slug_res = json_decode(wp_remote_retrieve_body($response));


					if($slug_res->status == 'na'){



						if (($key = array_search($plugin_slug_capture, $auto_update_plugin_list)) !== false) {
						    if($is_plugin_switch_yes == false || $is_plugin_switch_yes == 'false'){
						    	unset($auto_update_plugin_list[$key]);
						    	update_option('fv_plugin_auto_update_list', $auto_update_plugin_list);
						    }
						}else{
						    if($is_plugin_switch_yes == true || $is_plugin_switch_yes == 'true'){
								array_push($auto_update_plugin_list, $plugin_slug_capture);
								update_option('fv_plugin_auto_update_list', $auto_update_plugin_list);
							}
						}


					}elseif($slug_res->status == 'inserted'){

						if (($key = array_search($plugin_slug_capture, $auto_update_plugin_list)) !== false) {
						    if($is_plugin_switch_yes == false || $is_plugin_switch_yes == 'false'){
						    	unset($auto_update_plugin_list[$key]);
						    	update_option('fv_plugin_auto_update_list', $auto_update_plugin_list);
						    }
						}else{
						    if($is_plugin_switch_yes == true || $is_plugin_switch_yes == 'true'){
								array_push($auto_update_plugin_list, $plugin_slug_capture);
								update_option('fv_plugin_auto_update_list', $auto_update_plugin_list);
							}
						}

					}elseif($slug_res->status == 'updated'){

						if (($key = array_search($plugin_slug_capture, $auto_update_plugin_list)) !== false) {
						    if($is_plugin_switch_yes == false || $is_plugin_switch_yes == 'false'){
						    	unset($auto_update_plugin_list[$key]);
						    	update_option('fv_plugin_auto_update_list', $auto_update_plugin_list);
						    }
						}else{
						    if($is_plugin_switch_yes == true || $is_plugin_switch_yes == 'true'){
								array_push($auto_update_plugin_list, $plugin_slug_capture);
								update_option('fv_plugin_auto_update_list', $auto_update_plugin_list);
							}
						}


					}

		}
	}
	echo json_encode($slug_res);
}









/*
	By clicking download button
	Modal will pop up and fetch download buttons
	Based on licenses
*/
add_action('wp_ajax_fv_plugin_buttons_ajax_multiple', 'fv_plugin_buttons_ajax_multiple');
add_action('wp_ajax_nopriv_fv_plugin_buttons_ajax_multiple', 'fv_plugin_buttons_ajax_multiple');

function fv_plugin_buttons_ajax_multiple(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }

	$api_params = array(
		'license_key' => $_data_ls_key_no_id_vf,
	    'license_d' => $_ls_domain_sp_id_vf,
	    'license_pp' => $_SERVER['REMOTE_ADDR'],
	    'license_host'=> $_SERVER['HTTP_HOST'],
	    'license_mode'=> 'buttons_multiple',
	    'license_v'=>FV_PLUGIN_VERSION,
	    'product_hash'=> $_POST['product_hash'],
	);

	$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'get-pro-buttons-multiple'));	
    $response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
	
	if (is_wp_error($response)){
		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
		if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';			
		}
	}

	$license_data = json_decode(wp_remote_retrieve_body($response));
	echo json_encode($license_data);

} // end of show_download_buttons









/*
	By clicking download button
	Modal will pop up and fetch download buttons
	Based on licenses
*/
add_action('wp_ajax_fv_plugin_buttons_ajax', 'fv_plugin_buttons_ajax');
add_action('wp_ajax_nopriv_fv_plugin_buttons_ajax', 'fv_plugin_buttons_ajax');

function fv_plugin_buttons_ajax(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }

	$api_params = array(
		'license_key' => $_data_ls_key_no_id_vf,
	    'license_d' => $_ls_domain_sp_id_vf,
	    'license_pp' => $_SERVER['REMOTE_ADDR'],
	    'license_host'=> $_SERVER['HTTP_HOST'],
	    'license_mode'=> 'buttons',
	    'license_v'=>FV_PLUGIN_VERSION,
	    'product_hash'=> $_POST['product_hash'],
	);

	$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'get-pro-buttons'));	
    $response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
	
	if (is_wp_error($response)){
		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
		if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';			
		}
	}

	$license_data = json_decode(wp_remote_retrieve_body($response));
	echo json_encode($license_data);

} // end of show_download_buttons


/*
	By clicking download button
	Modal will pop up and fetch download buttons
	Based on licenses
*/
add_action('wp_ajax_fv_plugin_support_link', 'fv_plugin_support_link');
add_action('wp_ajax_nopriv_fv_plugin_support_link', 'fv_plugin_support_link');

function fv_plugin_support_link(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }

    $genData = null;

		//fv_plugin_support_link
		if(!empty($_data_ls_key_no_id_vf)){
			$genData = json_encode([
				'result' => 'success',
				'license_key' => $_data_ls_key_no_id_vf,
				'data_support_link' => $_POST['data_support_link'],
				'data_generated_slug' => $_POST['data_generated_slug'],
				'data_product_hash' => $_POST['data_product_hash'],
				'data_generated_name' => $_POST['data_generated_name'],
				
			]);
		}else{
			

			// Define the URL you want to redirect to
			$redirect_url = $_POST['data_support_link'];

			// Use wp_redirect() to redirect the user to the external URL
			wp_redirect( $redirect_url );

			$genData = json_encode([
				'result' => 'redirect',
				'license_key' => $_data_ls_key_no_id_vf,
			]);
			// Make sure to exit after calling wp_redirect()
			exit;

		}

	//$license_data = json_decode(wp_remote_retrieve_body($response));
	echo $genData;

} // end of show_download_buttons




/*
	By clicking download button
	Modal will pop up and fetch download buttons
	Based on licenses
*/
add_action('wp_ajax_fv_plugin_report_link', 'fv_plugin_report_link');
add_action('wp_ajax_nopriv_fv_plugin_report_link', 'fv_plugin_report_link');

function fv_plugin_report_link(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }

    $genData = null;

		//fv_plugin_report_link
		if(!empty($_data_ls_key_no_id_vf)){
			$genData = json_encode([
				'result' => 'success',
				'license_key' => $_data_ls_key_no_id_vf,
				'data_support_link' => $_POST['data_support_link'],
				'data_generated_slug' => $_POST['data_generated_slug'],
				'data_product_hash' => $_POST['data_product_hash'],
				'data_generated_name' => $_POST['data_generated_name'],
				
			]);
		}else{
			

			// Define the URL you want to redirect to
			$redirect_url = $_POST['data_support_link'];

			// Use wp_redirect() to redirect the user to the external URL
			wp_redirect( $redirect_url );

			$genData = json_encode([
				'result' => 'redirect',
				'license_key' => $_data_ls_key_no_id_vf,
			]);
			// Make sure to exit after calling wp_redirect()
			exit;

		}

	//$license_data = json_decode(wp_remote_retrieve_body($response));
	echo $genData;

} // end of show_download_buttons







function request_data_activation_process($params){
	$query = esc_url_raw(add_query_arg($params, YOUR_LICENSE_SERVER_URL.'request-data'));
	$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));

	if (is_wp_error($response)){
		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
		if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';			
		}
	}

}




add_action('wp_ajax_fv_plugin_download_ajax', 'fv_plugin_download_ajax');
add_action('wp_ajax_nopriv_fv_plugin_download_ajax', 'fv_plugin_download_ajax');

function fv_plugin_download_ajax(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }

	$api_params = array(
	    'license_d' => $_ls_domain_sp_id_vf,
	    'license_pp' => $_SERVER['REMOTE_ADDR'],
	    'license_host'=> $_SERVER['HTTP_HOST'],
	    'license_mode'=> 'download',
	    'license_v'=>FV_PLUGIN_VERSION,
	    'plugin_download_hash'=> $_POST['plugin_download_hash'],
	    'license_key'=> $_POST['license_key'],
	    'mfile'=> $_POST['mfile'],

	);

	$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'plugin-download'));
	
    $response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
	
	// Check for error in the response
	if (is_wp_error($response)){
		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
		if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';			
		}
	}

	$license_data = json_decode(wp_remote_retrieve_body($response));
	if($license_data->result == 'success'){
		request_data_activation_process(['ld_tm'=>$license_data->ld_tm, 'ld_type' => 'download', 'l_dat'=>$license_data->license_key, 'ld_dat'=>$_SERVER['HTTP_HOST'], 'rm_ip' => $_SERVER['REMOTE_ADDR'], 'status'=>$license_data->result, 'req_time'=>time(), 'res'=>'1']);
	}else{
	request_data_activation_process(['ld_tm'=>$license_data->ld_tm, 'ld_type' => 'download', 'l_dat'=>$license_data->license_key, 'ld_dat'=>$_SERVER['HTTP_HOST'], 'rm_ip' => $_SERVER['REMOTE_ADDR'], 'status'=>$license_data->msg, 'req_time'=>time(), 'res'=>'0']);
	}

	
	
	echo json_encode($license_data);



}




add_action('wp_ajax_fv_plugin_download_ajax_bundle', 'fv_plugin_download_ajax_bundle');
add_action('wp_ajax_nopriv_fv_plugin_download_ajax_bundle', 'fv_plugin_download_ajax_bundle');

function fv_plugin_download_ajax_bundle(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }

	$api_params = array(
	    'license_d' => $_ls_domain_sp_id_vf,
	    'license_pp' => $_SERVER['REMOTE_ADDR'],
	    'license_host'=> $_SERVER['HTTP_HOST'],
	    'license_mode'=> 'download_bundle',
	    'license_v'=>FV_PLUGIN_VERSION,
	    'plugin_download_hash'=> $_POST['plugin_download_hash'],
	    'license_key'=> $_POST['license_key'],
	    'mfile'=> $_POST['mfile'],

	);

	$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'plugin-download-multiple'));
	
    $response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
	
	// Check for error in the response
	if (is_wp_error($response)){
		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
		if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';			
		}
	}

	$license_data = json_decode(wp_remote_retrieve_body($response));
	if($license_data->result == 'success'){
		request_data_activation_process(['ld_tm'=>$license_data->ld_tm, 'ld_type' => 'download', 'l_dat'=>$license_data->license_key, 'ld_dat'=>$_SERVER['HTTP_HOST'], 'rm_ip' => $_SERVER['REMOTE_ADDR'], 'status'=>$license_data->result, 'req_time'=>time(), 'res'=>'1']);
	}else{
	request_data_activation_process(['ld_tm'=>$license_data->ld_tm, 'ld_type' => 'download', 'l_dat'=>$license_data->license_key, 'ld_dat'=>$_SERVER['HTTP_HOST'], 'rm_ip' => $_SERVER['REMOTE_ADDR'], 'status'=>$license_data->msg, 'req_time'=>time(), 'res'=>'0']);
	}

	


	
	
	echo json_encode($license_data);



}




add_action('wp_ajax_fv_plugin_install_bulk_ajax', 'fv_plugin_install_bulk_ajax');
add_action('wp_ajax_nopriv_fv_plugin_install_bulk_ajax', 'fv_plugin_install_bulk_ajax');

function fv_plugin_install_bulk_ajax(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }



	$api_params = array(
	    'license_d' => $_ls_domain_sp_id_vf,
	    'license_pp' => $_SERVER['REMOTE_ADDR'],
	    'license_host'=> $_SERVER['HTTP_HOST'],
	    'license_mode'=> 'install_bundle',
	    'license_v'=>FV_PLUGIN_VERSION,
	    'plugin_download_hash'=> $_POST['plugin_download_hash'],
	    'license_key'=> $_POST['license_key'],
	    'mfile'=> $_POST['mfile'],

	);

	$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'plugin-download-multiple'));
    $response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
	
	if (is_wp_error($response)){
		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
		if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';			
		}
	}

	$license_data = json_decode(wp_remote_retrieve_body($response));
	$processed_data = json_encode($license_data);

	$chk_any = 0;


//bulk install start 

				WP_Filesystem(); 

$directory_path = WP_CONTENT_DIR . '/custom-directory'; // Replace "custom-directory" with your desired directory name.


$upload_dir      = wp_upload_dir();
$fv_plugin_zip_upload_bulk_dir=$upload_dir["basedir"]."/fv_bulk_install_dir/";
$fv_plugin_zip_upload_bulk_dir_extract=$upload_dir["basedir"]."/fv_bulk_install_dir_extract/";


if ( ! file_exists( $fv_plugin_zip_upload_bulk_dir ) ) {
    wp_mkdir_p( $fv_plugin_zip_upload_bulk_dir );
}

if ( ! file_exists( $fv_plugin_zip_upload_bulk_dir_extract ) ) {
    wp_mkdir_p( $fv_plugin_zip_upload_bulk_dir_extract );
}

// get the file zip 
// unzip it 
//run the loop 
//for theme install theme 
//for plugin install plugin 

$return_data = [];



                $pathInfo=pathinfo($license_data->config->content_slug);
                $fileName=$pathInfo['filename'].'.zip';
                

                $tmpfile = download_url( $license_data->links, $timeout = 300 );

				if(is_wp_error($tmpfile) == true){

					// Initialize the cURL session
					$ch = curl_init($license_data->links);
					  
					$file_name = basename($license_data->links);
					  
					// Save file into file location
					$save_file_loc = $fv_plugin_zip_upload_bulk_dir.$fileName;
					  
					// Open file 
					$fp = fopen($save_file_loc, 'wb');
					  
					// It set an option for a cURL transfer
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					  
					// Perform a cURL session
					curl_exec($ch);
					  
					// Closes a cURL session and frees all resources
					curl_close($ch);
					  
					// Close file
					fclose($fp);
					  


				}else{
				    copy( $tmpfile, $fv_plugin_zip_upload_bulk_dir.$fileName );
				    //unlink($tmpfile);
				}

/*
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
		        if($ext=='zip'){
					
		            $basename=pathinfo($fileName,  PATHINFO_BASENAME);
		
		            $un= unzip_file($fv_plugin_zip_upload_bulk_dir.$basename,$fv_plugin_zip_upload_bulk_dir_extract);
		            if(!is_wp_error($un)){
		              //  unlink($fv_plugin_zip_upload_bulk_dir.$basename);
		            }
		        }
*/
	//$fv_plugin_zip_upload_bulk_dir = 'path/to/zip/file';
//$fv_plugin_zip_upload_bulk_dir_extract = 'path/to/extract/files';
$zip = new ZipArchive;
$res = $zip->open($fv_plugin_zip_upload_bulk_dir.$fileName);


if ($res === TRUE) {

    $zip->extractTo($fv_plugin_zip_upload_bulk_dir_extract);
    $zip->close();
    $files = scandir($fv_plugin_zip_upload_bulk_dir_extract);

    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
            $package = $fv_plugin_zip_upload_bulk_dir_extract . '/' . $file;
           // $return_data[] = $file;
            $type = '';
            if (strpos($file, '___theme___') !== false) {
                $type = 'theme';
            } else if (strpos($file, '___plugin___') !== false) {
                $type = 'plugin';
            }
            if (!empty($type)) {
                if ($type === 'theme') {
                    $un= unzip_file($package,get_theme_root());
                } else if ($type === 'plugin') {
                    $un= unzip_file($package,WP_PLUGIN_DIR);
                }
                unlink($package);
            }
        }
    }
}



//bulk install  end


	if($license_data->config->result == 'success'){
		request_data_activation_process(['ld_tm'=>$license_data->config->ld_tm, 'ld_type' => 'download', 'l_dat'=>$license_data->config->license_key, 'ld_dat'=>$_SERVER['HTTP_HOST'], 'rm_ip' => $_SERVER['REMOTE_ADDR'], 'status'=>$license_data->config->result, 'req_time'=>time(), 'res'=>'1']);
	}else{
	request_data_activation_process(['ld_tm'=>$license_data->config->ld_tm, 'ld_type' => 'download', 'l_dat'=>$license_data->config->license_key, 'ld_dat'=>$_SERVER['HTTP_HOST'], 'rm_ip' => $_SERVER['REMOTE_ADDR'], 'status'=>$license_data->config->msg, 'req_time'=>time(), 'res'=>'0']);
	}

	


	echo json_encode($license_data);




}











add_action('wp_ajax_fv_plugin_install_button_modal_generate', 'fv_plugin_install_button_modal_generate');
add_action('wp_ajax_nopriv_fv_plugin_install_button_modal_generate', 'fv_plugin_install_button_modal_generate');

function fv_plugin_install_button_modal_generate(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }

	$api_params = array(
		'license_key' => $_data_ls_key_no_id_vf,
	    'license_d' => $_ls_domain_sp_id_vf,
	    'license_pp' => $_SERVER['REMOTE_ADDR'],
	    'license_host'=> $_SERVER['HTTP_HOST'],
	    'license_mode'=> 'buttons',
	    'license_v'=>FV_PLUGIN_VERSION,
	    'product_hash'=> $_POST['product_hash'],
	);

	$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'get-pro-buttons'));
    $response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
	
	if (is_wp_error($response)){
		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
		if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';			
		}
	}

	$license_data = json_decode(wp_remote_retrieve_body($response));

	echo json_encode($license_data);



} // end of show_download_buttons


add_action('wp_ajax_fv_plugin_install_ajax', 'fv_plugin_install_ajax');
add_action('wp_ajax_nopriv_fv_plugin_install_ajax', 'fv_plugin_install_ajax');

function fv_plugin_install_ajax(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }


	$api_params = array(
	    'license_d' => $_ls_domain_sp_id_vf,
	    'license_pp' => $_SERVER['REMOTE_ADDR'],
	    'license_host'=> $_SERVER['HTTP_HOST'],
	    'license_mode'=> 'install',
	    'license_v'=>FV_PLUGIN_VERSION,
	    'plugin_download_hash'=> $_POST['plugin_download_hash'],
	    'license_key'=> $_POST['license_key'],
	    'mfile'=> $_POST['mfile'],

	);

	$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'plugin-download'));
    $response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
	
	if (is_wp_error($response)){
		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
		if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';			
		}
	}

	$license_data = json_decode(wp_remote_retrieve_body($response));
	$processed_data = json_encode($license_data);

	$chk_any = 0;


            if($license_data->result == 'success' && $license_data->content_type == 'plugin' && !empty($license_data->content_slug) && !empty($license_data->link)){


				WP_Filesystem(); 

                $pathInfo=pathinfo($license_data->content_slug);
                $fileName=$pathInfo['filename'].'.zip';
                
                $upload_dir      = wp_upload_dir();
                $fv_plugin_zip_upload_dir=$upload_dir["basedir"]."/fv_auto_update_directory/plugins/";

                $tmpfile = download_url( $license_data->link, $timeout = 300 );

				if(is_wp_error($tmpfile) == true){

					// Initialize the cURL session
					$ch = curl_init($license_data->link);
					  
					$file_name = basename($license_data->link);
					  
					// Save file into file location
					$save_file_loc = $fv_plugin_zip_upload_dir.$fileName;
					  
					// Open file 
					$fp = fopen($save_file_loc, 'wb');
					  
					// It set an option for a cURL transfer
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					  
					// Perform a cURL session
					curl_exec($ch);
					  
					// Closes a cURL session and frees all resources
					curl_close($ch);
					  
					// Close file
					fclose($fp);
					  


				}else{
				    copy( $tmpfile, $fv_plugin_zip_upload_dir.$fileName );
				    unlink($tmpfile);
				}


                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
		        if($ext=='zip'){
					
		            $basename=pathinfo($fileName,  PATHINFO_BASENAME);
		
		            $un= unzip_file($fv_plugin_zip_upload_dir.$basename,WP_PLUGIN_DIR);
		            if(!is_wp_error($un)){
		                unlink($fv_plugin_zip_upload_dir.$basename);
		            }

					$chk_any = 1;

				
			
					$final_success_data = [
						'result' => 'success',
						'slug'=> $license_data->content_slug,
						'content_type'=> $license_data->content_type,
						'link'=> $license_data->link,
						'activation'=> admin_url('admin.php?page=festinger-vault&actionrun=activation&activeslug='.$license_data->content_slug),
						'plan_limit' => $license_data->plan_limit,
						'download_current_limit' => $license_data->download_current_limit,
						'download_available' => $license_data->download_available,
						
					];
					echo json_encode($final_success_data);
		        }

				
            }




            if($license_data->result == 'success' && $license_data->content_type == 'theme' && !empty($license_data->content_slug) && !empty($license_data->link)){



				WP_Filesystem(); 

                $pathInfo=pathinfo($license_data->content_slug);
                $fileName=$pathInfo['filename'].'.zip';
               
                $upload_dir      = wp_upload_dir();
                $fv_theme_zip_upload_dir=$upload_dir["basedir"]."/fv_auto_update_directory/themes/";

                $tmpfile = download_url( $license_data->link, $timeout = 300 );

				if(is_wp_error($tmpfile) == true){

					// Initialize the cURL session
					$ch = curl_init($license_data->link);
					  
					// Use basename() function to return
					// the base name of file 
					$file_name = basename($license_data->link);
					  
					// Save file into file location
					$save_file_loc = $fv_theme_zip_upload_dir.$fileName;
					  
					// Open file 
					$fp = fopen($save_file_loc, 'wb');
					  
					// It set an option for a cURL transfer
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					  
					// Perform a cURL session
					curl_exec($ch);
					  
					// Closes a cURL session and frees all resources
					curl_close($ch);
					  
					// Close file
					fclose($fp);

				}else{
				    copy( $tmpfile, $fv_theme_zip_upload_dir.$fileName );
				    unlink($tmpfile);
				}



                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
		        if($ext=='zip'){
		            $basename=pathinfo($fileName,  PATHINFO_BASENAME);
		            $un= unzip_file($fv_theme_zip_upload_dir.'/'.$basename,get_theme_root());
	
					
		            if(!is_wp_error($un)){
		                unlink($fv_theme_zip_upload_dir.'/'.$basename);
		            }

		        }


					$chk_any = 1;

					$final_success_data = [
						'result' => 'success',
						'finelame'=>$fileName,
						'slug'=> $license_data->content_slug,
						'link'=> 'theme',
						'theme_preview'=> admin_url('themes.php?theme='.$license_data->content_slug),
						'plan_limit' => $license_data->plan_limit,
						'download_current_limit' => $license_data->download_current_limit,
						'download_available' => $license_data->download_available,
						
					];
					echo json_encode($final_success_data);

            }// end foreach





            if($chk_any == 0){

				$msg_data = isset($license_data->msg) ? $license_data->msg : 'Something went wrong';
				$final_failed_data = [
					'result' => 'failed',
					'msg'=> $msg_data,
					
				];
				echo json_encode($final_failed_data);
            }


	if($license_data->result == 'success'){
		request_data_activation_process(['ld_tm'=>$license_data->ld_tm, 'ld_type' => 'install', 'l_dat'=>$license_data->license_key, 'ld_dat'=>$_SERVER['HTTP_HOST'], 'rm_ip' => $_SERVER['REMOTE_ADDR'], 'status'=>$license_data->result, 'req_time'=>time(), 'res'=>'1']);
	}else{
	request_data_activation_process(['ld_tm'=>$license_data->ld_tm, 'ld_type' => 'install', 'l_dat'=>$license_data->license_key, 'ld_dat'=>$_SERVER['HTTP_HOST'], 'rm_ip' => $_SERVER['REMOTE_ADDR'], 'status'=>$license_data->msg, 'req_time'=>time(), 'res'=>'0']);
	}



}











function get_plugins_and_themes_matched_by_vault($plugin_theme, $get_slug){


    $retrive_plugins_data=[];
    $retrive_themes_data=[];
    $all_plugins = get_plugins();

    if(!empty($all_plugins)){

        foreach ($all_plugins as $plugin_slug=>$values){
            $slugArray=explode('/',$plugin_slug);

            $version=getPluginVersionFromRepository($values['Version']);
            $slug=get_plugin_slug_from_data($plugin_slug, $values);
            $retrive_plugins_data[]=['slug'=>$slug,'version'=>$version, 'dl_link'=>''];

        }
    }


    $allThemes = wp_get_themes();
    foreach($allThemes as $theme) {
    	$get_theme_slug = $theme->get('TextDomain');
    	if(empty($get_theme_slug)){
    		$get_theme_slug = $theme->template;
    	}
        $retrive_themes_data[]=['slug'=>$get_theme_slug,'version'=>$theme->Version, 'dl_link'=>''];
    }



	
	$_ls_domain_sp_id_vf ='';
	$_data_ls_key_no_id_vf='';

	$_ls_domain_sp_id_vf_2 ='';
	$_data_ls_key_no_id_vf_2='';

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }

	if(get_option('_data_ls_key_no_id_vf_2') && get_option('_ls_domain_sp_id_vf_2')){
		$_ls_domain_sp_id_vf_2 = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf_2 = get_option( '_data_ls_key_no_id_vf_2' );
    }





	if( (!empty( $_ls_domain_sp_id_vf ) && !empty( $_data_ls_key_no_id_vf )) || (!empty( $_ls_domain_sp_id_vf_2 ) && !empty( $_data_ls_key_no_id_vf_2 )) ){

		$api_params = array(
		    'license_key' => $_data_ls_key_no_id_vf,
		    'license_key_2' => $_data_ls_key_no_id_vf_2,
		    'license_d' => $_ls_domain_sp_id_vf,
		    'license_d_2' => $_ls_domain_sp_id_vf_2,
		    'all_plugin_list' => $retrive_plugins_data,
		    'all_theme_list' => $retrive_themes_data,
		    'license_pp' => $_SERVER['REMOTE_ADDR'],
		    'license_host'=> $_SERVER['HTTP_HOST'],
		    'license_mode'=> 'get_plugins_and_themes_matched_by_vault',
		    'license_v'=>FV_PLUGIN_VERSION,
		);

		$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'plugin-theme-updater'));
		
	    $response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
		
		if (is_wp_error($response)){
			$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
			if(is_wp_error($response)){
				echo 'SSLVERIFY ERROR';				
			}
		}

		$license_histories = json_decode(wp_remote_retrieve_body($response));

		if($plugin_theme == 'plugin' && !empty($slug)){

			foreach($license_histories->plugins as $plugin){
				if($slug == $plugin->slug){
					echo 1;
				}
			}
			
			echo 0;

		}



    }

}







add_action('wp_ajax_fv_discourse_post_new_version', 'fv_discourse_post_new_version');
add_action('wp_ajax_nopriv_fv_discourse_post_new_version', 'fv_discourse_post_new_version');

function fv_discourse_post_new_version(){


	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }

    //commentdata = "Please update ".$_POST['data_generated_name']." to ".$_POST['versionNumber']." @FestingerUpdates";

$api_params_new = [
	'plugin_name' => $_POST['data_generated_name'],
	'comment' => $_POST['versionNumber'],
	'postid' => $_POST['lastNumericValue'],
	'title' => $_POST['data_generated_name'],
	'license_key' => $_data_ls_key_no_id_vf,
];


		$query = esc_url_raw(add_query_arg($api_params_new, YOUR_LICENSE_SERVER_URL.'discourse-input'));



		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
		if (is_wp_error($response)){
			$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
			if(is_wp_error($response)){
					echo 'SSLVERIFY ERROR';			
			}
		}
		$license_histories = json_decode(wp_remote_retrieve_body($response));

	echo json_encode($license_histories);



} // end of show_download_buttons







add_action('wp_ajax_fv_discourse_post_new_report', 'fv_discourse_post_new_report');
add_action('wp_ajax_nopriv_fv_discourse_post_new_report', 'fv_discourse_post_new_report');

function fv_discourse_post_new_report(){


	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }

    //commentdata = "Please update ".$_POST['data_generated_name']." to ".$_POST['versionNumber']." @FestingerUpdates";

	$api_params_new = [
		'plugin_name' => $_POST['data_generated_name'],
		'comment' => $_POST['versionNumber'],
		'postid' => $_POST['lastNumericValue'],
		'title' => $_POST['data_generated_name'],
		'license_key' => $_data_ls_key_no_id_vf,
	];


		$query = esc_url_raw(add_query_arg($api_params_new, YOUR_LICENSE_SERVER_URL.'discourse-report'));



		$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => false));
		if (is_wp_error($response)){
			$response = wp_remote_post($query, array('timeout' => 200, 'sslverify' => true));
			if(is_wp_error($response)){
					echo 'SSLVERIFY ERROR';			
			}
		}
		$license_histories = json_decode(wp_remote_retrieve_body($response));

	echo json_encode($license_histories);



} // end of show_download_buttons






/*
	By clicking download button
	Modal will pop up and fetch demo contents download buttons
	Based on licenses
*/


add_action('wp_ajax_fv_fs_plugin_dc_buttons_ajax', 'fv_fs_plugin_dc_buttons_ajax');
add_action('wp_ajax_nopriv_fv_fs_plugin_dc_buttons_ajax', 'fv_fs_plugin_dc_buttons_ajax');

function fv_fs_plugin_dc_buttons_ajax(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }


	
	$api_params = array(
	    'product_hash'=> $_POST['product_hash'],
	    'data_dltype'=> $_POST['data_dltype'],
	    'license_host'=> $_SERVER['HTTP_HOST'],
	    'license_mode'=> 'dc_buttons_web_fv',
	    'user_id'=> $_data_ls_key_no_id_vf,
	);

	$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'get-pro-dc-buttons-web'));	
    $response = wp_remote_post($query, array('timeout' => 20, 'sslverify' => false));
	
	if (is_wp_error($response)){
	    echo "Unexpected Error! The query returned with an error.";
	}

	$license_data = json_decode(wp_remote_retrieve_body($response));
	echo json_encode($license_data);

} // end of show_download_buttons




add_action('wp_ajax_fv_fs_plugin_dc_contents_ajax', 'fv_fs_plugin_dc_contents_ajax');
add_action('wp_ajax_nopriv_fv_fs_plugin_dc_contents_ajax', 'fv_fs_plugin_dc_contents_ajax');




function fv_fs_plugin_dc_contents_ajax(){

	$fv_fs_api_params_demo_contents = array(
		'theme_plugin_id' => $_POST['product_hash'],
		'license_host'=> $_SERVER['HTTP_HOST'],
		'license_mode'=> 'first_server_return_demo_contents_fv',
	);

	$fv_fs_query_dc = esc_url_raw(add_query_arg($fv_fs_api_params_demo_contents, YOUR_LICENSE_SERVER_URL.'first-server-demo-contents-data-get'));


	$fv_fsresponse_dc = wp_remote_post($fv_fs_query_dc, array('timeout' => 20, 'sslverify' => false));

	if (is_wp_error($fv_fsresponse_dc)){
		echo "Unexpected Error! The query returned with an error.";
	}

	$fv_fs_all_demo_contents_data = json_decode(wp_remote_retrieve_body($fv_fsresponse_dc));

	echo json_encode($fv_fs_all_demo_contents_data);




}






add_action('wp_ajax_fv_fs_plugin_download_ajax_dc', 'fv_fs_plugin_download_ajax_dc');
add_action('wp_ajax_nopriv_fv_fs_plugin_download_ajax_dc', 'fv_fs_plugin_download_ajax_dc');

function fv_fs_plugin_download_ajax_dc(){

	if(get_option('_data_ls_key_no_id_vf') && get_option('_ls_domain_sp_id_vf')){
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf' );
    }else{
		$_ls_domain_sp_id_vf = get_option( '_ls_domain_sp_id_vf_2' );
		$_data_ls_key_no_id_vf = get_option( '_data_ls_key_no_id_vf_2' );
    }


	$api_params = array(
	    'license_host'=> $_SERVER['HTTP_HOST'],
	    'license_mode'=> 'download_web_dc',
	    'license_v'=> '1.0.0',
	    'plugin_download_hash'=> $_POST['plugin_download_hash'],
	    'license_key'=> $_data_ls_key_no_id_vf,
	    'download_type'=> $_POST['download_type'],

	);

	$query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL.'demo-content-download'));
	
    $response = wp_remote_post($query, array('timeout' => 20, 'sslverify' => false));
	
	// Check for error in the response
	if (is_wp_error($response)){
	    echo "Unexpected Error! The query returned with an error.";
	}

	$license_data = json_decode(wp_remote_retrieve_body($response));

	

	
	
	
	echo json_encode($license_data);



}


