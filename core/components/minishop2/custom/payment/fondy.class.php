<?php
define('FONDY_MERCHANT_ID', '1396424');
define('FONDY_SECRET_KEY', 'test');
define('FONDY_CURRENCY', 'RUB');
define('SUCCESS_URL', 'http://minishop/');









require_once dirname(dirname(dirname(__FILE__))) . '/custom/payment/fondyLib.php';

if (!class_exists('msPaymentInterface')) {
	require_once dirname(dirname(dirname(__FILE__))) . '/model/minishop2/mspaymenthandler.class.php';
}

class Fondy extends msPaymentHandler implements msPaymentInterface {
	public $config;
	public $modx;

	function __construct(xPDOObject $object, $config = array()) {
		$this->modx = & $object->xpdo;
    
		$siteUrl = $this->modx->getOption('site_url');
		$assetsUrl = $this->modx->getOption('minishop2.assets_url', $config, $this->modx->getOption('assets_url').'components/minishop2/');
		$paymentUrl = $siteUrl . substr($assetsUrl, 1) . 'payment/fondy.php';
    
		$this->config = array_merge(array(
			'paymentUrl' => $paymentUrl,
			'merchantId' => FONDY_MERCHANT_ID,
			'SecretId' => FONDY_SECRET_KEY,
			'currency'			=> FONDY_CURRENCY 
		), $config);
	}

	public function send(msOrder $order) {
		$id = $order->get('id');
		$sum = $order->get('cost');
		
		//echo 1;
		$ch = curl_init();
		$razd='|';
		$server_callback_url = $this->config['paymentUrl'];
		$response_url = SUCCESS_URL;
		$order_id = $id . FondyForm::ORDER_SEPARATOR . time();
		$order_desc = ("Оплата заказа - ".$id);
		$currency = $this->config['currency'];
		$amount = round($sum*100);
		$eamil = $_POST[email];
		$merchant_id = $this->config['merchantId'];
		$signature=sha1($this->config['SecretId'].$razd.$amount.$razd.$currency.$razd.$merchant_id.$razd.$order_desc.$razd.$order_id.$razd.$response_url.$razd.$eamil.$razd.$server_callback_url);  //   
		$data='server_callback_url='.$server_callback_url.'&response_url='.$response_url.'&order_id='.$order_id.'&order_desc='.$order_desc.'&currency='.$currency.'&amount='.$amount.'&signature='.$signature.'&merchant_id='.$merchant_id.'&sender_email='.$eamil;
		//Вместо этого адреса пишем нужный
		curl_setopt($ch, CURLOPT_URL, 'https://api.fondy.eu/api/checkout/url/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		$str=urldecode($result);
		parse_str($str,$mass);
		//echo $mass['checkout_url'];
		curl_close($ch);
		
		return $this->success('', array('redirect' => $mass['checkout_url']));
	}


	public function receive(msOrder $order) { 
		if (empty($_POST)) {
            $fap = json_decode(file_get_contents("php://input"));
            $_POST = array();
            foreach ($fap as $key => $val) {
                $_POST[$key] = $val;
            }
            $request = $_POST;
        }
	$isPaymentValid = FondyForm::isPaymentValid($_POST,FONDY_SECRET_KEY);
	if ($isPaymentValid == true)
    { 
		$id=explode('#', $_POST['order_id']);
      $miniShop2 = $this->modx->getService('miniShop2');
	  $miniShop2->changeOrderStatus($id[0], 2); // Setting status "paid"
	  //print_r ($_SERVER);die;
		
    } else {
      $this->paymentError($isPaymentValid, $params);
    }
	}

	public function paymentError($text, $request = array()) {
		$this->modx->log(modX::LOG_LEVEL_ERROR,'[miniShop2:fondy] ' . $text . ', request: '.print_r($request,1));
		header("HTTP/1.0 400 Bad Request");

		die('ERR: ' . $text);
	}

}