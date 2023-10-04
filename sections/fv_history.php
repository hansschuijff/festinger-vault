<div class="container-padding">
    <div class="row px-0 pt-3">
        <div class="col-md-12 plugin_updated_h4 mb-4">
            <h4>History</h4>
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
                                <th class="py-0" style="min-width: 100px;">Date ( Y-m-d )</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( NULL === $fv_api_downloads ): ?>
                                <tr>
                                    <td colspan="5" style="color: #fff;">No history found. </td>
                                </tr>
                            <?php
                            else:
                                foreach( $fv_api_downloads as $fv_download ):
                                ?>
                                <!-- Name -->
                                <tr class="text-white table-tr pb-3" style="box-sizing:border-box;">
                                    <td class="plugin_update_width_20" style="padding-left:20px;">
                                        <?php echo $fv_download->theme_plugin_name ; ?>
                                    </td>
                                    <!-- Domain & License Key -->
                                    <td class="plugin_update_width_40 pb-3">
                                        <div style="box-sizing:border-box;">
                                            <!-- Domain -->
                                            <p class="mb-2">
                                                <?php echo substr( $fv_download->website, 0, 5 ) . '******' . substr( $fv_download->website, -5 ); ?>
                                            </p>
                                            <!-- License Key -->
                                            <span class="bg-tag p-2 border-8 mb-2" style="font-size:13px;">
                                                <?php
                                                $fv_logged_license_key = $fv_download->logged_license;
                                                echo substr( $fv_logged_license_key, 0, 5 ) . '**********************' . substr( $fv_logged_license_key, -5 );
                                                ?>
                                            </span>
                                        </div>
                                    </td>
                                    <!-- Plan & Credit -->
                                    <td class=''>
                                        <div class='row'>
                                            <div class='col-6 text-left text-grey'>Plan Type</div>
                                            <div class='col-6 text-left'>
                                                <?php echo ucfirst( $fv_download->logged_license_type ); ?>
                                            </div>
                                            <div class='col-6 text-left text-grey'>Credit</div>
                                            <div class='col-6 text-left'>
                                                <?php echo ucfirst( $fv_download->credit_required ); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- History type -->
                                    <td class='plugin_update_width_15'>
                                        <?php echo ucfirst( str_replace( '_', ' ', $fv_download->history_type ) ); ?>
                                    </td>
                                    <!-- Date -->
                                    <td class=" plugin_update_width_15">
                                        <?php echo date( "Y-m-d", strtotime( $fv_download->created_at ) ); ?>
                                    </td>
                                </tr>
                                <?php
                                endforeach;
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
