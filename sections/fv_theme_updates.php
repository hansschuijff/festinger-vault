<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
<?php // $license_histories->result = 'domainblocked'; ?>
<?php // $license_histories->result = 'failed'; ?>
<?php // $license_histories->msg = 'HALLO HANS!'; ?>
<?php // $is_update_available = 0; ?>
<?php // $license_histories->manual_force_update = 'no'; ?>

<div class="container-padding">
    <div class="row" style="padding-top:20px">
        <div class="col-md-12 plugin_updated_h4 pb-2 px-0">
            <h4 class="mb-0">Automatic theme update management
                <?php
                /**
                 * Handle Blocked domain or license failure.
                 */
                if ( isset( $license_histories->result )
                &&   in_array( $license_histories->result, array( 'domainblocked', 'failed' ) ) ) :
                    ?>
                    <button class="btn btn-sm float-end btn-custom-color btn-danger">
                        <?= ( 'domainblocked' == $license_histories->result ) ? 'DOMAIN IS BLOCKED' : ''; ?>
                        <?= ( 'failed' == $license_histories->result ) ? 'NO ACTIVE LICENSE' : ''; ?>
                    </button>
                    <div class="row" style="padding-top:20px">
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <strong>Whoops!</strong>
                            <?= ( 'domainblocked' == $license_histories->result ) ? 'Your domain is blocked' : ''; ?>
                            <?= ( 'failed' == $license_histories->result ) ? 'No active license was found' : ''; ?>
                            <?= $license_histories->msg ? ": " . $license_histories->msg : ''; ?>
                        </div>
                    </div>
                    <?php
                    return;
                endif;

                /**
                 * Render force update buttons.
                 */
                if ( isset( $license_histories->manual_force_update )
                &&  $license_histories->manual_force_update == 'yes' ):
                    if ( $is_update_available ):
                        ?>
                        <!-- Force update now button -->
                        <form class="float-end" name="force_theme_update" method="POST">
                            <button class="btn btn-sm float-end primary-btn" id="themeforceupdate" type="submit" name="themeforceupdate" value="theme">
                                FORCE UPDATE NOW
                            </button>
                        </form>
                        <!-- Instant update all button -->
                        <form class="float-end" name="force_theme_update" method="POST">
                            <button class="btn btn-sm float-end primary-btn" id="themeforceupdate_instant" type="submit" name="themeforceupdate_instant" style="margin-right:10px" value="theme">
                                Instant Update All
                            </button>
                        </form>
                    <?php else: ?>
                        <!-- No updates available. -->
                        <button class="btn btn-sm float-end primary-btn" id="no_update_available">
                            NO UPDATES AVAILABLE
                        </button>
                        <button class="btn btn-sm float-end primary-btn" style="margin-right:10px" id="no_instant_update_available">
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
                    <button class="btn btn-sm float-end primary-btn" style="margin-right:10px" id="manual_force_update_instant_r">
                        Instant update not in plan
                    </button>
                <?php
                endif;
                ?>
            </h4>
        </div>
    </div>
    <?php

        $success_message = fv_get_succes_message();
        if ( $success_message ) :
        ?>
            <div class="alert alert-custom-clr alert-dismissible fade show" role="alert" style="background-color: #292055;">
                <strong><?php echo $success_message; ?></strong>
                <a href="<?= admin_url( 'admin.php?page=festinger-vault-theme-updates' ); ?>" class="btn-close" aria-label="Close"></a>
            </div>
        <?php endif; ?>

        <div class="col-md-12 card-bg-cus" style="overflow-x: scroll;">
            <table
                class="table table-responsive borderless table-borderless update_plugin"
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
                /**
                 * If remote Vault didn't return themes,
                 * then just render a message and skip further processing.
                 */
                if ( empty( $fetching_theme_lists ) ):
                    ?>
                    <tr>
                        <td colspan='5'>
                            <span style='color:#fff; text-align:center;'>
                                No theme data found.
                            </span>
                        </td>
                    </tr>
                    </table></div></div></div>
                    <?php
                    // Skip te rest of this script.
                    return;
                endif;

                /**
                 * Render a theme rows
                 */
                foreach( $allThemes as $theme ) {

                    // Is auto-update checked for this theme?
                    $is_toggle_checked = '';
                    if ( get_option( 'fv_themes_auto_update_list' ) == true
                    && ( array_search( $theme->template, get_option( 'fv_themes_auto_update_list' ) ) ) !== false ) {
                        $is_toggle_checked = 'checked';
                    }

                    $new_version     = '';
                    $chk_pkg_type    = '';
                    $theme_slug_get  = '';

                    if ( in_array( $theme->template, $fetching_theme_lists ) ) {

                        $active_theme_marker = '';
                        if ( $activeTheme->Name == $theme->Name ) {
                            $active_theme_marker = "<span class='badge bg-tag'>Active</span>";
                        }
                        ?>

                        <tr class="table-tr mb-2">
                            <!-- Name -->
                            <td class='plugin_update_width_20'>
                                <?php echo $theme->name; ?> <br/>
                                <?php echo $active_theme_marker; ?>
                            </td>
                            <!-- Desctription -->
                            <td class='plugin_update_width_40'><?php echo substr( wp_strip_all_tags( text: $theme->Description,  remove_breaks: true ), 0, 50 ) ?>...</td>
                            <!-- Plan -->
                            <td class='plugin_update_width_10'><span class='badge bg-tag'>
                            <?php
                            foreach( $fetching_theme_lists_full as $single_p ) {
                                if ( $single_p->slug == fv_get_wp_theme_slug( $theme ) ) {
                                    switch ( $single_p->pkg_str_t ) {
                                        case '1':
                                            echo 'Onetime';
                                            break;

                                        case '0':
                                            echo 'Recurring';
                                            break;

                                        default:
                                            echo 'Unknown';
                                            break;
                                    }
                                    break;
                                }
                            }
                            ?>
                            </span> </td>
                            <!-- Version -->
                            <td class='plugin_update_width_10'>
                                <!-- currently installed version -->
                                <div class='row'>
                                    <div class='col-6 text-left text-grey'>Current</div>
                                    <div class='col-6 text-left'><?php echo $theme->Version; ?>
                                </div>
                                <!-- available update -->
                                <?php
                                $bgredhere = '';
                                foreach( $fetching_theme_lists_full as $single_p ) {

                                    if ( $single_p->slug == fv_get_wp_theme_slug( $theme )
                                    &&   version_compare( $single_p->version, $theme['Version'], '>' ) ) {

                                        $new_version     = $single_p->version;
                                        $theme_slug_get  = $single_p->slug;
                                        $bgredhere       = 'style="background: #f33059; border-radius: 5px;"';
                                        ?>
                                        <div class="col-6 text-left text-grey">New</div>
                                        <div class="col-6 text-left" <?php echo $bgredhere; ?> > <?php echo $new_version; ?> </div>
                                        <?php
                                        continue;
                                    }
                                }
                                ?>
                            </td>
                            <!-- Auto-update -->
                            <td class='position-relative auto_theme_update_switch'>
                                <center style='white-space:nowrap!important; word-break:nowrap; position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50% );'>
                                    <input class='auto_theme_update_switch' data-id='<?php echo $theme->template; ?>' type='checkbox' <?php echo $is_toggle_checked; ?> data-toggle='toggle' data-style='custom' data-size='xs'>
                                </center>
                            </td>
                            <!-- Instant Update -->
                            <td class="text-center">
                                <?php
                                if ( ! empty( $new_version )
                                &&   version_compare( $new_version, $theme->Version, '>' ) ):
                                ?>
                                    <span style="position: absolute; top: 50%; left:50%;  transform: translate(-50%,-50% );">
                                        <form name="singlethemeupdaterequest" method="POST" onSubmit="if ( !confirm('Are you sure want to update now?' ) ) {return false;}">
                                            <input type="hidden" name="theme_name" value="<?= $theme->name; ?>" />
                                            <input type="hidden" name="slug" value="<?= $theme_slug_get; ?>" />
                                            <input type="hidden" name="version" value="<?= $new_version; ?>" />
                                            <button class="btn btn_rollback btn-sm float-end btn-custom-color" id="pluginrollback" type="submit" name="singlethemeupdaterequest" value="single_item_update">
                                                Update <?= $new_version; ?>
                                            </button>
                                        </form>
                                    </span>
                                <?php
                                endif;
                                ?>
                            </td>
                            <!-- Rollback -->
                            <td class="position-relative auto_theme_update_switch" style="display: table-cell; vertical-align: middle;  text-align:center;">
                                <div style="display: inline-block;">
                                <?php
                                    // NOTE: The name of this function is confusing.
                                    //       It checks, but also renders the html for this column.
                                    check_rollback_availability( $theme->template, $theme->Version, 'theme' );
                                ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>
    </div>
</div>

<?php
/**
 * Determine message, to display when a button form is succesfully processed.
 *
 * @return string
 */
function fv_get_succes_message(): string {
    if ( isset( $_GET['force'] ) && 'success' == $_GET['force'] ) {
        return 'Force update for themes run successfully!';
    }
    if ( isset( $_GET['rollback'] ) && 'success' == $_GET['rollback'] ) {
        return 'Rollback run successfully!';
    }
    if ( isset( $_GET['instant'] ) && 'success' == $_GET['instant'] ) {
        return 'Instant update run successfully!';
    }
    return '';
}