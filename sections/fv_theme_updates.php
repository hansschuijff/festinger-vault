<?php
/**
 * This file uses the following vars/data:
 *
 * $fv_api->result
 * $fv_api->msg
 * $fv_api->manual_force_update
 * $fv_themes
 * $fv_theme_updates
 */
?>
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
<div class="container-padding">
    <div class="row" style="padding-top: 20px;">
        <div class="col-md-12 theme_updated_h4 pb-2 px-0">
            <h4 class="mb-0">
                Automatic theme update management
                <?php
                if ( isset( $fv_api->result )
                && fv_api_call_failed( $fv_api->result ) ) {

                    fv_print_api_call_failed_notices( $fv_api );
                    if ( ! fv_has_any_license() ) {
                        fv_print_no_license_push_message();
                    }

                    return;
                }

                /**
                 * Render force update buttons.
                 */
                if ( isset( $fv_api->manual_force_update )
                &&          $fv_api->manual_force_update == 'yes' ):

                    if ( empty( $fv_themes_update ) ):
                    ?>
                        <!-- Force update now button -->
                        <form class="float-end" name="force_theme_update" method="POST">
                            <button class="btn btn-sm float-end primary-btn" id="themeforceupdate" type="submit" name="themeforceupdate" value="theme">
                                FORCE UPDATE NOW
                            </button>
                        </form>
                        <!-- Instant update all button -->
                        <form class="float-end" name="force_theme_update" method="POST">
                            <button class="btn btn-sm float-end primary-btn" id="themeforceupdate_instant" type="submit" name="themeforceupdate_instant" style="margin-right: 10px;" value="theme">
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

                    <?php
                    endif;
                else:
                ?>
                    <!-- Manual update not allowed in plan.  -->
                    <button class="btn btn-sm float-end primary-btn" id="manual_force_update_r">
                        FORCE UPDATE not in plan
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
         * Render success message if theme update or rollback has been performed.
         */
        $success_message = fv_get_succes_message( context: 'themes' );
        if ( $success_message ) :
        ?>
            <div class="alert alert-custom-clr alert-dismissible fade show" role="alert" style="background-color: #292055;">
                <strong><?php echo $success_message; ?></strong>
                <a href="<?= admin_url( 'admin.php?page=festinger-vault-theme-updates' ); ?>" class="btn-close" aria-label="Close"></a>
            </div>
        <?php endif; ?>

        <div class="col-md-12 card-bg-cus" style="overflow-x: scroll;">
            <table
                class="table table-responsive borderless table-borderless update_theme"
                style="border-collapse: separate; border-spacing: 0 12px;">
                <!-- table headers -->
                <tr>
                    <th class="text-grey">Name</th>
                    <th class="text-grey">Description</th>
                    <th class="text-grey">Plan</th>
                    <th class="text-grey" style="min-width: 130px;">Version</th>
                    <th class="text-grey text-center theme_update_width_10">Auto update</th>
                    <th class="text-grey text-center theme_update_width_10">Instant update</th>
                    <th class="text-grey theme_update_width_15 text-center" style="min-width: 125px;   ">Rollback</th>
                </tr>
                <?php
                // No theme data to show
                if ( empty( $fv_themes ) ):
                ?>
                    <tr>
                        <td colspan='5'>
                            <span style='color: #fff; text-align: center;'>
                                No theme data found.
                            </span>
                        </td>
                    </tr>
                    </table></div></div></div>
                    <?php
                    // Skip te rest of this script.
                    return;
                endif;

                $active_theme = wp_get_theme(); // Defaults to the active theme.

                /**
                 * Render theme rows
                 */
                foreach( $fv_themes as $fv_theme_stylesheet => $fv_theme ) {

                    if ( isset( $fv_theme_updates[ $fv_theme_stylesheet ] ) ) {
                        $fv_theme_has_update  = true;
                        $bgredhere            = 'style="background: #f33059; border-radius: 5px;"';
                    } else {
                        $fv_theme_has_update  = false;
                        $bgredhere            = '';
                    }

                    if ( fv_should_auto_update_theme( $fv_theme['slug'] ) ) {
                        $auto_update_toggle_checked = 'checked';
                    } else {
                        $auto_update_toggle_checked = '';
                    }
                    ?>
                    <tr class="table-tr mb-2">
                        <!-- Name -->
                        <td class='theme_update_width_20'>
                            <?php echo $fv_theme['name']; ?> <br/>
                            <?php if ( $fv_theme_stylesheet === $active_theme->stylesheet ): ?>
                                <span class='badge bg-tag'>Active</span>
                            <?php endif; ?>
                        </td>
                        <!-- Desctription -->
                        <td class='theme_update_width_40'>
                            <?php echo substr( wp_strip_all_tags( text: $fv_theme['description'], remove_breaks: true ), 0, 50 ) ?>...
                        </td>
                        <!-- Plan -->
                        <td class='theme_update_width_10'>
                            <span class='badge bg-tag'>
                                <?php echo ucfirst( fv_get_package_type( $fv_theme['pkg_str_t'] ) ); ?>
                            </span>
                        </td>
                        <!-- Version -->
                        <td class='theme_update_width_10'>
                            <div class='row'>
                                <!-- currently installed version -->
                                <div class='col-6 text-left text-grey'>Current</div>
                                <div class='col-6 text-left'>
                                    <?php echo $fv_theme['installed-version']; ?>
                                </div>
                                <!-- available update -->
                                <?php
                                if ( $fv_theme_has_update ):
                                ?>
                                    <div class="col-6 text-left text-grey">New</div>
                                    <div class="col-6 text-left" <?php echo $bgredhere; ?> >
                                        <?php echo $fv_theme['version']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <!-- Auto-update toggle -->
                        <td class='position-relative auto_theme_update_switch'>
                            <center style='white-space:nowrap!important; word-break:nowrap; position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50%);'>
                                <input class='auto_theme_update_switch' data-id='<?php echo $fv_theme['slug']; ?>' type='checkbox' <?php echo $auto_update_toggle_checked; ?> data-toggle='toggle' data-style='custom' data-size='xs'>
                            </center>
                        </td>
                        <!-- Instant Update -->
                        <td class="text-center">
                            <?php
                            if ( $fv_theme_has_update ):
                            ?>
                                <span style="position: absolute; top: 50%; left: 50%; transform: translate( -50%, -50% );">
                                    <form name="singlethemeupdaterequest" method="POST" onSubmit="if ( !confirm('Are you sure want to update now?' ) ) {return false;}">
                                        <input type="hidden" name="theme_name" value="<?= $fv_theme['name']; ?>" />
                                        <input type="hidden" name="slug" value="<?= $fv_theme['slug']; ?>" />
                                        <input type="hidden" name="version" value="<?= $fv_theme['version']; ?>" />
                                        <button class="btn btn_rollback btn-sm float-end btn-custom-color" id="themeforceupdate_instant" type="submit" name="singlethemeupdaterequest" value="single_item_update">
                                            Update <?= $fv_theme['version']; ?>
                                        </button>
                                    </form>
                                </span>
                            <?php
                            endif;
                            ?>
                        </td>
                        <!-- Rollback -->
                        <td class="position-relative">
                            <div style="display: inline-block;">
                                <span style='position: absolute; top: 50%; left:50%;  transform: translate( -50%,-50% );'>
                                    <?php fv_print_theme_rollback_button( $fv_theme_stylesheet, $fv_theme['installed-version'], 'theme' ); ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </table>
        </div>
    </div>
</div>
