<?php
require_once("LivrariOnline/LO.php");

use LivrariOnline\LO as LO;

$lo = new LO();

//f_login si RSA key vor fi setate in config
$lo->f_login = 99999;            // din interfata de comerciant
$lo->setRSAKey('RSA KEY');        // din interfata de comerciant
//end f_login si RSA key vor fi setate in config

$f_request_awb = array();
$f_request_awb['f_shipping_company_id'] = (int)99999;                // int 								obligatoriu
$f_request_awb['serviciuid'] = (int)99999;                // int								Obligatoriu
$f_request_awb['request_data_ridicare'] = '2013-11-28';                // date Y-m-d						optional
$f_request_awb['request_ora_ridicare'] = '14:00:00';                // time without time zone H:i:s 	optional
$f_request_awb['request_ora_ridicare_end'] = '14:00:00';                // time without time zone 			optional
$f_request_awb['request_ora_livrare_sambata'] = '14:00:00';                // time without time zone 			optional
$f_request_awb['request_ora_livrare_end_sambata'] = '14:00:00';                // time without time zone 			optional
$f_request_awb['request_ora_livrare'] = '14:00:00';                // time without time zone 			optional
$f_request_awb['request_ora_livrare_end'] = '14:00:00';                // time without time zone 			optional
$f_request_awb['descriere_livrare'] = 'Livrare de test';        // varchar 250						optional
$f_request_awb['referinta_expeditor'] = 'Referinta de test';        // varchar 255						Obligatoriu
$f_request_awb['valoare_declarata'] = '150.99';                    // decimal 10,2 					Obligatoriu
$f_request_awb['ramburs'] = '140.99';                    // decimal 10,2 					Obligatoriu
$f_request_awb['asigurare_la_valoarea_declarata'] = false;                    // boolean							Obligatoriu
$f_request_awb['retur_documente'] = false;                    // boolean 							optional
$f_request_awb['retur_documente_bancare'] = false;                    // boolean							optional
$f_request_awb['confirmare_livrare'] = false;                    // boolean							optional
$f_request_awb['livrare_sambata'] = false;                    // boolean							optional
$f_request_awb['currency'] = 'RON';                    // char 							Obligatoriu cand "valoare_declarata" > 0
$f_request_awb['currency_ramburs'] = 'RON';                    // char 							Obligatoriu cand "ramburs" > 0
$f_request_awb['notificare_email'] = true;                        // boolean							optional
$f_request_awb['notificare_sms'] = true;                        // boolean							optional
$f_request_awb['cine_plateste'] = (int)0;                    // 0 - merchant,2 - destinatar,1 - expeditor    Obligatoriu
$f_request_awb['request_mpod'] = false;                    // boolean	 						optional
$f_request_awb['plateste_rambursul_la_comerciant'] = (int)2;                    // 1 - cash (ramburs), 2 - banca    default 2
$f_request_awb['verificare_colet'] = false;                    // boolean							optional

$colete = array();

$colete[] = array(
	'greutate' => (float)1.01,                                        // decimal 10,2 kg
	'lungime'  => (int)10,                                            // integer      cm
	'latime'   => (int)10,                                            // integer      cm
	'inaltime' => (int)10,                                            // integer      cm
	'continut' => (int)1,                                                // int      1;"Acte" 2;"Tipizate" 3;"Fragile" 4;"Generale"
	'tipcolet' => (int)1,                                                // int		1;"Plic"2;"Colet"3;"Palet"
);
$colete[] = array(
	'greutate' => (float)10.01,                                        // decimal 10,2 kg
	'lungime'  => (int)10,                                            // integer      cm
	'latime'   => (int)10,                                            // integer      cm
	'inaltime' => (int)10,                                            // integer      cm
	'continut' => (int)1,                                                // int      1;"Acte" 2;"Tipizate" 3;"Fragile" 4;"Generale"
	'tipcolet' => (int)1,                                                // int		1;"Plic"2;"Colet"3;"Palet"
);

$f_request_awb['colete'] = $colete;

