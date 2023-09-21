<div style="display: none;" id="overlaybef">
    <div id="overlay">
        <div class="loading-container">
            <div class="loading"></div>
            <div id="loading-text">loading</div>

        </div>
    </div>
</div>
<div class="container-padding">
    <div class="row my-4 justify-content-center">
        <?php 
                if($all_license_data->validation->license_1 != 'notfound'):
                ?>
        <div class="col-md-6 mt-3 mt-3">



            <div class="card card-bg-cus h-100" style="min-width:100%;">
                <div class="py-4">
                    <div class="card-header card-bottom-border">
                        <h5 class="px-2"><b>License Key:</b></h5>
                        <div>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="w-50"><?php 
                                    
                                    $license_key_view = $all_license_data->license_1->license_data->license_key; 
                                    echo substr($license_key_view,0,5) . '**********************' . substr($license_key_view,-5);

                                  
                                  ?>
                                    </td>
                                    <td class="w-50">
                                        <p class="mb-0">

                                            <?php if($all_license_data->license_1->license_data->license_status == 'valid'): ?>
                                        <p style="color:#fff;" class="badge bg-tag mb-0">Active</p>
                                        <?php else: ?>
                                        <p style="color:#fff;" class="badge bg-danger mb-0">Suspended</p>
                                        <?php endif; ?>

                                        <?php if($all_license_data->license_1->license_data->license_type == 'onetime'): ?>

                                        <!-- Button trigger modal -->
                                        <button type="button" class="px-3 py-2 primary-btn border-0" style=""
                                            data-bs-toggle="modal" data-bs-target="#exampleModal">
                                            Refil Download Credit
                                        </button>

                                        <!-- Modal  modal fade-->
                                        <div class="modal fade" id="exampleModal" aria-labelledby="exampleModalLabel"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header" style="background:#292055;">
                                                        <h6 class="modal-title" id="exampleModalLabel"
                                                            style="font-size: 14px;">
                                                            <b>Plan:</b><?= $all_license_data->license_1->license_data->license_plan_name;?>
                                                            <br /><b>License Key:</b>

                                                            <?php 


                                                                $license_key_view1 = $all_license_data->license_1->license_data->license_key; 
                                                                echo substr($license_key_view1,0,5) . '**********************' . substr($license_key_view1,-5);

                                                            ?>
                                                        </h6>
                                                        <button type="button" class="btn-close text-white"
                                                            data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form class="form" id="ajax-license-refill-form" action="#">

                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <input type="hidden" id="license_key" name="license_key"
                                                                    value="<?= $all_license_data->license_1->license_data->license_key; ?>"
                                                                    required>
                                                                <label for="refill_key"
                                                                    class="form-label refill_button">Enter
                                                                    your
                                                                    unused ONETIME Plan license Key </label>
                                                                <input type="text"
                                                                    class="form-control refill_button text-white"
                                                                    id="refill_key" name="refill_key"
                                                                    placeholder="Please enter your unused one time license key..."
                                                                    required>
                                                            </div>

                                                            <div id="credit_refill_msg"></div>

                                                            <p><b>Note:</b> You can use your onetime purchased
                                                                license
                                                                key as
                                                                refill
                                                                key
                                                                if that is not previously used or activated as
                                                                license.
                                                                Once you
                                                                redeem,
                                                                it will add balance with current key. </p>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button
                                                                style="height: 32px; margin-top: 5px;margin-right: 4px;"
                                                                type="button" class="btn non_active_button"
                                                                data-bs-dismiss="modal">Close</button>

                                                            <a href="https://festingervault.com/pay-as-you-go/"
                                                                class="btn d-flex align-items-center  float-start  non_active_button"
                                                                target="_blank">Purchase Credit</a>
                                                            <a href=""
                                                                class="btn float-start refresh_button non_active_button"
                                                                style="display: none;">Refresh</a>
                                                            <button type="submit" class="btn btn-md primary-btn">Redeem
                                                                &
                                                                Refill Now</button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                        <?php endif;?>

                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                    <div class="card-body card-bottom-border">

                        <table class="table borderless table-borderless mb-0">
                            <tr>
                                <td class="text-grey" style="width:50%;">Plan Name: </td>
                                <td style="width:50%;">
                                    <?= $all_license_data->license_1->license_data->license_plan_name;?> </td>
                            </tr>
                            <tr>
                                <td class="text-grey" style="width:50%;">Plan Type: </td>
                                <td style="width:50%;">
                                    <?= ucfirst($all_license_data->license_1->license_data->license_type);?> </td>
                            </tr>
                            <tr>
                                <td class="text-grey" style="width:50%;">Plan Credit Limit: </td>
                                <td style="width:50%;">

                                    <span id="plan_limit_id">
                                        <?= $all_license_data->license_1->license_data->plan_credit_limit;?> </span>
                                    /
                                    <?= ucfirst($all_license_data->license_1->license_data->license_type);?>
                                </td>
                            </tr>

                            <tr>
                                <td class="text-grey" style="width:50%;">Credit Available: </td>
                                <td style="width:50%;">

                                    <span id="limit_available_id">
                                        <?= $all_license_data->license_1->license_data->plan_credit_available;?> </span>
                                    /
                                    <?= ucfirst($all_license_data->license_1->license_data->license_type);?>
                                </td>
                            </tr>

                            <tr>
                                <td class="text-grey" style="width:50%;">Expiration Date: </td>
                                <td style="width:50%;">
                                    <?= $all_license_data->license_1->license_data->expiration_date;?>
                                </td>
                            </tr>

                            <?php if($all_license_data->license_1->license_data->license_type == 'recurring'): ?>
                            <tr>
                                <td class="text-grey" style="width:50%;">Credit Limit Reset In</td>
                                <td style="width:50%;">
                                    <?php
                  date_default_timezone_set("UTC");
                  $datetime = new DateTime('tomorrow');
                  $next_day_start = $datetime->format('Y-m-d H:i:s');
                  $get_now = date('Y-m-d H:i:s');
                  $hourdiff = round((strtotime($next_day_start) - strtotime($get_now))/3600, 1);
                  echo $hourdiff;
                ?>
                                    Hour(s)</td>
                            </tr>
                            <?php endif; ?>

                        </table>

                    </div>

                    <div class="card-footer ">




                        <?php 

                            if($all_license_data->validation->license_1 != 'notfound'):
                            $get_license_own_db = '';
                            if(get_option( '_data_ls_key_no_id_vf' ) == $all_license_data->license_1->license_data->license_key):
                                $get_license_own_db = get_option( '_data_ls_key_no_id_vf' );
                            ?>
                        <form class="ajax-license-deactivation-form" action="#">
                            <input type="hidden" required id="license_key" name="license_key"
                                value="<?= $get_license_own_db ;?>">
                            <input type="hidden" required id="license_d" name="license_d"
                                value="<?php echo get_option( '_ls_domain_sp_id_vf' );?>">
                            <button type="submit" class="btn primary-btn btn-sm non_active_button">Remove
                                License</button>
                        </form>
                        <?php 


                            endif;
                            
                        ?>




                        <?php endif;?>


                        <div class="row">
                            <div class='deactivation_result'></div>
                        </div>


                    </div>

                </div>
            </div>
        </div>







        <?php endif; ?>


        <?php 
      if($all_license_data->validation->license_2 != 'notfound'):
    ?>
        <div class="col-md-6 mt-3 mt-3">
            <div class="card card-bg-cus h-100" style="min-width:100%;">
                <div class="py-4">
                    <div class="card-header card-bottom-border">
                        <h5 class="px-2"><b>License Key:</b></h5>
                        <div>
                            <table class="table borderless table-borderless mb-0">
                                <tr>
                                    <td class="w-50">
                                        <?php
								   
                                                            $license_key_view = $all_license_data->license_2->license_data->license_key; 
                                                            echo substr($license_key_view,0,5) . '**********************' . substr($license_key_view,-5);
                                                                            
                                                        ?>
                                    </td>
                                    <td class="w-50">
                                        <span>


                                            <?php if($all_license_data->license_2->license_data->license_status == 'valid'): ?>
                                            <span style="color:#fff;" class="badge bg-tag">Active</span>
                                            <?php else: ?>
                                            <span style="color:#fff;" class="badge bg-danger">Suspended</span>
                                            <?php endif; ?>



                                            <?php if($all_license_data->license_2->license_data->license_type == 'onetime'): ?>


                                            <!-- Button trigger modal -->
                                            <button type="button" class=" px-3 py-2 primary-btn border-0"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal">
                                                Refil Download Credit
                                            </button>

                                            <!-- Modal  modal fade-->
                                            <div class="modal fade" id="exampleModal" tabindex="-1"
                                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="background:#292055;">
                                                            <h6 class="modal-title" id="exampleModalLabel"
                                                                style="font-size: 14px;">
                                                                <b>Plan:</b><?= $all_license_data->license_2->license_data->license_plan_name;?>
                                                                <br /><b>License Key:</b>

                                                                <?php 


                                                                    $license_key_view1 = $all_license_data->license_1->license_data->license_key; 
                                                                    echo substr($license_key_view1,0,5) . '**********************' . substr($license_key_view1,-5);

                                                                ?>                                                            </h6>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form class="form" id="ajax-license-refill-form2" action="#">

                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <input type="hidden" id="license_key2"
                                                                        name="license_key"
                                                                        value="<?= $all_license_data->license_2->license_data->license_key; ?>"
                                                                        required>
                                                                    <label for="refill_key"
                                                                        class="form-label refill_button">Enter your
                                                                        unused ONETIME Plan license Key </label>
                                                                    <input type="text"
                                                                        class="form-control refill_button"
                                                                        id="refill_key2" name="refill_key"
                                                                        placeholder="Please enter your unused one time license key..."
                                                                        required>
                                                                </div>

                                                                <div id="credit_refill_msg"></div>

                                                                <p><b>Note:</b> You can use your onetime purchased
                                                                    license key as refill
                                                                    key
                                                                    if that is not previously used or activated as
                                                                    license. Once you
                                                                    redeem,
                                                                    it will add balance with current key. </p>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <button
                                                                    style="height: 32px; margin-top: 5px;margin-right: 4px;"
                                                                    type="button" class="btn btn-grey-color"
                                                                    data-bs-dismiss="modal">Close</button>

                                                                <a href="https://festingervault.com/pay-as-you-go/"
                                                                    class="btn btn-primary float-start refill_button btn-grey-color"
                                                                    target="_blank">Purchase Credit</a>
                                                                <a href=""
                                                                    class="btn btn-primary float-start refresh_button btn-grey-color"
                                                                    style="display: none;">Refresh</a>
                                                                <button type="submit"
                                                                    class="btn btn-primary btn-md refill_button"
                                                                    style="height:29px; margin-top:4px;font-size:16px!important;">Redeem
                                                                    &
                                                                    Refill Now</button>
                                                            </div>
                                                        </form>

                                                    </div>
                                                </div>
                                            </div>

                                            <?php endif;?>

                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="card-body ">

                        <table class="table borderless table-borderless mb-0">
                            <tr>
                                <td class="text-grey w-50">Plan Name: </td>
                                <td class="w-50"> <?= $all_license_data->license_2->license_data->license_plan_name;?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-grey w-50">Plan Type: </td>
                                <td class="w-50">
                                    <?= ucfirst($all_license_data->license_2->license_data->license_type);?> </td>
                            </tr>
                            <tr>
                                <td class="text-grey w-50">Plan Credit Limit: </td>
                                <td class="w-50">

                                    <span id="plan_limit_id">
                                        <?= $all_license_data->license_2->license_data->plan_credit_limit;?> </span>
                                    /
                                    <?= ucfirst($all_license_data->license_2->license_data->license_type);?>
                                </td>
                            </tr>

                            <tr>
                                <td class="text-grey w-50">Credit Available: </td>
                                <td class="w-50">

                                    <span id="limit_available_id">
                                        <?= $all_license_data->license_2->license_data->plan_credit_available;?>
                                    </span>
                                    /
                                    <?= ucfirst($all_license_data->license_2->license_data->license_type);?>
                                </td>
                            </tr>

                            <tr>
                                <td class="text-grey w-50">Expiration Date: </td>
                                <td class="w-50">
                                    <?= $all_license_data->license_2->license_data->expiration_date;?>
                                </td>
                            </tr>
                            <?php if($all_license_data->license_2->license_data->license_type == 'recurring'): ?>
                            <tr>
                                <td class="text-grey w-50">Credit Limit Reset In</td>
                                <td class="w-50">
                                    <?php
                                                            date_default_timezone_set("UTC");
                                                            $datetime = new DateTime('tomorrow');
                                                            $next_day_start = $datetime->format('Y-m-d H:i:s');
                                                            $get_now = date('Y-m-d H:i:s');
                                                            $hourdiff = round((strtotime($next_day_start) - strtotime($get_now))/3600, 1);
                                                            echo $hourdiff;
                                                                     ?>
                                    Hour(s)</td>
                            </tr>
                            <?php endif; ?>
                        </table>

                    </div>

                    <div class="card-footer">




                        <?php 

            if($all_license_data->validation->license_2 != 'notfound'):
              $get_license_own_db = '';

             
              if(get_option( '_data_ls_key_no_id_vf_2' ) == $all_license_data->license_2->license_data->license_key):
                $get_license_own_db_2 = get_option( '_data_ls_key_no_id_vf_2' );
              ?>

                        <form class="ajax-license-deactivation-form-2" action="#">
                            <input type="hidden" required id="license_key_2" name="license_key_2"
                                value="<?= $get_license_own_db_2 ;?>">
                            <input type="hidden" required id="license_d_2" name="license_d_2"
                                value="<?php echo get_option( '_ls_domain_sp_id_vf_2' );?>">
                            <button type="submit" class="btn primary-btn btn-sm non_active_button">Remove
                                License</button>
                        </form>
                        <?php endif;?>

                        <?php endif;?>


                        <div class="row">
                            <div class='deactivation_result2'></div>
                        </div>


                    </div>

                </div>
            </div>
        </div>


        <?php endif; //end license 2action  ?>



                      <ul class="dropdown-menu dropdown-menu-end cart-dropdown get-cart-dropdownsub" aria-labelledby="cart-dropdown" style="width:400px;padding:10px; display: none;">
                        <div id="cart-items" class="cart-items"></div>
                        <div id="cart-items-notfound" class="cart-items-notfound">No item found</div>
                        <li><button id="download-button" class="btn btn-success btn-md btn-block">Download All</button></li>
                        <li><button id="install-button" class="btn btn-primary btn-md btn-block">Install All</button></li>
                        <li><button id="clearall-button" class="btn btn-warning btn-md btn-block">Clear All</button></li>
                      </ul>

        <div class="col-md-6 mt-3">
            <div class="card card-bg-cus h-100 px-2" style="min-width:100%;">
                <div class="py-4">
                    <div class="card-header card-bottom-border">
                        <h5><b>License Activation</b></h5>
                        <?php 

