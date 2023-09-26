<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
    rel="stylesheet">

<div class="container-padding">
    <div class="row" style="padding-top:20px;">
        <div class="col-md-12 plugin_updated_h4 pb-2 px-0">
            <h4 class="mb-0">Automatic plugin update management
                <?php
                    if ( isset( $pluginUpdate_get_data->result ) && $pluginUpdate_get_data->result == 'domainblocked' ):
                ?>
                <button class="btn btn-sm float-end btn-custom-color btn-danger">DOMAIN IS BLOCKED</button>
                <?php
                    else:
                      if ( isset($pluginUpdate_get_data->manual_force_update ) && $pluginUpdate_get_data->manual_force_update == 'yes' ):
                          if ( $is_update_available == 0 ):
                ?>
                <button class="btn btn-sm float-end primary-btn" id="no_update_available">FORCE UPDATE NOW</button>
                <?php else: ?>
                <form class="float-end" name="force_theme_update" method="POST">
                    <button class="btn btn-sm float-end primary-btn" id="pluginforceupdate" type="submit"
                        name="pluginforceupdate" value="plugin">FORCE UPDATE NOW</button>
                </form>
                <?php endif;?>
                <?php else: ?>
                <button class="btn btn-sm float-end primary-btn" id="manual_force_update_r">FORCE UPDATE
                    NOW</button>
                <?php endif; ?>
                <?php endif; ?>

                <?php
                    if ( isset($pluginUpdate_get_data->result ) && $pluginUpdate_get_data->result == 'domainblocked' ):
                ?>
                <button class="btn btn-sm float-end btn-custom-color btn-danger">DOMAIN IS BLOCKED</button>
                <?php
                    else:

                      if ( isset($pluginUpdate_get_data->manual_force_update ) && $pluginUpdate_get_data->manual_force_update == 'yes' ):
                          if ( $is_update_available == 0 ):
                ?>
                <button class="btn btn-sm float-end primary-btn" style="margin-right: 10px;" id="no_instant_update_available">Instant Update All</button>
                <?php else: ?>
                <form class="float-end" name="force_theme_update" method="POST">
                    <button class="btn btn-sm float-end primary-btn" id="pluginforceupdateinstant" type="submit"
                        name="pluginforceupdateinstant" style="margin-right: 10px;" value="plugin">Instant Update All</button>
                </form>
                <?php endif;?>
                <?php else: ?>
                <button class="btn btn-sm float-end primary-btn" style="margin-right: 10px;" id="manual_force_update_instant_r">Instant Update All</button>
                <?php endif; ?>
                <?php endif; ?>
            </h4>

        </div>
    </div>

    <div class="row" style="padding-top:20px;">

        <?php if ( isset($pluginUpdate_get_data->result ) && ($pluginUpdate_get_data->result == 'domainblocked' || $pluginUpdate_get_data->result == 'failed' ) ):?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <strong>Whoops!</strong> <?= $pluginUpdate_get_data->result == 'domainblocked'? 'Domain Blocked:':''; ?>
            <?= $pluginUpdate_get_data->msg;?>
        </div>
        <?php endif;?>

        <?php if ( isset($_GET['force'] ) ):
        if ( $_GET['force'] == 'success' )
    ?>

        <div class="alert alert-custom-clr alert-dismissible fade show" role="alert" style="background-color: #292055;">
            <strong>Force update for plugins run successfully!</strong>
            <a href="<?= admin_url('admin.php?page=festinger-vault-updates' );?>" class="btn-close"
                aria-label="Close"></a>
        </div>

        <?php elseif(isset($_GET['rollback'] ) ):
        if ( $_GET['rollback'] == 'success' )
    ?>

        <div class="alert alert-custom-clr alert-dismissible fade show" role="alert" style="background-color: #292055;">
            <strong>Rollback run successfully!</strong>
            <a href="<?= admin_url('admin.php?page=festinger-vault-updates' );?>" class="btn-close"
                aria-label="Close"></a>
        </div>

        <?php elseif(isset($_GET['instant'] ) ):
        if ( $_GET['instant'] == 'success' )
    ?>

        <div class="alert alert-custom-clr alert-dismissible fade show" role="alert" style="background-color: #292055;">
            <strong>Instant update run successfully!</strong>
            <a href="<?= admin_url('admin.php?page=festinger-vault-updates' );?>" class="btn-close"
                aria-label="Close"></a>
        </div>

        <?php endif;?>

        <div class="col-md-12 card-bg-cus" style="overflow-x: scroll;">

            <table class="table borderless table-borderless table-responsive update_plugin"
                style="border-collapse: separate; border-spacing: 0 12px;">
                <tr>
                    <th class="fw-bolder text-grey">Name</th>
                    <th class="fw-bolder text-grey">Description</th>
                    <th class="fw-bolder text-grey" style="min-width: 130px;">Version</th>
                    <th class="fw-bolder plugin_update_width_10 text-grey text-center">Auto update</th>
                    <th class="fw-bolder plugin_update_width_10 text-grey text-center">Instant Update</th>
                    <th class=" fw-bolder plugin_update_width_20 text-grey text-center" style="min-width: 125px;">Rollback</th>
                </tr>

                <?php

                if ( count($fetching_plugin_lists ) ==0  ):

                    echo "<tr>";
                        echo "<td colspan='6'>";
                            echo "<span style='color:#fff; text-align:center;'>No plugin data found. </span>";
                        echo "</td>";
                    echo "</tr>";
                endif;
            foreach($allPlugins as $key => $value ) {
                $is_toggle_checked = '';

                if ( get_option('fv_plugin_auto_update_list' ) == true && (array_search(get_plugin_slug_from_data($key, $value ), get_option('fv_plugin_auto_update_list' ) ) ) !== false ) {
                    $is_toggle_checked = 'checked';
                }

            if ( $fetching_plugin_lists != null ){

                if (in_array(get_plugin_slug_from_data($key, $value ), $fetching_plugin_lists ) ){

                    $new_version  = null;
                    $plugin_slug_get   = '';
                    $chk_pkg_type = '';
                    $bgredhere = '';

                    foreach($fetching_plugin_lists_full as $single_p ){

                        if ( $single_p->slug == get_plugin_slug_from_data($key, $value ) && $single_p->pkg_str == 1 ){
                            $chk_pkg_type = 'onetime';
                        }

                        if ( $single_p->slug == get_plugin_slug_from_data($key, $value ) && $single_p->pkg_str == 0 ){
                            $chk_pkg_type = 'recurring';
                        }

                        if ( $single_p->slug == get_plugin_slug_from_data($key, $value ) ){
                            $plugin_slug_get = $single_p->slug;
                            $new_version = ($single_p->version );
                            // if ( \function_exists( '\d'  )  ) {
                            //     \d(
                            //         $value['Name'],
                            //         $single_p,
                            //      );
                            // }
                            continue;

                        }

                    }

                    if ( !empty($new_version ) ){
                        $bgredhere = 'style="background: #f33059; border-radius: 5px;"';
                    }

                    if ( in_array($key, $activePlugins ) ) { // display active only
                        $textval = substr( wp_strip_all_tags( $value['Description'] ), 0, 100 );
                        echo '<tr class="table-tr mb-2">';
                        echo "<td class='plugin_update_width_30'>
                                {$value['Name']} <br/>

                                <span class='badge bg-tag'>Active</span>
                                <span class='badge bg-tag'>".ucfirst($chk_pkg_type )."</span>
                             </td>";
                        echo "<td class='plugin_update_width_60'>".$textval."...</td>";
                        echo "<td>
                                <div class='row'>
                                    <div class='col-6 text-left text-grey'>Current</div>
                                    <div class='col-6 text-left'>{$value['Version']}</div>
                             ";

                                    if ( (int )($new_version ) > (int )($value['Version'] ) ){
                                        echo "
                                    <div class='col-6 text-left text-grey'>New</div>

                                        <div class='col-6 text-left' ".$bgredhere.">{$new_version}</div>";
                                    }

                                    $version1 = $value['Version'];
                                    $version2 = $new_version;

                                    if ( version_compare( $version1, $version2, '<' ) ) {
                                        echo "
                                    <div class='col-6 text-left text-grey'>New</div>

                                        <div class='col-6 text-left' ".$bgredhere.">{$new_version}</div>";
                                    }

                                echo  "</div>
                            </td>";

                        echo "<td class='position-relative'>
                                <center style='position: absolute; top: 50%; left:50%; transform: translate(-50%,-50% );'>
                                    <input class='auto_plugin_update_switch btn-secondary".get_plugin_slug_from_data($key, $value )."' type='checkbox' ".$is_toggle_checked." data-id='".get_plugin_slug_from_data($key, $value )."' data-toggle='toggle' data-style='custom' data-size='xs'>
                                </center>
                              </td>";

                         echo "<td class='text-center'>";
                             if ( !empty($new_version ) ):

                            $version1 = $value['Version'];
                            $version2 = $new_version;

                            if ( version_compare( $version1, $version2, '<' ) ):

                         ?>
                                 <span style="position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50% );">
                                    <form name="singlepuginupdaterequest" method="POST" onSubmit="if ( !confirm('Are you sure want to update now?' ) ){return false;}">
                                        <input type="hidden" name="plugin_name" value="<?= $value['Name'];?>" />
                                        <input type="hidden" name="slug" value="<?= $plugin_slug_get;?>" />
                                        <input type="hidden" name="version" value="<?= $new_version;?>" />
                                        <button class="btn btn_rollback btn-sm float-end btn-custom-color" id="pluginrollback" type="submit" name="singlepuginupdaterequest"
                                            value="single_item_update">Update <?= $new_version;?></button>
                                    </form>
                                </span>

                         <?php

                                 endif;
                             endif;
                         echo '</td>';

                        echo "<td class='position-relative'><span style='position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50% );'>";
                         check_rollback_availability(get_plugin_slug_from_data($key, $value ), $value['Version'], 'plugin' );
                        echo '</td>';

                        echo '</tr>';

                    }else{

                        echo '<tr class="table-tr mb-2">';
                        echo "<td class='plugin_update_width_30'>
                                {$value['Name']} <br/>
                                <span class='badge bg-danger'>Deactive</span>
                                <span class='badge bg-tag'>".ucfirst($chk_pkg_type )."</span>
                             </td>";
                        echo "<td class='plugin_update_width_60'>" . substr( $value['Description'], 0, 100  ) . "...</td>";
                        echo "<td ><div class='row'><div class='col-6 text-left text-grey'>Current</div><div class='col-6 text-left'>{$value['Version']}</div>";

                        // if ( \function_exists( '\d'  )  ) {
                        //     \d(
                        //         $new_version,
                        //         (int )($new_version ),
                        //         $value['Version'],
                        //         (int )($value['Version'] ),
                        //         (int )($new_version ) > (int )($value['Version'] ),
                        //      );
                        // }

                        // de (int ) resulteert alleen in true als een hele versie-nummer verschilt
                        // bijv. "1.2.3-beta" wordt int 1
                        // terwijl "2.2.3" vertaalt in int 2
                        if ( (int )($new_version ) > (int )($value['Version'] ) ){
                            echo "
                                <div class='col-6 text-left text-grey'>New</div>
                                <div class='col-6 text-left' ".$bgredhere.">{$new_version}</div>";

                        // begin update TVU/DWP
                        } else {
                            // zet dit blok in een else zodat de nieuwe versie alert niet twee keer wordt getoond.
                            // end update TVU/DWP
                            $version1 = $value['Version'];
                            $version2 = $new_version;
                            if ( version_compare($version1, $version2, '<' ) ) {
                                echo "
                                    <div class='col-6 text-left text-grey'>New</div>
                                    <div class='col-6 text-left' ".$bgredhere.">{$new_version}</div>";
                            }
                            // begin update TVU/DWP
                        }
                        // end update TVU/DWP

                        echo "</td>";

                        // echo "<td class='text-center'>";
                        //     echo $new_version;

                        echo '</td>';

                        echo "<td class='position-relative'>
                                <center style='position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50% );'>
                                    <input class='auto_plugin_update_switch ".get_plugin_slug_from_data($key, $value )."' type='checkbox' ".$is_toggle_checked." data-id='".get_plugin_slug_from_data($key, $value )."' data-toggle='toggle' data-style='custom' data-size='xs'/>
                                </center>
                        </td>";

                         echo "<td class='text-center'>";
                             if ( !empty($new_version ) ):
                         ?>
                         <span style="position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50% );">
                            <form name="singlepuginupdaterequest" method="POST" onSubmit="if ( !confirm('Are you sure want to update now?' ) ){return false;}">
                                <input type="hidden" name="plugin_name" value="<?= $value['Name'];?>" />
                                <input type="hidden" name="slug" value="<?= $plugin_slug_get;?>" />
                                <input type="hidden" name="version" value="<?= $new_version;?>" />
                                <button class="btn btn_rollback btn-sm float-end btn-custom-color" id="pluginrollback" type="submit" name="singlepuginupdaterequest"
                                    value="single_item_update">Update <?= $new_version;?></button>
                            </form>
                        </span>

                         <?php

                             endif;
                         echo '</td>';

                        echo "<td class='position-relative'>
                                <span style='position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50% );'>";
                                     echo check_rollback_availability(get_plugin_slug_from_data($key, $value ), $value['Version'], 'plugin' );
                        echo    '</span>
                              </td>';

                        echo '</tr>';

                    }
                }
            }
        }

                ?>
            </table>

        </div>
    </div>
</div>