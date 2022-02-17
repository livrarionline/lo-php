<?php
	$lang = 'ro';
	$is_user_logged_in = true;

	$current_user = (object)[];
	$current_user->user_email = 'email@domain.com';

	if (!$is_user_logged_in) { // daca userul nu este logat
		// adauga script fara email
		echo '<script async type="text/javascript" src="https://static.livrarionline.ro/getppid/0/' . $lang . '/pp.js"></script>';
	} else {
		// adauga script cu email
		$email_hash = hash('sha256', strtolower($current_user->user_email . 'pp'), 0);
		echo '<script async type="text/javascript" src="https://static.livrarionline.ro/getppid/' . $email_hash . '/' . $lang . '/pp.js"></script>';
}

/*
 * scriptul adauga un cookie denumit postapanduri cu urmatorul continut json:

{"default_dpid": 12,"default_dpname": "Centrul de activitati www.deSirnea.ro","info": "Comanda acum si poti ridica coletul pe 19-02-2022 dupa ora 12:00","judet": "Brasov","localitate": "Sirnea","adresa": "Strada Principala 213B","tip": "db","default_dptip": "1"}

folosind pachetomatul setat in cookie se poate preselecta in procesul de comanda Pachetomatul default sau Oficiul Postal preferat de catre client

in functie de valoarea dp_tip: 0 - Punct de ridicare PostaRomana, 1 - Pachetomat PostaPanduri

*/
