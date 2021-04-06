<?php
require_once('lib/lo.php');
$lo = new LO();
//f_login si RSA key vor fi setate in config
$lo->f_login = 99999;            // din interfata de comerciant
$lo->setRSAKey('RSA KEY');        // din interfata de comerciant
//end f_login si RSA key vor fi setate in config

$f_request = array();
$f_request['dulapid'] = (int)99999;
$f_request['email'] = strtolower('email@domain.com');

$response = $lo->SetPachetomatDefault($f_request);

if (isset($response->status) && ($response->status == 'error')) {
	echo json_encode($response);
} else {
	echo json_encode(array('status' => 'success', 'message' => $response));
}
?>
