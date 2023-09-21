<div id="overlay">
    <div class="loading-container">
        <div class="loading"></div>
        <div id="loading-text">loading</div>
    </div>
</div>

<div class="toast position-fixed bottom-0 end-0 active_button" role="alert" aria-live="assertive" aria-atomic="true">
  <div class="toast-body text-white">
    Item has been added successfully.
  </div>
</div>

<div class="container-fluid p-0" id="fv_nav_main">

    <nav class="navbar fv_nav_bg container-fluid padding-horizontal py-2" style="">
        <div class="mx-auto" style="width:100%; max-width:1400px; justify-content:flex-end!important; display: block;">
            <div class="navbar-brand float-start margin-bottom-xs" style="margin-top: 10px;">



                <img src="<?= get_adm_men_img();?> " style="margin-top: -4px;margin-right: 3px;">

                <?= get_adm_men_name();?> <br />
                <small class="d-none d-sm-block" style="margin-left: 24px;font-size: 14px;">
                    <?= get_adm_men_slogan();?>
                </small>


            </div>


            <div class="modal fade" id="messages" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body" style="color: black;"></div>
                    </div>
                </div>
            </div>




            <div class="modal fade" id="empModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
                style="background: rgba(0,0,0,0.8);">
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
              date_default_timezone_set("UTC");
              $datetime = new DateTime('tomorrow');
              $next_day_start = $datetime->format('Y-m-d H:i:s');
              $get_now = date('Y-m-d H:i:s');
              $hourdiff = round((strtotime($next_day_start) - strtotime($get_now))/3600, 1);
              $reset_in_next_hours =  $hourdiff.' hour(s)';
            ?>


            <?php if($all_license_data->validation->license_1 != 'notfound'): ?>




            <div class="float-end margin-bottom-xs border-8 dark-blue p-3 mx-2">

                <table cellpadding="3">
                    <tr>
                        <td class="text-grey" style='margin-right:15px;'>
                            <i class="fas fa-bullhorn" style="font-size:15px!important;"></i>
                            <span>Plan</span>

                        </td>
                        <td>
                            <span style=" padding-left:10px!important;">
                                <?php echo($all_license_data->license_1->license_data->license_plan_name);?>
                                <?php if($all_license_data->license_1->license_data->license_status == 'valid'): ?>
                                <span style="color:#fff;" class="badge bg-tag">Activated</span>
                                <?php elseif($all_license_data->license_1->license_data->license_status == 'invalid'): ?>
                                <span style="color:#fff;" class="badge bg-danger">Suspended</span>
                                <?php else: ?>
                                <span style="color:#fff;"
                                    class="badge bg-danger"><?= ucfirst($all_license_data->license_1->license_data->license_status); ?></span>
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-grey" style='margin-right:15px;'>
                            <i class="fas fa-coins" style="font-size:15px!important;"></i><span>Credit Limit </span>
                        </td>
                        <td id="<?= $all_license_data->license_1->license_data->license_key;?>">
                            <span id="limit_available_id" style=" padding-left:10px!important;"> </span> <span
                                id="current_limit_id">
                                <?= $all_license_data->license_1->license_data->plan_credit_available;?> /
                                <?= $all_license_data->license_1->license_data->plan_credit_limit;?> </span>

                            <?= ucfirst($all_license_data->license_1->license_data->license_type);?><br />


                        </td>
                    </tr>



                    <?php if($all_license_data->license_1->license_data->license_type == 'onetime'){
                                echo "<tr><td colspan='2' class='text-grey' style='margin-right:15px;'><i class='fas fa-sync-alt'></i>&nbsp;<span>Need more credits?</span> <a style='font-size:12px;' target='_blank' href='https://festingervault.com/pay-as-you-go/'>Purchase</a></td></tr></table>";
                                }else{
                                echo "<tr><td class='text-grey' style='margin-right:15px;'><i class='fas fa-sync-alt' style='font-size:15px!important;'></i><span>Reset</span> </td><td><span style=' padding-left:10px!important;''>Credit limit reset in ".$reset_in_next_hours."</span></td></tr></table>";
                                }
                                ?>

                    <!-- Table is closed in the PHP string above -->



            </div>




            <?php endif;?>



            <?php if($all_license_data->validation->license_2 != 'notfound'): ?>



            <div class="float-end margin-bottom-xs border-8 dark-blue px-3 mx-2">
                <p style="margin-top:15px;color:#fff;">
                <table cellpadding="3">
                    <tr>
                        <td class="text-grey" style='margin-right:15px;'>
                            <i class="fas fa-bullhorn" style="font-size:15px;"></i>
                            <span>Plan</span>

                        </td>
                        <td>
                            <?php echo($all_license_data->license_2->license_data->license_plan_name);?>
                            <?php if($all_license_data->license_2->license_data->license_status == 'valid'): ?>
                            <span style="color:#fff;" class="badge bg-tag">Activated</span>
                            <?php elseif($all_license_data->license_2->license_data->license_status == 'invalid'): ?>
                            <span style="color:#fff;" class="badge bg-danger">Suspended</span>
                            <?php else: ?>
                            <span style="color:#fff;"
                                class="badge bg-danger"><?= ucfirst($all_license_data->license_2->license_data->license_status); ?></span>
                            <?php endif; ?>

                        </td>
                    </tr>
                    <tr>
                        <td class="text-grey" style='margin-right:15px;font-size:15px!important;'>
                            <i class="fas fa-coins" style="font-size:15px!important;"></i>&nbsp; <span>Credit Limit
                            </span>
                        </td>
                        <td id="<?= $all_license_data->license_2->license_data->license_key;?>">
                            <span id="limit_available_id"> </span> <span id="current_limit_id">
                                <?= $all_license_data->license_2->license_data->plan_credit_available;?> /
                                <?= $all_license_data->license_2->license_data->plan_credit_limit;?> </span>

                            <?= ucfirst($all_license_data->license_2->license_data->license_type);?><br />

                        </td>
                    </tr>



                    <?php if($all_license_data->license_2->license_data->license_type == 'onetime'){
                                   echo "</table><i class='fas fa-sync-alt' style='font-size:15px!important;'></i>&nbsp;<span>Need more credits?</span> <a style='font-size:12px;' target='_blank' href='https://festingervault.com/pay-as-you-go/'>Purchase</a>";

                                }else{
                                    echo "<tr><td class='text-grey'><i class='fas fa-sync-alt' style='font-size:15px!important;'></i>&nbsp;<span>Reset</span> </td><td>Credit limit reset in ".$reset_in_next_hours."</td></tr></table>";

                                }
                            ?>

                    </p>

            </div>



            <?php endif;?>




            <?php 
	if($all_license_data->validation->result == 'domainblocked'):
