<?php

define( 'API_USERNAME', 'oclistingapi' );
define( 'API_PASSWORD', 'oclistingapi1' );
define( 'API_SITEID', '1003806' );

define( 'API_USERNAME_UPSELL', 'oclistingapi' );
define( 'API_PASSWORD_UPSELL', 'oclistingapi1' );
define( 'API_SITEID_UPSELL', '1003806' );

$shipping_date = @Date('d M Y', strtotime("+2 days"));
$purchase_date = @Date('d M Y');

$_SESSION['inprod'] = 0;

$_SESSION['product_detail'] = array
(
    '1003806' => array(
	'1' => array('product_id' => '1','product_name' => 'Garcinia Cambogia','product_key' => '78954b34-1bc2-4ef6-9dba-e5ed36907e3d', 'price'=>'4.95'),
	'2' => array('product_id' => '2','product_name' => 'ProDerma Medics Phytoceramide Eye Serum','product_key' => '7c854abe-7574-4898-9a06-c2b538ca9e84', 'price'=>'4.96'),
    ),
);


$param= Array
(
    "domain"   => "",
    "product"  => "",
    "product2"  => "ProDerma Medics Phytoceramide Eye Serum",
    "product3" => "",

    "company2" => "",
    "company" => "",
    "address" => "",
    "phone"   => "1 ",
    "phone2" => "866-353-2387",
    "phone3" => "866-353-2053",
    "email"   => "support@premiumnutritionbrands.com",

    'p_product'  => '0.00',
    'p_subtotal' => '0.00',
    'p_ship'     => '4.95',
    'p_discount' => '0.00',
    'p_total'    => '4.95',
    'p_total2'    => '84.93',
    'p_total3'    => '54.94',
    'p_enroll'   => '89.97', //for 69.95
    "shipping"    => 4.95,
    "p_ship2"      => 4.96,
    "p_ship3"      => 4.97,
    "p_bill"	  => 89.97,
    "p_bill2"	  => 79.97,
    "p_bill3"	  => 49.97,

);
?>