if($all_license_data->validation->license_1 == 'notfound' || $all_license_data->validation->license_2 == 'notfound'):?>


                        <p class="card-text my-3">Please enter your activation license key to activate and enjoy our service. Don't have a license key? Buy now and get access to all of our 25K+ themes and plugins directory.
                        </p>
                    </div>
                    <div class="card-body card-bottom-border">






                        <form class="form" id="ajax-license-activation-form" action="#">


                            <div class="mb-3">
                                <label for="licenseKeyInput" class="form-label">Please enter your license
                                    key</label>
                                <input type="text" required class="form-control search_bg_clr" id="licenseKeyInput"
                                    name="licenseKeyInput" aria-describedby="licenseKeyHelp"
                                    placeholder="Please enter your license key here...">
                                <!-- <div id="licenseKeyHelp" class="form-text">Don't share your license key with anyone
                                    else.
                                </div> -->
                            </div>

                            <button type="submit" class="btn non_active_button primary-btn">Activate now</button>
                        </form>

                        <?php endif;?>

                        <div class="row">
                            <div id='activation_result'></div>
                        </div>


                    </div>

                    <div class="card-footer ">
                        <div class="dark-blue border-8 py-3 px-4 mt-4">
                            <small class="">
                                <?php if($all_license_data->validation->license_1 == 'valid' || $all_license_data->validation->license_2 == 'valid'):?>

                                Your license is registered with <b><?php echo get_home_url();?></b> domain.</small>
                            <?php else: ?>
                            You are registering your license with <b><?php echo get_home_url();?></b>
                            domain.</small>
                            <?php endif;?>
                        </div>
                    </div>



                </div>
            </div>

        </div>
    </div>
</div>
</div>