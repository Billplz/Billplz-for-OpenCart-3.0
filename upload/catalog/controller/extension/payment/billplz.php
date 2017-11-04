<?php

/**
 * Billplz OpenCart Plugin
 * 
 * @package Payment Gateway
 * @author Wan Zulkarnain <wan@billplz.com>
 * @version 3.1
 */

require_once __DIR__ .'/billplz-api.php';

class ControllerExtensionPaymentBillplz extends Controller
{

    public function index()
    {
        $this->load->language('extension/payment/billplz');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        //$data['country'] = $order_info['payment_iso_code_2'];
        //$data['currency'] = $order_info['currency_code'];
        $products = $this->cart->getProducts();
        foreach ($products as $product) {
            $data['prod_desc'][] = $product['name'] . " x " . $product['quantity'];
        }
        //$data['lang'] = $this->session->data['language'];

        $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $_SESSION['email'] = empty($order_info['email']) ? '' : $order_info['email'];
        $_SESSION['description'] = "Order " . $this->session->data['order_id'] . " - " . implode($data['prod_desc']);
        $_SESSION['mobile'] = empty($order_info['telephone']) ? '' : $order_info['telephone'];
        $_SESSION['reference_1_label'] = "ID";


        $_SESSION['reference_1'] = $this->session->data['order_id'];
        $_SESSION['amount'] = $amount;
        $_SESSION['redirect_url'] = $this->url->link('extension/payment/billplz/return_ipn', '', true);
        $_SESSION['callback_url'] = $this->url->link('extension/payment/billplz/callback_ipn', '', true);
        $_SESSION['delivery'] = $this->config->get('payment_billplz_delivery'); //0-4
        $data['action'] = $this->url->link('extension/payment/billplz/proceed', '', true);

        return $this->load->view('extension/payment/billplz', $data);
    }

    public function proceed()
    {

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $api_key = $this->config->get('payment_billplz_api_key_value');
        $x_signature = $this->config->get('payment_billplz_x_signature_value');
        $collection_id = $this->config->get('payment_billplz_collection_id_value');

        $deliver = $_SESSION['delivery'];
        $name = $_SESSION['name'];
        $email = $_SESSION['email'];
        $description = $_SESSION['description'];
        $mobile = $_SESSION['mobile'];
        $reference_1_label = $_SESSION['reference_1_label'];
        $reference_1 = $_SESSION['reference_1'];
        $amount = $_SESSION['amount'];
        $redirect_url = $_SESSION['redirect_url'];
        $callback_url = $_SESSION['callback_url'];

        unset($_SESSION['name']);
        unset($_SESSION['email']);
        unset($_SESSION['description']);
        unset($_SESSION['mobile']);
        unset($_SESSION['reference_1_label']);
        unset($_SESSION['reference_1']);
        unset($_SESSION['amount']);
        unset($_SESSION['redirect_url']);
        unset($_SESSION['callback_url']);
        unset($_SESSION['delivery']);

        $billplz = new Billplz_API(trim($api_key));
        
        $billplz
            ->setCollection($collection_id)
            ->setAmount($amount)
            ->setName($name)
            ->setDeliver($deliver)
            ->setMobile($mobile)
            ->setEmail($email)
            ->setDescription($description)
            ->setReference_1($reference_1)
            ->setReference_1_Label($reference_1_label)
            ->setPassbackURL($callback_url, $redirect_url)
            ->create_bill(true);
        header('Location: ' . $billplz->getURL());
    }

    public function return_ipn()
    {
        $this->load->model('checkout/order');

        $api_key = $this->config->get('payment_billplz_api_key_value');
        $x_signature = $this->config->get('payment_billplz_x_signature_value');
        $data = Billplz_API::getRedirectData($x_signature);
        
        /*
         * Create Billplz Class Instance.
         * Please refer below for the classes.
         */
        $billplz = new Billplz_API($api_key);
        $moreData = $billplz->check_bill($data['id']);
        
        $orderid = $moreData['reference_1'];
        $status = $moreData['paid'];
        $amount = $moreData['amount'];
        $paydate = $data['paid_at'];
        $order_info = $this->model_checkout_order->getOrder($orderid); // orderid

        if ($status) {
            $order_status_id = $this->config->get('payment_billplz_completed_status_id');
        } elseif (!$status) {
            $order_status_id = $this->config->get('payment_billplz_failed_status_id');
        }
        else {
        	$order_status_id = $this->config->get('payment_billplz_pending_status_id');
        }

        if (!$order_info['order_status_id'])
            $this->model_checkout_order->addOrderHistory($orderid, $order_status_id, "Redirect: " . $paydate . " URL:" . $moreData['url'], false);
        else {
            /*
             * Prevent same order status id from adding more than 1 update
             */
            if ($order_status_id != $order_info['order_status_id'])
                $this->model_checkout_order->addOrderHistory($orderid, $order_status_id, "Redirect: " . $paydate . " URL:" . $moreData['url'], false);
        }

        /*
         * Determine which page the buyer should go based on
         * payment status
         */

        if ($status)
            $goTo = $this->url->link('checkout/success');
        else
            $goTo = $this->url->link('checkout/checkout');

        if (!headers_sent()) {
            header('Location: ' . $goTo);
        } else {
            echo "If you are not redirected, please click <a href=" . '"' . $goTo . '"' . " target='_self'>Here</a><br />"
            . "<script>location.href = '" . $goTo . "'</script>";
        }

        exit();
    }
    /*     * ***************************************************
     * Callback with IPN(Instant Payment Notification)
     * **************************************************** */

    public function callback_ipn()
    {
        $this->load->model('checkout/order');
        
        $api_key = $this->config->get('payment_billplz_api_key_value');
        $x_signature = $this->config->get('payment_billplz_x_signature_value');

        $data = Billplz_API::getCallbackData($x_signature);
      
        /*
         * Create Billplz Class Instance.
         * Please refer below for the classes.
         */
        $billplz = new Billplz_API($api_key);
        $moreData = $billplz->check_bill($data['id']);
        
        $orderid = $moreData['reference_1'];
        $status = $moreData['paid'];
        $amount = $moreData['amount'];
        $paydate = $data['paid_at'];
        $order_info = $this->model_checkout_order->getOrder($orderid); // orderid

        if ($status) {
            $order_status_id = $this->config->get('payment_billplz_completed_status_id');
        } elseif (!$status) {
            $order_status_id = $this->config->get('payment_billplz_pending_status_id');
        }
        if (!$order_info['order_status_id']) {
            $this->model_checkout_order->addOrderHistory($orderid, $order_status_id, "Callback: " . $paydate . " URL:" . $moreData['url'], false);
        } else {
            /*
             * Prevent same order status id from adding more than 1 update
             */
            if ($order_status_id != $order_info['order_status_id'])
                $this->model_checkout_order->addOrderHistory($orderid, $order_status_id, "Callback: " . $paydate . " URL:" . $moreData['url'], false);
        }
        exit('Callback Success');
    }
}