?>


            <div class="float-end margin-bottom-xs border-8 dark-blue px-3 mx-2">

                <div style="margin-left: 20px; min-height: auto!important; padding:4px!important;"
                    class="btn btn-danger btn-sm btn-block">
                    WARNING: Domain is Blocked
                    <hr />

                    <?= $all_license_data->validation->msg; ?>
                </div>


            </div>

            <?php else: ?>





            <?php if($all_license_data->validation->license_1 =='notfound' && $all_license_data->validation->license_2 =='notfound'): ?>
            <div class="float-end border-8 dark-blue p-3 mx-2 activate-license-notice">

                <a style="" href="<?= admin_url('admin.php?page=festinger-vault-activation'); ?>"
                    class="btn non_active_button activate-your-license btn-sm btn-block text-center d-flex align-items-center mb-2">ACTIVATE
                    YOUR
                    LICENSE</a>

                <a href="https://festingervault.com/get-started/" target="_blank"
                    class="btn btn-sm btn-block primary-btn mt-2 purchase-license">PURCHASE LICENSE</a>
            </div>
            <?php endif; ?>

            <?php endif; ?>




        </div>
    </nav>



</div>


<div class="container-fluid">

    <?php if(isset($_GET['installation']) && isset($_GET['slug']) ): 
	if($_GET['installation'] == 'success')
?>

    <div class="alert alert-custom-clr alert-dismissible fade show mt-3" role="alert">
        <strong>Congratulations</strong> <?= remove_under_middle_score($_GET['slug']) ?> is installed successfully.
        <a href="<?= admin_url('admin.php?page=festinger-vault');?>" class="btn-close" aria-label="Close"></a>
    </div>

    <?php endif;?>
    <?php if($all_license_data->validation->result == 'failed' || $all_license_data->validation->result == 'notfound' || $all_license_data->validation->result == 'invalid' ):?>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-center">Warning !</h3>
                    <hr />
                    <h4 class="text-center text-danger">
                        <?= $all_license_data->validation->msg; ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <?php else: 

