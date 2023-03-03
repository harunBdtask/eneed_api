<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

class Customer_dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //header
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
    }
    public function index()
    {
        dd('Hi');
        return FALSE;
    }
    //Manage order Api
    public  function manage_order()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $this->db->select('o.date,o.order_id,o.order_no,o.total_amount,o.paid_amount,o.order_status');
        $this->db->from('order o');
        $this->db->where('o.customer_id', $customer_id);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $result[$key]['due_amount'] = ($value['total_amount'] - $value['paid_amount']);
            }
            $data = $this->remaining_time($result);
            JSONSuccessOutput($data);
        } else {
            $cust_check = $this->customer_check($customer_id);
            if ($cust_check) {
                JSONNoOutput("No order Found");
            } else {
                JSONNoOutput("No customer found");
            }
        }
    }
    //Check customer
    public function customer_check($customer_id)
    {
        $cust_check = $this->db->select("COUNT(customer_id) as customer")->from("customer_information")->where("customer_id", $customer_id)->get()->row();
        if (!empty($cust_check->customer)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    public function customerlogin_check($customer_id)
    {
        $cust_check = $this->db->select("COUNT(customer_id) as customer")->from("customer_login")->where("customer_id", $customer_id)->get()->row();
        if (!empty($cust_check->customer)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    //Order check
    public function order_policy()
    {
        $this->db->select("payment_duration,delivery_duration");
        $this->db->from("order_policy");
        $this->db->where("id", 1);
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }
    //Remaining time
    public function remaining_time($result)
    {
        $orderPolicy = $this->order_policy();
        foreach ($result as $key => $res) {
            if ($res["order_status"] == 1) {
                $date = $res["date"];
                $maxdate = date("Y-m-d", strtotime($date . "+$orderPolicy->payment_duration days"));
                $remaining = strtotime($maxdate) - time();
                $convtime = strtotime($maxdate) * 1000;
                $sec = ($remaining % 60);
                $remtime = "";
                if ($sec) $remtime = $convtime;
                $status = TRUE;
                if ($remtime == "" | $remaining <= 0) {
                    $remtime = "Payment Time is Over, Please Contact with e-needz";
                    $status = FALSE;
                }
                $result[$key]["remainingTime"] = $remtime;
                $result[$key]["remainingStatus"] = $status;
            }
            if ($res["order_status"] == 2) {
                $date = $res["date"];
                $maxdate = date("Y-m-d", strtotime($date . "+$orderPolicy->delivery_duration days"));
                $remaining = strtotime($maxdate) - time();
                $convtime = strtotime($maxdate) * 1000;
                $sec = ($remaining % 60);
                $remtime = "";
                if ($sec) $remtime = $convtime;
                if ($remtime == "" | $remaining <= 0) {
                    $remtime = "Delivery Time is Over, Please Contact with e-needz";
                }
                $result[$key]["remainingTime"] = $remtime;
                $result[$key]["remainingStatus"] = FALSE;
            }
        }
        return $result;
    }
    //Cancel Order Api
    public function cancel_order()
    {
        $order_id = filter_input_data($this->input->post('order_id', TRUE));
        if (empty($order_id)) {
            JSONErrorOutput("Order Id is required!");
        }
        $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
        if (empty($order_check->orderid)) {
            JSONErrorOutput("Invalid order id");
        } else {
            $status_check = $this->db->select("date,order_status")->from("order")->where("order_id", $order_id)->get()->row();
            $orderPolicy = $this->order_policy();
            $date = $status_check->date;
            $durationDate = date("Y-m-d", strtotime($date . "+$orderPolicy->payment_duration days"));
            $currentDate = date("Y-m-d");
            if ($status_check->order_status == 1 && $durationDate < $currentDate) {
                $result = $this->db->where("order_id", $order_id)->update("order", array("order_status" => 6));
                if ($result) {
                    JSONSuccessOutput(NULL, 'Order cancelled successfully');
                } else {
                    JSONErrorOutput('Please try again');
                }
            } else {
                JSONErrorOutput('Order cancelation does not possible at this time');
            }
        }
    }
    //Search Order
    public function search_order()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $order_no = filter_input_data($this->input->post('order_no', TRUE));
        $order_status = filter_input_data($this->input->post('order_status', TRUE));
        $date = filter_input_data($this->input->post('date', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        } else {
            $searchdata = $this->search_details($customer_id, $order_no, $order_status, $date);
            if ($searchdata) {
                JSONSuccessOutput($searchdata);
            } else {
                JSONNoOutput('No Order Found');
            }
        }
    }
    //search details
    public function search_details($customer_id, $order_no = NULL, $order_status = NULL, $date = NUll)
    {
        $cust_check = $this->customer_check($customer_id);
        if ($cust_check) {
            $this->db->select('o.date,o.order_id,o.order_no,o.total_amount,o.order_status');
            $this->db->from('order o');
            $this->db->where("o.customer_id", $customer_id);
            if ($order_no) {
                $this->db->where("order_no", $order_no);
            }
            if ($order_status) {
                $this->db->where("order_status", $order_status);
            }
            if ($date) {
                $this->db->where("date", $date);
            }
            $query = $this->db->get();
            $result = $query->result_array();
            if (!empty($result)) {
                $data = $this->remaining_time($result);
                return $data;
            } else {
                JSONNoOutput('No Order Found');
            }
        } else {
            JSONNoOutput('No Customer Found');
        }
    }
    //Order details
    public function details_order()
    {
        $order_id = filter_input_data($this->input->post('order_id', TRUE));
        if (empty($order_id)) {
            JSONErrorOutput("Order Id is required!");
        }
        $this->db->select("o.order_id,o.order_no,o.date,sh.customer_id,sh.customer_name,sh.customer_short_address,sh.customer_mobile,sh.customer_email,pg.agent pmethod,o.total_discount,o.total_amount,o.paid_amount,o.order_status,o.file_path");
        $this->db->from("order o");
        $this->db->join("shipping_info sh", "sh.order_id = o.order_id", "left");
        $this->db->join("order_payment op", "op.order_id = o.order_id", "left");
        $this->db->join("payment_gateway pg", "pg.code = op.payment_id", "left");
        $this->db->where("o.order_id", $order_id);
        $query = $this->db->get();
        $result = $query->row();
        if (!empty($result)) {
            $result->{"due_amount"} = $result->total_amount - $result->paid_amount;
            $result->{"payment_status"} = $this->payment_status($result->order_no);
            $result->{"company_details"} = $this->company_details();
            $result->{"product_information"} = $this->product_info($order_id);
            JSONSuccessOutput($result);
        } else {
            JSONErrorOutput("Invalid order id");
        }
    }
    public function company_details()
    {
        $this->db->select("c.company_name,c.email,c.address,c.mobile,c.website");
        $this->db->from("company_information c");
        $this->db->where("c.company_id", "4JE5HGQDS3GZW2V");
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }
    public function payment_status($oNo)
    {
        $this->db->select("paid_amount,order_status");
        $this->db->from("order");
        $this->db->where("order_no", $oNo);
        $query = $this->db->get();
        $row = $query->row();
        if ($row->order_status != 1 && $row->order_status != 6) {
            return "Paid";
        } else {
            if ($row->order_status == 1) {
                if ($row->paid_amount > 0) {
                    return "Partial Paid";
                } else {
                    return "Unpaid";
                }
            } else {
                return "Unpaid";
            }
        }
    }
    public function product_info($oNo)
    {
        $this->db->select("pi.product_id,pi.seller_id,pt.title,pi.unit,v.variant_name,so.campaign_id,so.quantity,so.rate,so.total_price,so.discount_per_product,pimg.image_path as img_url,pimg.image_name");
        $this->db->from("seller_order so");
        $this->db->join("product_title pt", "pt.product_id=so.product_id AND pt.lang_id='english' ", "left");
        $this->db->join('product_image pimg', "pimg.product_id = so.product_id AND pimg.image_type = 1 AND pimg.status = 1", 'left');
        $this->db->join("product_information pi", "pi.product_id=so.product_id", "left");
        $this->db->join("variant v", "v.variant_id=so.variant_id", "left");
        $this->db->where("so.order_id", $oNo);
        $query = $this->db->get();
        $result = $query->result();
        foreach ($result as $key => $res) {
            if ($res->quantity == 0) {
                $result[$key]->{"sell_price"} = 0;
            }else {
                $result[$key]->{"sell_price"} = ($res->total_price / $res->quantity);
            }
            $result[$key]->{"amount"} = $res->total_price ;
            $result[$key]->{"image_path"} = (!empty($res->img_url)) ? $res->img_url : THUMB_CDN_DIR . $res->image_name;
        }
        return $result;
    }
    //Payment order 
    public function payment_history()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $this->db->select('cmpl.payment_id,o.order_no,cmpl.order_id,ci.customer_name,cmpl.payment_amount');
        $this->db->from('customer_make_payment_list cmpl');
        $this->db->join('customer_information ci', 'ci.customer_id=cmpl.customer_id', 'left');
        $this->db->join('order o', 'o.order_id=cmpl.order_id', 'left');
        $this->db->where('cmpl.customer_id', $customer_id);
        $this->db->order_by('cmpl.payment_id', 'DESC');
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            JSONSuccessOutput($result);
        } else {
            $cust_check = $this->customer_check($customer_id);
            if ($cust_check) {
                JSONNoOutput("No order Found");
            } else {
                JSONNoOutput("No customer found");
            }
        }
    }
    public function payment_details()
    {
        $order_no = filter_input_data($this->input->post('order_no', TRUE));
        if (empty($order_no)) {
            JSONErrorOutput("Order no is required!");
        }
        $this->db->select('cmpl.payment_id_no,cmpl.order_id,ci.customer_name,cmpl.payment_method,o.date,cmpl.status as payment_status,cmpl.payment_amount');
        $this->db->from('customer_make_payment_list cmpl');
        $this->db->join('customer_information ci', 'ci.customer_id=cmpl.customer_id', 'left');
        $this->db->join('order o', 'o.order_id=cmpl.order_id', 'left');
        $this->db->where('o.order_no', $order_no);
        $this->db->order_by('cmpl.payment_id', 'ASC');
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            foreach ($result as $key => $res) {
                if ($res->payment_method == 'bank') {
                    $result[$key]->bank_details = $this->bankPayment_details($res->payment_id_no);
                }
            }
            JSONSuccessOutput($result);
        } else {
            JSONErrorOutput("Invalid order no");
        }
    }
    public function bankPayment_details($id)
    {
        $this->db->select("bsl.bank_name,bsl.bank_ac_no,bsl.payment_slip,bsl.status as payment_status");
        $this->db->from("bank_statement_list bsl");
        $this->db->where("bsl.payment_id_no", $id);
        $this->db->where("bsl.status", 2);
        $query = $this->db->get();
        $row = $query->row();
        if (!empty($row)) {
            return $row;
        }
    }
    public function customer_profile()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $this->db->select('co.first_name,co.last_name,co.customer_name,co.customer_email,co.customer_mobile,co.customer_short_address,co.customer_address_1,co.customer_address_2,co.state,co.city,co.zip,co.country,co.company,co.image');
        $this->db->from('customer_information co');
        $this->db->where('co.customer_id', $customer_id);
        $query = $this->db->get();
        $result = $query->row();
        if (!empty($result)) {
            JSONSuccessOutput($result);
        } else {
            JSONErrorOutput("Invalid customer id");
        }
    }
    public function profile_update()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $first_name = filter_input_data($this->input->post('first_name', TRUE));
        $last_name = filter_input_data($this->input->post('last_name', TRUE));
        $customer_email = filter_input_data($this->input->post('customer_email', TRUE));
        $customer_mobile = filter_input_data($this->input->post('customer_mobile', TRUE));
        $customer_short_address = filter_input_data($this->input->post('customer_short_address', TRUE));
        $customer_address_1 = filter_input_data($this->input->post('customer_address_1', TRUE));
        $customer_address_2 = filter_input_data($this->input->post('customer_address_2', TRUE));
        $state = filter_input_data($this->input->post('state', TRUE));
        $city = filter_input_data($this->input->post('city', TRUE));
        $zip = filter_input_data($this->input->post('zip', TRUE));
        $country = filter_input_data($this->input->post('country', TRUE));
        $company = filter_input_data($this->input->post('company', TRUE));
        // $image = filter_input_data($this->input->post('image', TRUE));
        if ($_FILES['image']['name']) {
            $sizes = array(1300 => 1300, 235 => 235);
            $file_location = $this->do_upload_file($_FILES['image'], $sizes, 'customer_img');
            $image_name = explode('/', $file_location[0]);
            $image_name = end($image_name);
            $base_path = SPACE_URL;
            $customer_img = $base_path.'/'. 'customer_img/' .$image_name;
        }
        $old_image = filter_input_post('old_image');
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $check = $this->customer_check($customer_id);
        if (!$check) {
            JSONErrorOutput("Customer not found!");
        }
        if (empty($first_name)) {
            JSONErrorOutput("First name is required!");
        }
        if (empty($last_name)) {
            JSONErrorOutput("Last name is required!");
        }
        if (empty($customer_email)) {
            JSONErrorOutput("Customer email is required!");
        }
        if (empty($customer_mobile)) {
            JSONErrorOutput("Customer mobile  is required!");
        }
        if (empty($customer_short_address)) {
            JSONErrorOutput("Customer short address id is required!");
        }
        if (empty($state)) {
            JSONErrorOutput("State is required!");
        }
        if (empty($city)) {
            JSONErrorOutput("City is required!");
        }
        if (empty($zip)) {
            JSONErrorOutput("Zip is required!");
        }
        if (empty($country)) {
            JSONErrorOutput("Country is required!");
        }
        $this->db->select('co.customer_address_1,co.customer_address_2,co.image');
        $this->db->from('customer_information co');
        $this->db->where('co.customer_id', $customer_id);
        $query = $this->db->get();
        $result = $query->row();
        if (!empty($result)) {
            $oldaddress1 = $result->customer_address_1;
            $oldaddress2 = $result->customer_address_2;
            $oldimage = $result->image;
        }
        $data = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'customer_name' => $first_name.' '.$last_name,
            'customer_email' => $customer_email,
            'customer_mobile' => $customer_mobile,
            'customer_short_address' => $customer_short_address,
            'customer_address_1' => !empty($customer_address_1) ? $customer_address_1 : $oldaddress1,
            'customer_address_2' => !empty($customer_address_2) ? $customer_address_2 : $oldaddress2,
            'state' => $state,
            'city' => $city,
            'zip' => $zip,
            'country' => $country,
            'company' => $company,
            'image' => !empty($customer_img) ? $customer_img : $old_image,
        );
        $update = $this->db->where("customer_id", $customer_id)->update("customer_information", $data);
        if ($update) {
            JSONSuccessOutput("Successfully Updated.");
        } else {
            JSONErrorOutput("Please try again");
        }
    }
    public function change_password()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $email = filter_input_data($this->input->post('email', TRUE));
        $password = filter_input_data($this->input->post('password', TRUE));
        $newpassword = filter_input_data($this->input->post('newpassword', TRUE));
        $retypepassword = filter_input_data($this->input->post('retypepassword', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $check = $this->customerlogin_check($customer_id);
        if (!$check) {
            JSONErrorOutput("Customer not found!");
        }
        if (empty($email)) {
            JSONErrorOutput("Email is required!");
        }
        if (empty($password)) {
            JSONErrorOutput("Old password is required!");
        }
        $this->db->select('cl.email,cl.password');
        $this->db->from('customer_login cl');
        $this->db->where('cl.customer_id', $customer_id);
        $query = $this->db->get();
        $result = $query->row();
        if (!empty($result)) {
            $oldemail = $result->email;
            $oldpassword = $result->password;
            if ($email != $oldemail) {
                JSONErrorOutput("Email does not match!");
            }
            if (md5("gef" . $password) != trim($oldpassword)) {
                JSONErrorOutput("Password does not match!");
            }
        }
        if (empty($newpassword)) {
            JSONErrorOutput("New password is required!");
        }
        if ($newpassword != $retypepassword) {
            JSONErrorOutput("New password does not match!");
        }
        $data = array(
            'email' => $email,
            'password' => md5("gef" . $newpassword),
        );
        $update = $this->db->where("customer_id", $customer_id)->update("customer_login", $data);
        if ($update) {
            JSONSuccessOutput("Successfully Updated.");
        } else {
            JSONErrorOutput("Please try again");
        }
    }
    public function manage_invoice()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $this->db->select('i.invoice,i.date,id.total_price');
        $this->db->from('invoice i');
        $this->db->join('invoice_details id', 'id.invoice_id=i.invoice_id', 'left');
        $this->db->where('i.customer_id', $customer_id);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            JSONSuccessOutput($result);
        } else {
            $cust_check = $this->customer_check($customer_id);
            if ($cust_check) {
                JSONNoOutput("No invoice Found");
            } else {
                JSONNoOutput("No customer found");
            }
        }
    }
    public function search_invoice()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $invoice_no = filter_input_data($this->input->post('invoice_no', TRUE));
        $date = filter_input_data($this->input->post('date', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        } else {
            $searchdata = $this->searchinv_details($customer_id, $invoice_no, $date);
            if ($searchdata) {
                JSONSuccessOutput($searchdata);
            } else {
                JSONNoOutput('No Order Found');
            }
        }
    }
    public function searchinv_details($customer_id, $invoice_no = NULL, $date = NUll)
    {
        $cust_check = $this->customer_check($customer_id);
        if ($cust_check) {
            $this->db->select('i.invoice,i.date,id.total_price');
            $this->db->from('invoice i');
            $this->db->join('invoice_details id', 'id.invoice_id=i.invoice_id', 'left');
            $this->db->where('i.customer_id', $customer_id);
            if ($invoice_no) {
                $this->db->where("invoice", $invoice_no);
            }
            if ($date) {
                $this->db->where("date", $date);
            }
            $this->db->order_by('id', 'DESC');
            $query = $this->db->get();
            $result = $query->result();
            if (!empty($result)) {
                return $result;
            } else {
                JSONNoOutput('No invoice found');
            }
        } else {
            JSONNoOutput('No customer found');
        }
    }
    public function insert_question()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $seller_id = filter_input_data($this->input->post('seller_id', TRUE));
        $product_id = filter_input_data($this->input->post('product_id', TRUE));
        $category_id = filter_input_data($this->input->post('category_id', TRUE));
        $question_details = filter_input_data($this->input->post('question_details', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("No customer found");
        }
        if (empty($seller_id)) {
            JSONErrorOutput("Seller id is required!");
        }
        if (empty($product_id)) {
            JSONErrorOutput("Product id is required!");
        }
        if (empty($category_id)) {
            JSONErrorOutput("Category id is required!");
        }
        if (empty($question_details)) {
            JSONErrorOutput("Question details is required!");
        }

        $data = array(
            'customer_id' => $customer_id,
            'seller_id' => $seller_id,
            'product_id' => $product_id,
            'category_id' => $category_id,
            'question_details' => $question_details,
        );
        $insert = $this->db->insert("user_questions", $data);
        if ($insert) {
            JSONSuccessOutput(NULL, "Save Successfully");
        } else {
            JSONErrorOutput("Please try again");
        }
    }
    public function question_view()
    {
        $product_id = filter_input_data($this->input->post('product_id', TRUE));
        $seller_id = filter_input_data($this->input->post('seller_id', TRUE));
        if (empty($product_id)) {
            JSONErrorOutput("Product id is required!");
        }
        if (empty($seller_id)) {
            JSONErrorOutput("Seller id is required!");
        }
        $this->db->select("uq.product_id");
        $this->db->from("user_questions uq");
        $this->db->where("uq.product_id", $product_id);
        $this->db->where("uq.seller_id", $seller_id);
        $this->db->order_by("uq.question_id", "DESC");
        $this->db->group_by("uq.product_id");
        $query = $this->db->get();
        $result = $query->result();
        if ($result) {
            foreach ($result as $key => $res) {
                $result[$key]->details = $this->question_details($res->product_id);
            }
            JSONSuccessOutput($result);
        } else {
            $check_product = $this->db->select("COUNT(product_info_id) as product")->from("product_information")->where("product_id", $product_id)->where("seller_id", $seller_id)->get()->row();
            if ($check_product) {
                JSONNoOutput("No question found");
            } else {
                JSONErrorOutput("Invalid product or seller id");
            }
        }
    }
    public function question_details($id)
    {
        $this->db->select("uq.question_id,uq.category_id,uq.seller_id,uq.question_details, uq.question_answer");
        $this->db->from("user_questions uq");
        $this->db->where("uq.product_id", $id);
        $this->db->order_by("uq.question_id", "DESC");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    public function question_answer()
    {
        $seller_id = filter_input_data($this->input->post('seller_id', TRUE));
        $question_id = filter_input_data($this->input->post('question_id', TRUE));
        $answer_details = filter_input_data($this->input->post('answer_details', TRUE));
        if (empty($seller_id)) {
            JSONErrorOutput("Seller id is required!");
        }
        if (empty($question_id)) {
            JSONErrorOutput("Question id is required!");
        }
        $seller_check = $this->db->select("COUNT(seller_id) as seller")->from("user_questions")->where("question_id", $question_id)->where("seller_id", $seller_id)->get()->row();
        if (!$seller_check->seller) {
            JSONErrorOutput("Invalid question or seller id");
        }
        if (empty($answer_details)) {
            JSONErrorOutput("Answer details is required!");
        }

        $data = array(
            'question_answer' => $answer_details,
        );
        $update = $this->db->where("question_id", $question_id)->update("user_questions", $data);
        if ($update) {
            JSONSuccessOutput(NULL, "Save Successfully");
        } else {
            JSONErrorOutput("Please try again");
        }
    }
    public function customer_review()
    {
        $product_id = filter_input_data($this->input->post('product_id', TRUE));
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $seller_id = filter_input_data($this->input->post('seller_id', TRUE));
        $rating_no = filter_input_data($this->input->post('rating_no', TRUE));
        $title = filter_input_data($this->input->post('title', TRUE));
        $review_details = filter_input_data($this->input->post('review_details', TRUE));
        if (empty($product_id)) {
            JSONErrorOutput("Product id is required!");
        }
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        if (empty($seller_id)) {
            JSONErrorOutput("Seller id is required!");
        }
        if (empty($rating_no)) {
            JSONErrorOutput("Rating no is required!");
        }
        if ($rating_no > 5 | $rating_no < 1) {
            JSONErrorOutput("Please, rate between 1 to 5");
        }
        if (empty($title)) {
            JSONErrorOutput("Title is required!");
        }
        if (empty($review_details)) {
            JSONErrorOutput("Review details no is required!");
        }
        $order = $this->check_order($product_id, $customer_id);
        if (!$order) {
            JSONErrorOutput("You did not complete the order process");
        }
        $review = $this->review_check($order);
        if (!$review) {
            JSONErrorOutput("You aready submit a review for this product");
        }
        $data = array(
            'product_id' => $product_id,
            'reviewer_id' => $customer_id,
            'seller_id' => $seller_id,
            'order_id' => $order,
            'rate' => $rating_no,
            'title' => $title,
            'comments' => $review_details,
            'date_time' => date("Y-m-d H:i:s"),
            'status' => 1
        );
        $insert = $this->db->insert("product_review", $data);
        if ($insert) {
            JSONSuccessOutput(NULL, "Save Successfully");
        } else {
            JSONErrorOutput("Please try again");
        }
    }
    public function check_order($product_id, $customer_id)
    {
        $this->db->select("so.order_id");
        $this->db->from("seller_order so");
        $this->db->where("so.product_id", $product_id);
        $this->db->where("so.customer_id", $customer_id);
        $this->db->order_by("so.seller_order_id", "DESC");
        $query = $this->db->get();
        $row = $query->row();
        if (!empty($row)) {
            $this->db->select("COUNT(o.order_status) as num");
            $this->db->from("order o");
            $this->db->where("o.order_id", $row->order_id);
            $this->db->where("o.order_status", 4);
            $subQuery = $this->db->get();
            $status = $subQuery->row();
            if (!empty($status)) {
                return $row->order_id;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function is_order($order_id, $customer_id)
    {
        $this->db->select("so.order_id");
        $this->db->from("seller_order so");
        $this->db->where("so.order_id", $order_id);
        $this->db->where("so.customer_id", $customer_id);
        $this->db->order_by("so.seller_order_id", "DESC");
        $query = $this->db->get();
        $row = $query->row();
        if (!empty($row)) {
            $this->db->select("COUNT(o.order_status) as num");
            $this->db->from("order o");
            $this->db->where("o.order_id", $row->order_id);
            $subQuery = $this->db->get();
            $status = $subQuery->row();
            if (!empty($status)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function review_check($order)
    {
        $this->db->select("COUNT(pr.product_review_id) as review");
        $this->db->from("product_review pr");
        $this->db->where("pr.order_id", $order);
        $query = $this->db->get();
        $row = $query->row();
        if ($row->review > 0) {
            return false;
        } else {
            return true;
        }
    }
    public function show_review()
    {
        $product_id = filter_input_data($this->input->post('product_id', TRUE));
        $seller_id = filter_input_data($this->input->post('seller_id', TRUE));
        if (empty($product_id)) {
            JSONErrorOutput("Product id is required!");
        }
        if (empty($seller_id)) {
            JSONErrorOutput("Seller id is required!");
        }
        $this->db->select("pr.product_review_id,ci.customer_id,ci.customer_name,pr.rate as rating_no,pr.title,pr.comments,pr.date_time");
        $this->db->from("product_review pr");
        $this->db->join("customer_information ci", "ci.customer_id=pr.reviewer_id", "left");
        $this->db->where("pr.product_id", $product_id);
        $this->db->where("pr.seller_id", $seller_id);
        $this->db->order_by("pr.product_review_id", "DESC");
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            foreach ($result as $key => $res) {
                $result[$key]->is_virified = "varified";
            }

            $this->db->select('count(product_review_id) as total_review, round(AVG(rate), 2) AS avg_rating, SUM(IF(rate=5, 1, 0)) AS five_star, SUM(IF(rate=4, 1, 0)) AS four_star, SUM(IF(rate=3, 1, 0)) AS three_star, SUM(IF(rate=2, 1, 0)) AS two_star, SUM(IF(rate=1, 1, 0)) AS one_star');
            $this->db->from('product_review');
            $this->db->where('status', 1);
            $this->db->where('product_id', $product_id);
            $query = $this->db->get();
            $info  =  $query->row_array();
            // JSONSuccessOutput($result);
            header('Content-Type: application/json');
            $response['response_status'] = 200;
            $response['message'] = '';
            $response['status'] = 'success';
            $response['info'] = $info;
            $response['data'] = $result;
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        } else {
            JSONNoOutput("No review found");
        }
    }
    public function payment_gateway_list()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $this->db->select("pg.id,pg.code,pg.agent,pg.currency,pg.status,pg.customer_dashboard_status");
        $this->db->from("payment_gateway pg");
        $this->db->order_by("agent", "ASC");
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            JSONSuccessOutput($result);
        } else {
            JSONNoOutput("No payment gateway list found");
        }
    }
    public function make_payment_submit()
    {
        $payment_amount = filter_input_data($this->input->post('payment_amount', TRUE));
        $payment_method = filter_input_data($this->input->post('payment_method', TRUE));
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $order_id = filter_input_data($this->input->post('order_id', TRUE));
        if (empty($payment_amount)) {
            JSONErrorOutput("Payment amount is required!");
        }
        if (empty($payment_method)) {
            JSONErrorOutput("Payment method is required!");
        }
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        if (empty($order_id)) {
            JSONErrorOutput("Order id is required!");
        }
        $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
        if (empty($order_check->orderid)) {
            JSONErrorOutput("Invalid order id!");
        }
        $this->db->select('o.date,o.order_status');
        $this->db->from('order o');
        $this->db->where('o.customer_id', $customer_id);
        $this->db->where('o.order_id', $order_id);
        $this->db->where('o.order_status', 1);
        $query = $this->db->get();
        $result = $query->result_array();
        if (!empty($result)) {
            $data = $this->remaining_time($result);
            if ($data[0]["remainingTime"] == "Payment Time is Over, Please Contact with e-needz") {
                JSONErrorOutput("Payment Time is Over, Please Contact with e-needz");
            }
        } else {
            JSONErrorOutput("Please Contact with e-needz");
        }
        ///////////////////////

        $payment_entry = array(
            'payment_id_no' => generator(15),
            'order_id' => $order_id,
            'customer_id' => $customer_id,
            'payment_method' => $payment_method,
            'payment_amount' => $payment_amount,
            'payment_date' => date('Y-m-d'),
        );

        $order_info = $this->db->where('order_id', $order_id)->get('order')->row_array();
        if (empty($order_info)) {
            JSONErrorOutput("Invalid Info");
        }

        $order_no = $order_info['order_no'];

        if ($payment_method == 'sslcommerz') {
            $response_url = filter_input_data($this->input->post('response_url', TRUE));
            if (empty($response_url)) {
                JSONErrorOutput("Response URL is required!");
            }
            $trans_id = "eneedz" . uniqid();
            $data_sslcommerz = array(
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'payment_method' => $payment_method,
                'payment_amount' => $payment_amount,
                'payment_date' => date("Y-m-d"),
                'payment_from' => "customer_dashboard",
                'trans_id' => $trans_id,
                'response_url' => $response_url
            );
            $sslcommerz_order_info = array(
                'trans_id' => $trans_id,
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'payment_amount' => $payment_amount,
                'payment_from' => "customer_dashboard",
                'response_url' => $response_url,
            );
            $this->db->insert('sslcommerz_order_info', $sslcommerz_order_info);
            $this->payment_by_sslcommerz($data_sslcommerz);
        }

        if ($payment_method == 'nagad') {
            $response_url = filter_input_data($this->input->post('response_url', TRUE));
            if (empty($response_url)) {
                JSONErrorOutput("Response URL is required!");
            }
            $nagad_inv = $order_no . generator(6);
            $this->db->select('nagad_inv');
            $this->db->where('nagad_inv', $nagad_inv);
            $query = $this->db->get('nagad_order_info');
            $result = $query->num_rows();
            if ($result > 0) {
                $nagad_inv = $order_no . generator(6);
            }
            //nagad_order_info Entry
            $nagad_order_info = array(
                'nagad_inv'     => $nagad_inv,
                'order_id'      => $order_id,
                'customer_id'   => $customer_id,
                'cart_total'    => $payment_amount,
                'date'          => date("Y-m-d h:i a"),
                'response_url'    => $response_url,
            );
            $this->db->insert('nagad_order_info', $nagad_order_info);
            $this->nagad_payment($nagad_inv, $payment_amount, "customer_dashboard");
        }

        if ($payment_method == 'bank') {
            $bank_name = filter_input_data($this->input->post('bank_name', TRUE));
            $bank_ac_no = filter_input_data($this->input->post('bank_ac_no', TRUE));
            if (empty($bank_name)) {
                JSONErrorOutput("Bank name is required!");
            }
            if (empty($bank_ac_no)) {
                JSONErrorOutput("Bank account no is required!");
            }
            if (!empty($_FILES['payment_slip']['name'])) {
                $sizes = array(1300 => 1300, 235 => 235);
                $file_location = $this->do_upload_file($_FILES['payment_slip'], $sizes, 'bankPayslip');
                $image_name = explode('/', $file_location[0]);
                $image_name = end($image_name);
                $base_path = SPACE_URL;
                $payment_slip = $base_path . '/' . 'bankPayslip/' . $image_name;
            }
            ///////////////
            $data_bank =
                array(
                    'payment_id_no' => $payment_entry['payment_id_no'],
                    'order_id'      => $order_id,
                    'customer_id'   => $customer_id,
                    'bank_name'     => filter_input_post('bank_name', true),
                    'bank_ac_no'     => filter_input_post('bank_ac_no', true),
                    'payment_amount' => $payment_amount,
                    'payment_slip'     => (!empty($payment_slip) ? $payment_slip : null),
                );
            $this->db->insert('customer_make_payment_list', $payment_entry);
            $result = $this->db->insert('bank_statement_list', $data_bank);
            ////////////////////
            if ($result) {
                JSONSuccessOutput(NULL, "Save Successfully");
            } else {
                JSONErrorOutput("Please try again");
            }
        }
    }
    public function payment_by_sslcommerz($data)
    {
        $CI = &get_instance();
        $gateway = $this->db->select('*')->from('payment_gateway')->where('code', 'sslcommerz')->get()->row();
        $total_amount = number_format($data['payment_amount'], 2, '.', '');
        if ($total_amount >= 500000) {
            JSONErrorOutput("SSLCOMMERZ Amount Limitation");
        }
        $trans_id = $data['trans_id'];
        // Set Session for payment
        // $paysession = array(
        //     'trans_id' => $trans_id,
        //     'amount' => $total_amount,
        //     'currency_type' => $gateway->currency,
        //     'currency_amount' => $total_amount
        // );
        // $CI->session->set_userdata($paysession);
        // $CI->session->set_userdata('order_id', $data['order_id']);
        // $CI->session->set_userdata('customer_id', $data['customer_id']);
        $post_data = array();
        $post_data['store_id'] = $gateway->public_key;
        $post_data['store_passwd'] = $gateway->private_key;
        $post_data['total_amount'] = $total_amount;
        $post_data['currency'] = $gateway->currency;
        $post_data['tran_id'] = $trans_id;
        if ($data['payment_from'] == "web") {
            $post_data['success_url'] = base_url('api/react/customer_dashboard/sslcommerz_payment_success_web');
        }else {
            $post_data['success_url'] = base_url('api/react/customer_dashboard/sslcommerz_payment_success');
        }
        $post_data['fail_url'] = base_url('api/react/customer_dashboard/sslcommerz_payment_failed');
        $post_data['cancel_url'] = base_url('api/react/customer_dashboard/sslcommerz_payment_cancel');
        # EMI INFO
        $post_data['emi_option'] = "0";
        // $cus_email = $CI->session->userdata('customer_email');
        // $ship_email = $CI->session->userdata('ship_email');
        // $customer_email = (!empty($cus_email) ? $cus_email : $ship_email);
        # CUSTOMER INFORMATION
        // $post_data['cus_name'] = $CI->session->userdata('customer_name');
        // $post_data['cus_email'] = $customer_email;
        // $post_data['cus_add1'] = $CI->session->userdata('customer_address_1');
        // $post_data['cus_add2'] = $CI->session->userdata('customer_address_1');
        // $post_data['cus_city'] = $CI->session->userdata('city');
        // $post_data['cus_state'] = $CI->session->userdata('state');
        // $post_data['cus_postcode'] = $CI->session->userdata('zip');
        // $post_data['cus_country'] = $CI->session->userdata('country');
        // $post_data['cus_phone'] = $CI->session->userdata('customer_mobile');
        # OPTIONAL PARAMETERS
        $post_data['value_a'] = $data['order_id'];
        $post_data['value_b'] = $data['payment_method'];
        $post_data['value_c'] = $data['customer_id'];
        $post_data['value_d'] = $data['response_url'];
        $product_amount = '';
        $post_data['product_amount'] = '';
        // check is live pay or sandbox
        if (!empty($gateway->is_live)) {
            $direct_api_url = "https://securepay.sslcommerz.com/gwprocess/v3/api.php";
        } else {
            $direct_api_url = "https://sandbox.sslcommerz.com/gwprocess/v3/api.php";
        }

        # REQUEST SEND TO SSLCOMMERZ
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $direct_api_url);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC

        $content = curl_exec($handle);

        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($code == 200 && !(curl_errno($handle))) {
            curl_close($handle);
            $sslcommerzResponse = $content;
        } else {
            curl_close($handle);

            JSONErrorOutput("FAILED TO CONNECT WITH SSLCOMMERZ API");
        }

        # PARSE THE JSON RESPONSE
        $sslcz = json_decode($sslcommerzResponse, true);

        if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
            $url = substr($sslcz['GatewayPageURL'], 8, strlen($sslcz['GatewayPageURL']));
            $redirect_url = array(
                "url" => $url
            );
            JSONSuccessOutput($redirect_url);
        } else {
            if (!empty($sslcz) && !empty($sslcz['failedreason'])) {
                $err_msg = $sslcz['failedreason'];
            } else {
                $err_msg = 'Payment Configuration error!';
            }
            JSONErrorOutput($err_msg);
        }
    }

    public function sslcommerz_payment_success_web()
    {
        $response_url = $_POST['value_d'];
        $tran_id = $_POST['tran_id'];
        $order_id = $_POST['value_a'];

        $this->db->where('trans_id', $tran_id);
        $this->db->where('order_id', $order_id);
        $res = $this->db->get('sslcommerz_order_info')->row_array();
        
        if (isset($_POST) && !empty($_POST) && !empty($res)) {
            $cart_contents = json_decode($res['cart_contents']);
            $postdata = $_POST;
            $paid_amount = $postdata['amount'];
            $customer_id = $postdata['value_c'];

            if (!empty($res)) {
                if ($res['is_confirmed'] == "1") {
                    JSONErrorOutput("Invalid Info!");
                }
            } else {
                JSONErrorOutput("Invalid Info!");
            }

            //update nagad_order_info
            $this->db->update('sslcommerz_order_info', array('sslcommerz_api_response' => json_encode($_POST), 'is_confirmed' => '1'), array('trans_id' => $tran_id));
            $costing_info = array(
                'cart_total_amount' => 0,
                'vat_amount' => 0,
                'ship_cost' => 0,
                'coupon_amnt' => 0,
                'discount' => 0,
                'totalAmount' => 0,
            );
            //confirm order
            $return_order_id = $this->confirm_order($order_id, $customer_id, $cart_contents, "sslcommerz", $paid_amount, $paid_amount, $costing_info);
            // $this->order_inserted_data($return_order_id);
            redirect ($response_url.'?status=success&message=Transaction Complete');
        } else {
            redirect ($response_url.'?status=failed&message=Transaction Failed');
        }
    }

    public function sslcommerz_payment_success()
    {
        $response_url = $_POST['value_d'];
        if (isset($_POST) && !empty($_POST)) {
            $postdata = $_POST;
            $txn_id = $postdata['tran_id'];
            $paid_amount = $postdata['amount'];
            $order_id = $postdata['value_a'];
            $customer_id = $postdata['value_c'];
            $payment_method = "sslcommerz";

            $return_msg = $this->customer_order_payment($order_id, $customer_id, $payment_method, $paid_amount);
            if (!empty($return_msg)) {
                redirect ($response_url.'?status=success&message=Transaction Complete');
            } else {
                redirect ($response_url.'?status=failed&message=Transaction Failed');
            }
        } else {
            redirect ($response_url.'?status=failed&message=Transaction Failed');
        }
    }
    public function sslcommerz_payment_failed()
    {
        $response_url = $_POST['value_d'];
        redirect ($response_url.'?status=failed&message=Transaction Failed');
        
        // if ($_POST['status'] == 'FAILED') {
        //     return JSONErrorOutput("Transaction Failed");
        // } else {
        //     return JSONErrorOutput("Payment failed");
        // }
    }
    public function sslcommerz_payment_cancel()
    {
        $response_url = $_POST['value_d'];
        redirect ($response_url.'?status=failed&message=Transaction Failed');
        
        // if ($_POST['status'] == 'FAILED') {
        //     return JSONErrorOutput("Transaction cancelled");
        // } else {
        //     return JSONErrorOutput("Payment cancelled");
        // }
    }

    public function customer_order_payment($order_id, $customer_id, $payment_method, $paid_amount)
    {
        $msg = '';
        //insert customer_make_payment_list
        $payment_entry = array(
            'payment_id_no' => generator(15),
            'order_id' => $order_id,
            'customer_id' => $customer_id,
            'payment_method' => $payment_method,
            'payment_amount' => $paid_amount,
            'payment_date' => date('Y-m-d'),
            'status' => 2,
            'updated_by' => 'customer',
            'updated_date' => date("Y-m-d h:i:sa"),
        );
        $this->db->insert('customer_make_payment_list', $payment_entry);
        //Order update
        $result = $this->db->set('paid_amount', 'paid_amount+' . $paid_amount, FALSE)->where('order_id', $order_id)->update('order');

        #*************************
        #	start full paid
        #*************************
        $this->db->select('*');
        $this->db->from('order a');
        $this->db->join('customer_information b', 'a.customer_id = b.customer_id', 'left');
        $this->db->where('a.order_id', $order_id);
        $query = $this->db->get();
        $result_query = $query->row();
        $customer_email = $result_query->customer_email;
        $due_amount = $result_query->total_amount - $result_query->paid_amount;
        if ($due_amount == 0) {
            $order_status = 2;
            $this->db->set('order_status', $order_status)->set('payment_date', date("Y-m-d h:i:sa"))->where('order_id', $order_id)->update('order');
            $order_tracking_data = array(
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'date' => date("Y-m-d h:i:sa"),
                'message' => 'Order Processing',
                'order_status' => $order_status,
            );
            $this->db->insert('order_tracking', $order_tracking_data);
            $this->Orders->order_paid_data($order_id);
            $this->db->set('date', date("Y-m-d"))
                ->set('invoice_status', $order_status)
                ->where('order_no', $result_query->order_no)
                ->update('invoice');
            #*************************
            #	start sending email
            #*************************
            $setting_detail = $this->Soft_settings->retrieve_email_editdata();
            $company_info   = $this->Companies->company_list();
            $template       = $this->Email_templates->retrieve_template($order_status);
            ///////////////
            $config = array(
                'protocol'      => $setting_detail[0]['protocol'],
                'smtp_host'     => $setting_detail[0]['smtp_host'],
                'smtp_port'     => $setting_detail[0]['smtp_port'],
                'smtp_user'     => $setting_detail[0]['sender_email'],
                'smtp_pass'     => $setting_detail[0]['password'],
                'mailtype'      => $setting_detail[0]['mailtype'],
                'charset'       => 'utf-8'
            );
            $this->email->initialize($config);
            $this->email->set_mailtype($setting_detail[0]['mailtype']);
            $this->email->set_newline("\r\n");
            //Email content
            $this->email->to($customer_email);
            $this->email->from($setting_detail[0]['sender_email'], $company_info[0]['company_name']);
            $this->email->subject($template->subject);
            $this->email->message($template->message);
            $email = $this->test_input($customer_email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($this->email->send()) {
                    $msg .= "Email sent to customer and ";
                } else {
                    $msg .= "Email does not sent and ";
                }
            } else {
                $msg .= "Successfully updated and ";
            }
            #*************************
            #	end sending email
            #*************************
        }
        #*************************
        #	end full paid
        #*************************
        if ($result) {
            $msg .= 'payment successfully completed';
        } else {
            $msg .= "payment failed! contact with admin";
        }
        return $msg;
    }
    //Retrive Email Data
    public function retrieve_email_editdata()
    {
        $this->db->select('*');
        $this->db->from('email_configuration');
        $this->db->where('email_id', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    #=============Company List=============#
    public function company_list()
    {
        $this->db->select('*');
        $this->db->from('company_information');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    //Retrieve Template
    public function retrieve_template($status)
    {

        if ($status == 7) {
            $status = 13;
        }

        $this->db->select('*');
        $this->db->from('email_template');
        $this->db->where('status', $status);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }
    public function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    //Order paid to invoice
    public function order_paid_data($order_id = null, $pmethod = NULL)
    {
        $invoice_id = generator(15);
        $result = $this->db->select('*')
            ->from('order')
            ->where('order_id', $order_id)
            ->where('status', 1)
            ->get()
            ->row();

        if ($result) {

            $order_no = $result->order_no;
            if ($pmethod == "cash" || ($pmethod) == "") {
                $due_amount = $result->total_amount - $result->paid_amount;
            } else {
                $due_amount = 0;
            }
            $data = array(
                'invoice_id'         => $invoice_id,
                'order_no'             => $order_no,
                'customer_id'         => $result->customer_id,
                'shipping_id'         => $result->shipping_id,
                'invoice'             => $this->number_generator(),
                'date'                 => date('Y-m-d'),
                'total_amount'         => $result->total_amount,
                'vat'                 => $result->vat,
                'total_discount'    => $result->total_discount,
                'invoice_discount'    => $result->order_discount,
                'service_charge'     => $result->service_charge,
                'paid_amount'         => $result->paid_amount,
                'due_amount'         => $due_amount,
                'status'             => $result->status,
                'invoice_status'    => $result->order_status,
            );
            $this->db->insert('invoice', $data);

            //Update to customer ledger Table 
            $data2 = array(
                'transaction_id'    =>    generator(15),
                'customer_id'        =>    $result->customer_id,
                'invoice_no'        =>    $invoice_id,
                'order_no'             =>  $order_no,
                'date'                =>    date('Y-m-d'),
                'amount'            =>    $result->total_amount,
                'payment_type'        => ($pmethod) ? $pmethod : "cash",
                'status'            =>    1
            );
            $ledger = $this->db->insert('customer_ledger', $data2);
        } else {
            return true;
        }

        if ($ledger) {

            //order update
            $this->db->set('status', '2');
            $this->db->where('order_id', $order_id);
            $order = $this->db->update('order');

            $order_details = $this->db->select('*')
                ->from('seller_order')
                ->where('order_id', $order_id)
                ->get()
                ->result();

            if ($order_details) {
                foreach ($order_details as $details) {


                    $invoice_details = array(
                        'invoice_details_id' => generator(15),
                        'invoice_id'          => $invoice_id,
                        'order_no'               => $order_no,
                        'seller_id'              =>    $details->seller_id,
                        'category_id'         =>    $details->category_id,
                        'product_id'          => $details->product_id,
                        'variant_id'         => $details->variant_id,
                        'quantity'             => $details->quantity,
                        'rate'                 => $details->rate,
                        'total_price'         => $details->total_price,
                        'discount'             => $details->discount_per_product,
                        'seller_percentage'  =>    $details->seller_percentage,
                    );

                    $order_details = $this->db->insert('invoice_details', $invoice_details);
                }
            }
        }

        //Tax summary entry start
        $this->db->select('*');
        $this->db->from('order_tax_col_summary');
        $this->db->where('order_id', $order_id);
        $query = $this->db->get();
        $tax_summary = $query->result();

        if ($tax_summary) {
            foreach ($tax_summary as $summary) {
                $tax_col_summary = array(
                    'tax_collection_id' => $summary->order_tax_col_id,
                    'invoice_id'         => $invoice_id,
                    'tax_id'             => $summary->tax_id,
                    'tax_amount'         => $summary->tax_amount,
                    'date'                 => $summary->date,
                );
                $this->db->insert('tax_collection_summary', $tax_col_summary);
            }
        }
        //Tax summary entry end

        //Tax details entry start
        $this->db->select('*');
        $this->db->from('order_tax_col_details');
        $this->db->where('order_id', $order_id);
        $query = $this->db->get();
        $tax_details = $query->result();

        if ($tax_details) {
            foreach ($tax_details as $details) {
                $tax_col_details = array(
                    'tax_col_de_id'     => $details->order_tax_col_de_id,
                    'invoice_id'         => $invoice_id,
                    'product_id'         => $details->product_id,
                    'variant_id'         => $details->variant_id,
                    'tax_id'             => $details->tax_id,
                    'amount'             => $details->amount,
                    'date'                 => $details->date,
                );
                $this->db->insert('tax_collection_details', $tax_col_details);
            }
        }
        //Tax details entry end
        return true;
    }
    //NUMBER GENERATOR
    public function number_generator()
    {
        $this->db->select_max('invoice');
        $query         = $this->db->get('invoice');
        $result     = $query->result_array();
        $invoice_no     = $result[0]['invoice'];
        if (!empty($invoice_no)) {
            $invoice_no = $invoice_no + 1;
        } else {
            $invoice_no = 1000;
        }
        return $invoice_no;
    }
    function do_upload_file($FILES, $sizes, $folder)
    {
        // Load Space Library
        $this->load->library('Space');
        $this->spaceobj = new Space();

        // settings
        $max_file_size = 1500 * 1500; // 1MB
        $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

        $filetype = array('main', 'thumb');
        if ($FILES['size'] < $max_file_size) {
            // get file extension
            $ext = strtolower(pathinfo($FILES['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $valid_exts)) {
                $ext = explode(".", $FILES['name']);
                $filename = time() . '.' . end($ext);

                /* resize image */
                $k = 0;
                foreach ($sizes as $w => $h) {

                    $files[] = $this->resize_file($w, $h, $FILES, $filetype[$k], $filename, $folder);
                    $k++;
                }
            } else {
                $files['msg'] = $msg = 'Unsupported file';
            }
        } else {
            $files['msg'] = $msg = 'Please upload image smaller than 200KB';
        }
        sleep(1);
        return $files;
    }
    function resize_file($width, $height, $FILES, $filetype, $filename, $folder)
    {
        // $this->do_resize($width, $height, $FILES);
        if ($filetype == 'main') {
            $save_as = $folder .'/' . $filename;
            $this->spaceobj->upload_to_space($FILES['tmp_name'], $save_as);
        }
        return $filename;
    }
    //Customer multiple address
    public function customer_address()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $address = $this->db->select("customer_id")->from("customer_address")->where("customer_id", $customer_id)->group_by("customer_id")->get()->row();
        if (!empty($address)) {
            $address->address_list = $this->db->select("cd.address_id,cd.customer_name,cd.customer_phone,cd.division,cd.city,cd.area,cd.address,cd.is_primary")->from("customer_address cd")->where("customer_id", $customer_id)->get()->result();
            JSONSuccessOutput($address);
        } else {
            JSONNoOutput("No address found");
        }
    }
    public function create_address()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $customer_name = filter_input_data($this->input->post('customer_name', TRUE));
        $customer_phone = filter_input_data($this->input->post('customer_phone', TRUE));
        $division = filter_input_data($this->input->post('division', TRUE));
        $city = filter_input_data($this->input->post('city', TRUE));
        $area = filter_input_data($this->input->post('area', TRUE));
        $address = filter_input_data($this->input->post('address', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        if (empty($customer_name)) {
            JSONErrorOutput("Customer name is required!");
        }
        if (empty($customer_phone)) {
            JSONErrorOutput("Customer phone is required!");
        }
        if (empty($division)) {
            JSONErrorOutput("Division id is required!");
        }
        if (empty($city)) {
            JSONErrorOutput("City is required!");
        }
        if (empty($area)) {
            JSONErrorOutput("Area is required!");
        }
        if (empty($address)) {
            JSONErrorOutput("Address is required!");
        }
        $address_check = $this->db->select("COUNT(address_id) as address")->from("customer_address")->where("customer_id", $customer_id)->get()->row();
        if (!empty($address_check->address)) {
            $is_primary = 0;
        } else {
            $is_primary = 1;
        }
        $data = array(
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'division' => $division,
            'city' => $city,
            'area' => $area,
            'address' => $address,
            'is_primary' => $is_primary,
        );
        $insert = $this->db->insert("customer_address", $data);
        if ($insert) {
            JSONSuccessOutput(NULL, "Save Successfully");
        } else {
            JSONErrorOutput("Please try again");
        }
    }
    public function update_address()
    {
        $address_id = filter_input_data($this->input->post('address_id', TRUE));
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $customer_name = filter_input_data($this->input->post('customer_name', TRUE));
        $customer_phone = filter_input_data($this->input->post('customer_phone', TRUE));
        $division = filter_input_data($this->input->post('division', TRUE));
        $city = filter_input_data($this->input->post('city', TRUE));
        $area = filter_input_data($this->input->post('area', TRUE));
        $address = filter_input_data($this->input->post('address', TRUE));
        $is_primary = filter_input_data($this->input->post('is_primary', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $address_check = $this->db->select("COUNT(address_id) as address")->from("customer_address")->where("address_id", $address_id)->where("customer_id", $customer_id)->get()->row();
        if (empty($address_check->address)) {
            JSONErrorOutput("Invalid customer address!");
        }
        if (empty($customer_name)) {
            JSONErrorOutput("Customer name is required!");
        }
        if (empty($customer_phone)) {
            JSONErrorOutput("Customer phone is required!");
        }
        if (empty($division)) {
            JSONErrorOutput("Division id is required!");
        }
        if (empty($city)) {
            JSONErrorOutput("City is required!");
        }
        if (empty($area)) {
            JSONErrorOutput("Area is required!");
        }
        if (empty($address)) {
            JSONErrorOutput("Address is required!");
        }
        $old_address = $this->db->select("is_primary")->from("customer_address")->where("address_id", $address_id)->get()->row();
        $data = array(
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'division' => $division,
            'city' => $city,
            'area' => $area,
            'address' => $address,
            'is_primary' => !empty($is_primary) ? $is_primary : $old_address->is_primary,
        );
        $update = $this->db->where("address_id", $address_id)->update("customer_address", $data);
        if ($update) {
            if ($old_address->is_primary == 0 & $data['is_primary'] == 1) {
                $address_total = $this->db->select("address_id")->from("customer_address")->where("customer_id", $customer_id)->get()->result();
                for ($i = 0; $i < count($address_total); $i++) {
                    if ($address_id != $address_total[$i]->address_id) {
                        $this->db->where("address_id", $address_total[$i]->address_id)->update("customer_address", array("is_primary" => 0));
                    }
                }
            }
            JSONSuccessOutput(NULL, "Updated Successfully");
        } else {
            JSONErrorOutput("Please try again");
        }
    }
    
    public function order_tracking()
    {
        $array1 = [];
        $order_no = filter_input_data($this->input->post('order_no', TRUE));
        if (empty($order_no)) {
            JSONErrorOutput("Order No is required!");
        }
        // $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        // if (empty($customer_id)) {
        //     JSONErrorOutput("Customer Id is required!");
        // }
        // $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("customer_id", $customer_id)->where("order_no", $order_no)->get()->row();
        // if (empty($order_check->orderid)) {
        //     JSONErrorOutput("Invalid order id!");
        // }
        $data = $this->db->select("order_id, customer_id, order_status")->from("order")->where("order_no", $order_no)->get()->row();
        if (!empty($data)) {
            $result = $this->db->select("ot.date,ot.order_status,et.subject as message")
            ->from("order o")
            ->join('order_tracking ot', 'ot.order_id = o.order_id', 'left')
            ->join('email_template et', 'et.status = ot.order_status', 'left')
            ->where("ot.order_id", $data->order_id)
            ->order_by("ot.id", "DESC")
            ->get()->result_array();
            // foreach ($result as $k => $val) {
            //     $message = $this->db->select("subject as message")->from("email_template")->where("status", $val->order_status)->get()->row();
            //     $result[$k]->message = $message->message;
            // }

            $this->db->select('a.updated_date as date, o.order_status, CONCAT("TK ",a.payment_amount, " Paid ", "by ", a.payment_method) AS  message');
            $this->db->from('customer_make_payment_list a');
            $this->db->join('order o', 'o.order_id = a.order_id', 'left');
            $this->db->where('a.status', 2);
            $this->db->where('a.order_id', $data->order_id);
            $this->db->where('a.customer_id', $data->customer_id);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $result_array = $query->result_array();
                $array1 = ($result_array);
            }
            $result = array_merge($array1, $result);
            // d($result);
            $price = array();
            foreach ($result as $key => $row)
            {
                $price[$key] = $row['date'];
            }
            array_multisort($price, SORT_DESC, $result);
            // d($result);
            $data->track_details = $result;
            JSONSuccessOutput($data);
        } else {
            JSONNoOutput("No order Found");
        }
    }
    
    public function issuetype_list()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $this->db->select("it.issueType_id,it.type_name");
        $this->db->from("issue_type it");
        $this->db->where("status", 1);
        $this->db->order_by("issueType_id", "DESC");
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            JSONSuccessOutput($result);
        } else {
            JSONNoOutput("No issue list found");
        }
    }
    public function is_valid_customer()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer id required!");
        }
        $cust_check = $this->db->select("COUNT(customer_id) as cust")->from("customer_login")->where("customer_id", $customer_id)->get()->row();
        if (empty($cust_check->cust)) {
            JSONErrorOutput("Invalid customer!");
        } else {
            JSONSuccessOutput(NULL, "Valid Customer");
        }
    }
    public function issue_create()
    {
        $status = filter_input_data($this->input->post('status', TRUE));
        if ($status == 1) {
            $issue_id = filter_input_data($this->input->post('issue_id', TRUE));
            if (empty($issue_id)) {
                JSONErrorOutput("Issue id required!");
            }
            $issue = $this->db->select("status")->from("customer_issue")->where("issue_id", $issue_id)->where("action", 1)->get()->row();
            if ($issue->status == 0) {
                JSONErrorOutput("Your issue marked as invalid");
            }
            if ($issue->status == 1) {
                JSONErrorOutput("Your issue already resolved");
            }
            $data = array(
                'status' => $status,
            );
            $update = $this->db->where("issue_id", $issue_id)->update("customer_issue", $data);
            if (!empty($update)) {
                JSONSuccessOutput(NULL, "Save successfully");
            } else {
                JSONErrorOutput("Please try again");
            }
        } else {
            $order_id = filter_input_data($this->input->post('order_id', TRUE));
            $details = filter_input_data($this->input->post('details', TRUE));
            $submited_by = filter_input_data($this->input->post('submited_by', TRUE));
            $action = filter_input_data($this->input->post('action', TRUE));
            $submited_type = "customer";
            $date_time = date("Y-m-d H:i:sa");
            if (empty($order_id)) {
                JSONErrorOutput("Order id is required!");
            }
            $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
            if (empty($order_check->orderid)) {
                JSONErrorOutput("Invalid order id");
            }
            if (empty($details)) {
                JSONErrorOutput("Details is required!");
            }
            if (empty($order_id)) {
                JSONErrorOutput("Order id is required!");
            }
            $customer_order = $this->is_order($order_id, $submited_by);
            if (!$customer_order) {
                JSONErrorOutput("Invalid customer order!");
            }
            $cust_check = $this->customer_check($submited_by);
            if (!$cust_check) {
                JSONErrorOutput("Invalid customer id!");
            }
            if (empty($action)) {
                JSONErrorOutput("Action is required!");
            }
            if ($action == 2) {
                $issue_id = filter_input_data($this->input->post('issue_id', TRUE));
                $status = null;
                $parent_id = $issue_id;
                if (empty($issue_id)) {
                    JSONErrorOutput("Issue id required!");
                }
                $issue = $this->db->select("COUNT(issue_id) as issue")->from("customer_issue")->where("issue_id", $issue_id)->where("status!=", 0)->where("action", 1)->get()->row();
                if (empty($issue->issue)) {
                    JSONErrorOutput("You do not have any valid issue");
                }
            }
            if ($action == 1) {
                $issueType_id = filter_input_data($this->input->post('issueType_id', TRUE));
                if (empty($issueType_id)) {
                    JSONErrorOutput("Issue type is required!");
                }
                $issue_check = $this->db->select("COUNT(issueType_id) as issue")->from("issue_type")->where("issueType_id", $issueType_id)->get()->row();
                if (empty($issue_check->issue)) {
                    JSONErrorOutput("Invalid issue type");
                }
                $status = 2;
                $parent_id = 0;
                $issuecheck = $this->db->select("COUNT(issue_id) as issue")->from("customer_issue")->where("order_id", $order_id)->where("status!=", 1)->where("action", 1)->get()->row();
                if (!empty($issuecheck->issue)) {
                    JSONErrorOutput("You already submitted an issue");
                }
            }
            if (!empty($_FILES['attachment']['name'])) {
                $sizes = array(1300 => 1300, 235 => 235);
                
                $file_location = $this->do_upload_file($_FILES['attachment'], $sizes, 'issueAttachment');
                $image_name = explode('/', $file_location[0]);
                $image_name = end($image_name);
                $base_path = SPACE_URL;
                $attachment = $base_path . '/' . 'issueAttachment/' . $image_name;
            }
            $data = array(
                'issueType_id' => $issueType_id,
                'parent_id' => $parent_id,
                'order_id' => $order_id,
                'details' => $details,
                'submited_by' => $submited_by,
                'action' => $action,
                'submited_type' => $submited_type,
                'date_time' => $date_time,
                'status' => $status,
                'attachment' => !empty($attachment) ? $attachment : null,
            );
            $insert = $this->db->insert("customer_issue", $data);
            if (!empty($insert)) {
                JSONSuccessOutput(NULL, "Save successfully");
            } else {
                JSONErrorOutput("Please try again");
            }
        }
    }
    public function issue_list()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $order_id = filter_input_data($this->input->post('order_id', TRUE));
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
        if (empty($order_check->orderid)) {
            JSONErrorOutput("Invalid order id");
        }
        $customer_order = $this->is_order($order_id, $customer_id);
        if (!$customer_order) {
            JSONErrorOutput("Invalid customer order!");
        }
        $issue = $this->db->select("ci.issue_id,ci.issueType_id,it.type_name,ci.date_time,ci.order_id,ci.details,ci.attachment,ci.status")
        ->from("customer_issue ci")
        ->join("issue_type it", "it.issueType_id=ci.issueType_id", "left")
        ->where("order_id", $order_id)
        ->where("submited_by", $customer_id)
        ->where("action", 1)
        ->order_by('ci.issue_id', 'desc')
        ->get()->result();
        if (!empty($issue)) {
            foreach ($issue as $key => $val) {
                $issue[$key]->date_time = strtotime($val->date_time) * 1000;
            }
            JSONSuccessOutput($issue);
        } else {
            JSONNoOutput("No data dound");
        }
    }
    public function issue_details()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $order_id = filter_input_data($this->input->post('order_id', TRUE));
        $issue_id = filter_input_data($this->input->post('issue_id', TRUE));
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $order_check = $this->db->select("COUNT(order_no) as orderid")->from("order")->where("order_id", $order_id)->get()->row();
        if (empty($order_check->orderid)) {
            JSONErrorOutput("Invalid order id");
        }
        $customer_order = $this->is_order($order_id, $customer_id);
        if (!$customer_order) {
            JSONErrorOutput("Invalid customer order!");
        }
        if (empty($issue_id)) {
            JSONErrorOutput("Issue id required!");
        }
        $issue = $this->db->select("ci.issue_id,ci.issueType_id,it.type_name,ci.date_time,ci.order_id,ci.details,ci.attachment,ci.status")->from("customer_issue ci")->join("issue_type it", "it.issueType_id=ci.issueType_id", "left")->where("issue_id", $issue_id)->where("order_id", $order_id)->where("submited_by", $customer_id)->where("action", 1)->get()->row();
        if (!empty($issue)) {
            $comment = $this->db->select("ci.issue_id as cmnt_id,ci.order_id,ci.date_time,ci.details as msg,ci.submited_type,ci.attachment")->from("customer_issue ci")->where("parent_id", $issue_id)->where("order_id", $order_id)->where("action", 2)->get()->result();
            foreach($comment as $k =>$val){
                $comment[$k]->date_time = strtotime($val->date_time) * 1000;
            }
            $name = $this->db->select("customer_name")->from("customer_information")->where("customer_id", $customer_id)->get()->row();
            $issue->customer_name = $name->customer_name;
            $issue->comment = !empty($comment) ? $comment : null;
            $issue->date_time = strtotime($issue->date_time) * 1000;
            JSONSuccessOutput($issue);
        } else {
            JSONErrorOutput("Invalid issue id!");
        }
    }
    public function statelist()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $statelist = $this->db->select("id,name")->from("states")->where("country_id", COUNTRY_ID)->get()->result();
        if ($statelist) {
            JSONSuccessOutput($statelist);
        } else {
            JSONNoOutput("No data found");
        }
    }
    public function citylist()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $state_id = filter_input_data($this->input->post('state_id', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        if (empty($state_id)) {
            JSONErrorOutput("State Id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        $citylist = $this->db->select("id,name")->from("cities")->where("state_id", $state_id)->get()->result();
        if ($citylist) {
            JSONSuccessOutput($citylist);
        } else {
            JSONNoOutput("No data found");
        }
    }
    //Submit checkout
    public function submit_checkout()
    {
        $customer_id = filter_input_data($this->input->post('customer_id', TRUE));
        $cart_details = $this->input->post('cart_details', TRUE);
        $address_id = filter_input_data($this->input->post('address_id', TRUE));
        $coupon_code = filter_input_data($this->input->post('coupon_code', TRUE));
        $payment_method = filter_input_data($this->input->post('payment_method', TRUE));
        if (empty($customer_id)) {
            JSONErrorOutput("Customer Id is required!");
        }
        $cust_check = $this->customer_check($customer_id);
        if (!$cust_check) {
            JSONErrorOutput("Invalid customer id!");
        }
        if (empty($cart_details)) {
            JSONErrorOutput("Cart details field is required!");
        }
        if (empty($address_id)) {
            JSONErrorOutput("Address Id is required!");
        }
        $address = $this->db->select("COUNT(address_id) as num")->from("customer_address")->where("address_id", $address_id)->where("customer_id", $customer_id)->get()->row();
        if (empty($address->num)) {
            JSONErrorOutput('Invalid customer address');
        }
        // $pmethod = $this->db->select("COUNT(id) as num")->from("payment_gateway")->where("code", $payment_method)->get()->row();
        // if (empty($pmethod->num)) {
        //     JSONErrorOutput("Payment method  is required!");
        // }
        $coupon_amnt = 0;
        if (!empty($coupon_code)) {
            $cart_details = json_decode($cart_details);
            $coupon_res = $this->apply_coupon($customer_id, $coupon_code, $cart_details);
            $coupon_amnt = $coupon_res['coupon_amnt'];
        }
        //customer_id & customer_code set
        if (!empty($customer_id)) {
            //customer_information by customer_id
            $this->db->select('customer_code,customer_email,company');
            $this->db->from('customer_information');
            $this->db->where('customer_id', $customer_id);
            $query = $this->db->get();
            $data   =  $query->row();
            if (!empty($data)) {
                $customer_code = $data->customer_code;
                $customer_email = $data->customer_email;
                $company = $data->company;
            } else {
                JSONErrorOutput('Invalid customer ID');
            }
        }
        //select address details
        $this->db->select('*');
        $this->db->from('customer_address');
        $this->db->where('address_id', $address_id);
        $query = $this->db->get();
        $result = $query->row();

        $customer_name = $result->customer_name;
        $customer_phone = $result->customer_phone;
        $division_id = $result->division;
        $city_id = $result->city;
        $area_name = $result->area;
        $address_name = $result->address;

        if (!empty(COUNTRY_ID)) {
            $this->db->select('*');
            $this->db->from('countries');
            $this->db->where('id', COUNTRY_ID);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $country_name = $result->name;
            } else {
                JSONErrorOutput('Invalid country id!');
            }
        }
        //select state name by id id
        if (!empty($division_id)) {
            $this->db->select('*');
            $this->db->from('states');
            $this->db->where('id', $division_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $state_name = $result->name;
            } else {
                JSONErrorOutput('Invalid state id!');
            }
        }
        //select city name by city id
        if (!empty($city_id)) {
            $this->db->select('*');
            $this->db->from('cities');
            $this->db->where('id', $city_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $city_name = $result->name;
            } else {
                JSONErrorOutput('Invalid city id!');
            }
        }
        //shipping cost by city
        if (!empty($city_id)) {
            $this->db->select('*');
            $this->db->from('shipping_method');
            $this->db->where('city', $city_id);
            $query = $this->db->get();
            $result = $query->row();
            if (!empty($result)) {
                $ship_cost = $result->charge_amount;
            } else {
                $ship_cost = 0;
            }
        }

        //set $order_info->data 
        $order_id               = generator(15);
        $payment_method         = $payment_method;

        // Check product status before order
        // check product status
        $is_exist = 'yes';
        $cart_total_amount = 0;
        $vat_amount = 0;
        $discount = 0;
        $order_details = "";
        $temp = null;
        if (!empty($cart_details)) {
            $cart_detail = json_decode($cart_details);
            $dateTime = date("Y-m-d H:i:s");
            if (count($cart_detail) > 1 ) {
                $f=0;
            }else {
                $f=1;
            }
            foreach ($cart_detail as $items) {
                $checkcategory = $this->db->select('pi.category_id, pi.seller_id')->from('product_information pi')->where('product_id', $items->product_id)->get()->row();
                if (empty($checkcategory)) {
                    JSONErrorOutput("Invalid productid: $items->product_id!");
                }
                if (empty($items->campaign_id)) {
                    JSONErrorOutput("Campaign id required!");
                }
                $campaign_info = $this->db->select("*")->from("campaign_product_info")->where("campaign_id", $items->campaign_id)->get()->row();
                if (empty($campaign_info) & $items->campaign_id != 1) {
                    JSONErrorOutput("Invalid campaign_id: $items->campaign_id!");
                }
                if ($items->qty <= 0) {
                    JSONErrorOutput("Invalid Quantity: $items->qty!");
                }
                if ($temp == null) {
                    $temp = $checkcategory->seller_id;
                } else if ($temp == $checkcategory->seller_id) {
                    $f=1;
                }
                $multiInvCheck = $this->db->select("COUNT(category_id) as num")->from("product_category")->where("category_id", $checkcategory->category_id)->where("multi_inv", 1)->get()->row();
                if ($multiInvCheck->num > 0 && $items->qty > 1) {
                    JSONErrorOutput('Please don\'t order more than 1 item and 1 quantity, We may cancel your Order if you order more than one in one invoice');
                }
                if ($items->campaign_id != 1 ) {
                    $this->db->where('campaign_id', $items->campaign_id);
                    $this->db->where('start_datetime<=', $dateTime);
                    $this->db->where('end_datetime>=', $dateTime);
                    $query  = $this->db->get('campaign_info');
                    $result = $query->result_array();
                    if (count($result) == 1) {
                        $order_info = $this->db->select("product_campaign_price as price")->from("campaign_product_info")->where("campaign_id", $items->campaign_id)->where("product_id", $items->product_id)->get()->row();
                        if (empty($order_info)) {
                            JSONErrorOutput('Campaign product not found!');
                        }
                    } else {
                        JSONErrorOutput('Campaign id not found!');
                    }
                    $cart_total_amount += $order_info->price*$items->qty;
                } else if (!empty($items->product_id) & $items->campaign_id == 1) {
                    $this->db->where('product_id', $items->product_id);
                    $query  = $this->db->get('product_information');
                    $result = $query->result_array();
                    if (count($result) == 1) {
                        $pinfo = $this->db->select('status,on_sale')
                            ->from('product_information')
                            ->where('product_id', $items->product_id)
                            ->get()->row();
                        if ($pinfo->status != '2') {
                            $is_exist = 'no';
                        } else {
                            if ($pinfo->on_sale == 0) {
                                $order_info = $this->db->select('pi.seller_id,pi.category_id,pi.price,pi.quantity,pi.vat')
                                    ->from('product_information pi')
                                    ->where('product_id', $items->product_id)
                                    ->get()->row();
                                $cart_total_amount += $order_info->price*$items->qty;
                            } else {
                                $order_info = $this->db->select('pi.seller_id,pi.category_id,pi.offer_price,pi.price,pi.quantity,pi.vat')
                                    ->from('product_information pi')
                                    ->where('product_id', $items->product_id)
                                    ->get()->row();
                                $cart_total_amount += $order_info->offer_price*$items->qty;
                                $discount += ($order_info->price - $order_info->offer_price)*$items->qty;
                            }
                            $vat_amount += $order_info->vat*$items->qty;
                        }
                    } else {
                        JSONErrorOutput('Product ID not found!');
                    }
                } else {
                    JSONErrorOutput("Campaign Products and General Products can not be in same Cart!, Please Order Differently");
                }
            }
            //end foreach
            // if ($f == 0) {
            //     JSONErrorOutput('Please don\'t order different seller product in same cart, We may cancel your Order if you order more than one in one invoice');
            
            // }
            if ($cart_total_amount < 500) {
                JSONErrorOutput('You have to orderd 500tk minimum to procced checkout');
            }
        } else {
            JSONErrorOutput('No product is added in cart!');
        }
        // If all ordered products not approved
        if ($is_exist == 'no') {
            JSONErrorOutput('Failed! Products not exist!');
        }
        $totalAmount = $cart_total_amount + $vat_amount + $ship_cost;
        $paid_amount = null;
        //set amount parameter in costing_info array
        $costing_info = array(
            'cart_total_amount' => $cart_total_amount,
            'vat_amount' => $vat_amount,
            'ship_cost' => $ship_cost,
            'coupon_amnt' => $coupon_amnt,
            'discount' => $discount,
            'totalAmount' => $totalAmount,
            'paid_amount' => $paid_amount,
        );
        $order_details_info = array(
            'customer_id' => $customer_id,
            'order_id' => $order_id,
            'order_details' => $order_details,
            'payment_method' => $payment_method,
            'city_id' => $city_id,
        );

        //new customer new shipping address and order entry
        $country_name = "";
        $billing_info = array(
            'customer_id'           => $customer_id,
            'customer_code'         => $customer_code,
            'customer_name'         => $customer_name,
            'first_name'            => $customer_name,
            'last_name'             => "",
            'customer_short_address' => $city_name . "," . $state_name . "," . $area_name,
            'customer_address_1'    => $city_name . "," . $state_name . "," . $area_name,
            'customer_address_2'    => "",
            'city'                  => $city_name,
            'state'                 => $state_name,
            'country'               => $country_name,
            'zip'                   => $area_name,
            'company'               => $company ? $company : "None",
            'customer_mobile'       => $customer_phone,
            'customer_email'        => $customer_email,
            'image'                 => 'my-assets/image/avatar.png',
        );
        // $this->shipping_entry($billing_info, $order_id);

        $return_order_id = $this->order_entry($cart_detail, $order_details_info, $costing_info, $billing_info, $f);
        if (!empty($return_order_id)) {
            JSONSuccessOutput(Null, 'Product Successfully Ordered');
        }else {
            JSONErrorOutput("Order Failed!");
        }
        exit;



        
        if (!($payment_method == 'cash' | $payment_method == 'bank' | $payment_method == 'sslcommerz' | $payment_method == 'pay_later' | $payment_method == 'nagad')) {
            JSONErrorOutput('Invalid Payment Method!');
        }
        //Cash on delivery
        if ($payment_method == 'cash' | $payment_method == 'pay_later') {
            //Order entry
            $return_order_id = $this->order_entry($cart_detail, $order_details_info, $costing_info, $billing_info);
            if (!empty($return_order_id)) {
                JSONSuccessOutput(Null, 'Product Successfully Ordered');
                //gererating order pdf
                // $this->order_html_data($return_order_id);
            }
        } elseif ($payment_method == 'sslcommerz') {
            $response_url = filter_input_data($this->input->post('response_url', TRUE));
            if (empty($response_url)) {
                JSONErrorOutput("Response URL is required!");
            }
            $trans_id = "eneedz" . uniqid();
            $data_sslcommerz = array(
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'payment_method' => $payment_method,
                'payment_amount' => $totalAmount,
                'payment_date' => date("Y-m-d"),
                'cart_contents' => json_encode($cart_detail),
                'costing_info' => json_encode($costing_info),
                'payment_from' => "web",
                'response_url' => $response_url,
                'trans_id' => $trans_id
            );
            $sslcommerz_order_info = array(
                'trans_id' => $trans_id,
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'payment_amount' => $totalAmount,
                'payment_from' => "web",
                'cart_contents' => json_encode($cart_detail),
                'costing_info' => json_encode($costing_info),
                'response_url' => $response_url,
            );
            $this->db->insert('sslcommerz_order_info', $sslcommerz_order_info);
            $this->payment_by_sslcommerz($data_sslcommerz);
        } elseif ($payment_method == 'nagad') {
            $response_url = filter_input_data($this->input->post('response_url', TRUE));
            if (empty($response_url)) {
                JSONErrorOutput("Response URL is required!");
            }
            $nagad_inv = $order_id . generator(5);
            $this->db->select('nagad_inv');
            $this->db->where('nagad_inv', $nagad_inv);
            $query = $this->db->get('nagad_order_info');
            $result = $query->num_rows();
            if ($result > 0) {
                $nagad_inv = $order_id . generator(5);
            }
            //nagad_order_info Entry
            $nagad_order_info = array(
                'nagad_inv'     => $nagad_inv,
                'order_id'      => $order_id,
                'customer_id'   => $customer_id,
                'cart_total'    => $totalAmount,
                'cart_contents' => json_encode($cart_detail),
                'date'          => date("Y-m-d h:i a"),
                'response_url'  => $response_url,
            );
            $this->db->insert('nagad_order_info', $nagad_order_info);
            $this->nagad_payment($nagad_inv, $totalAmount, "web");
        } else {
            JSONNoOutput("Payment gateway not available current time");
        }
    }

    
    public function shipping_entry($data, $order_id = NULL)
    {
        if ($order_id != NULL) {
            $data['order_id'] = $order_id;
        }
        $result = $this->db->insert('shipping_info', $data);
        if ($result) {
            return true;
        }
        return false;
    }
    public function order_entry($cart_details = null, $order_details_info = null, $costing_info = null, $billing_info = null, $diff_seller = null)
    {
        //costing_info
        $vat            = (!empty($costing_info['vat_amount']) ? $costing_info['vat_amount'] : 0);
        $cart_ship_cost = (!empty($costing_info['ship_cost']) ? $costing_info['ship_cost'] : 0);
        $discount       = (!empty($costing_info['discount']) ? $costing_info['discount'] : 0);
        $coupon_amnt    = (!empty($costing_info['coupon_amnt']) ? $costing_info['coupon_amnt'] : 0);
        $totalAmount    = (!empty($costing_info['totalAmount']) ? $costing_info['totalAmount'] : 0);
        $paid_amount    = (!empty($costing_info['paid_amount']) ? $costing_info['paid_amount'] : 0);
        //order_details_info
        $customer_id = $order_details_info['customer_id'];
        $order_details = $order_details_info['order_details'];
        $payment_method = 'cash';
        // $payment_method = $order_details_info['payment_method'];
        $city_id = $order_details_info['city_id'];

        if ($diff_seller == 0) {
            if ($cart_details) {
                $quantity = 0;
                foreach ($cart_details as $items) {
                    $order_id = generator(15);
                    if ($items->campaign_id != 1) {
                        $stock = $this->db->select('product_quantity as quantity')
                            ->from('campaign_product_info')
                            ->where('product_id', $items->product_id)
                            ->where('campaign_id', $items->campaign_id)
                            ->get()
                            ->row();
                        if (!empty($stock)) {
                            if ($stock->quantity < $items->qty) {
                                JSONErrorOutput("You can not order more than stock");
                            }
                        }
                    } else {
                        $stock = $this->db->select('*')
                            ->from('product_information')
                            ->where('product_id', $items->product_id)
                            //->where('pre_order',1)
                            ->get()
                            ->row();
                        if (!empty($stock)) {
                            if ($stock->quantity < $items->qty) {
                                JSONErrorOutput("You can not order more than stock");
                            }
                        }
                    }
                    if (!empty($items)) {
                        //Seller percentage
                        $comission_rate = $this->comission_info($items->product_id);
                        $category_id   = $this->category_id($items->product_id);
                        $sinfo = $this->product_infos($items->product_id);
                        $seller_id = $sinfo->seller_id;
                        $rate = $sinfo->price;
    
                        //seller_order_data
                        if ($items->campaign_id != 1) {
                            $order_info = $this->db->select("product_campaign_price as price")->from("campaign_product_info")->where("campaign_id", $items->campaign_id)->where("product_id", $items->product_id)->get()->row();
                            $total_price = $order_info->price;
                            $discount_per_product = $rate - $order_info->price;
    
                        } else {
                            if ($sinfo->on_sale == 0) {
                                $total_price = $rate;
                                $discount_per_product = 0;
                            } else {
                                $total_price = $sinfo->offer_price;
                                $discount_per_product = $rate - $sinfo->offer_price;
                            }
                        }
                        $seller_order_data = array(
                            'order_id'                =>    $order_id,
                            'seller_id'                =>    $seller_id,
                            'seller_percentage'     =>  $comission_rate,
                            'customer_id'            =>    $customer_id,
                            'campaign_id'            =>    $items->campaign_id,
                            'category_id'            =>    $category_id,
                            'product_id'            =>    $items->product_id,
                            'variant_id'            =>    '',
                            'quantity'                =>    $items->qty,
                            'rate'                    =>    $rate,
                            'total_price'           => ($total_price * $items->qty),
                            'discount_per_product'    =>    $discount_per_product,
                            'product_vat'            =>    '',
                        );
                        //Total quantity count
                        $quantity += $items->qty;
                        $this->db->insert('seller_order', $seller_order_data);
                        if ($items->campaign_id != 1) {
                            //Product stock update
                            $this->db->set('product_quantity', 'product_quantity-' . $items->qty, FALSE);
                            $this->db->where('product_id', $items->product_id);
                            $this->db->update('campaign_product_info');
                        } else {
                            //Product stock update
                            $this->db->set('quantity', 'quantity-' . $items->qty, FALSE);
                            $this->db->where('product_id', $items->product_id);
                            $this->db->update('product_information');
                        }
                    }

                    //insert shipping info
                    $this->shipping_entry($billing_info, $order_id);
                    //order_payment entry start
                    $order_payment_data = array(
                        'order_payment_id' => generator(15), //api_helper.php
                        'payment_id'        => $payment_method,
                        'order_id'            => $order_id,
                        'details'           => $order_details,
                    );
                    $this->db->insert('order_payment', $order_payment_data);
                    ////////
                    $order_no = "EZ" . mt_rand(100000000000, 999999999999);
                    $this->db->select('order_no');
                    $this->db->where('order_no', $order_no);
                    $query = $this->db->get('order');
                    $result = $query->num_rows();
                    if ($result > 0) {
                        $order_no = "EZ" . mt_rand(100000000000, 999999999999);
                    }
                    //Data insert into order table
                    $n_order = array(
                        'order_id'           => $order_id,
                        'order_no'           => $order_no,
                        'customer_id'     => $customer_id,
                        'shipping_id'     => $city_id,
                        'date'               => date("Y-m-d"),
                        'time'               => date("h:i a"),
                        // 'details'          => $order_details,
                        'total_amount'    => $seller_order_data['total_price'],
                        'paid_amount'     => 0,
                        'total_discount'  => 0,
                        // 'coupon_discount'   => $coupon_amnt,
                        // 'service_charge' => $cart_ship_cost,
                        // 'vat'             => $vat,
                        'order_status'     => 1,
                        'pending'        => date("Y-m-d")
                    );
                    $this->db->insert('order', $n_order);
            
                    //Order intsert in order_tracking table
                    $order_tracking = array(
                        'order_id'      => $order_id,
                        'customer_id'   => $customer_id,
                        'date'          => date("Y-m-d h:i a"),
                        'message'       => 'Order Placed',
                        'order_status'  => 1
            
                    );
                    $this->db->insert('order_tracking', $order_tracking);
            
                    //delete coupon_log
                    $this->db->delete('coupon_logs', array('customer_id' => $customer_id));
                    
                    // return $order_id;
                }
                //end foreach
                JSONSuccessOutput(Null, 'Product Successfully Ordered');
            }
            
        }else {
            $order_id = $order_details_info['order_id'];
            //insert shipping info
            $this->shipping_entry($billing_info, $order_id);
            //order_payment entry start
            $order_payment_data = array(
                'order_payment_id' => generator(15), //api_helper.php
                'payment_id'        => $payment_method,
                'order_id'            => $order_id,
                'details'           => $order_details,
            );
            $this->db->insert('order_payment', $order_payment_data);
            //order_payment entry end
            //Insert order to seller_order table and update quantity in product_information start
            $seller_order = $this->seller_order($order_id, $customer_id, $cart_details);
            if ($seller_order == 0) {
                JSONErrorOutput('Order did not Placed');
            }
            ////////
            $order_no = "EZ" . mt_rand(100000000000, 999999999999);
            $this->db->select('order_no');
            $this->db->where('order_no', $order_no);
            $query = $this->db->get('order');
            $result = $query->num_rows();
            if ($result > 0) {
                $order_no = "EZ" . mt_rand(100000000000, 999999999999);
            }
            //Data insert into order table
            $n_order = array(
                'order_id'           => $order_id,
                'order_no'           => $order_no,
                'customer_id'     => $customer_id,
                'shipping_id'     => $city_id,
                'date'               => date("Y-m-d"),
                'time'               => date("h:i a"),
                // 'details'          => $order_details,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paid_amount,
                'total_discount' => ($discount + $coupon_amnt),
                // 'coupon_discount'   => $coupon_amnt,
                // 'service_charge' => $cart_ship_cost,
                // 'vat'             => $vat,
                'order_status'     => 1,
                'pending'        => date("Y-m-d")
            );
            $this->db->insert('order', $n_order);
    
            //Order intsert in order_tracking table
            $order_tracking = array(
                'order_id'      => $order_id,
                'customer_id'   => $customer_id,
                'date'          => date("Y-m-d h:i a"),
                'message'       => 'Order Placed',
                'order_status'  => 1
    
            );
            $this->db->insert('order_tracking', $order_tracking);
    
            //delete coupon_log
            $this->db->delete('coupon_logs', array('customer_id' => $customer_id));
            
            return $order_id;
        }
    }



    //Comission info by product id from model website\Homes.php
    public function comission_info($product_id)
    {
        $comission = $this->db->select('*')
            ->from('product_information')
            ->where('product_id', $product_id)
            ->get()
            ->row();

        if ($comission) {
            return $comission->comission;
        } else {
            return 0;
        }
    }
    //Category id by product id from model website\Homes.php
    public function category_id($product_id)
    {
        $category = $this->db->select('*')
            ->from('product_information')
            ->where('product_id', $product_id)
            ->get()
            ->row();

        if ($category) {
            return $category->category_id;
        } else {
            return null;
        }
    }
    public function product_infos($product_id)
    {
        return $this->db->select('*')
            ->from('product_information')
            ->where('product_id', $product_id)
            ->get()
            ->row();
    }
    //Order html Data
    public function order_html_data($order_id)
    {
        $CI = &get_instance();
        $CI->load->library('occational');
        $CI->load->library('Pdfgenerator');

        $order_detail         = $this->retrieve_order_html_data($order_id);
        if (empty($order_detail)) {
            JSONErrorOutput('Invalid Info');
        }

        //Payment Method
        $paymethod = $this->get_payment_method($order_id);

        $subTotal_quantity     = 0;

        if (!empty($order_detail)) {
            $i = 1;
            foreach ($order_detail as $k => $v) {
                $order_detail[$k]['final_date'] = $CI->occational->dateConvert($order_detail[$k]['date']);
                $subTotal_quantity = $subTotal_quantity + $order_detail[$k]['quantity'];

                $order_detail[$k]['sl'] = $i;
                $i++;
            }
        }

        $currency_details = $this->retrieve_currency_info();
        $company_info       = $this->retrieve_company();
        $agent = $this->db->select("agent")->from("payment_gateway")->where("code", $paymethod['payment_id'])->get()->row();
        $data = array(
            'title'                =>    display('order_details'),
            'order_id'            =>    $order_detail[0]['order_id'],
            'order_no'            =>    $order_detail[0]['order_no'],
            'customer_address'    =>    $order_detail[0]['ship_address'],
            'customer_city'     =>  $order_detail[0]['ship_city'],
            'customer_state'     =>  $order_detail[0]['ship_state'],
            'seller_name'     =>  $order_detail[0]['seller_name'],
            'customer_name'        =>    $order_detail[0]['customer_name'],
            'customer_mobile'    =>    $order_detail[0]['customer_mobile'],
            'customer_email'    =>    $order_detail[0]['customer_email'],
            'final_date'        =>    $order_detail[0]['final_date'],
            'total_amount'        =>    $order_detail[0]['total_amount'],
            'order_discount'     =>    $order_detail[0]['order_discount'],
            'total_discount'     =>    $order_detail[0]['total_discount'] + $order_detail[0]['order_discount'],
            'paid_amount'        =>    $order_detail[0]['paid_amount'],
            'due_amount'        =>    $order_detail[0]['total_amount'] - $order_detail[0]['paid_amount'],
            'details'            =>    $order_detail[0]['details'],
            'service_charge'    =>    $order_detail[0]['service_charge'],
            'subTotal_quantity'    =>    $subTotal_quantity,
            'order_all_data'     =>    $order_detail,
            'company_info'        =>    $company_info,
            'currency'             =>     $currency_details[0]['currency_icon'],
            'position'             =>     $currency_details[0]['currency_position'],
            'paymethod'         =>     $paymethod,
            'paymethod_name'         =>  $agent->agent,
            'vats'         =>  $order_detail[0]['vat'],
        );


        $send_email = '';
        if (ENVIRONMENT == "production") {
            $chapterList = $CI->parser->parse('order/order_pdf', $data, true);
            $dompdf = new DOMPDF();
            $dompdf->loadHtml($chapterList);
            $dompdf->render();
            $output = $dompdf->output();
            file_put_contents('my-assets/pdf/' . $order_detail[0]['order_no'] . '.pdf', $output);
            $file_path = 'my-assets/pdf/' . $order_detail[0]['order_no'] . '.pdf';
            //File path save to database
            $CI->db->set('file_path', base_url($file_path));
            $CI->db->where('order_id', $order_id);
            $CI->db->update('order');

            if (!empty($data['customer_email'])) {
                $send_email = $this->setmail($data['customer_email'], $file_path, null);
            }
        }
        if ($send_email != null) {
            return true;
        } else {
            JSONSuccessOutput(null, 'Product successfully ordered');
        }
    }
    //Retrieve order_html_data
    public function retrieve_order_html_data($order_id)
    {
        $lang_id   = 0;
        $user_lang = $this->session->userdata('language');
        if (empty($user_lang)) {
            $lang_id = 'english';
        } else {
            $lang_id = $user_lang;
        }

        $this->db->select('
			a.*,
			b.*,
			c.*,
			d.product_id,d.thumb_image_url,
			d.product_model,d.unit,
			e.unit_short_name,
			f.variant_name,
			g.title as product_name,
			a.details, 
			CONCAT(s.first_name," ", s.last_name) as seller_name,
			p.customer_address_1 as ship_address, p.city as ship_city, p.state as ship_state
			');
        $this->db->from('order a');
        $this->db->join('customer_information b', 'b.customer_id = a.customer_id');
        $this->db->join('seller_order c', 'c.order_id = a.order_id');
        $this->db->join('seller_information s', 'c.seller_id = s.seller_id and s.status=1', 'left');
        $this->db->join('shipping_info p', 'a.customer_id = p.customer_id and a.order_id = p.order_id', 'left');
        $this->db->join('product_information d', 'd.product_id = c.product_id');
        $this->db->join('unit e', 'e.unit_id = d.unit', 'left');
        $this->db->join('variant f', 'f.variant_id = c.variant_id', 'left');
        $this->db->join('product_title g', 'g.product_id = d.product_id', 'left');
        $this->db->where('a.order_id', $order_id);
        $this->db->where('g.lang_id', $lang_id);
        $query = $this->db->get();
        //echo $this->db->last_query();

        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Retrieve pre order_html_data
    public function retrieve_pre_order_html_data($order_id)
    {
        $lang_id   = 0;
        $user_lang = $this->session->userdata('language');
        if (empty($user_lang)) {
            $lang_id = 'english';
        } else {
            $lang_id = $user_lang;
        }

        $this->db->select('
			a.*,
			b.*,
			c.*,
			d.product_id,
			d.product_model,d.unit,
			e.unit_short_name,
			f.variant_name,
			g.title as product_name,
			a.details
			');
        $this->db->from('pre_order a');
        $this->db->join('customer_information b', 'b.customer_id = a.customer_id');
        $this->db->join('seller_pre_order c', 'c.order_id = a.order_id');
        $this->db->join('product_information d', 'd.product_id = c.product_id');
        $this->db->join('unit e', 'e.unit_id = d.unit', 'left');
        $this->db->join('variant f', 'f.variant_id = c.variant_id', 'left');
        $this->db->join('product_title g', 'g.product_id = d.product_id', 'left');
        $this->db->where('a.order_id', $order_id);
        $this->db->where('g.lang_id', $lang_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    // Get payment method name
    public function get_payment_method($order_id)
    {
        $this->db->where('order_id', $order_id);
        $result = $this->db->get('order_payment')->row_array();
        return $result;
    }
    //Retrieve currency info
    public function retrieve_currency_info()
    {
        $this->db->select('*');
        $this->db->from('currency_info');
        $this->db->where('default_status', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Retrieve company Edit Data
    public function retrieve_company()
    {
        $this->db->select('*');
        $this->db->from('company_information');
        $this->db->limit('1');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
    //Send Customer Email with invoice
    public function setmail($email, $file_path, $order_email)
    {
        $CI = &get_instance();

        if ($email) {

            //send email with as a link
            $setting_detail = $this->retrieve_email_editdata();
            $company_info   = $this->company_list();
            $template         = $this->retrieve_template('8');

            $config = array(
                'protocol'      => $setting_detail[0]['protocol'],
                'smtp_host'     => $setting_detail[0]['smtp_host'],
                'smtp_port'     => $setting_detail[0]['smtp_port'],
                'smtp_user'     => $setting_detail[0]['sender_email'],
                'smtp_pass'     => $setting_detail[0]['password'],
                'mailtype'      => $setting_detail[0]['mailtype'],
                'charset'       => 'utf-8'
            );
            $CI->email->initialize($config);
            $CI->email->set_mailtype($setting_detail[0]['mailtype']);
            $CI->email->set_newline("\r\n");

            //Email content
            $CI->email->to($email);
            $CI->email->from($setting_detail[0]['sender_email'], $company_info[0]['company_name']);
            $CI->email->subject($template->subject);
            $CI->email->message($order_email);
            $CI->email->attach($file_path);

            $email = $this->test_input($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($CI->email->send()) {
                    JSONSuccessOutput(display('product_successfully_order') . '(' . $email . ')');
                } else {
                    JSONErrorOutput(display('email_not_send'));
                }
            } else {
                JSONSuccessOutput(display('please_enter_valid_email'));
            }
        } else {
            JSONErrorOutput(display('your_email_was_not_found'));
        }
    }
    //NUMBER GENERATOR from model website\Homes.php
    public function order_number_generator()
    {
        $order_no = "EZ" . mt_rand(100000000000, 999999999999);
        // $order_no = "EZ".strtotime("now").mt_rand(10,99);
        $this->db->select('order_no');
        $this->db->where('order_no', $order_no);
        $query = $this->db->get('order');
        $result = $query->num_rows();
        if ($result > 0) {
            $order_no = "EZ" . mt_rand(100000000000, 999999999999);
        }

        return $order_no;
    }
    public function get_payment_method_name($payment_id)
    {
        $this->db->where('id', $payment_id);
        $this->db->or_where('code', $payment_id);
        $result = $this->db->get('payment_gateway')->row_array();
        if (!empty($result)) {
            return $result['agent'];
        } else return '';
    }

    /*
    |-------------------------------
    |   Modification Start
    |-------------------------------
    */
    public function apply_coupon($customer_id, $coupon_code, $cartitems)
    {
        if (empty($customer_id) || empty($coupon_code) || empty($cartitems)) {
            JSONErrorOutput('Invalid Info');
        } else {
            $result = $this->db->select('*')
                ->from('coupon')
                ->where('coupon_discount_code', $coupon_code)
                ->where('status', 1)
                ->get()
                ->row();
            if ($result) {
                $products = array_column($cartitems, 'product_id');
                $quantity = array_column($cartitems, 'qty');
                // Check Coupon product is exist in cart or not
                $productpos = array_search($result->product_id, $products);
                // var_dump($products);exit;
                if (in_array($result->product_id, $products)) {
                    $start_date = strtotime($result->start_date);
                    $end_date = strtotime($result->end_date);
                    $today_date = time();
                    if (($today_date >= $start_date) && ($today_date <= $end_date)) {
                        $total_dis = 0;
                        if ($result->discount_type == 1) {
                            $total_dis = $result->discount_amount * $quantity[$productpos];
                        } elseif ($result->discount_type == 2) {
                            $dis = ($this->cart->total() * $result->discount_percentage) / 100;
                            $total_dis = $dis * $quantity[$productpos];
                        }

                        $coupondata = array(
                            'coupon_id' =>  uniqid(),
                            'coupon_code' => $coupon_code,
                            'customer_id' => $customer_id,
                            'product_id' => $result->product_id,
                            'quantity' => $quantity[$productpos],
                            'discount_amt' => $total_dis
                        );
                        $return_response = $this->check_coupon_logs($coupondata);
                        if (!empty($return_response)) {
                            return $return_response;
                        }
                    } else {
                        JSONErrorOutput(display('coupon_is_expired'));
                    }
                } else {
                    JSONErrorOutput(display('this_coupon_is_not_applicable'));
                }
            } else {
                JSONErrorOutput(display('invalid_coupon'));
            }
        }
    }

    // Coupon Udate
    public function check_coupon_logs($cdata)
    {
        $this->db->where('customer_id', $cdata['customer_id']);
        $this->db->where('product_id', $cdata['product_id']);
        $cinfo = $this->db->get('coupon_logs')->row();
        if ($cinfo) {
            $this->db->update('coupon_logs', $cdata, array('customer_id' => $cdata['customer_id'], 'product_id' => $cdata['product_id']));
        } else {
            $this->db->insert('coupon_logs', $cdata);
        }
        // Get Total Suam data
        $total_amt = $this->db->select('IFNULL(SUM(discount_amt), 0) as discount_amt')
            ->from('coupon_logs')
            ->where('coupon_id', $cdata['coupon_id'])
            ->where('customer_id', $cdata['customer_id'])
            ->get()->row();
        $return_array = array(
            'coupon_id' => $cdata['coupon_id'],
            'coupon_amnt' => $total_amt->discount_amt
        );
        return $return_array;
    }

    public function seller_order($order_id, $customer_id, $cart_details)
    {
        if ($cart_details) {
            $quantity = 0;
            foreach ($cart_details as $items) {
                if ($items->campaign_id != 1) {
                    $stock = $this->db->select('product_quantity as quantity')
                        ->from('campaign_product_info')
                        ->where('product_id', $items->product_id)
                        ->where('campaign_id', $items->campaign_id)
                        ->get()
                        ->row();
                    if (!empty($stock)) {
                        if ($stock->quantity < $items->qty) {
                            JSONErrorOutput("You can not order more than stock");
                        }
                    }
                } else {
                    $stock = $this->db->select('*')
                        ->from('product_information')
                        ->where('product_id', $items->product_id)
                        //->where('pre_order',1)
                        ->get()
                        ->row();
                    if (!empty($stock)) {
                        if ($stock->quantity < $items->qty) {
                            JSONErrorOutput("You can not order more than stock");
                        }
                    }
                }
                if (!empty($items)) {
                    //Seller percentage
                    $comission_rate = $this->comission_info($items->product_id);
                    $category_id   = $this->category_id($items->product_id);
                    $sinfo = $this->product_infos($items->product_id);
                    $seller_id = $sinfo->seller_id;
                    $rate = $sinfo->price;

                    //seller_order_data
                    if ($items->campaign_id != 1) {
                        $order_info = $this->db->select("product_campaign_price as price")->from("campaign_product_info")->where("campaign_id", $items->campaign_id)->where("product_id", $items->product_id)->get()->row();
                        $total_price = $order_info->price;
                        $discount_per_product = $rate - $order_info->price;

                    } else {
                        if ($sinfo->on_sale == 0) {
                            $total_price = $rate;
                            $discount_per_product = 0;
                        } else {
                            $total_price = $sinfo->offer_price;
                            $discount_per_product = $rate - $sinfo->offer_price;
                        }
                    }
                    $seller_order_data = array(
                        'order_id'                =>    $order_id,
                        'seller_id'                =>    $seller_id,
                        'seller_percentage'     =>  $comission_rate,
                        'customer_id'            =>    $customer_id,
                        'campaign_id'            =>    $items->campaign_id,
                        'category_id'            =>    $category_id,
                        'product_id'            =>    $items->product_id,
                        'variant_id'            =>    '',
                        'quantity'                =>    $items->qty,
                        'rate'                    =>    $rate,
                        'total_price'           => ($total_price * $items->qty),
                        'discount_per_product'    =>    $discount_per_product,
                        'product_vat'            =>    '',
                    );
                    //Total quantity count
                    $quantity += $items->qty;
                    $this->db->insert('seller_order', $seller_order_data);
                    if ($items->campaign_id != 1) {
                        //Product stock update
                        $this->db->set('product_quantity', 'product_quantity-' . $items->qty, FALSE);
                        $this->db->where('product_id', $items->product_id);
                        $this->db->update('campaign_product_info');
                    } else {
                        //Product stock update
                        $this->db->set('quantity', 'quantity-' . $items->qty, FALSE);
                        $this->db->where('product_id', $items->product_id);
                        $this->db->update('product_information');
                    }
                }
            }
            //end foreach
            return 1;
        }else{
            return 0;
        }
    }

    public function confirm_order($order_id, $customer_id, $cart_contents, $payment_method, $total_amount, $paid_amount, $costing_info)
    {
        //Delivery order payment entry
        $data = array(
            'order_payment_id'  => generator(15),
            'payment_id'        => $payment_method,
            'order_id'          => $order_id,
        );
        $this->db->insert('order_payment', $data);
        ////////
        $seller_order = $this->seller_order($order_id, $customer_id, $cart_contents);
        if ($seller_order == 0) {
            JSONErrorOutput('Order did not Placed');
        }
        ////////
        $order_no = "EZ" . mt_rand(100000000000, 999999999999);
        $this->db->select('order_no');
        $this->db->where('order_no', $order_no);
        $query = $this->db->get('order');
        $result = $query->num_rows();
        if ($result > 0) {
            $order_no = "EZ" . mt_rand(100000000000, 999999999999);
        }
        ///////////////////
        if ($total_amount == $paid_amount) {
            $n_order = array(
                'order_id'        => $order_id,
                'order_no'        => $order_no,
                'customer_id'     => $customer_id,
                'shipping_id'     => 0,
                'date'            => date("Y-m-d"),
                'time'            => date("h:i a"),
                'total_amount'    => $total_amount,
                'paid_amount'     => $paid_amount,
                'order_status'    => 2,
                'payment_date'    => date("Y-m-d h:i:sa"),
                'pending'         => date("Y-m-d")
            );
            $this->db->insert('order', $n_order);
            //Order intsert info order tracking
            $order_tracking_pending = array(
                'order_id'           => $order_id,
                'customer_id'     => $customer_id,
                'date'          => date("Y-m-d h:i:sa"),
                'message'       => 'Order Placed',
                'order_status'  => 1

            );
            $this->db->insert('order_tracking', $order_tracking_pending);
            $order_tracking = array(
                'order_id'           => $order_id,
                'customer_id'     => $customer_id,
                'date'          => date("Y-m-d h:i:sa"),
                'message'       => 'Order Processing',
                'order_status'  => 2

            );
            $this->db->insert('order_tracking', $order_tracking);
        } else {
            $n_order = array(
                'order_id'           => $order_id,
                'order_no'           => $order_no,
                'customer_id'     => $customer_id,
                'shipping_id'     => 0,
                'date'               => date("Y-m-d"),
                'time'               => date("h:i a"),
                'total_amount'    => $total_amount,
                'paid_amount'     => $paid_amount,
                'order_status'     => 1,
                'pending'        => date("Y-m-d")
            );
            $this->db->insert('order', $n_order);
            //Order intsert info order tracking
            $order_tracking_pending = array(
                'order_id'      => $order_id,
                'customer_id'   => $customer_id,
                'date'          => date("Y-m-d h:i:sa"),
                'message'       => 'Order Placed',
                'order_status'  => 1

            );
            $this->db->insert('order_tracking', $order_tracking_pending);
        }

        return $order_id;
    }

    public function order_inserted_data($order_id)
    {
        if (ENVIRONMENT == 'production') {
            JSONSuccessOutput(Null, 'Product Successfully Ordered');
            // $content = $this->order_html_data($order_id);
            // return $content;
        } else {
            JSONSuccessOutput(Null, 'Product Successfully Ordered');
        }
    }


    /*
    |-------------------------------
    |   Nagad Start
    |-------------------------------
    */
    public function nagad_gateway_setting()
    {
        $gateway = $this->db->select('*')->from('payment_gateway')->where('code', 'nagad')->get()->row();
        return $gateway;
    }

    public function getPgPublicKey()
    {
        return $this->nagad_gateway_setting()->public_key;
    }

    public function getMerchantPrivateKey()
    {
        return $this->nagad_gateway_setting()->private_key;
    }

    public function getBaseUrl()
    {
        return $this->nagad_gateway_setting()->shop_id;
    }

    public function getMerchantID()
    {
        return $this->nagad_gateway_setting()->secret_key;
    }

    public function apiUrl()
    {
        $apiUrl = 'api/dfs/check-out/initialize/';
        return $apiUrl;
    }

    public function statusCheckAPI()
    {
        $url = 'api/dfs/verify/payment/';
        return $url;
    }

    public function nagad_payment($order_id, $amount, $payment_from)
    {
        $postUrl = $this->getBaseUrl() . $this->apiUrl()
            . $this->getMerchantID() .
            "/" . $order_id;
        $sensitiveData = array(
            'merchantId' => $this->getMerchantID(),
            'datetime' => Date('YmdHis'),
            'orderId' => $order_id,
            'challenge' => $this->generateRandomString(40, 'you', 'me')
        );

        $postData = array(
            'dateTime' => Date('YmdHis'),
            'sensitiveData' => $this->EncryptDataWithPublicKey(json_encode($sensitiveData)),
            'signature' => $this->SignatureGenerate(json_encode($sensitiveData))
        );

        $resultData = $this->HttpPostMethod($postUrl, $postData);
        $this->initUrl = $postUrl;

        if (is_array($resultData) && array_key_exists('reason', $resultData)) {
            $this->showResponse($resultData, $sensitiveData, $postData);
            return $this->response;
        } else if (is_array($resultData) && array_key_exists('error', $resultData)) {
            $this->showResponse($resultData, $sensitiveData, $postData);
            return $this->response;
        }

        if (array_key_exists('sensitiveData', $resultData) && array_key_exists('signature', $resultData)) {
            if (!empty($resultData['sensitiveData']) && !empty($resultData['signature'])) {
                $PlainResponse = json_decode($this->DecryptDataWithPrivateKey($resultData['sensitiveData']), true);
                if (isset($PlainResponse['paymentReferenceId']) && isset($PlainResponse['challenge'])) {
                    $paymentReferenceId = $PlainResponse['paymentReferenceId'];
                    $challenge = $PlainResponse['challenge'];

                    $SensitiveDataOrder = array(
                        'merchantId' => $this->getMerchantID(),
                        'orderId' => $order_id,
                        'currencyCode' => "050",
                        'amount' => (int)$amount,
                        'challenge' => $challenge
                    );
                    if ($payment_from != 'web') {
                        $PostDataOrder = array(
                            'sensitiveData' => $this->EncryptDataWithPublicKey(json_encode($SensitiveDataOrder)),
                            'signature' => $this->SignatureGenerate(json_encode($SensitiveDataOrder)),
                            'merchantCallbackURL' => base_url('api/react/customer_dashboard/nagad_customer_dashboard')
                        );
                        $OrderSubmitUrl = $this->getBaseUrl() . "api/dfs/check-out/complete/" . $paymentReferenceId;
                        $Result_Data_Order = $this->HttpPostMethod($OrderSubmitUrl, $PostDataOrder);
                        if (array_key_exists('status', $Result_Data_Order)) {
                            if ($Result_Data_Order['status'] == "Success") {
                                $data = array(
                                    "url" => $Result_Data_Order['callBackUrl']
                                );
                                JSONSuccessOutput($data);
                            }
                        }
                    } else {
                        $PostDataOrder = array(
                            'sensitiveData' => $this->EncryptDataWithPublicKey(json_encode($SensitiveDataOrder)),
                            'signature' => $this->SignatureGenerate(json_encode($SensitiveDataOrder)),
                            'merchantCallbackURL' => base_url('api/react/customer_dashboard/nagad_api_response_web')
                        );
                        $OrderSubmitUrl = $this->getBaseUrl() . "api/dfs/check-out/complete/" . $paymentReferenceId;
                        $Result_Data_Order = $this->HttpPostMethod($OrderSubmitUrl, $PostDataOrder);
                        if (array_key_exists('status', $Result_Data_Order)) {
                            if ($Result_Data_Order['status'] == "Success") {
                                $data = array(
                                    "url" => $Result_Data_Order['callBackUrl']
                                );
                                JSONSuccessOutput($data);
                            }
                        }
                    }
                }
            }
        }
    }

    private function showResponse($resultData, $sensitiveData, $postData)
    {
        $this->response = [
            'status' => 'error',
            'response' => $resultData,
            'request' => [
                'environment' => 'development',
                'time' => [
                    'request time' => date('Y-m-d H:i:s'),
                    'timezone' => 'Asia/Dhaka'
                ],
                'url' => [
                    'base_url' => $this->getBaseUrl(),
                    'api_url' => $this->apiUrl(),
                    'request_url' => $this->getBaseUrl() . $this->apiUrl()
                ],
                'data' => [
                    'sensitiveData' => $sensitiveData,
                    'postData' => $postData
                ],

            ],
            'server' => $this->serverDetails()
        ];
    }

    public function generateRandomString($length = 40, $prefix = '', $suffix = '')
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        if (!empty($prefix)) {
            $randomString = $prefix . $randomString;
        }
        if (!empty($suffix)) {
            $randomString .= $suffix;
        }
        return $randomString;
    }

    public function EncryptDataWithPublicKey($data)
    {
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . $this->getPgPublicKey() . "\n-----END PUBLIC KEY-----";
        $keyResource = openssl_get_publickey($publicKey);
        openssl_public_encrypt($data, $cryptoText, $keyResource);
        return base64_encode($cryptoText);
    }

    public function SignatureGenerate($data)
    {
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . $this->getMerchantPrivateKey() . "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }


    public function HttpPostMethod($PostURL, $PostData)
    {
        $url = curl_init($PostURL);
        $postToken = json_encode($PostData);
        $header = array(
            'Content-Type:application/json',
            'X-KM-Api-Version:v-0.2.0',
            'X-KM-IP-V4:' . $this->getClientIP(),
            'X-KM-Client-Type:PC_WEB'
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $postToken);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($url, CURLOPT_SSL_VERIFYPEER, 0);
        $resultData = curl_exec($url);
        $curl_error = curl_error($url);

        if (!empty($curl_error)) {
            return [
                'error' => $curl_error
            ];
        } else {
            $response = json_decode($resultData, true, 512);
            curl_close($url);
            return $response;
        }
    }

    public function getClientIP()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN IP';
        }
        return $ipaddress;
    }

    public function DecryptDataWithPrivateKey($cryptoText)
    {
        $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $this->getMerchantPrivateKey() . "\n-----END RSA PRIVATE KEY-----";
        openssl_private_decrypt(base64_decode($cryptoText), $plain_text, $private_key);
        return $plain_text;
    }

    public function generateFakeInvoice($length = 20, $capitalize = false, $prefix = '', $suffix = '')
    {
        $invoice = $prefix . $this->generateRandomString($length) . $suffix;
        if ($capitalize === true) {
            $invoice = strtoupper($invoice);
        }
        return $invoice;
    }

    public static function errorLog($data)
    {
        if (!file_exists('logs/nagadApi') && !mkdir('logs', 0775) && !is_dir('logs')) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', 'logs'));
        }

        if (!file_exists('logs/nagadApi/error.log')) {

            $logFile = "logs/error.log";
            $fh = fopen($logFile, 'w+') or die("can't open file");
            fclose($fh);
            chmod($logFile, 0755);
        }
        $date = '=====================' . date('Y-m-d H:i:s') . '=============================================\n';
        file_put_contents('logs/nagadApi/error.log', print_r($date, true), FILE_APPEND);
        file_put_contents('logs/nagadApi/error.log', PHP_EOL . print_r($data, true), FILE_APPEND);
        $string = '=====================' . date('Y-m-d H:i:s') . '=============================================' . PHP_EOL;
        file_put_contents('logs/nagadApi/error.log', print_r($string, true), FILE_APPEND);
    }

    public static function serverDetails()
    {
        return [
            'base' => $_SERVER['SERVER_ADDR'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'port' => $_SERVER['REMOTE_PORT'],
            'request_url' => $_SERVER['REQUEST_URI'],
            'user agent' => $_SERVER['HTTP_USER_AGENT'],
        ];
    }

    public function successResponse($response)
    {
        $parts = parse_url($response);
        parse_str($parts['query'], $query);
        return $query;
    }
    /*
    |-------------------------------
    |   Nagad End
    |-------------------------------
    */


    //route: api/react/customer_dashboard/nagad_api_response_web
    public function nagad_api_response_web()
    {
        $nagad_inv = $_GET['order_id'];
        $this->db->where('nagad_inv', $nagad_inv);
        $res = $this->db->get('nagad_order_info')->row_array();
        $response_url = $res['response_url'];
        $cart_contents = json_decode($res['cart_contents']);
        if ($_GET['status_code'] == '00_0000_000' && $_GET['status'] == 'Success') {
            $statusCheckAPI = $this->getBaseUrl() . $this->statusCheckAPI() . $_GET['payment_ref_id'];
            $getdata = file_get_contents($statusCheckAPI);
            $result = json_decode($getdata);
            if ($result->status != "Success") {
                JSONErrorOutput("Invalid Info!");
            }
            
            if (!empty($res)) {
                if ($res['is_confirmed'] == "1") {
                    JSONErrorOutput("Invalid Info!");
                }
            } else {
                JSONErrorOutput("Invalid Info!");
            }

            //update nagad_order_info
            $this->db->update('nagad_order_info', array('nagad_api_response' => json_encode($_GET), 'is_confirmed' => '1'), array('nagad_inv' => $nagad_inv));
            $costing_info = array(
                'cart_total_amount' => 0,
                'vat_amount' => 0,
                'ship_cost' => 0,
                'coupon_amnt' => 0,
                'discount' => 0,
                'totalAmount' => 0,
            );
            //confirm order
            $return_order_id = $this->confirm_order($res['order_id'], $res['customer_id'], $cart_contents, "nagad", $res['cart_total'], $res['cart_total'], $costing_info);
            // $this->order_inserted_data($return_order_id);
            redirect ($response_url.'?status=success&message=Transaction Complete');
        } else {
            redirect ($response_url.'?status=failed&message=Transaction Failed');
        }
    }

    //route: api/react/customer_dashboard/nagad_customer_dashboard
    public function nagad_customer_dashboard()
    {
        $nagad_inv = $_GET['order_id'];
        $this->db->where('nagad_inv', $nagad_inv);
        $res = $this->db->get('nagad_order_info')->row_array();
        $response_url = $res['response_url'];
        if ($_GET['status_code'] == '00_0000_000' && $_GET['status'] == 'Success') {
            
            $statusCheckAPI = $this->getBaseUrl() . $this->statusCheckAPI() . $_GET['payment_ref_id'];
            $getdata = file_get_contents($statusCheckAPI);
            $result = json_decode($getdata);
            if ($result->status != "Success") {
                JSONErrorOutput("Invalid Info!");
            }
            
            if (!empty($res)) {
                if ($res['is_confirmed'] == "1") {
                    JSONErrorOutput("Invalid Info!");
                }
            } else {
                JSONErrorOutput("Invalid Info!");
            }

            //update nagad_order_info
            $this->db->update('nagad_order_info', array('nagad_api_response' => json_encode($_GET), 'is_confirmed' => '1'), array('nagad_inv' => $nagad_inv));
            

            $order_id       = $res['order_id'];
            $customer_id    = $res['customer_id'];
            $paid_amount    = $res['cart_total'];
            $payment_method = "nagad";

            $return_msg = $this->customer_order_payment($order_id, $customer_id, $payment_method, $paid_amount);
            if (!empty($return_msg)) {
                redirect ($response_url.'?status=success&message=Transaction Complete');
            } else {
                redirect ($response_url.'?status=failed&message=Transaction Failed');
            }
        } else {
            redirect ($response_url.'?status=failed&message=Transaction Failed');
        }
    }

    /*
    |-------------------------------
    |   Modification End
    |-------------------------------
    */

}
