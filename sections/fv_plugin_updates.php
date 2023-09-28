<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
    rel="stylesheet">

<?php
// d(
//     fv_get_plugins(),
//     $fv_plugins,
//     $fv_plugin_updates,
// );
?>
<div class="container-padding">
    <div class="row" style="padding-top:20px;">
        <div class="col-md-12 plugin_updated_h4 pb-2 px-0">
            <h4 class="mb-0">Automatic plugin update management
                <?php
                /**
                 * Handle Blocked domain or license failure.
                 */
                if ( isset( $pluginUpdate_get_data->result )
                &&   fv_api_call_failed( $pluginUpdate_get_data->result ) ) :
                    ?>
                    <button class="btn btn-sm float-end btn-custom-color btn-danger">
                        <?php
                        switch ( true ) {
                            case fv_domain_blocked( $pluginUpdate_get_data->result ):
                                echo 'DOMAIN IS BLOCKED';
                                break;

                            case fv_license_failed( $pluginUpdate_get_data->result ):
                                echo 'NO ACTIVE LICENSE';
                                break;

                            default:
                                break;
                        }
                        ?>
                    </button>
                    <div class="row" style="padding-top:20px">
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <strong>Whoops!</strong>
                            <?php
                            switch ( true ) {
                                case fv_domain_blocked( $pluginUpdate_get_data->result ):
                                    echo 'Your domain is blocked';
                                    break;

                                case fv_license_failed( $pluginUpdate_get_data->result ):
                                    echo 'No active license was found, please activate a license first';
                                    break;

                                default:
                                    break;
                            }
                            echo $pluginUpdate_get_data->msg ? ": " . $pluginUpdate_get_data->msg : '';
                            ?>
                        </div>
                    </div>
                    <?php
                    return;
                endif;

               /**
                 * Render force update buttons.
                 */
                if ( isset( $pluginUpdate_get_data->manual_force_update )
                &&          $pluginUpdate_get_data->manual_force_update == 'yes' ):

                    if ( ! empty( $fv_plugin_updates ) ):
                    ?>
                        <!-- Force update now button -->
                        <form class="float-end" name="force_theme_update" method="POST">
                            <button class="btn btn-sm float-end primary-btn" id="pluginforceupdate" type="submit" name="pluginforceupdate" value="plugin">
                                FORCE UPDATE NOW
                            </button>
                        </form>
                        <!-- Instant update all button -->
                        <form class="float-end" name="force_theme_update" method="POST">
                            <button class="btn btn-sm float-end primary-btn" id="pluginforceupdateinstant" type="submit" name="pluginforceupdateinstant" style="margin-right: 10px;" value="plugin">
                                Instant Update All
                            </button>
                        </form>
                   <?php else: ?>
                        <!-- No updates available. -->
                        <button class="btn btn-sm float-end primary-btn" id="no_update_available">
                            NO UPDATES AVAILABLE
                        </button>
                        <button class="btn btn-sm float-end primary-btn" style="margin-right: 10px;" id="no_instant_update_available">
                            No updates available
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Manual update not allowed in plan.  -->
                    <button class="btn btn-sm float-end primary-btn" id="manual_force_update_r">
                        FORCE UPDATE NOT IN PLAN
                    </button>
                    <button class="btn btn-sm float-end primary-btn" style="margin-right: 10px;" id="manual_force_update_instant_r">
                        Instant update not in plan
                    </button>
                <?php endif; ?>
            </h4>
        </div>
    </div>
    <div class="row" style="padding-top:20px;">
        <?php
        /**
         * Render success message if plugin update or rollback has been performed.
         */
        $success_message = fv_get_succes_message( context: 'plugins' );

        if ( $success_message ) :
        ?>
            <div class="alert alert-custom-clr alert-dismissible fade show" role="alert" style="background-color: #292055;">
                <strong><?php echo $success_message; ?></strong>
                <a href="<?= admin_url( 'admin.php?page=festinger-vault-updates' ); ?>" class="btn-close" aria-label="Close"></a>
            </div>
        <?php endif; ?>

        <div class="col-md-12 card-bg-cus" style="overflow-x: scroll;">
            <table class="table borderless table-borderless table-responsive update_plugin" style="border-collapse: separate; border-spacing: 0 12px;">
                <!-- table headers -->
                <tr>
                    <th class="fw-bolder text-grey">Name</th>
                    <th class="fw-bolder text-grey">Description</th>
                    <th class="fw-bolder text-grey" style="min-width: 130px;">Version</th>
                    <th class="fw-bolder plugin_update_width_10 text-grey text-center">Auto update</th>
                    <th class="fw-bolder plugin_update_width_10 text-grey text-center">Instant Update</th>
                    <th class=" fw-bolder plugin_update_width_20 text-grey text-center" style="min-width: 125px;">Rollback</th>
                </tr>
                <?php
                // No plugin data to show
                if ( empty( $fv_plugins ) ):
                ?>
                    <tr>
                        <td colspan='6'>
                            <span style='color:#fff; text-align:center;'>
                                No plugin data found.
                            </span>
                        </td>
                    </tr>
                    </table></div></div></div>
                    <?php
                    return;
                endif;

                /**
                 * Render plugin rows
                 */

                $installed_plugins = fv_get_plugins();

                foreach( $fv_plugins as $plugin_basename => $fv_plugin_data ):

                    if ( isset( $fv_plugin_updates[ $plugin_basename ] ) ) {
                        $fv_plugin_has_update = true;
                        $bgredhere            = 'style="background: #f33059; border-radius: 5px;"';
                    } else {
                        $fv_plugin_has_update = false;
                        $bgredhere            = '';
                    }
                    if ( fv_should_plugin_auto_update( $fv_plugin_data->slug ) ) {
                        $auto_update_toggle_checked = 'checked';
                    } else {
                        $auto_update_toggle_checked = '';
                    }
                    $installed_plugin = $installed_plugins[ $plugin_basename ];
                    $is_active_plugin = is_plugin_active( $plugin_basename );
                    ?>
                    <tr class="table-tr mb-2">
                        <!-- Name column -->
                        <td class='plugin_update_width_30'>
                            <?php echo $installed_plugin['Name'] ?><br/>
                            <span class='badge <?php echo $is_active_plugin ? 'bg-tag' : 'bg-danger' ?>'>
                                <?php echo $is_active_plugin ? 'Active' : 'Inactive' ?>
                            </span>
                            <span class='badge bg-tag'>
                                <?php echo ucfirst( fv_get_package_type( $fv_plugin_data->pkg_str ) ) ?>
                            </span>
                        </td>
                        <!-- Desctiption column -->
                        <td class='plugin_update_width_60'>
                            <?php echo substr( wp_strip_all_tags( text: $installed_plugin['Description'], remove_breaks: true ), 0, 100 ) ?>...
                        </td>
                        <!-- Version column -->
                        <td>
                            <div class='row'>
                                <div class='col-6 text-left text-grey'>Current</div>
                                <div class='col-6 text-left'>
                                    <?php echo $installed_plugin['Version'] ?>
                                </div>
                                <?php if ( version_compare( $fv_plugin_data->version, $installed_plugin['Version'], '>' ) ): ?>
                                    <div class='col-6 text-left text-grey'>New</div>
                                    <div class='col-6 text-left' <?php echo $bgredhere ?>>
                                        <?php echo $fv_plugin_data->version ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <!-- Auto-update column -->
                        <td class='position-relative'>
                            <center style='position: absolute; top: 50%; left:50%; transform: translate( -50%,-50% );'>
                                <input class='auto_plugin_update_switch btn-secondary <?php echo $fv_plugin_data->slug ?>' type='checkbox' <?php echo $auto_update_toggle_checked ?> data-id='<?php echo $fv_plugin_data->slug ?>' data-toggle='toggle' data-style='custom' data-size='xs'>
                            </center>
                        </td>
                        <!-- Instant Update column -->
                        <td class='text-center'>
                            <?php if ( $fv_plugin_has_update ): ?>
                                <span style="position: absolute; top: 50%; left:50%;  transform: translate( -50%,-50% );">
                                    <form name="singlepuginupdaterequest" method="POST" onSubmit="if ( !confirm( 'Are you sure want to update now?' ) ) {return false;}">
                                        <input type="hidden" name="plugin_name" value="<?= $installed_plugin['Name']; ?>" />
                                        <input type="hidden" name="slug" value="<?= $fv_plugin_data->slug; ?>" />
                                        <input type="hidden" name="version" value="<?= $fv_plugin_data->version; ?>" />
                                        <button class="btn btn_rollback btn-sm float-end btn-custom-color" id="pluginrollback" type="submit" name="singlepuginupdaterequest" value="single_item_update">
                                            Update <?= $fv_plugin_data->version; ?>
                                        </button>
                                    </form>
                                </span>
                            <?php endif; ?>
                        </td>
                        <!-- Rollback column -->
                        <td class='position-relative'>
                            <span style='position: absolute; top: 50%; left:50%;  transform: translate( -50%,-50% );'>
                            <?php echo check_rollback_availability( fv_shorten_slug( $plugin_basename ), $installed_plugin['Version'], 'plugin' ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>