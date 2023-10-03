<div class="container-padding">
    <div class="">
        <div class="col-md-12">
            <?php
            /**
             * Only show whitelabel when not active (will be reset on plugin reactivation)
             */
            if ( ! fv_should_white_label() ) :
                ?>
                <h5 class="text-white mt-4 fw-bold">
                    Festinger Vault white labeling feature
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
                                <!-- Plugin description -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="fv_plugin_description" class="form-label">
                                            Plugin Description
                                        </label>
                                        <textarea class="form-control search_bg_clr"
                                            name="fv_plugin_description"
                                            rows="4">
                                            <?= get_option('wl_fv_plugin_description_wl_'); ?>
                                        </textarea>
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
                                            name="fv_wl_submit"
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
            <?php endif;?>
        </div>
    </div>
    <div class="row">
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
                                        name="an_fv_dis_adm_not_hid"
                                        value="1"
                                        <?php echo fv_should_hide_dismissable_admin_notices() ? 'checked' : ''; ?>
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
                                        name="an_fv_all_adm_not_hid"
                                        value="1"
                                        <?php echo fv_should_hide_all_admin_notices() ? 'checked' : ''; ?>
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
                                    name="fv_admin_notice"
                                    value="Update"
                                />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
