<?php
/**
 * HTML for the Festinger Vault page.
 *
 * Presumes the following vars:
 * $fv_api
 */
?>
<!-- spinner -->
<div id="overlay">
    <div class="loading-container">
        <div class="loading"></div>
        <div id="loading-text">loading</div>
    </div>
</div>
<!-- succes-message -->
<div class="toast position-fixed bottom-0 end-0 active_button" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body text-white">
        Item has been added successfully.
    </div>
</div>
<!-- page header -->
<div class="container-fluid p-0" id="fv_nav_main">
    <nav class="navbar fv_nav_bg container-fluid padding-horizontal py-2">
        <div class="mx-auto" style="width: 100%; max-width: 1400px; justify-content: flex-end !important; display: block;">
            <!--  site logo and name -->
            <div class="navbar-brand float-start margin-bottom-xs" style="margin-top: 10px;">
                <img src="<?php echo fv_perhaps_white_label_plugin_icon_url(); ?>" style="margin-top: -4px;margin-right: 3px;">
                <?php echo fv_perhaps_white_label_plugin_name(); ?>
                <br />
                <small class="d-none d-sm-block" style="margin-left: 24px;font-size: 14px;">
                    <?php echo fv_perhaps_white_label_plugin_slogan(); ?>
                </small>
            </div>
            <!--  modal message -->
            <div class="modal fade" id="messages" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body" style="color: black;"></div>
                    </div>
                </div>
            </div>
            <!-- install and activate modal -->
            <div class="modal fade" id="empModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="background: rgba( 0, 0, 0, 0.8 );">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="color: black;"></div>
                        <div class="progress" role="progressbar" aria-label="Animated striped example" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>
                        <div id="installation-status"></div>
                    </div>
                </div>
            </div>
            <?php
            // <!-- First License -->
            $license                          = $fv_api->license_1->license_data;
            $license->license_status_bg_class = ( 'Activated' === $license->license_status ) ? 'bg-tag' : 'bg-danger';
            if ( fv_license_found( $fv_api->validation->license_1 ) ):
                print_license_table_markup( $license );
            endif;

            // <!-- Second license -->
            $license                          = $fv_api->license_2->license_data;
            $license->license_status_bg_class = fv_active_license( $license->license_status ) ? 'bg-tag' : 'bg-danger';
            if ( fv_license_found( $fv_api->validation->license_1 ) ):
                print_license_table_markup( $license );
            endif;

	        if ( fv_domain_blocked( $fv_api->validation->result ) ):
                ?>
                <!-- domain is blocked -->
                <div class="float-end margin-bottom-xs border-8 px-3 mx-2">
                    <div style="margin-left: 20px; min-height: auto !important; padding: 4px !important;"
                        class="btn btn-danger btn-sm btn-block">
                        WARNING: Domain is Blocked
                        <hr />
                        <?php echo $fv_api->validation->msg; ?>
                    </div>
                </div>
                <?php
            elseif ( fv_license_not_found( $fv_api->validation->license_1 )
                &&   fv_license_not_found( $fv_api->validation->license_2 ) ):
                ?>
                <!-- activate or buy a license -->
                <div class="float-end border-8 dark-blue p-3 mx-2 activate-license-notice">
                    <a  href="<?php echo admin_url( 'admin.php?page=festinger-vault-activation' ); ?>"
                        class="btn non_active_button activate-your-license btn-sm btn-block text-center d-flex align-items-center mb-2">
                        ACTIVATE YOUR LICENSE
                    </a>
                    <a  href="https://festingervault.com/get-started/"
                        target="_blank"
                        class="btn btn-sm btn-block primary-btn mt-2 purchase-license">
                        PURCHASE LICENSE
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</div>
<div class="container-fluid">
    <?php
        // <!-- Installation success notice -->
        if ( isset( $_GET['installation'] )
        &&   isset( $_GET['slug'] ) ) :
	        if ( 'success' === $_GET['installation'] ) :
                ?>
                <div class="alert alert-custom-clr alert-dismissible fade show mt-3" role="alert">
                    <strong>Congratulations</strong> <?php echo fv_slug_to_title( wp_strip_all_tags( $_GET['slug'] ) ) ?> is installed successfully.
                    <a href="<?php echo admin_url( 'admin.php?page=festinger-vault' ); ?>" class="btn-close" aria-label="Close"></a>
                </div>
                <?php
            endif;
        endif;
        if ( in_array( $fv_api->validation->result, array( 'failed', 'notfound', 'invalid' ) ) ): ?>
            <!-- License validation warning -->
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Warning !</h3>
                            <hr />
                            <h4 class="text-center text-danger"><?php echo $fv_api->validation->msg; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- filters bar -->
            <div class="row" style="background: #292055;padding: 20px 0px;">
                <div class="container-padding">
                    <!-- Top filter bar -->
                    <div class=" my-3">
                        <div class="row justify-content-between">
                            <!-- search field -->
                            <div class="col-md-4">
                                <div class="input-group h-100">
                                    <input type="text" class="form-control  search_bg_clr btn-block margin-bottom-xs" style="text-align: left;" id='ajax_search' placeholder="Search here... ( e.g. Elementor Pro )" name="ajax_search">
                                </div>
                            </div>
                            <!-- Allowance filter (bronze, silver, gold)-->
                            <div class="col-md-2">
                                <select class="form-select home_con_select btn-block margin-bottom-xs" id="filter_item"
                                    aria-label="Default select example">
                                    <option selected value="all">Filter allowence</option>
                                    <option value="bronze">Bronze</option>
                                    <option value="silver">Silver</option>
                                    <option value="gold">Gold</option>
                                </select>
                            </div>
                            <!-- Type filter (plugins, themes, elementor) -->
                            <div class="col-md-2">
                                <select class="form-select home_con_select btn-block margin-bottom-xs" id="filter_type"
                                    aria-label="Default select example">
                                    <option selected value="all">Filter type</option>
                                    <option value="wordpress-plugins">WordPress Plugins</option>
                                    <option value="wordpress-themes">WordPress Themes</option>
                                    <option value="elementor-template-kits">Elementor Template Kits</option>
                                </select>
                            </div>
                            <!-- Category filter (plugin/themes agencies) -->
                            <div class="col-md-2">
                                <select class="form-select home_con_select btn-block margin-bottom-xs" id="filter_category"
                                    aria-label="Default select example">
                                    <option selected value="all">Filter Category</option>
                                    <?php
                                    foreach( json_decode( $fv_api->category_list ) as $category ):
                                        if ( strlen( $category->category_slug )>1 ):
                                            ?>
                                            <option data-type="<?php echo $category->type_slug; ?>" class="filter_type_cate_val" value="<?php echo $category->category_slug; ?>">
                                                <?php echo ucwords( str_replace( '-', ' ', $category->category_slug ) ); ?>
                                            </option>
                                            <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                            <!-- Reset button -->
                            <div class="col-md-1">
                                <button type="button" class="btn float-end non_active_button_purple" id="reset_filter" value="reset_filter" style="height: 37px;">
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Lower filter bar -->
                    <div class="row my-3">
                        <!-- Tabs: Popular / Recent / Featured / My List -->
                        <div class="col-md-8">
                            <div class="d-inline-block dark-blue border-8 p-2">
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="mr-1 btn  non_active_button margin-bottom-xs active_button" id="popular" value="popular">
                                        Popular
                                    </button>
                                    <button type="button" class="mx-1 btn  non_active_button margin-bottom-xs" id="recent" value="recent">
                                        Recent
                                    </button>
                                    <button type="button" class="mx-1 btn  non_active_button margin-bottom-xs" id="featured" value="featured">
                                        Featured
                                    </button>
                                    <button type="button" class="mx-1 btn  non_active_button margin-bottom-xs" id="mylist" value="mylist">
                                        My List
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Bulk actions -->
                        <div class="col-md-4">
                            <div class="dropdown float-end">
                                <button class="btn btn-secondary dropdown-toggle cart-get-dropdown" type="button" id="cart-dropdown" aria-expanded="false">
                                    <i class="fas fa-cloud-download-alt"></i>
                                    Bulk
                                    <span class="cart-count"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end cart-dropdown get-cart-dropdownsub" aria-labelledby="cart-dropdown" style="width: 400px;padding: 10px;">
                                    <div id="cart-items" class="cart-items"></div>
                                    <div id="cart-items-notfound" class="cart-items-notfound">No item found</div>
                                    <li>
                                        <button id="download-button" class="btn btn-success btn-md btn-block">
                                            Download All
                                        </button>
                                    </li>
                                    <li>
                                        <button id="install-button" class="btn btn-primary btn-md btn-block">
                                            Install All
                                        </button>
                                    </li>
                                    <li>
                                        <button id="clearall-button" class="btn btn-warning btn-md btn-block">
                                            Clear All
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Festinger Vault Content (filled by JS) -->
            <div class="row mt-2">
                <div class="container-padding">
                    <div id="list">
                        <div class="wrapper"></div>
                    </div>
                    <nav>
                        <ul class="pagination" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>

