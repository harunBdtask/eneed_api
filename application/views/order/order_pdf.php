<?php
    $CI =& get_instance();
    $CI->load->model('Soft_settings');
    $Soft_settings = $CI->Soft_settings->retrieve_setting_editdata();
    $image =  $Soft_settings[0]['invoice_logo'];
    $invoice_logo = "my-assets/image/logo/".substr($image, strrpos($image, '/') + 1);
?>

<style>
    @import url(https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i);
    body{
        margin: 0;
        padding: 0;
        font-size: 10px;
        font-family:'Alegreya Sans',sans-serif;
    }

    table {
        font-size: 10px;
        font-weight: 500;
        line-height: 1.4;
        color: #000;
        width: 100%;
        text-align: center;
    }

    table tbody tr td {
        vertical-align: middle;
        /* padding: 1.3px; */
    }
    .cizgili td {
        border: 1px solid  #000;
    }
   
</style>
<!-- Order pdf start -->
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd">
            <div id="printableArea">
                <div class="panel-body">
                    <div class="row" style="height: 200px">
                        <div class="col-sm-8" style="display: inline-block;width: 64%">
                            <img src="<?php if (isset($invoice_logo)) {echo $invoice_logo; }?>" class="" alt="" style="margin-bottom:5px;height: 60px;margin-left: 0px">
                            <br>
                            <span class="label label-success-outline m-r-15 p-10" ><?php echo display('order_from') ?></span>
                            {company_info}
                            <address style="margin-top:10px">
                                <strong>{company_name}</strong><br>
                                {address}<br>
                                <abbr><b><?php echo display('mobile') ?>:</b></abbr> {mobile}<br>
                                <abbr><b><?php echo display('email') ?>:</b></abbr> 
                                {email}<br>
                                <abbr><b><?php echo display('website') ?>:</b></abbr> 
                                {website}<br>
                            </address>
                            {/company_info}
                        </div>
                        
                        <div class="col-sm-4 text-left" style="display: inline-block;margin-left: 5px;">
                            <h4 class="m-t-10">
                                <?php if ($gateway_id == 'cash' || $gateway_id == '1') { ?>
                                    <span class="label label-danger-outline "><?php echo display('unpaid') ?></span>
                                <?php }elseif ($total_amount == $paid_amount) { ?>
                                    <span class="label label-success-outline "><?php echo display('paid') ?></span>
                                <?php }elseif(($paid_amount > 0) && ($paid_amount <
                                        $total_amount)){ ?>
                                    <span class="label label-warning-outline"><?php echo display('partial_paid') ?></span>
                                <?php }elseif ($paid_amount == 0) {
                                ?>
                                    <span class="label label-danger-outline"><?php echo display('unpaid') ?></span>
                                <?php } ?>
                            </h4>
                            <h2 class="m-t-0"><?php echo display('order') ?></h2>
                            <div><?php echo display('order_no') ?>: {order_no}</div>
                            <!-- <div><?php //echo display('invoice_no') ?>: {invoice_no}</div> -->
                            <div class="m-b-15" style="margin-bottom:15px"><?php echo display('order_date') ?>: {final_date}</div>

                            <span class="label label-success-outline m-r-15"><?php echo display('order_to') ?></span>
                              <address style="margin-top:10px;"> 
                                  <strong>{customer_name} </strong><br>
                                    <abbr><?php echo display('address') ?>:</abbr>
                                    <?php if ($customer_address) { ?>
	                                <c style="width: 10px;margin: 0px;padding: 0px;">{customer_address}</c>
	                                <?php }  ?><br>
                                    <abbr><?php echo display('mobile') ?>:</abbr><?php if ($customer_mobile) { ?>{customer_mobile}<?php }if ($customer_email) { ?>
                                    <br>
                                    <abbr><?php echo display('email') ?>:</abbr>{customer_email}
                                   	<?php } ?><br><br>
                                    <?php echo $gateway_name; ?>


                            </address>
                        </div>
                    </div> <br><br>
                    <div class="row">
                        <table class="cizgili" cellspacing="0">
                            <tr>
                                <th><?php echo display('sl') ?></th>
                                <th><?php echo display('product_name') ?></th>
                                <th><?php echo display('variant') ?></th>
                                <th><?php echo display('unit') ?></th>
                                <th><?php echo display('quantity') ?></th>
                                <th><?php echo display('price') ?></th>
                                <th><?php echo display('discount') ?></th>
                                <th><?php echo display('vat') ?></th>
                                <th><?php echo display('ammount') ?></th>
                            </tr>
                            <?php foreach($order_all_data as $single_order){ ?>
                                <tr>
                                    <td><?php echo @$single_order['sl'];?></td>
                                    <td><strong><?php echo @$single_order['product_name'] ?> - (<?php echo @$single_order['product_model']; ?>)</strong></td>
                                    <td><?php echo @$single_order['variant_name']; ?></td>
                                    <td><?php @$single_order['unit_short_name'];?></td>
                                    <td><?php echo @$single_order['quantity'];?></td>
                                    <td><?php echo (($position==0) ? "$currency {$single_order['rate']}" : "{$single_order['rate']} $currency"); ?></td>
                                    <td><?php echo (($position==0)?"$currency {$single_order['discount_per_product']}":"{$single_order['discount_per_product']} $currency"); ?></td>
                                    <td><?php echo (($position==0)?"$currency {$single_order['product_vat']}":"{$single_order['product_vat']} $currency"); ?></td>
                                    <td><?php $total_price_per_product = $single_order['total_price'];  echo (($position==0)?"$currency $total_price_per_product":"$total_price_per_product $currency"); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-sm-12" style="margin-top: 10px">
                        	<div class="" style="display: inline-block;width: 65%">
                                <p><strong>{details}</strong></p>
                            </div>

                            <div class="" style="display: inline-block;width: 35%;">

		                        <table class="table table-striped table-bordered cizgili" border="0" cellspacing="0" cellpadding="0">
		                            <?php if ($total_discount != 0) {?>
	                            	<tr>
	                            		<th style="border: 1px solid  #000;"><?php echo display('total_discount') ?> : </th>
	                            		<td><?php echo (($position==0)?"$currency {total_discount}":"{total_discount} $currency") ?> </td>
	                            	</tr>
		                            <?php } 
									$this->db->select('a.*,b.tax_name');
									$this->db->from('order_tax_col_summary a');
									$this->db->join('tax b','a.tax_id = b.tax_id');
									$this->db->where('a.order_id',$order_id);
									$this->db->where('a.tax_id','H5MQN4NXJBSDX4L');
									$tax_info = $this->db->get()->row();

									if ($tax_info) { ?>
			                    	<tr>
			                    		<th style="border: 1px solid  #000;" class="total_cgst"><?php echo $tax_info->tax_name ?> :</th>
			                    		<td class="total_cgst"><?php echo (($position==0)?$currency.$tax_info->tax_amount:$tax_info->tax_amount.$currency); ?>
			                    		</td>
			                    	</tr>
									<?php } 
									$this->db->select('a.*,b.tax_name');
									$this->db->from('order_tax_col_summary a');
									$this->db->join('tax b','a.tax_id = b.tax_id');
									$this->db->where('a.order_id',$order_id);
									$this->db->where('a.tax_id','52C2SKCKGQY6Q9J');
									$tax_info = $this->db->get()->row();

									if ($tax_info) { ?>
			                    	<tr>
			                    		<th style="border: 1px solid  #000;" class="total_sgst"><?php echo $tax_info->tax_name ?> :</th>
			                    		<td class="total_sgst"><?php echo (($position==0)?$currency.$tax_info->tax_amount:$tax_info->tax_amount.$currency);?>
			                    		</td>
			                    	</tr>
									<?php } 
									$this->db->select('a.*,b.tax_name');
									$this->db->from('order_tax_col_summary a');
									$this->db->join('tax b','a.tax_id = b.tax_id');
									$this->db->where('a.order_id',$order_id);
									$this->db->where('a.tax_id','5SN9PRWPN131T4V');
									$tax_info = $this->db->get()->row();

									if ($tax_info) {
									?>
			                    	<tr>
			                    		<th style="border: 1px solid  #000;" class="total_igst"><?php echo $tax_info->tax_name ?> :</th>
			                    		<td class="total_igst"><?php echo (($position==0)?$currency.$tax_info->tax_amount:$tax_info->tax_amount.$currency); ?>
			                    		</td>
			                    	</tr>
									<?php } ?>
                                    <?php if ($order_all_data[0]['service_charge'] != 0) {?>
                                    <tr>
                                        <th style="border: 1px solid  #000;" class="service_charge"><?php echo display('delivery_charge') ?> :</th>
                                        <td class="service_charge"><?php echo (($position==0)?"$currency {service_charge}":"{service_charge} $currency") ?></td>
                                    </tr>
                                    <?php } ?>
                                            <tr>

                                        <th style="border: 1px solid  #000;"><?php echo display('vat') ?> : </th>
                                        <td><?php echo (($position==0)?"$currency $vats":"$vats $currency") ?></td>
                                    </tr>
	                            	<tr>
	                            		<th style="border: 1px solid  #000;" class="grand_total"><?php echo display('grand_total') ?> :</th>
	                            		<td class="grand_total"><?php echo (($position==0)?"$currency {total_amount}":"{total_amount} $currency") ?></td>
	                            	</tr>
	                            	<tr>
	                            		<th style="border: 1px solid  #000;"><?php echo display('paid_ammount') ?> : </th>
	                            		<td><?php echo (($position==0)?"$currency {paid_amount}":"{paid_amount} $currency") ?></td>
	                            	</tr>				 
		                            <?php if ($due_amount != 0) { ?>
	                            	<tr>
	                            		<th style="border: 1px solid  #000;"><?php echo display('due') ?> : </th>
	                            		<td><?php echo (($position==0)?"$currency {due_amount}":"{due_amount} $currency") ?></td>
	                            	</tr>
	                            	<?php } ?>
	                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Order pdf end -->