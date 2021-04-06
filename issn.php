<?php
	require_once('lib/lo.php');
	$lo = new LO();
	//f_login si RSA key vor fi setate in config
	$lo->f_login = 99999; 			// din interfata de comerciant
	$lo->setRSAKey('RSA KEY');		// din interfata de comerciant
	//end f_login si RSA key vor fi setate in config
	$lo->issn();
?>