$f_request_awb['destinatar'] = array(
	'first_name'   => 'test',                                            //Obligatoriu
	'last_name'    => 'test',                                                //Obligatoriu
	'email'        => 'test@test.com',                                //Obligatoriu
	'phone'        => '',                                                    //phone sau mobile Obligatoriu
	'mobile'       => '0741055805',
	'lang'         => 'ro',                                                //Obligatoriu ro/en
	'company_name' => 'Companie de test',                                    //optional
	'j'            => 'J35/38/2013',                                        //optional
	'bank_account' => 'RO89RNCB1546844678',                                //optional
	'bank_name'    => 'BCR',                                                //optional
	'cui'          => '1234567'                                            //optional
);

$f_request_awb['shipTOaddress'] = array(
	//Obligatoriu
	'address1'   => 'Str. 2 Cocosi 21',
	'address2'   => '',
	'city'       => 'Bucuresti',
	'state'      => 'Bucuresti',
	'zip'        => '800000',
	'country'    => 'Romania',
	'phone'      => '0740000000',
	'observatii' => 'Observatii de test',
);

$f_request_awb['shipFROMaddress'] = array(
	'email'        => 'test@test.com',                //{Email-ul de la Meniul "Settings" -> "Adrese de ridicare"} -- Obligatoriu
	'first_name'   => 'AAA',
	'last_name'    => 'BBB',
	'mobile'       => '0741000000',
	'main_address' => 'Adresa principala',
	'city'         => 'Bucuresti',
	'state'        => 'Bucuresti',
	'zip'          => '3456789',
	'country'      => 'Romania',
	'phone'        => '021447414',
	'instructiuni' => 'instructiuni',
);

//generare AWB
$response_awb = $lo->GenerateAwb($f_request_awb);

//raspuns generare AWB
if ($response_awb->status == 'error') {
	echo $response_awb->message;// Daca "status" = error afisez "message", care contine motivul erorii
} else {
	print_r($response_awb->f_awb_collection); //array de awb-uri (awb pe fiecare colet)

	// se salveaza in tabela lo_awb informatiile obtinute, inclusiv token care va fi folosit la print
}

//////////////////////////////////////////////////////////////////////////////////////////////
// 						  			ESTIMARE PRET LIVRARE 									//
//////////////////////////////////////////////////////////////////////////////////////////////
$response_estimare = $lo->EstimeazaPret($f_request_awb);
if (isset($response_estimare->status) && $response_estimare->status == 'error') {
	echo $response_estimare->message;// Daca "status" = error afisez "message", care contine motivul erorii
} else {
	print_r((float)$response_estimare->f_pret); // afisez costul estimat al livrarii
}
//////////////////////////////////////////////////////////////////////////////////////////////
// 						  			ESTIMARE PRET LIVRARE 									//
//////////////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////
// 											  PRINT AWB										//
//////////////////////////////////////////////////////////////////////////////////////////////
$f_request_print = array('awb' => $response_awb->f_awb_collection[0], $response_awb->f_token);
$response_print = $lo->PrintAwb($f_request_print);
//raspuns PRINT AWB
echo $response_print;
//////////////////////////////////////////////////////////////////////////////////////////////
// 										  END PRINT AWB										//
//////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////
// 											TRACKING AWB									//
//////////////////////////////////////////////////////////////////////////////////////////////
$f_request_tracking = array('awb' => $response_awb->f_awb_collection[0]);

$response_tracking = $lo->Tracking($f_request_tracking);

// raspuns TRACKING
if (isset($response_tracking->status) && $response_tracking->status == 'error') {
	echo $response_tracking->message;
} else {
	$stare_curenta = $response_tracking->f_stare_curenta;
	$istoric = $response_tracking->f_istoric;
}
//////////////////////////////////////////////////////////////////////////////////////////////
// 										END TRACKING AWB									//
//////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////
// 											CANCEL AWB										//
//////////////////////////////////////////////////////////////////////////////////////////////
$f_request_cancel = array('awb' => $response_awb->f_awb_collection[0]);
$response_cancel = $lo->CancelLivrare($f_request_cancel);

//raspuns CANCEL LIVRARE
if ($response_cancel->status == 'error') {
	echo $response_cancel->message;
} else {
	echo $response_cancel->status;
}

//////////////////////////////////////////////////////////////////////////////////////////////
// 										  END CANCEL AWB									//
//////////////////////////////////////////////////////////////////////////////////////////////