?>

    <div class="row" style="background: #292055;padding: 20px 0px;">
        <div class="container-padding">
            <div class=" my-3">
                <div class="row justify-content-between">
                    <div class="col-md-4">
                        <div class="input-group h-100">
                            <input type="text" class="form-control  search_bg_clr btn-block margin-bottom-xs"
                                style="text-align: left;" id='ajax_search'
                                placeholder="Search here... (e.g. Elementor Pro)" name="ajax_search">
                        </div>
                    </div>
                    <div class="col-md-2">

                        <select class="form-select home_con_select btn-block margin-bottom-xs" id="filter_item"
                            aria-label="Default select example">
                            <option selected value="all">Filter allowence</option>
                            <option value="bronze">Bronze</option>
                            <option value="silver">Silver</option>
                            <option value="gold">Gold</option>
                        </select>

                    </div>
                    <div class="col-md-2">

                        <select class="form-select home_con_select btn-block margin-bottom-xs" id="filter_type"
                            aria-label="Default select example">
                            <option selected value="all">Filter type</option>
                            <option value="wordpress-plugins">WordPress Plugins</option>
                            <option value="wordpress-themes">WordPress Themes</option>
                            <option value="elementor-template-kits">Elementor Template Kits</option>
                        </select>

                    </div>

                    <div class="col-md-2">
                        <select class="form-select home_con_select btn-block margin-bottom-xs" id="filter_category"
                            aria-label="Default select example">
                            <option selected value="all">Filter Category</option>
                            <?php 
							 foreach(json_decode($all_license_data->category_list) as $category):
                                if(strlen($category->category_slug)>1):
							?>
                                <option data-type="<?= $category->type_slug; ?>" class="filter_type_cate_val"
                                value="<?= $category->category_slug; ?>">
                                <?= ucwords(str_replace('-', ' ', $category->category_slug)); ?></option>
                            <?php 
                                endif; 
                                endforeach;
                            ?>
                        </select>

                    </div>


                    <div class="col-md-1">
                        <button type="button" class="btn float-end non_active_button_purple" id="reset_filter"
                            value="reset_filter" style="height: 37px;">Reset</button>
                    </div>
                </div>
            </div>
            <div class="row my-3 ">
                <div class="col-md-8">
                    <div class="d-inline-block dark-blue border-8 p-2">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="mr-1 btn  non_active_button margin-bottom-xs active_button"
                                id="popular" value="popular">Popular</button>
                            <button type="button" class="mx-1 btn  non_active_button margin-bottom-xs" id="recent"
                                value="recent">Recent</button>
                            <button type="button" class="mx-1 btn  non_active_button margin-bottom-xs" id="featured"
                                value="featured">Featured</button>
                            <button type="button" class="mx-1 btn  non_active_button margin-bottom-xs" id="mylist"
                                value="mylist">My List</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">


                    <!-- The cart dropdown -->
                    <div class="dropdown float-end">
                      <button class="btn btn-secondary dropdown-toggle cart-get-dropdown" type="button" id="cart-dropdown" aria-expanded="false">
                        <i class="fas fa-cloud-download-alt"></i> Bulk <span class="cart-count"></span>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end cart-dropdown get-cart-dropdownsub" aria-labelledby="cart-dropdown" style="width:400px;padding:10px;">
                        <div id="cart-items" class="cart-items"></div>
                        <div id="cart-items-notfound" class="cart-items-notfound">No item found</div>
                        <li><button id="download-button" class="btn btn-success btn-md btn-block">Download All</button></li>
                        <li><button id="install-button" class="btn btn-primary btn-md btn-block">Install All</button></li>
                        <li><button id="clearall-button" class="btn btn-warning btn-md btn-block">Clear All</button></li>
                      </ul>
                    </div>



                </div>
            </div>
        </div>
    </div>

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
</div>

<?php endif; ?>
<?= ($all_license_data->cat_func);?>