<?php
    $CI =& get_instance();
    // $CI->load->model('Soft_settings');
    // $Soft_settings = $CI->Soft_settings->retrieve_setting_editdata();
    // $image =  $Soft_settings[0]['invoice_logo'];
    // $invoice_logo = "my-assets/image/logo/".substr($image, strrpos($image, '/') + 1);
?>
    <table width="700" align="center" bgcolor="#fff">
        <tbody>
            <tr>
                <td>
                    <table width="100%" style="background:url(https://i.imgur.com/MmkcbUk.jpg); background-size: cover; padding: 50px;">
                        <tr>
                            <td class="banner_inner" style="align-items:center; display:flex; justify-content:space-between">
                                <h2 class="banner_heading" style="color:#fff; font-size:40px; max-width:650px">Thank you for choosing Limarket !</h2>
                            </td>
                            <td>
                                <img src="https://imgur.com/8fYN2iw.jpg" alt="" style="max-width: 190px;">
                            </td>
                        </tr>
                    </table>
                    
                    <table bgcolor="#fff" width="90%" style="padding:100px 0;" align="center">
                        <tr>
                            <td class="" align="center" style="">
                                <h2 class="section_title" style="color:#010149; text-transform: uppercase; font-size:45px; margin-bottom:30px; margin-top:0">Thanks for your order</h2>
                                <p class="info" style="color:#011d65; font-size:22px; margin:0 auto 40px; max-width:864px; position:relative; z-index:1">Hi {customer_name}, we have received your order with the number <span style="color:#009c00; font-weight:600">#{order_no}</span> and we are working on it now. We'll email you updates concerning your order till delivery is done. Stay tuned while we work things out for you.</p>
                                <!-- <div class="btn-group">
                                    <button onclick="window.location.href='https://www.limarket.net/login';" class="btn btn-orange btn-hover" style="color:#fff; border: 0; background-color: #ff5d00; font-size:15px; line-height:50px; padding:0 35px; margin-right:15px; overflow:hidden; position:relative">View your order details</button>
                                    <button onclick="window.location.href='https://www.limarket.net/login';" class="btn btn-blue btn-hover" style="color:#fff; background-color: #010149; border: 0; font-size:15px; line-height:50px; padding:0 35px; overflow:hidden; position:relative">Track your order</button>
                                </div> -->
                            </td>
                        </tr>
                    </table>
                    
                    <table width="90%" bgcolor="#e9e9e9" align="center" style="border-radius:8px; margin-bottom:35px; padding:10px 25px; position:relative;">
                        <tr>
                            <td width="70">
                                <img src="https://imgur.com/mmvMm5z.png" class="sec_img" alt="">
                            </td>
                            <td>
                                <h2 class="m-0 sec_title" style="">Order &amp; Shipping Details</h2>
                            </td>
                        </tr>
                    </table>

                    <table bgcolor="#fff" width="90%" align="center">
                        <tbody>
                            
                            <tr class="mb-50" style="margin-bottom: 50px;">
                                <td width="100%">
                                    <h2 style="margin-bottom:30px; color: #ff5d00;">Order Details</h2>
                                    <p><span style="color:#010149; font-weight:700">Order Number:</span> #{order_no}</p>
                                    <!-- <p><span style="color:#010149; font-weight:700">Invoice NO:</span> #invoice_no</p> -->
                                    <p><span style="color:#010149; font-weight:700">Payment Method:</span> <?php echo $CI->Orders->get_payment_method_name($paymethod['payment_id']); ?></p>
                                    <p><span style="color:#010149; font-weight:700">Phone Number:</span> {customer_mobile}</p>
                                </td>
                            </tr>
                            <tr>
                                <td width="100%">
                                    <h2 style="margin-bottom:30px; color: #ff5d00;">Shipping Details</h2>
                                    <p><span style="color:#010149; font-weight:800">Shipping City :</span> {customer_city}</p>
                                    <p><span style="color:#010149; font-weight:800">Area :</span> {customer_state}</p>
                                    <p><span style="color:#010149; font-weight:800">Payment Method :</span> <?php echo $CI->Orders->get_payment_method_name($paymethod['payment_id']); ?></p>
                                    <p><span style="color:#010149; font-weight:800">Address Details :</span> {customer_address}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="order_area" bgcolor="#fff" width="90%" style="margin: 50px auto 20px;">
                        <tbody>
                            <tr class="row">
                               <td>
                                    <table class="section_heading" width="100%" bgcolor="#e9e9e9" style="border-radius:8px; margin-bottom:35px; padding:10px 25px; position:relative;">
                                        <tr>
                                            <td width="70">
                                                <img src="https://imgur.com/Si0oDUz.png" class="sec_img" alt="">
                                            </td>
                                            <td>
                                                <h2 class="m-0 sec_title" style="">ORDERED ITEMS</h2>
                                            </td>
                                        </tr>
                                    </table>
                               </td>
                            </tr>

                        </tbody>
                    </table>
                    <?php 
                    // foreach ($order_all_data as $single_data) {
                    //     $total_vat[] = $single_data['product_vat'];
                    // }
                    // $vats=array_sum($total_vat); 
                    $vats = 0;
                    $sub_total_amount = 0;

                    foreach($order_all_data as $single_order): ?>
                    <table class="order_area" bgcolor="#fff" width="90%" style="margin: 0 auto;">
                        <tbody>
                            <?php if(!empty($single_order['thumb_image_url'])){ ?>
                            <tr class="row" style="margin-bottom:50px">
                                <td align="left" width="50%">
                                    <img src="<?php echo @$single_order['thumb_image_url'] ?>" alt="Product Image" style="max-width: 340px; border: 1px solid #ddd;">
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td width="100%">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <h2 style="color:#ff5d00;font-size: 30px;"><?php echo @$single_order['product_name'] ?></h2>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p style="font-size:20px; margin: 0 0 8px;"><span style="color:#010149; font-weight:800">Merchant:</span> <?php echo @$single_order['seller_name'] ?></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p style="font-size:20px; margin: 0 0 8px;"><span style="color:#010149; font-weight:800">Date of order :</span> {final_date}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p style="font-size:20px; margin: 0;"><span style="color:#010149; font-weight:800">Estimated delivery :</span> 1 - 3 Working days</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width="100%"  style="margin-top: 30px;">
                                        <thead>
                                            <tr>
                                                <th bgcolor="#e9e9e9" style="color:#010149; font-size:20px; padding:25px 15px" align="center">Item Price</th>
                                                <th bgcolor="#e9e9e9" style="color:#010149; font-size:20px; padding:25px 15px" align="center">Quantity</th>
                                                <th bgcolor="#e9e9e9" style="color:#010149; font-size:20px; padding:25px 15px" align="center">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size:16px; padding:25px 15px" align="center"><?php echo ($position==0)?$currency." ".$single_order['rate']:$single_order['rate']." ".$currency; ?></td>
                                                <td style="font-size:16px; padding:25px 15px" align="center"><?php echo $single_order['quantity'];?></td>
                                                <td style="font-size:16px; padding:25px 15px" align="center"><?php echo ($position==0)?$currency." ".$single_order['total_price']:$single_order['total_price']." ".$currency; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <?php 
                        $vats += floatval($single_order['product_vat']);
                        $sub_total_amount += floatval($single_order['total_price']);

                    endforeach;

                    $grand_total_amount =  ($sub_total_amount + floatval($service_charge) + $vats ) - floatval($total_discount);

                    ?>

                    <table width="90%" align="center">
                        <tbody>
                            <tr style="margin-bottom:50px">
                                <td bgcolor="#ff5d00" style="color:#fff;font-size: 22px;line-height: 50px;font-weight: 600;padding: 15px;" align="center">Purchase Summary</td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="100%">
                                        <tbody>
                                            <tr>
                                                <td width="50%" style="border:0; font-size:16px; font-weight:800; padding: 8px 0;">Sub Total:</td>
                                                <td width="50%" style="border:0; font-size:16px; text-align:right; padding: 8px 0;" align="right"><?php echo (($position==0)?"$currency $sub_total_amount":"$sub_total_amount $currency") ?></td>
                                            </tr>
                                            <?php //if ($order_all_data[0]['service_charge'] != 0) {?>
                                            <tr>
                                                <td width="50%" style="border:0; font-size:16px; font-weight:800; padding: 8px 0;">Shipping cost:</td>
                                                <td width="50%" style="border:0; font-size:16px; text-align:right; padding: 8px 0;" align="right"><?php echo (($position==0)?"$currency {service_charge}":"{service_charge} $currency") ?></td>
                                            </tr>
                                            <?php //} ?>
                                            <tr>
                                                <td width="50%" style="border:0; font-size:16px; font-weight:800; padding: 8px 0;">VAT:</td>
                                                <td width="50%" style="border:0; font-size:16px; text-align:right; padding: 8px 0;" align="right"><?php echo (($position==0)?"$currency $vats":"$vats $currency") ?></td>
                                            </tr>
                                            <?php //if ($total_discount != 0) {?>
                                            <tr>
                                                <td width="50%" style="border:0; font-size:16px; font-weight:800; padding: 8px 0;">Discount Amount :</td>
                                                <td width="50%" style="border:0; font-size:16px; text-align:right; adding: 8px 0;;" align="right"><?php echo (($position==0)?"$currency {total_discount}":"{total_discount} $currency") ?></td>
                                            </tr>
                                        <?php //} ?>
                                        <?php //if ($due_amount != 0) { ?>
                                            <tr>
                                                <td width="50%" style="border:0; font-size:16px; font-weight:800; border-top: 1px solid #ddd; padding: 8px 0;">Total Due Amount:</td>
                                                <td width="50%" style="border:0; font-size:16px; text-align:right; border-top: 1px solid #ddd; padding: 8px 0;" align="right"><?php echo (($position==0)?"$currency $grand_total_amount":"$grand_total_amount $currency") ?></td>
                                            </tr>
                                            <?php //} ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table bgcolor="#fff" width="90%" style="margin: 50px auto;">
                        <tbody>
                            <tr>
                                <td bgcolor="#010149" width="49%" style="padding: 45px 60px; border-radius: 10px;">
                                    <h2 style="color:#fa813b; margin-bottom:35px; margin-top:15px">NEED HELP ?</h2>
                                    <p style="color:#fff; font-size: 21px;">Call Us on <span style="color:#00f7ff">(+237 664 98 43 88)</span> or visit us online for assistance</p>
                                </td>
                                <td bgcolor="#010149" width="49%" style="padding: 45px 60px; border-radius: 10px;">
                                    <h2 style="color:#fa813b; margin-bottom:35px; margin-top:15px">OUR GUARANTEE</h2>
                                    <p style="color:#fff; font-size: 21px;">Your satisfaction is 100% guaranteed. See our <span style="color:#00f7ff"><a href="<?php echo base_url()?>page/return_policy">Return and exchange policies.</span></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table bgcolor="#fff" align="center" width="90%" style="padding:100px 0; margin: 50px auto;">
                        <tbody>
                            <tr>
                                <td align="center" style="color:#010149; font-size:30px;">Our payments methods</td>
                            </tr>
                            <tr>
                                <td class="payment_img" align="center" style="display:block; margin:70px 0">
                                    <img src="https://imgur.com/lh1ZtjQ.jpg" alt="" style="display:inline-block; margin:15px 10px">
                                    <img src="https://imgur.com/eKGAVQS.jpg" alt="" style="display:inline-block; margin:15px 10px">
                                    <img src="https://imgur.com/Zc6Ibgz.jpg" alt="" style="display:inline-block; margin:15px 10px">
                                    <img src="https://imgur.com/E6Rhjc0.jpg" alt="" style="display:inline-block; margin:15px 10px">
                                    <img src="https://imgur.com/qsSSoUW.jpg" alt="" style="display:inline-block; margin:15px 10px">
                                    <img src="https://imgur.com/5qQvihD.jpg" alt="" style="display:inline-block; margin:15px 10px">
                                </td>
                            </tr>
                            <tr>
                                <td class="payment_info" align="center" width="80%" style="color:#707070; font-size:18px; margin:0 auto;">Please add <span style="color:#00f7ff">cs-limarket@gmail.com</span> to your address book. You have received this email, because you or someone else has confirmed that the email address <span style="color:#00f7ff">{customer_email}</span> would like to receive email communication from Limarket. We will never share your personal information (such as your email address with any other 3rd party without your consent).</td>
                            </tr>
                        </tbody>
                    </table>

                    <table bgcolor="#050d45" width="100%" align="center" style="padding: 50px 25px;">
                        <tbody>
                            <tr>
                                <td style="padding-right: 25px">
                                    <img src="https://imgur.com/eEbNo1t.jpg" alt="" style="max-width: 110px;">
                                </td>
                                <td>
                                    <h4 style="color: #fff;">About Us</h4>
                                    <ul style="padding: 0; list-style: none; color: #fff;">
                                        <li><a href="<?php echo base_url()?>page/career" style="color: #fff; text-decoration: none; font-size: 13px;">Caraer</a></li>
                                        <li><a href="<?php echo base_url()?>page/who-we-are" style="color: #fff; text-decoration: none; font-size: 13px;">Who we are?</a></li>
                                        <li><a href="<?php echo base_url()?>privacy_policy" style="color: #fff; text-decoration: none; font-size: 13px;">Privacy Policy</a></li>
                                    </ul>
                                </td>
                                <td>
                                    <h4 style="color: #fff; font-size: 15px;">Make Money</h4>
                                    <ul style="padding: 0; list-style: none; color: #fff;">
                                        <li><a href="<?php echo base_url()?>page/faq" style="color: #fff; text-decoration: none; font-size: 13px;">FAQ</a></li>
                                        <li><a href="<?php echo base_url()?>page/how-to-buy-on-limarket" style="color: #fff; text-decoration: none; font-size: 13px;">How to buy</a></li>
                                        <li><a href="<?php echo base_url()?>page/how-to-sell-on-limarket" style="color: #fff; text-decoration: none; font-size: 13px;">How to sale</a></li>
                                    </ul>
                                </td>
                                <!-- <td class="col-vxs-12 col-xs-7 col-sm-3">
                                    <h4 style="color: #fff;">Download our App</h4>
                                    <div class="app_link">
                                        <a href="#" style="display:inline-block; margin-right:10px">
                                            <img src="https://imgur.com/K829Qti.jpg" style="max-width: 90px" alt="">
                                        </a>
                                        <a href="#">
                                            <img src="https://imgur.com/m4Wsyyj.jpg" style="max-width: 24px" alt="">
                                        </a>
                                    </div>
                                </td> -->
                            </tr>
                        </tbody>
                    </table>
                    
                </td>
            </tr>
        </tbody>
        
        
    </table>
    
    