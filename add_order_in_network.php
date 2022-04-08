<?php
require_once("LivrariOnline/LO.php");

use LivrariOnline\LO as LO;

$lo = new LO();
//f_login si RSA key vor fi setate in config
$lo->f_login = 99999;            // din interfata de comerciant
$lo->setRSAKey('RSA KEY');        // din interfata de comerciant

$f_request = array();
$f_request['dulapid'] = (int)99999;
$f_request['lungime'] = (int)1;
$f_request['latime'] = (int)1;
$f_request['inaltime'] = (int)1;
$f_request['greutate'] = (float)1.0;
$f_request['mpod_hash'] = 'hash';
$f_request['awb'] = 'AWB';
$f_request['orderid'] = 'Order ID';
$f_request['phone'] = 'Customer phone';
$f_request['email'] = 'Customer email';
$f_request['name'] = 'Customer name';

$response = $lo->AddOrderInNetwork($f_request);

if (isset($response->status) && ($response->status == 'error')) {
	echo json_encode($response);
} else {
	echo json_encode(array('status' => 'success', 'message' => $response));
}
?>
