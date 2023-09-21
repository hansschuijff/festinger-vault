<div class="container-padding">

    <div class="row px-0 pt-3">
        <div class="col-md-12 plugin_updated_h4 mb-4">
            <h4>History </h4>
            <p class="mb-0">Your Subscriptions, Downloads, Refill Update history will be here.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card history_page_card" style="max-width: 100%">
                <div class="card-body table-responsive">
                    <table id="" class="display table table-responsive table-borderless"
                        style="border-collapse: separate; border-spacing: 0 12px;">
                        <thead>
                            <tr class="text-grey">
                                <th class="py-0" style="padding-left:20px;">Name</th>
                                <th class="py-0">Website & License Key</th>
                                <!-- <th>License Key</th> -->
                                <th class="py-0" style="min-width: 100px;">Plan & Credit</th>
                                <!-- <th>Credit</th> -->
                                <th class="py-0" style="min-width: 100px;">History Type</th>
                                <!-- update, download, istall-->
                                <th class="py-0" style="min-width: 100px;">Date (Y-m-d)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($license_histories == NULL): ?>
                                <tr>
                                    <td colspan="5" style="color: #fff;">No history found. </td>
                                </tr>
                            <?php 
                            else:
                            foreach($license_histories as $history): ?>

                            <tr class="text-white table-tr pb-3" style="box-sizing:border-box;">
                                <td class="plugin_update_width_20" style="padding-left:20px;">
                                    <?= $history->theme_plugin_name ;?></td>
                                <td class="plugin_update_width_40 pb-3">
                                    <div style="box-sizing:border-box;">
                                        <p class="mb-2">

                                        <?= substr($history->website,0,5) . '******' . substr($history->website,-5);?>

                                           </p>
                                        <span class="bg-tag p-2 border-8 mb-2" style="font-size:13px;"><?php  
												$license_key_view = $history->logged_license; 
												echo substr($license_key_view,0,5) . '**********************' . substr($license_key_view,-5);
											?>
                                        </span>
                                </td>
                                <td class=''>
                                    <div class='row'>
                                        <div class='col-6 text-left text-grey'>Plan Type</div>
                                        <div class='col-6 text-left'><?= ucfirst($history->logged_license_type) ;?>
                                        </div>
                                        <div class='col-6 text-left text-grey'>Credit</div>
                                        <div class='col-6 text-left'><?= ucfirst($history->logged_license_type) ;?>
                                        </div>
                                    </div>
                                </td>
                               
                                <td class='plugin_update_width_15'><?= ucfirst(str_replace('_', ' ', $history->history_type)) ;?></td>
                                <td class=" plugin_update_width_15">
                                    <?= date("Y-m-d",strtotime($history->created_at)) ;?></td>
                            </tr>
                            <?php endforeach; 
                                endif;
                            ?>
                        </tbody>
                    </table>

                </div>
            </div>



        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {


    $('#history_table').DataTable({
        "pageLength": 50,
        "order": [
            [6, "DESC"]
        ]
    });

});
</script>