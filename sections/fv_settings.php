<div class="container-padding">
    <div class="">
        <div class="col-md-12">
            <?php if(get_option('wl_fv_plugin_wl_enable') !=1):?>
            <h5 class="text-white mt-4 fw-bold">
                Festinger Vault white labeling feature
            </h5>
            <div class="card mb-3 card-bg-cus" style="padding: 20px 0 !important;min-width: 100%;">

                <div class="card-body container-padding">

                    <form method="post" action="" name="wl_form">
                        <div class="row">
                            <div class="col-md-12">
                                <p class="fw-bold text-secondary mb-0 secondary-heading-light">AGENCY DETAIL</p>

                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="agency_author" class="form-label">Agency Author</label>
                                    <input type="text" class="form-control search_bg_clr"
                                        value="<?= get_option('wl_fv_plugin_agency_author_wl_'); ?>"
                                        name="agency_author">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="agency_author" class="form-label">Agency Author URL</label>
                                    <input type="text" class="form-control search_bg_clr"
                                        value="<?= get_option('wl_fv_plugin_author_url_wl_'); ?>"
                                        name="agency_author_url">
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <p class="fw-bold text-secondary mb-2 mt-3 secondary-heading-light">PLUGIN DETAIL</p>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fv_plugin_name" class="form-label">Plugin Name</label>
                                    <input type="text" class="form-control search_bg_clr"
                                        value="<?= get_option('wl_fv_plugin_name_wl_'); ?>" name="fv_plugin_name">
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fv_plugin_name" class="form-label">Plugin Slogan</label>
                                    <input type="text" class="form-control search_bg_clr"
                                        value="<?= get_option('wl_fv_plugin_slogan_wl_'); ?>" name="fv_plugin_slogan">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="fv_plugin_icon_url" class="form-label">Plugin Icon URL</label>
                                    <input type="text" class="form-control search_bg_clr"
                                        value="<?= get_option('wl_fv_plugin_icon_url_wl_'); ?>"
                                        name="fv_plugin_icon_url">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="fv_plugin_description" class="form-label">Plugin Description</label>
                                    <textarea class="form-control search_bg_clr" name="fv_plugin_description"
                                        rows="4"><?= get_option('wl_fv_plugin_description_wl_'); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">

                                <div class="form-check mt-3">
                                    <input class="form-check-input custom-checkbox-color" id="fv_plugin_wl_enable" type="checkbox" value="1"
                                        name="fv_plugin_wl_enable">
                                    <label class="form-check-label" for="fv_plugin_wl_enable"
                                        style="margin-top: -19px;">
                                        Enable white label
                                    </label>
                                </div>

                                <div id="white_label_help" class="form-text">By enabling white label, The white label
                                    settings will be removed.

                                    If you want to access while label settings in future, simply deactivate the
                                    Festinger Vault plugin and activate it again. </div>


                            </div>
                        </div>
      


                        <div class='row'>
                            <div class='col-md-12'>
                                <hr style="margin: 25px auto; height:0.5px; background-color: #4d378e !important;" />
                            </div>
                            <div class='col-md-12'>
                                <?php 
				  			if($all_license_data->license_1->options->white_label == 'yes' || $all_license_data->license_2->options->white_label=='yes'):

				  		?>
                                <input type="submit" class="btn  non_active_button primary-btn" name="fv_wl_submit"
                                    value="Submit" />
                                <?php else: ?>
                                <button class="btn  non_active_button primary-btn" id="white_label">Submit</button>
                                <?php endif; ?>

                            </div>

                            <div class='col-md-10'></div>
                        </div>

                </div>

                </form>

            </div>
        </div>

        <?php endif;?>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h5 class="text-white mt-4 fw-bold">
                Hide/Block admin notices
            </h5>
            <div class="card card-bg-cus mb-3 " style="padding: 20px 0 !important; min-width: 100%;">

                <div class="card-body padding-horizontal">
                    <form method="post" action="" name="an_form">
                        <div class="row mt-3">
                            <div class="col-md-5">

                                <div class="form-check">
                                    <input class="form-check-input custom-checkbox-color" type="checkbox"
                                        <?php if(get_option('an_fv_dis_adm_not_hid') == 1){echo 'checked';} ?>
                                        name="an_fv_dis_adm_not_hid" value="1" id="only_dismissable">
                                    <label class="form-check-label" for="only_dismissable" style="margin-top: -19px;">
                                        Block only Dismissable admin notices
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-5 margin-bottom-xs-20">
                                <div class="form-check">
                                    <input class="form-check-input custom-checkbox-color" name="an_fv_all_adm_not_hid"
                                        type="checkbox"
                                        <?php if(get_option('an_fv_all_adm_not_hid') == 1){echo 'checked';} ?> value="1"
                                        id="all_notices">
                                    <label class="form-check-label" for="all_notices" style="margin-top: -19px;">
                                        Block All admin notices
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class='col-md-12'>
                                <hr style="margin: 25px auto; height:0.5px; background-color: #4d378e !important;" />
                            </div>
                            <div class="col-md-2"><input type="submit"
                                    class="mt-3 btn btn-block non_active_button primary-btn" name="fv_admin_notice"
                                    value="Update" /></div>
                        </div>
                </div>

                </form>

            </div>
        </div>

    </div>
</div>
</div>









</div>