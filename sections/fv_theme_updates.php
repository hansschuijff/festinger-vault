<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
    rel="stylesheet">

<div class="container-padding">

    <div class="row" style="padding-top:20px">
        <div class="col-md-12 plugin_updated_h4 pb-2 px-0">
            <h4 class="mb-0">Automatic theme update management
                <?php 
					if(isset($license_histories->result) && $license_histories->result == 'domainblocked'):
				?>
                <button class="btn btn-sm float-end btn-custom-color btn-danger">DOMAIN IS BLOCKED</button>
                <?php
					  else:

					  if(isset($license_histories->manual_force_update) && $license_histories->manual_force_update == 'yes'): 
					  	if($is_update_available == 0):
				?>
                <button class="btn btn-sm float-end primary-btn" id="no_update_available">FORCE UPDATE NOW</button>
                <?php else: ?>
                <form class="float-end" name="force_theme_update" method="POST">
                    <button class="btn btn-sm float-end primary-btn" id="themeforceupdate" type="submit"
                        name="themeforceupdate" value="theme">FORCE UPDATE NOW</button>
                </form>
                <?php endif;?>

                <?php else: ?>
                <button class="btn btn-sm float-end primary-btn" id="manual_force_update_r">FORCE UPDATE
                    NOW</button>
                <?php endif; ?>
                <?php endif; ?>



                <?php 
					if(isset($license_histories->result) && $license_histories->result == 'domainblocked'):
				?>
                <button class="btn btn-sm float-end btn-custom-color btn-danger">DOMAIN IS BLOCKED</button>
                <?php
					  else:

					  if(isset($license_histories->manual_force_update) && $license_histories->manual_force_update == 'yes'): 
					  	if($is_update_available == 0):
				?>
                <button class="btn btn-sm float-end primary-btn" style="margin-right:10px;" id="no_instant_update_available">Instant Update All</button>
                <?php else: ?>
                <form class="float-end" name="force_theme_update" method="POST">
                    <button class="btn btn-sm float-end primary-btn" id="themeforceupdate_instant" type="submit"
                        name="themeforceupdate_instant" style="margin-right:10px;" value="theme">Instant Update All</button>
                </form>
                <?php endif;?>

                <?php else: ?>
                <button class="btn btn-sm float-end primary-btn" style="margin-right:10px;" id="manual_force_update_instant_r">Instant Update All</button>
                <?php endif; ?>
                <?php endif; ?>
            </h4>
        </div>
    </div>



    <div class="row" style="padding-top:20px;">

        <?php if(isset($license_histories->result) && ($license_histories->result == 'domainblocked' || $license_histories->result == 'failed')):?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <strong>Whoops!</strong> <?= $license_histories->result == 'domainblocked'? 'Domain Blocked:':''; ?>
            <?= $license_histories->msg;?>
        </div>
        <?php endif;?>


        <?php if(isset($_GET['force'])): 
		if($_GET['force'] == 'success')
	?>

        <div class="alert alert-custom-clr alert-dismissible fade show" role="alert" style="background-color: #292055;">
            <strong>Force update for themes run successfully!</strong>
            <a href="<?= admin_url('admin.php?page=festinger-vault-theme-updates');?>" class="btn-close"
                aria-label="Close"></a>
        </div>

        <?php elseif(isset($_GET['rollback'])): 
		if($_GET['rollback'] == 'success')
	?>

        <div class="alert alert-custom-clr alert-dismissible fade show" role="alert" style="background-color: #292055;">
            <strong>Rollback run successfully!</strong>
            <a href="<?= admin_url('admin.php?page=festinger-vault-theme-updates');?>" class="btn-close"
                aria-label="Close"></a>
        </div>

        <?php elseif(isset($_GET['instant'])): 
		if($_GET['instant'] == 'success')
	?>

        <div class="alert alert-custom-clr alert-dismissible fade show" role="alert" style="background-color: #292055;">
            <strong>Instant update run successfully!</strong>
            <a href="<?= admin_url('admin.php?page=festinger-vault-updates');?>" class="btn-close"
                aria-label="Close"></a>
        </div>

        <?php endif;?>


        <div class="col-md-12 card-bg-cus" style="overflow-x: scroll;">
            <table class="table table-responsive borderless table-borderless update_plugin"
                style="border-collapse: separate; border-spacing: 0 12px;">
                <tr>
                    <th class="text-grey">Name</th>
                    <th class="text-grey">Description</th>
                    <th class="text-grey">Plan</th>
                    <th class="text-grey" style="min-width: 130px;">Version</th>
                    <th class="text-grey text-center plugin_update_width_10">Auto update</th>
                    <th class="text-grey text-center plugin_update_width_10">Instant update</th>
                    <th class="text-grey plugin_update_width_15 text-center" style=" min-width: 125px;">Rollback</th>
                </tr>




                <?php 



                if(count($fetching_theme_lists) ==0 ):

                	echo "<tr>";
                		echo "<td colspan='5'>";
                			echo "<span style='color:#fff; text-align:center;'>No theme data found. </span>";
                		echo "</td>";
                	echo "</tr>";
                endif;

        // traversing $allPlugins array
        foreach($allThemes as $theme) {

        	$is_toggle_checked = '';

			if (get_option('fv_themes_auto_update_list')==true && (array_search($theme->template, get_option('fv_themes_auto_update_list'))) !== false) {
				$is_toggle_checked = 'checked';
			}

			if($fetching_theme_lists != null){


				$new_version  = '';
				$chk_pkg_type = '';
				$plugin_slug_get = '';


				if (in_array($theme->template, $fetching_theme_lists)){







	        		$active_theme = '';
					if($activeTheme->Name == $theme->Name){
	            		$active_theme = "<span class='badge bg-tag'>Active</span>";
	            	}

	                echo '<tr class="table-tr mb-2">';
	                echo "<td class='plugin_update_width_20'>
	                		{$theme->name} <br/>
	                	".$active_theme."
	                </td>";
	                echo "<td class='plugin_update_width_40'>". substr($theme->Description, 0, 50)."...
	                	 </td>";
	                echo "<td class='plugin_update_width_10'><span class='badge bg-tag'>";


					foreach($fetching_theme_lists_full as $single_p){
						if($single_p->slug == $theme->template && $single_p->pkg_str_t == 1){
							echo 'Onetime';
							continue;
						}

						if($single_p->slug == $theme->template && $single_p->pkg_str_t == 0){
							echo 'Recurring';
							continue;
						}

					}



	                echo "</span> </td>";
					echo "<td class='plugin_update_width_10'><div class='row'><div class='col-6 text-left text-grey'>Current</div><div class='col-6 text-left'>{$theme->Version}</div>";
	                // echo "<td class='text-center'>{$theme->Version}</td>";
					
					// echo "<td class='text-center'>";
					$bgredhere = '';
					foreach($fetching_theme_lists_full as $single_p){
						if($single_p->slug == $theme->template && ($single_p->version > $theme['Version'])){
							$new_version = $single_p->version;
							$plugin_slug_get = $single_p->slug;


							if(!empty($new_version)){
								$bgredhere = 'style="background: #f33059; border-radius: 5px;"';
							}	

							if(floatval($new_version) > floatval($theme['Version'])){
								echo "
								<div class='col-6 text-left text-grey'>New</div>
								<div class='col-6 text-left' ".$bgredhere.">".$single_p->version.'</div>';
							}

							continue;
						}
					}
					echo "</td>";
					// echo '</td>';				
					


					



	                echo "<td class='position-relative auto_theme_update_switch'><center style='white-space:nowrap!important; word-break:nowrap; position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50%);'><input class='auto_theme_update_switch' data-id='".$theme->template."' type='checkbox' ".$is_toggle_checked." data-toggle='toggle' data-style='custom' data-size='xs'></center>
	                </td>";





	                echo "<td class='text-center'>";

 						if(!empty($new_version) && (floatval($new_version) > floatval($theme->Version) || floatval($new_version) != floatval($theme->Version))):
 					?>
 					<span style="position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50%);">
						<form name="singlethemeupdaterequest" method="POST" onSubmit="if(!confirm('Are you sure want to update now?')){return false;}">
						    <input type="hidden" name="theme_name" value="<?= $theme->name;?>" />
						    <input type="hidden" name="slug" value="<?= $plugin_slug_get;?>" />
						    <input type="hidden" name="version" value="<?= $new_version;?>" />
						    <button class="btn btn_rollback btn-sm float-end btn-custom-color" id="pluginrollback" type="submit" name="singlethemeupdaterequest"
						        value="single_item_update">Update <?= $new_version;?></button>
						</form>
					</span>


 					<?php 

 						endif;
					 echo '</td>';





	                echo "<td class='position-relative auto_theme_update_switch' style='display: table-cell; vertical-align: middle;  text-align:center;'><div style='display: inline-block;'>";
						check_rollback_availability($theme->template, $theme->Version, 'theme');
					echo '</div></td>';

	                echo '</tr>';
	            }
        	}	
            
        }

				





				?>
            </table>

        </div>
    </div>
</div>