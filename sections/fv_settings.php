<div class="container-padding">
    <h1 class="text-white mt-4 fw-bold">
        Festinger Vault Settings
    </h1>
    <div id="tabs">
        <ul>
            <li><a class="nav-tab" href="#tab-auto-update">Auto Update</a></li>
            <?php if ( ! fv_should_white_label() ) : ?>
            <li><a class="nav-tab" href="#tab-white-label">White Label</a></li>
            <?php endif; ?>
            <li><a class="nav-tab" href="#tab-admin-notices">Admin Notices</a></li>
            <li><a class="nav-tab" href="#tab-ignore-plugins">Ignore Plugins</a></li>
            <li><a class="nav-tab" href="#tab-ignore-themes">Ignore Themes</a></li>
        </ul>
        <!-- Auto-update settings -->
        <div id="tab-auto-update" class="row">
            <div class="col-md-12">
                <h5 class="text-white mt-4 fw-bold">
                    Plugins & Themes Auto Update
                </h5>
                <div class="card card-bg-cus mb-3" style="padding: 20px 0 !important; min-width: 100%;">
                    <div class="card-body padding-horizontal">
                        <form method="post" action="" name="autpupdatestatus_form">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input type="checkbox" id="only_dismissable" class="form-check-input custom-checkbox-color" name="an_fv_dis_adm_not_hid" value="1"
                                            <?php echo ( $fv_api->auto_update_status == 1 ) ? 'checked' : ''; ?> >
                                        <label for="only_dismissable"  class="form-check-label" style="margin-top: -19px;">
                                            Enable Auto Update
                                        </label>
                                    </div>
                                    <?php if ( ! empty( $last_auto_update_time ) ) : ?>
                                        <div class="text-white">Last updated: <?= $last_auto_update_time; ?></div>
                                    <?php endif;?>
                                </div>
                            </div>
                            <div class="row">
                                <div class='col-md-12'>
                                    <hr style="margin: 25px auto; height:0.5px; background-color: #4d378e !important;" />
                                </div>
                                <div class="col-md-2">
                                    <input type="submit" class="mt-3 btn btn-block non_active_button primary-btn" name="autpupdatestatus_form" value="Update" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        /**
         * Only show whitelabel when not active (will be reset on plugin reactivation)
         */
        if ( ! fv_should_white_label() ) :
        ?>
        <!-- White Label settings -->
        <div id="tab-white-label" class="row">
            <div class="col-md-12">
                <h5 class="text-white mt-4 fw-bold">
                    White labeling Festinger Vault
                </h5>
                <!-- Festinger Vault white labeling feature  -->
                <div class="card mb-3 card-bg-cus" style="padding: 20px 0 !important;min-width: 100%;">
                    <div class="card-body container-padding">
                        <form method="post" action="" name="wl_form">
                            <div class="row">
                                <!-- Agency details section -->
                                <div class="col-md-12">
                                    <p class="fw-bold text-secondary mb-0 secondary-heading-light">
                                        AGENCY DETAIL
                                    </p>
                                </div>
                                <!-- Author -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="agency_author" class="form-label">
                                            Agency Author
                                        </label>
                                        <input type="text" class="form-control search_bg_clr"
                                            name="agency_author"
                                            value="<?= get_option('wl_fv_plugin_agency_author_wl_'); ?>"
                                        />
                                    </div>
                                </div>
                                <!-- Author URL -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="agency_author" class="form-label">
                                            Agency Author URL
                                        </label>
                                        <input type="text" class="form-control search_bg_clr"
                                            name="agency_author_url"
                                            value="<?= get_option('wl_fv_plugin_author_url_wl_'); ?>"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Author detail section -->
                                <div class="col-md-12">
                                    <p class="fw-bold text-secondary mb-2 mt-3 secondary-heading-light">
                                        PLUGIN DETAIL
                                    </p>
                                </div>
                                <!-- Plugin name -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fv_plugin_name" class="form-label">
                                            Plugin Name
                                        </label>
                                        <input type="text" class="form-control search_bg_clr"
                                            name="fv_plugin_name"
                                            value="<?= get_option('wl_fv_plugin_name_wl_'); ?>"
                                        />
                                    </div>
                                </div>
                                <!-- Plugin slogan -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fv_plugin_name" class="form-label">
                                            Plugin Slogan
                                        </label>
                                        <input type="text" class="form-control search_bg_clr"
                                            name="fv_plugin_slogan"
                                            value="<?= get_option('wl_fv_plugin_slogan_wl_'); ?>"
                                        />
                                    </div>
                                </div>
                                <!-- Plugin icon URL -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="fv_plugin_icon_url" class="form-label">
                                            Plugin Icon URL
                                        </label>
                                        <input type="text" class="form-control search_bg_clr"
                                            name="fv_plugin_icon_url"
                                            value="<?= get_option('wl_fv_plugin_icon_url_wl_'); ?>"
                                        />
                                    </div>
                                </div>
                                <!-- Default product image URL (used as featured image on products that have no image) -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="fv_default_product_image_url" class="form-label">
                                            Default Product Image URL (used when plugin of theme has no image)
                                        </label>
                                        <input type="text" class="form-control search_bg_clr"
                                            name="fv_default_product_image_url"
                                            value="<?= get_option('wl_fv_default_product_image_url_wl_'); ?>"
                                        />
                                    </div>
                                </div>
                                <!-- Plugin description -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="fv_plugin_description" class="form-label">
                                            Plugin Description
                                        </label>
                                        <textarea class="form-control search_bg_clr"
                                            name="fv_plugin_description"
                                            rows="4"><?= get_option('wl_fv_plugin_description_wl_'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Enable white labeling -->
                                <div class="col-12">
                                    <div class="form-check mt-3">
                                        <input class="form-check-input custom-checkbox-color"
                                            type="checkbox"
                                            id="fv_plugin_wl_enable"
                                            name="fv_plugin_wl_enable"
                                            value="1">
                                        <label class="form-check-label"
                                            for="fv_plugin_wl_enable"
                                            style="margin-top: -19px;">
                                            Enable white label
                                        </label>
                                    </div>
                                    <div id="white_label_help" class="form-text">
                                        By enabling white label, The white label settings will be removed.
                                        If you want to access while label settings in future, simply deactivate the
                                        Festinger Vault plugin and activate it again.
                                    </div>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-12'>
                                    <hr style="margin: 25px auto; height:0.5px; background-color: #4d378e !important;" />
                                </div>
                                <!-- White label submit button -->
                                <div class='col-md-12'>
                                    <?php
                                    if ( $fv_api->license_1->options->white_label == 'yes'
                                    ||   $fv_api->license_2->options->white_label == 'yes' ):
                                        ?>
                                        <input type="submit" class="btn non_active_button primary-btn"
                                            name="fv_white_label_form_submit_button"
                                            value="Submit"
                                        />
                                    <?php else: ?>
                                        <button class="btn non_active_button primary-btn"
                                            id="white_label">
                                            Submit
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class='col-md-10'></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif;?>
        <!-- Hide admin notices settings -->
        <div id="tab-admin-notices" class="row">
            <div class="col-md-12">
                <h5 class="text-white mt-4 fw-bold">
                    Hide/Block admin notices
                </h5>
                <!-- Hide/Block admin notices -->
                <div class="card card-bg-cus mb-3 " style="padding: 20px 0 !important; min-width: 100%;">
                    <div class="card-body padding-horizontal">
                        <!-- Hide admin notices form -->
                        <form method="post" action="" name="an_form">
                            <div class="row mt-3">
                                <!-- hide only dismissable admin notices  -->
                                <div class="col-md-5">
                                    <div class="form-check">
                                        <input id="only_dismissable" class="form-check-input custom-checkbox-color"
                                            type="checkbox"
                                            name="fv_hide_dismissable_admin_notices"
                                            value="1"
                                            <?php echo fv_set_checked_attr( fv_should_hide_dismissable_admin_notices() ); ?>
                                        />
                                        <label class="form-check-label" for="only_dismissable" style="margin-top: -19px;">
                                            Block only Dismissable admin notices
                                        </label>
                                    </div>
                                </div>
                                <!-- hide all admin notices  -->
                                <div class="col-md-5 margin-bottom-xs-20">
                                    <div class="form-check">
                                        <input id="all_notices" class="form-check-input custom-checkbox-color"
                                            type="checkbox"
                                            name="fv_hide_all_admin_notices"
                                            value="1"
                                            <?php echo fv_set_checked_attr( fv_should_hide_all_admin_notices() ); ?>
                                        />
                                        <label class="form-check-label"
                                            for="all_notices"
                                            style="margin-top: -19px;">
                                            Block All admin notices
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class='col-md-12'>
                                    <hr style="margin: 25px auto; height:0.5px; background-color: #4d378e !important;" />
                                </div>
                                <!-- hide admin notices submit button -->
                                <div class="col-md-2">
                                    <input class="mt-3 btn btn-block non_active_button primary-btn"
                                        type="submit"
                                        name="fv_admin_notice_form_submit_button"
                                        value="Update"
                                    />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Select plugins to ignore settings -->
        <div id="tab-ignore-plugins" class="row">
            <div class="col-md-12">
                <h5 class="text-white mt-4 mb-3 fw-bold">
                    Ignore and hide plugins on plugin updates page.
                </h5>
                <!-- Ignore plugins feature  -->
                <div class="card mb-3 card-bg-cus" style="padding: 20px 0 !important;min-width: 100%;">
                    <div class="card-body container-padding">
                        <form method="post" action="" name="fv_plugins_ignore_form">
                            <div class="row">
                                <!-- Ignore disabled plugins section -->
                                <div class="col-md-12">
                                    <div class="fw-bold text-secondary secondary-heading-light" style='font-size: 1.2rem !important; margin-bottom: 1rem !important;'>
                                        Check to ignore disabled plugins
                                    </div>
                                    <!-- ignore disabled plugins checkbox -->
                                    <div class="col-md-5">
                                        <div class="form-check">
                                            <input id="fv_ignore_disabled_plugins" class="form-check-input custom-checkbox-color"
                                                type="checkbox"
                                                name="fv_ignore_disabled_plugins"
                                                value="1"
                                                <?php echo fv_set_checked_attr( fv_should_ignore_disabled_plugins() ); ?>
                                            />
                                            <label class="form-check-label" for="fv_ignore_disabled_plugins" style="margin-top: -19px;">
                                                Auto-update active plugins only.
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Ignore plugins section -->
                                <div class="col-md-12">
                                    <p class="fw-bold text-secondary mb-0 secondary-heading-light" style='font-size: 1.3rem !important; margin-top: 1rem !important;'>
                                        Ignore plugins
                                    </p>
                                </div>
                                <!-- Ignore plugins in list toggle -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input id="fv_ignore_plugins_in_list" class="form-check-input custom-checkbox-color"
                                                type="checkbox"
                                                name="fv_ignore_plugins_in_list"
                                                value="1"
                                                <?php echo fv_set_checked_attr( fv_should_ignore_plugins_in_list() ); ?>
                                            />
                                            <label class="form-check-label" for="fv_ignore_plugins_in_list" style="margin-top: -19px;">
                                                Ignore plugins as listed below.
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Ignore plugins in list toggle -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input id="fv_reset_plugins_in_ignore_list" class="form-check-input custom-checkbox-color"
                                                type="checkbox"
                                                name="fv_reset_plugins_in_ignore_list"
                                                value="1"
                                            />
                                            <label class="form-check-label" for="fv_reset_plugins_in_ignore_list" style="margin-top: -19px;">
                                                Reset ignored plugins list (below).
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Ignore plugins list  -->
                                <div class="col-md-12">
                                    <p class="fw-bold text-secondary mb-0 secondary-heading-light" style='font-size: 1.3rem !important; margin-top: 1rem !important; margin-bottom: 1.5rem !important;'>
                                        Select plugins to ignore
                                    </p>
                                </div>
                                <div class="row mt-3">
                                    <p class="text-secondary" style='font-size: 1.1rem !important; color: #fff !important;'>
                                    Please check the plugins you want to ignore in plugin updates.
                                    </p>
                                </div>
                                <!-- Ignore checked plugins  -->
                                <?php
                                foreach ( fv_get_plugins( use_ignore_plugins_list: false ) as $basename => $plugin ) :
                                    ?>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-check">
                                                <input id="<?php echo sanitize_title( $basename ) ?>" class="form-check-input custom-checkbox-color"
                                                    type="checkbox"
                                                    name="fv_ignore_plugins_list[<?php echo $basename ?>]"
                                                    value="1"
                                                    <?php echo fv_set_checked_attr( fv_should_ignore_plugin( $basename ) ); ?>
                                                />
                                                <label class="form-check-label" for="<?php echo sanitize_title( $basename ) ?>" style="margin-top: -19px;">
                                                <?php echo wp_strip_all_tags( $plugin['Name'] . ' - by ' . $plugin['Author'] ); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                endforeach;
                                ?>
                                <!-- Form submit button -->
                                <div class="row">
                                    <div class='col-md-12'>
                                        <hr style="margin: 25px auto; height:0.5px; background-color: #4d378e !important;" />
                                    </div>
                                    <div class="col-md-2">
                                        <input class="mt-3 btn btn-block non_active_button primary-btn"
                                            type="submit"
                                            name="fv_plugins_ignore_form"
                                            value="Update"
                                        />
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Select themes to ignore settings -->
        <div id="tab-ignore-themes" class="row">
            <div class="col-md-12">
                <h5 class="text-white mt-4 fw-bold">
                    Ignore and hide themes on theme updates page.
                </h5>
                <!-- Ignore themes feature  -->
                <div class="card mb-3 card-bg-cus" style="padding: 20px 0 !important;min-width: 100%;">
                    <div class="card-body container-padding">
                        <form method="post" action="" name="fv_themes_ignore_form">
                            <div class="row">
                                <!-- Ignore disabled themes section -->
                                <div class="col-md-12">
                                    <div class="fw-bold text-secondary secondary-heading-light" style='font-size: 1.2rem !important; margin-bottom: 1rem !important;'>
                                        Check to ignore disabled themes
                                    </div>
                                </div>
                                <!-- ignore disabled themes checkbox -->
                                <div class="col-md-5">
                                    <div class="form-check">
                                        <input id="fv_ignore_disabled_themes" class="form-check-input custom-checkbox-color"
                                            type="checkbox"
                                            name="fv_ignore_disabled_themes"
                                            value="1"
                                            <?php echo fv_set_checked_attr( fv_should_ignore_disabled_themes() ); ?>
                                        />
                                        <label class="form-check-label" for="fv_ignore_disabled_themes" style="margin-top: -19px;">
                                            Auto-update active themes only.
                                        </label>
                                    </div>
                                </div>
                                <!-- Ignore themes section -->
                                <div class="col-md-12 mt-4 mb-1">
                                    <p class="fw-bold text-secondary mb-0 secondary-heading-light" style='font-size: 1.3rem !important;'>
                                        Ignore and hide themes
                                    </p>
                                </div>
                                <!-- Ignore themes in list toggle -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input id="fv_ignore_themes_in_list" class="form-check-input custom-checkbox-color"
                                                type="checkbox"
                                                name="fv_ignore_themes_in_list"
                                                value="1"
                                                <?php echo fv_set_checked_attr( fv_should_ignore_themes_in_list() ); ?>
                                            />
                                            <label class="form-check-label" for="fv_ignore_themes_in_list" style="margin-top: -19px;">
                                                Ignore and hide themes that are checked in the list below.
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Ignore themes in list toggle -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input id="fv_reset_themes_in_ignore_list" class="form-check-input custom-checkbox-color"
                                                type="checkbox"
                                                name="fv_reset_themes_in_ignore_list"
                                                value="1"
                                            />
                                            <label class="form-check-label" for="fv_reset_themes_in_ignore_list" style="margin-top: -19px;">
                                                Reset the ignore/hide themes list below (removes all checks).
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Ignore themes list  -->
                                <div class="col-md-12">
                                    <p class="fw-bold text-secondary mb-1 mt-4 secondary-heading-light" style='font-size: 1.3rem !important;'>
                                        Select themes to ignore and hide in the update page.
                                    </p>
                                </div>
                                <div class="row mt-2">
                                    <p class="text-secondary" style='font-size: 1.1rem !important; color: #fff !important;'>
                                    Please check the themes you want to ignore and hide in the theme updates page.
                                    </p>
                                </div>
                                <?php
                                foreach ( fv_get_themes( use_ignore_themes_list: false ) as $stylesheet => $theme ) :
                                    ?>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-check">
                                                <input id="<?php echo sanitize_title( $stylesheet ); ?>" class="form-check-input custom-checkbox-color"
                                                    type="checkbox"
                                                    name="fv_ignore_themes_list[<?php echo $stylesheet ?>]"
                                                    value="1"
                                                    <?php echo fv_set_checked_attr( fv_should_ignore_theme( $stylesheet ) ); ?>
                                                />
                                                <label class="form-check-label" for="<?php echo sanitize_title( $stylesheet ); ?>" style="margin-top: -19px;">
                                                <?php echo wp_strip_all_tags( $theme->Name . ' - by ' . $theme->Author ); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                endforeach;
                                ?>
                                <div class="row">
                                    <div class='col-md-12'>
                                        <hr style="margin: 25px auto; height:0.5px; background-color: #4d378e !important;" />
                                    </div>
                                    <!-- hide admin notices submit button -->
                                    <div class="col-md-2">
                                        <input class="mt-3 btn btn-block non_active_button primary-btn"
                                            type="submit"
                                            name="fv_themes_ignore_form"
                                            value="Update"
                                        />
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
