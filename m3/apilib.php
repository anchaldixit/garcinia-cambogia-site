<?php 
session_start();
// include("geoip.inc");
ini_set('display_errors','1');
error_reporting(1);
date_default_timezone_set('America/Los_Angeles');
include("config.php"); 
class ResponseAPI
{
private $cc_number;
private $cc_cvv;
private $cc_expmonth;
private $cc_expyear;
private $cc_chname;
private $api_username;
private $api_password;
private $cc_type;
public $product_name;
public $product_key;
private $site_id;
private $billing_info;

public function setCCInfo($cc_info)
{
	if(isset($cc_info['cc_number'])){
		$this->cc_number = str_replace("-","",$cc_info['cc_number']);
		$this->cc_number = str_replace(" ","",$this->cc_number);
	}
	if(isset($cc_info['expmonth'])){
		$this->cc_expmonth = $cc_info['expmonth'];
	}
	if(isset($cc_info['expyear'])){
		$this->cc_expyear = $cc_info['expyear'];
	}
	if(isset($cc_info['CVV'])){
		$this->cc_cvv = $cc_info['CVV'];
	}
	if(isset($cc_info['firstName']) && isset($cc_info['firstName'])){
		$this->cc_chname = $cc_info['firstName'].' '.$cc_info['firstName'];
	}

	if(isset($this->cc_number))
	{
		if(substr($this->cc_number , 0,1)=="3"){
			$this->cc_type = "Amex";
		}
		elseif(substr($this->cc_number , 0,1)=="4"){
			$this->cc_type = "Visa";
		}
		elseif(substr($this->cc_number , 0,1)=="5"){
			$this->cc_type = "MasterCard";
		}
		elseif(substr($this->cc_number , 0,1)=="6"){
			$this->cc_type = "Discover";
		}
		else{
			$this->cc_type = "visa";
		}
	}
	//set the cc in session so that it can be used again for last upsell API call
	$_SESSION['cc_info']= $cc_info;
}

function getSiteInfo(){

	$this->api_username = API_USERNAME;
	$this->api_password = API_PASSWORD;
	$this->site_id = API_SITEID;
	$_SESSION['siteid'] = API_SITEID;
	$siteid = API_SITEID;

    // upsell
    if( $_REQUEST['process'] == 'upsell' )
    {
        $this->api_username = API_USERNAME_UPSELL;
        $this->api_password = API_PASSWORD_UPSELL;
        $_SESSION['siteid'] = API_SITEID_UPSELL;
        $siteid = API_SITEID_UPSELL;
    }

	$productData = $_SESSION['product_detail'][$siteid];
	$product_id = 1;	
//var_dump($product_id);
//die();
	$this->product_key   = (isset($productData[$product_id]['product_key']))?trim($productData[$product_id]['product_key']):'';
	$this->product_name  = (isset($productData[$product_id]['product_name']))?trim($productData[$product_id]['product_name']):'';

	if($_SESSION['inprod'] != '1')
		$_SESSION['cart']['product_name'] = $this->product_name;

	$this->setCustomerInfo();
}

private function setCustomerInfo(){
	foreach($_REQUEST as $key=>$value)
	{
		if($value)
		        $_SESSION['cart'][$key] = $value;
	}
	if($_REQUEST['tshipping'] && $_REQUEST['tshipping'] != 'S')
	{
		foreach($_REQUEST as $key=>$value)
		{
			if(substr($key,0,8) == 'shipping')
			{
				$_SESSION['cart'][str_replace('shipping','billing',$key)] = $value;
			}
		}

	}
	if(isset($_SESSION['affiliate'])){
	    $_SESSION['cart']['affiliate'] = $_SESSION['affiliate'] ;
	}
	if(isset($_SESSION['sid'])){
	    $_SESSION['cart']['affiliate_sub'] = $_SESSION['sid'] ;
	}
		

}
public function create_template( $type = null )
{
	$this->setCustomerInfo();
	if(!file_exists('orders/'.session_id().'.e'))
	{
		$fe = fopen ('orders/'.session_id().'.e','w');
		fwrite($fe,$_SESSION['cart']['billingEmail']);
		fclose($fe);
	}
	if(file_exists('orders/'.session_id().'.d'))
		$data = file_get_contents('orders/'.session_id().'.d');
	else
		$data = file_get_contents('source.html');
	$data = str_replace("##BILLINGID##", $_SESSION["customer_id"], $data);
	$data = str_replace("##ORDER_DATE##", date("Y-m-d"), $data);
	$data = str_replace("##SHIPPING_ADD1##", $_SESSION['cart']['billingAddress'], $data);
	$data = str_replace("##SHIPPING_ADD2##", $_SESSION['cart']['billingAddress2'], $data);
	$data = str_replace("##FNAME##", $_SESSION['cart']['billingFirstName'], $data);                                                 
	$data = str_replace("##LNAME##", $_SESSION['cart']['billingLastName'], $data);
	$data = str_replace("##SHIPPING_CITY##", $_SESSION['cart']['billingCity'], $data);
	$data = str_replace("##SHIPPING_STATE##", $_SESSION['cart']['billingState'], $data);
	$data = str_replace("##SHIPPING_ZIP##", $_SESSION['cart']['billingZipPostal'], $data);
	if($type == '2')
	{
		$data = str_replace("##ADD1##", $_SESSION['cart']['billingAddress'], $data);
		$data = str_replace("##ADD2##", $_SESSION['cart']['billingAddress2'], $data);
		$data = str_replace("##CITY##", $_SESSION['cart']['billingCity'], $data);
		$data = str_replace("##STATE##", $_SESSION['cart']['billingState'], $data);
		$data = str_replace("##ZIP##", $_SESSION['cart']['billingZipPostal'], $data);

	}
	if($type == '3')
	{
		if(!$_SESSION['shiptotal']) $_SESSION['shiptotal'] = 0;
		$data = str_replace("##PRODUCT_AMOUNT##", show_currency($_SESSION['ordertotal']), $data);
		$data = str_replace("##SH_AMOUNT##", show_currency($_SESSION['shiptotal']), $data);
		$data = str_replace("##TOTAL_AMOUNT##", show_currency($_SESSION['ordertotal']+$_SESSION['shiptotal']), $data);
	}
	if($type == '4')
	{
		$data2 = file_get_contents('orders/'.session_id().'.od');
		$_SESSION['orderdata'] = '<table>'.$data2.'</table>';
		$data = str_replace("##ORDER_REVIEW_TABLE##", $_SESSION['orderdata'], $data);
	}
	$success = file_put_contents('orders/'.session_id().'.d', $data);	
}
function create_ot($p)
{
$f = fopen ('orders/'.session_id().'.od','a+');
$f2 = fopen ('orders/'.session_id().'.ot','a+');
if($p == '1')
{
	fwrite($f, '<tr>
	       <td>
	       <h5>'.$_SESSION['product_detail']['1004175'][$_SESSION['p1']]['product_name'].'.</h5>
	       </td>
	       <td class="text-right" id="mainprice"><h5>A$ '.show_currency($_SESSION['product_detail']['1004175'][$_SESSION['p1']]['price']).'</h5></td>
	      </tr>'."\n");
	$_SESSION['ordertotal'] = $_SESSION['product_detail']['1004175'][$_SESSION['p1']]['price'];
	fwrite($f2,'"'.$_SESSION['product_detail']['1004175'][$_SESSION['p1']]['price'].'",');
}
if($p == '2')
	{
	fwrite($f, '<tr>
      <tr>
       <td>
       <h5>'.$_SESSION['product_detail']['1004175'][$_SESSION['p2']]['product_name'].'.</h5>
       </td>
       <td class="text-right" id="mainprice"><h5>A$ '.show_currency($_SESSION['product_detail']['1004175'][$_SESSION['p2']]['price']).'</h5></td>
      </tr>'."\n");
	$_SESSION['ordertotal'] = $_SESSION['product_detail']['1004175'][$_SESSION['p2']]['price'];
	fwrite($f2,'"'.$_SESSION['product_detail']['1004175'][$_SESSION['p2']]['price'].'",');
	}

if($p == '3')
	{
	fwrite($f, '<tr>
      <tr>
       <td>
      <h5>'.$_SESSION['product_detail']['1004175'][$_SESSION['u1']]['product_name'].'.</h5>
       </td>
       <td class="text-right" id="upsell1price"><h5>A$ '.show_currency($_SESSION['product_detail']['1004175'][$_SESSION['u1']]['price']).'</h5></td>
      </tr>'."\n");
	$_SESSION['ordertotal'] += $_SESSION['product_detail']['1004175'][$_SESSION['u1']]['price'];
	fwrite($f2,'"'.$_SESSION['product_detail']['1004175'][$_SESSION['u1']]['price'].'",');
	}      

if($p == '4')
	{
	fwrite($f, '<tr>
      <tr>
       <td>
	 <h5>'.$_SESSION['product_detail']['1004175'][$_SESSION['u2']]['product_name'].'.</h5>
       </td>
       <td class="text-right" id="upsell2price"><h5>A$ '.show_currency($_SESSION['product_detail']['1004175'][$_SESSION['u2']]['price']).'</h5></td>
      </tr>'."\n");
	$_SESSION['ordertotal'] += $_SESSION['product_detail']['1004175'][$_SESSION['u2']]['price'];
	fwrite($f2,'"'.$_SESSION['product_detail']['1004175'][$_SESSION['u2']]['price'].'",');
	}
if($p == '5')
	{
	fwrite($f, '<tr>      
      <tr>                                   
       <td>
	 <h5>'.$_SESSION['product_detail']['1004175'][$_SESSION['u3']]['product_name'].'.</h5>
       </td>
       <td class="text-right" id="upsell3price"><h5>A$ '.show_currency($_SESSION['product_detail']['1004175'][$_SESSION['u3']]['price']).'</h5></td>
      </tr>'."\n");
	$_SESSION['ordertotal'] += $_SESSION['product_detail']['1004175'][$_SESSION['u3']]['price'];
	fwrite($f2,'"'.$_SESSION['product_detail']['1004175'][$_SESSION['u3']]['price'].'",');
	}
fclose($f);
fclose($f2);
}
public function setProcessorId($pid)
{
    if($pid)
    {
	$this->processor_id = $pid;
        return $this;
    }
    else
    {
	$this->processor_id = '';
	return true;
    }
}
public function runTransaction($ordertype = 'signup'){

//var_dump($_SESSION);
//	if(!empty($_SESSION['cart']['shippingFirstName']) && !empty($_SESSION['cart']['shippingLastName'])){
//		$this->cc_chname = $_SESSION['cart']['shippingFirstName'].' '.$_SESSION['cart']['shippingLastName'];
//	}
//var_dump(!empty($_SESSION['cart']['billingFirstName']));
//	if(!empty($_SESSION['cart']['billingFirstName']) && !empty($_SESSION['cart']['billingLastName'])){
//		$this->cc_chname = $_SESSION['cart']['billingFirstName'].' '.$_SESSION['cart']['billingLastName'];
//	}
//var_dump($this->cc_chname);
//	if($this->cc_chname)
//	    $nameoncard = $this->cc_chname;
//	else
//	    $nameoncard = $_SESSION['cart']['billingLastName']." ".$_SESSION['cart']['billingFirstName'];
///var_dump($this->cc_chname);
//var_dump($_REQUEST);
//die();
//
//    if( $_REQUEST['process'] == 'upsell' )
//    {
//	$ordertype = "upsell";
//    }
//    else
//	$ordertype = "signup";
	
	//if($_POST['sship'] != 'on')
	//	$this->setCustomerInfo();
	$transaction_xml = '<?xml version="1.0" encoding="utf-8"?>
	<run_transaction>
	<authorization>
	<username>'.$this->api_username.'</username>
	<password>'.$this->api_password.'</password>
	</authorization>
	<transactions>
	<transaction>
	<customerid>'.$_SESSION["customer_id"].'</customerid>
	<ordertype>'.$ordertype.'</ordertype>
	<processor_id></processor_id>
	<ipaddress>'.$_SERVER['REMOTE_ADDR'].'</ipaddress>
	<cardtype>'.$this->cc_type.'</cardtype>
	<ccnumber>'.$this->cc_number.'</ccnumber>
	<name_on_card>'.$this->cc_chname.'</name_on_card>
	<cvv>'.$this->cc_cvv.'</cvv>
	<expmonth>'.$this->cc_expmonth.'</expmonth>
	<expyear>'.$this->cc_expyear.'</expyear>
	<address>
	<firstname>'.$this->billing_info['billingFirstName'].'</firstname>
	<lastname>'.$this->billing_info['billingLastName'].'</lastname>
	<address1>'.$this->billing_info['billingAddress'].'</address1>
	<address2></address2>
	<city>'.$this->billing_info['billingCity'].'</city>
	<state>'.$this->billing_info['billingState'].'</state>
	<zipcode>'.$this->billing_info['billingZipPostal'].'</zipcode>
	<country_iso>'.$this->billing_info['billingCountry'].'</country_iso>
	</address>
	<product_groups>
	<product_group>
	<product_group_key>'.$this->product_key.'</product_group_key>
	</product_group>
	</product_groups>
	</transaction>
	</transactions>
	</run_transaction>';
	//echo htmlentities($transaction_xml);
//var_dump($_POST);	
//die();
file_put_contents('mydata.xml', "\r\nREQUEST\r\n".$transaction_xml, FILE_APPEND);

	$ch = curl_init();
	$headers = array();
	$headers[] = 'Accept: application/xml';
	$headers[] = 'Content-Type: application/xml; charset=UTF-8';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt ($ch, CURLOPT_URL, 'https://api.responsecrm.com/transaction');
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $transaction_xml);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec ($ch);
	file_put_contents('mydata.xml', "\r\n RESPONSE\r\n".$result, FILE_APPEND);
	$result_array = simplexml_load_string($result);
	//print_r($result_array);
	//exit;
	$_SESSION['transaction_result'] = (string)$result_array->run_transaction_results->run_transaction_result->response_text;
	$_SESSION['transaction_id'] = (string)$result_array->run_transaction_results->run_transaction_result->transactionid;
	$transaction_code = (string)$result_array->run_transaction_results->run_transaction_result->response;
	$_SESSION['transaction_code'] = $transaction_code;
	curl_close($ch);
	if($transaction_code==1){
		$_SESSION['transaction_id'] = $transaction_code;
		
	}else{
		unset($_SESSION['transaction_id']);
	}
	$this->create_template(2);	
//	setcookie("transaction_id", $transaction_id, time()+6000);
	//setcookie("transaction_result", $transaction_result, time()+600);
	return $transaction_code;
}

public function getCustomerId($customer_info){
//var_dump($_REQUEST);
//var_dump($_SESSION);
	if(1)
	{
	$insertcustom_xml = '<?xml version="1.0" encoding="utf-8"?>
	<insert_customer>
	<authorization>
	<username>'.$this->api_username.'</username>
	<password>'.$this->api_password.'</password>
	</authorization>
	<customers>
	<customer>
	<siteid>'.$this->site_id.'</siteid>
	<phone>'.$customer_info['phone'].'</phone>
	<email>'.$customer_info['email'].'</email>
	<aff_id>'.$_SESSION['affiliate'].'</aff_id>
	<sub_id>'.$_SESSION['affiliate_sub'].'</sub_id>
	<ipaddress>'.$_SERVER['REMOTE_ADDR'].'</ipaddress>
	<address>
	<firstname>'.$customer_info['firstName'].'</firstname>
	<lastname>'.$customer_info['lastName'].'</lastname>
	<address1>'.$customer_info['shippingAddress1'].'</address1>
	<address2></address2>
	<city>'.$customer_info['shippingCity'].'</city>
	<state>'.$customer_info['shippingState'].'</state>
	<zipcode>'.$customer_info['shippingZip'].'</zipcode>
	<country_iso>'.$customer_info['shippingCountry'].'</country_iso>
	</address>
	</customer>
	</customers>
	</insert_customer>';

	}
	else
	{
	$insertcustom_xml = '<?xml version="1.0" encoding="utf-8"?>
	<insert_customer>
	<authorization>
	<username>'.$this->api_username.'</username>
	<password>'.$this->api_password.'</password>
	</authorization>
	<customers>
	<customer>
	<siteid>'.$_SESSION['siteid'].'</siteid>
	<phone>'.$_SESSION['cart']['billingPhone'].'</phone>
	<email>'.$_SESSION['cart']['billingEmail'].'</email>
	<aff_id>'.$_SESSION['cart']['affiliate'].'</aff_id>
	<sub_id>'.$_SESSION['cart']['affiliate_sub'].'</sub_id>
	<ipaddress>'.$_SERVER['REMOTE_ADDR'].'</ipaddress>
	<address>
	<firstname>'.$_SESSION['cart']['billingFirstName'].'</firstname>
	<lastname>'.$_SESSION['cart']['billingLastName'].'</lastname>
	<address1>'.$_SESSION['cart']['billingAddress'].'</address1>
	<address2>'.$_SESSION['cart']['billingAddress2'].'</address2>
	<city>'.$_SESSION['cart']['billingCity'].'</city>
	<state>'.$_SESSION['cart']['billingState'].'</state>
	<zipcode>'.$_SESSION['cart']['billingZipPostal'].'</zipcode>
	<country_iso>'.$_SESSION['cart']['billingCountry'].'</country_iso>
	</address>
	</customer>
	</customers>
	</insert_customer>';
	}
	//echo htmlentities($insertcustom_xml);
//die();
	$headers = array();
	$headers[] = 'Accept: application/xml';
	$headers[] = 'Content-Type: application/xml; charset=UTF-8';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt ($ch, CURLOPT_URL, 'https://api.responsecrm.com/customer');
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $insertcustom_xml);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec ($ch);
//var_dump($_SESSION);
//var_dump($insertcustom_xml);
//  die();
	$xml_result = simplexml_load_string($result);
	$customer_id = (string)$xml_result->insert_customer_results->insert_customer_result->customerid;
	$customer_result = (string)$xml_result->insert_customer_results->insert_customer_result->response;
	curl_close($ch);
//print_r($xml_result);
//	var_dump($customer_id);
//die();
	$_SESSION['customer_id']= $customer_id;
	$_SESSION['customer_result'] = $customer_result;
//	setcookie("customer_id", $customer_id, time()+6000);
//	setcookie("customer_result", $customer_result, time()+6000);
	//$this->create_template();
	return $customer_id;

}

function get_url($type){
	$url_param = '';
	if($type == '1'){
		$url_param = '?billingFirstName='.$_SESSION['cart']['billingFirstName'].
		'&billingLastName='.$_SESSION['cart']['billingLastName'].
		'&billingAddress='.$_SESSION['cart']['billingAddress'].
		'&billingCity='.$_SESSION['cart']['billingCity'].
		'&billingState='.$_SESSION['cart']['billingState'].
		'&billingZipPostal='.$_SESSION['cart']['billingZipPostal'].
		'&billingPhone='.$_SESSION['cart']['billingPhone'].
		'&billingEmail='.$_SESSION['cart']['billingEmail'].
		'&aff_id='.$_SESSION['cart']['affiliate'].
		'&subid='.$_SESSION['cart']['affiliate_sub'].
		'&subid2='.$_SESSION['cart']['affiliate_sub2'];
	}else if($type == '2'){
		$url_param = '?aff_id='.$_SESSION['cart']['affiliate'].
		'&subid='.$_SESSION['cart']['affiliate_sub'].
		'&subid2='.$_SESSION['cart']['affiliate_sub2'];
	}
	return $url_param;
}

function convert_currency($price,$convertfrom,$convertto){

	$output=0;
				
	$symbol = array(
		"EUR"    =>	"&#8364;",
		"USD"	 =>	"&#36;",
		"CAD"	 =>	"&#36;",
		"VEF"	 =>	"&#66;&#115;",
		"UYU"	 =>	"&#36;&#85;",
		"PEN"	 =>	"&#83;&#47;&#46;",
		"PYG"	 =>	"&#71;&#115;",
		"PAB"	 =>	"&#66;&#47;&#46;",
		"NIO"	 =>	"&#66;&#36;",
		"MXN"	 =>	"&#36;",
		"HNL"	 =>	"&#76;",
		"GTQ"	 =>	"&#81;",
		"SVC"	 =>	"&#83;&#47;&#46;",
		"ECS"	 =>	"&#36;",
		"DOP"	 =>	"&#82;&#68;&#36;",
		"CRC"	 =>	"&#8353;",
		"COP"	 =>	"&#36;",
		"CLP"	 =>	"&#36;",
		"BOB"	 =>	"&#36;b",
		"ARS"	 =>	"&#36;",
		"GBP"    => "&#163;",
		"AUD"    => "",
		"SGD"    => "SG$",
		"HKD"    => "HK$",
		"MYR"    => "RM",
		"INR"    => "Rs.",
		"PHP"    => "P",
		"NZD"    => "",
		"USD"    => "&#36;"
	);

	foreach ($symbol as $k => $v){
		if($k == $convertto){
			$currency_symbol = $v;
		}
	}

	$amount           = $price;
	$from_Currency    = $convertfrom;
	$to_Currency      = $convertto;
/*
	$amount           = urlencode($amount);
	$from_Currency    = urlencode($from_Currency);
	$to_Currency      = urlencode($to_Currency);
	$get		      = file_get_contents("https://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency");
	$get              = explode("<span class=bld>",$get);
	if(isset($get[1])){
		$get			  = explode("</span>",$get[1]);  
		$converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
		$output           = round($converted_amount, 2);
	}
*/	
	$output = $this->get_ex_rate($convertfrom)*$price;
	return array($currency_symbol,$output);
	
}

function get_ex_rate($tc = null)
{
if($x==1)
{
    $price = 1;
    $ausarray   = array('13');
    $zlarray    = array('153');
    $caarray    = array('CA');
    $ukarray    = array('225');
    $euarray    = array('DE','FR','IT','206','199','193','192','21','14','33','56','57','58','70','75','76','83','87','100','104','106','118','123','124','150','153','171','172','176',
				//'211','102','103','196','191','129','169','99','224'

			);
//var_dump($_SESSION['cart']['billingCountry']);	    
    if(in_array($_SESSION['cart']['billingCountry'],$ukarray))
	$toCur = 'GBP';
    elseif(in_array($_SESSION['cart']['billingCountry'],$zlarray))
	$toCur = 'NZD';
    elseif(in_array($_SESSION['cart']['billingCountry'],$ausarray))
	$toCur = 'AUD';
    elseif(in_array($_SESSION['cart']['billingCountry'],$caarray))
	$toCur = 'CAD';
    elseif($_SESSION['cart']['billingCountry'] == '99')
	$toCur = 'SGD';
    elseif($_SESSION['cart']['billingCountry'] == '191')
	$toCur = 'HKD';
    elseif($_SESSION['cart']['billingCountry'] == '169')
	$toCur = 'PHP';
    elseif($_SESSION['cart']['billingCountry'] == '129')
	$toCur = 'MYR';
    elseif($_SESSION['cart']['billingCountry'] == '102')
	$toCur = 'INR';
    elseif(in_array($_SESSION['cart']['billingCountry'],$euarray))    
	$toCur = 'EUR';
    else
        $toCur = 'USD';
	if($tc)
		$toCur = $tc;
	$cprice = $this->convert_currency($price,'USD',$toCur);
	$rate = $cprice[1] / $price;
	return $rate;
}
else
{
$f = fopen('rates.dat','r');
//$data = fread($f,1000);
if ($f) {
    while (($buffer = fgets($f, 4096)) !== false) {
	if(substr($buffer,0,3) == $tc)
	{
		$rate = substr($buffer,strpos($data,$tc)+4);	
	        return $rate;
	}
    }
}
//var_dump($data);
//$rate = substr($data,strpos($data,$tc)+4,);
//var_dump($rate);
//die();
fclose($f);
}
}

function show_currency($price,$round=false)
{
    $ausarray   = array('13');
    $zlarray    = array('153');
    $caarray    = array('CA');
    $ukarray    = array('225');
    $euarray    = array('DE','FR','IT','206','199','193','192','21','14','33','56','57','58','70','75','76','83','87','100','104','106','118','123','124','150','153','171','172','176',
				//'211','102','103','196','191','129','169','99','224'

			);
//var_dump($_SESSION['cart']['billingCountry']);	    
    if(in_array($_SESSION['cart']['billingCountry'],$ukarray))
	$toCur = 'GBP';
    elseif(in_array($_SESSION['cart']['billingCountry'],$zlarray))
	$toCur = 'NZD';
    elseif(in_array($_SESSION['cart']['billingCountry'],$ausarray))
	$toCur = 'AUD';
    elseif(in_array($_SESSION['cart']['billingCountry'],$caarray))
	$toCur = 'CAD';
    elseif($_SESSION['cart']['billingCountry'] == '99')
	$toCur = 'SGD';
    elseif($_SESSION['cart']['billingCountry'] == '191')
	$toCur = 'HKD';
    elseif($_SESSION['cart']['billingCountry'] == '169')
	$toCur = 'PHP';
    elseif($_SESSION['cart']['billingCountry'] == '129')
	$toCur = 'MYR';
    elseif($_SESSION['cart']['billingCountry'] == '102')
	$toCur = 'INR';
    elseif(in_array($_SESSION['cart']['billingCountry'],$euarray))    
	$toCur = 'EUR';
    else
        $toCur = 'USD';
//var_dump($toCur);
	$cprice = $this->convert_currency($price,'USD',$toCur);
	if($cprice[1] == '0') $cprice[1] = '0.00';
    if($toCur == 'NZD' || $toCur == 'AUD') $sshowCur = $toCur;
    if($toCur == 'CAD') $pshowCur = 'C';
    if($round == true) $cprice[1] = round($cprice[1]);
    return $pshowCur." ".$cprice[0].round($this->get_ex_rate($toCur)*$cprice[1],2)." ".$sshowCur;
}


function getCountry()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $gi = geoip_open("GeoIP.dat", GEOIP_STANDARD);
    return geoip_country_code_by_addr($gi, $ip);
}
function setBillingInfo($info){
	$this->billing_info = $info;
	//set the cc in session so that it can be used again for last upsell API call
	$_SESSION['billing']=$info;
	
}
function setBilling4rmLastCheckoutAction(){
	$this->billing_info = $_SESSION['billing'];
}
function setCCInfo4rmLastCheckoutAction(){
	$this->setCCInfo($_SESSION['cc_info']);
}


}
?>