<?php
// this inserts a js-script (inserts the content from fv api)
echo ( $fv_api->cat_func );

function print_license_table_markup( stdClass $license ) : void {
    $gap = 'margin-left: 10px !important;';
    ?>
    <!-- License 2 info block -->
    <div class="float-end margin-bottom-xs border-8 dark-blue px-3 mx-2">
        <p style="margin-top: 15px;color: #fff;">
            <table cellpadding="3">
                <tr>
                    <!-- plan label -->
                    <td class="text-grey">
                        <i class="fas fa-bullhorn" style="font-size: 15px !important;"></i>
                        <span>Plan</span>
                    </td>
                    <!--  plan name + status-->
                    <td>
                        <span style="<?php echo $gap ?>">
                            <?php echo $license->license_plan_name; ?>
                            <span style="color: #fff; <?php echo $gap ?>" class="badge <?php echo $license->license_status_bg_class?>">
                                <?php echo fv_get_license_status_text( $license->license_status ) ?>
                            </span>
                        </span>
                    </td>
                </tr>
                <tr>
					<!-- credit limit label -->
                    <td class="text-grey" font-size: 15px !important;'>
                        <i class="fas fa-coins" style="font-size: 15px !important;"></i>
                        <span>Credit Limit</span>
                    </td>
					<!-- credit limit -->
                    <td id="<?php echo $license->license_key; ?>">
                        <span id="limit_available_id" style="<?php echo $gap ?>"> </span>
                        <span id="current_limit_id">
                            <?php echo $license->plan_credit_available; ?>
                            /
                            <?php echo $license->plan_credit_limit; ?>
                        </span>
                        <?php echo ucfirst( $license->license_type ); ?><br />
                    </td>
                </tr>
                <?php if ( 'onetime' === $license->license_type ): ?>
                    <!-- one time license credit -->
                    <tr>
                        <td colspan='2' class='text-grey'>
                            <i class='fas fa-sync-alt'></i>&nbsp;
                            <span>Need more credits?</span>
                            <a style='font-size: 12px;' target='_blank' href='https://festingervault.com/pay-as-you-go/'>
                                Purchase
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
					<!-- Recurring license credit -->
                    <tr>
                        <td class='text-grey'>
                            <i class='fas fa-sync-alt' style='font-size: 15px !important;'></i>&nbsp;
                            <span>Reset</span>
                        </td>
                        <td>
							<span style='<?php echo $gap; ?>'>
								Credit limit reset in <?php echo get_hours_to_midnight() ?> hour(s)
							</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </p>
    </div>
    <?php
}
