<?php
header('Content-Type: application/json');

require('apilib.php');

$_ResponseAPI = new ResponseAPI();

if($_GET['method']=='new_prospect'){
    
    //API call
    
    // redirect call
    
    //$obj = new stdClass();
    //$obj->redirect = '/of0211m15/m3/discount.html';
    //var_dump($_REQUEST);

    	$_ResponseAPI->getSiteInfo();
		$customer_info = array(
							   'firstName'=>$_POST['firstName'],
							   'lastName'=>$_POST['lastName'],
							   'shippingAddress1'=>$_POST['shippingAddress1'],
							   'shippingCity'=>$_POST['shippingCity'],
							   'shippingZip'=>$_POST['shippingZip'],
							   'shippingCountry'=>$_POST['shippingCountry'],
							   'shippingState'=>$_POST['shippingState'],
							   'phone'=>$_POST['phone'],
							   'email'=>$_POST['email']
							   );
    $customer_id = $_ResponseAPI->getCustomerId($customer_info);
	
    //echo 'session id';
    //var_dump($_SESSION);
	//session_destroy();
    if(empty($customer_id)){
	   
	   	echo json_encode(array(
                           'context'=>array('errorFound'=>1, 'errorMessage'=>'Oops, there is some problem please try again')));
	exit();
	   
	}else{
    
    echo json_encode(array('redirect'=>'discount.html',
                           'context'=>array('errorFound'=>0)));
	}
    
}elseif($_GET['method']=='downsell1'){
	   
	   $_ResponseAPI->getSiteInfo();
	   
	   $cc_info=array(
			  'cc_number' => $_POST['creditCardNumber'],
			  'CVV' => $_POST['CVV'],
			  'expmonth' => $_POST['expmonth'],
			  'expyear' => $_POST['expyear'],
			  'firstName' => $_POST['billingFirstName'],
			  'lastName' => $_POST['billingLastName'],
			  
	   );
	   
	   $_ResponseAPI->setCCInfo($cc_info);
	   
	   		$billing_info = array(
							   'billingFirstName'=>$_POST['billingFirstName'],
							   'billingLastName'=>$_POST['billingLastName'],
							   'billingAddress1'=>$_POST['billingAddress1'],
							   'billingCity'=>$_POST['billingCity'],
							   'billingZip'=>$_POST['billingZip'],
							   'billingCountry'=>$_POST['billingCountry'],
							   'billingState'=>$_POST['billingState'],
							   );
	   
	   
	   $_ResponseAPI->setBillingInfo($billing_info);
	   
	   if($_SESSION['customer_id']){
			  $response = $_ResponseAPI->runTransaction();
			  
			  if($response==1){
					 echo json_encode(array('redirect'=>'summary.html',
                           'context'=>array('errorFound'=>0)));
					 exit();
			  
			  }else{
					 echo json_encode(array(
                           'context'=>array('errorFound'=>1, 'errorMessage'=>'Oops, there is some problem please try again')));
	exit();
					 
			  }
			         
			  //if $response = 100 then all good
	   }else{
			  //Return error
			  	echo json_encode(array(
                           'context'=>array('errorFound'=>1, 'errorMessage'=>'Oops, Seems like 1 step is not completed')));
	exit();
			  
	   }

}elseif($_GET['method']=='upsell'){
	   //print_r($_SESSION);
	   if(!empty($_SESSION['transaction_id'])){//transaction id is set, customer opted 4 uosell
			 
			 $_ResponseAPI->getSiteInfo();
			  $_ResponseAPI->setBilling4rmLastCheckoutAction();
			  $_ResponseAPI->setCCInfo4rmLastCheckoutAction();
			  
			  $response = $_ResponseAPI->runTransaction('upsell');
			  var_dump($response);
			  if($response==1){
					 //all good
					 echo json_encode(array('redirect'=>'thankyou.php',
                           'context'=>array('errorFound'=>0))); 
	exit();
			  }else{
					 //something is wrong, do i need to send old transaction id?
					 echo json_encode(array(
                           'context'=>array('errorFound'=>1, 'errorMessage'=>'Oops, there is some problem please try again')));
	exit();
			  }
			  //distroy session in thanku page for Order complete

	   }else{
			  echo json_encode(array(
                           'context'=>array('errorFound'=>1, 'errorMessage'=>'Oops, Seems like few steps are skipped. Please go to step one')));
	exit();
			  
	   }
    
       
}elseif($_GET['method']=='noupsell'){
	   
	   //distroy session in thanku page for Order complete
	   
	   //nothing to say, just display the thanku page
       echo json_encode(array('redirect'=>'thankyou.php',
                           'context'=>array('errorFound'=>0))); 
}elseif($_GET['validate']=='summary_page'){
	   
	   if(empty($_SESSION['transaction_id'])){
			  //error
			  echo json_encode(array('error'=>true));
			  
	   }else{
			  echo json_encode(array('error'=>false));
			  //all good
	   }
}elseif($_GET['validate']=='discount_page'){
	   
	   if(empty($_SESSION['customer_id'])){
			  //error
			  echo json_encode(array('error'=>true));
			  
	   }else{
			  echo json_encode(array('error'=>false));
			  //all good
	   }
}else{
	   
	   echo json_encode(array('redirect'=>'index.html',
                           'context'=>array('errorFound'=>0))); 
	exit();
	   
}