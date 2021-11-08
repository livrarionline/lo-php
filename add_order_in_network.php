<?php
require_once("LivrariOnline/LO.php");

use LivrariOnline\LO as LO;

$lo = new LO();
//f_login si RSA key vor fi setate in config
$lo->f_login = 99999;            // din interfata de comerciant
$lo->setRSAKey('RSA KEY');        // din interfata de comerciant

$f_request = array();
$f_request['dulapid'] = (int)99999;
$f_request['tip_celula'] = 'S';
$f_request['orderid'] = 'order ID';
$f_request['phone'] = '0740000000';
$f_request['email'] = 'email@domain.com';
$f_request['name'] = 'Customer name';

$response = $lo->AddOrderInNetwork($f_request);

if (isset($response->status) && ($response->status == 'error')) {
	echo json_encode($response);
} else {
	echo json_encode(array('status' => 'success', 'message' => $response));
}
?>
