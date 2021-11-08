<?php
namespace LivrariOnline;

use sylouuu\Curl\Method as Curl;
use phpseclib\Crypt\AES as AES;
use phpseclib\Crypt\RSA as RSA;

class LO
{
	//private
	private $f_request = null;
	private $f_secure = null;
	private $aes_key = null;
	private $iv = '285c02831e028bff';
	private $rsa_key = null;

	//definesc erorile standard: nu am putut comunica cu serverul, raspunsul de la server nu este de tip JSON. Restul de erori vin de la server
	private $error = array('server' => 'Nu am putut comunica cu serverul', 'notJSON' => 'Raspunsul primit de la server nu este formatat corect (JSON)');
	private $conn = null; //conexiunea la baza de date
	//public
	public $f_login = null;
	public $version = null;

	//////////////////////////////////////////////////////////////
	// 						METODE PUBLICE						//
	//////////////////////////////////////////////////////////////

	//setez versiunea de kit
	public function __construct()
	{
		$this->version = "LO2.0";
		self::registerAutoload('phpseclib');
		self::registerAutoload('Curl');
		//conectare la baza de date
		$this->conn = mysqli_connect('localhost', 'user', 'password', 'smartlocker') or die('Could not connect to DATABASE');
	}

	//setez cheia RSA
	public function setRSAKey($rsa_key)
	{
		$this->rsa_key = $rsa_key;
	}

	//helper pentru validarea bifarii unui checkbox si trimiterea de valori boolean catre server
	public function checkboxSelected($value)
	{
		if ($value) {
			return true;
		}
		return false;
	}

	public function encrypt_ISSN($input)
	{
		$aes_key = substr($this->rsa_key, 0, 16) . substr($this->rsa_key, -16);
		$aes = new AES();
		$aes->setIV($this->iv);
		$aes->setKey($aes_key);
		return base64_encode($aes->encrypt($input));
	}

	public function decrypt_ISSN($input)
	{
		$aes_key = substr($this->rsa_key, 0, 16) . substr($this->rsa_key, -16);
		$aes = new AES();
		$aes->setIV($this->iv);
		$aes->setKey($aes_key);
		$issn = $aes->decrypt(base64_decode($input));
		return json_decode($issn);
	}

	//////////////////////////////////////////////////////////////
	// 				METODE COMUNICARE CU SERVER					//
	//////////////////////////////////////////////////////////////

	public function AddOrderInNetwork($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://api.livrarionline.ro/Lobackend.asmx/AddOrderInNetwork');
	}

	public function CancelLivrare($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://api.livrarionline.ro/Lobackend.asmx/CancelLivrare');
	}

	public function GenerateAwb($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://api.livrarionline.ro/Lobackend.asmx/GenerateAwb');
	}

	public function GenerateAwbSmartloker($f_request, $delivery_point_id, $rezervation_id, $order_id)
	{
		$f_request['dulapid'] = (int)$delivery_point_id;
		$f_request['rezervationid'] = (int)$rezervation_id; // obtinut prin call-ul de rezervare prin metoda get_reservationid
		$f_request['orderid'] = strval($order_id);

		$sql = "SELECT * FROM lo_delivery_points where dp_id = " . $delivery_point_id;
		$query = mysqli_query($this->conn, $sql);
		$row = mysqli_fetch_array($query);

		$f_request['shipTOaddress'] = array(
			//Obligatoriu
			'address1'   => $row['dp_adresa'],
			'address2'   => '',
			'city'       => $row['dp_oras'],
			'state'      => $row['dp_judet'],
			'zip'        => $row['dp_cod_postal'],
			'country'    => $row['dp_tara'],
			'phone'      => '',
			'observatii' => '',
		);
		return $this->LOCommunicate($f_request, 'https://api.livrarionline.ro/Lobackend.asmx/GenerateAwb');
	}

	public function RegisterAwb($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://api.livrarionline.ro/Lobackend.asmx/RegisterAwb');
	}

	public function PrintAwb($f_request, $class = '')
	{
		return '<a class="' . $class . '" id="print-awb" href="https://api.livrarionline.ro/Lobackend_print/PrintAwb.aspx?f_login=' . $this->f_login . '&awb=' . $f_request['awb'] . '&f_token=' . $f_request['f_token'] . '" target="_blank">Click pentru print AWB</a>';
	}

	public function Tracking($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://api.livrarionline.ro/Lobackend.asmx/Tracking');
	}

	public function EstimeazaPret($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://estimare.livrarionline.ro/EstimarePret.asmx/EstimeazaPret');
	}

	public function EstimeazaPretSmartlocker($f_request, $delivery_point_id, $order_id)
	{
		$f_request['dulapid'] = (int)$delivery_point_id;
		$f_request['orderid'] = strval($order_id);

		$sql = "SELECT * FROM lo_delivery_points where dp_id = " . $delivery_point_id;
		$query = mysqli_query($this->conn, $sql);
		$row = mysqli_fetch_array($query);

		$f_request['shipTOaddress'] = array(
			//Obligatoriu
			'address1'   => $row['dp_adresa'],
			'address2'   => '',
			'city'       => $row['dp_oras'],
			'state'      => $row['dp_judet'],
			'zip'        => $row['dp_cod_postal'],
			'country'    => $row['dp_tara'],
			'phone'      => '',
			'observatii' => '',
		);
		return $this->LOCommunicate($f_request, 'https://estimare.livrarionline.ro/EstimarePret.asmx/EstimeazaPret');
	}

	public function GetPachetomatePR($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://api.livrarionline.ro/Lobackend.asmx/GetPachetomatePR');
	}

	public function SetPachetomatDefault($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://api.livrarionline.ro/Lobackend.asmx/SetPachetomatDefault');
	}

	public function getExpectedIn($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://smartlocker.livrarionline.ro/api/GetLockerExpectedInID', true);
	}

	public function cancelExpectedIn($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://smartlocker.livrarionline.ro/api/CancelLockerExpectedInID', true);
	}

	public function get_sl_cell_reservation_id($f_request)
	{
		return $this->LOCommunicate($f_request, 'https://smartlocker.livrarionline.ro/api/GetLockerCellResevationID', true);
	}

	//////////////////////////////////////////////////////////////
	// 				END METODE COMUNICARE CU SERVER				//
	//////////////////////////////////////////////////////////////

	// CAUTARE PACHETOMATE DUPA LOCALITATE, JUDET SI DENUMIRE
	public function get_all_delivery_points($search)
	{
		$sql = "SELECT
			    dp.*,
			    COALESCE(group_concat(
					CASE
						WHEN p.day_active = 0 THEN CONCAT('<div>', p.day, ': Inchis')
						WHEN p.day_active = 1 THEN CONCAT('<div>', p.`day`, ': ', p.dp_start_program, ' - ', p.dp_end_program)
						WHEN p.day_active = 2 THEN CONCAT('<div>', p.day, ': Non-Stop')
					END
					order by p.day_sort_order
					separator '</div>'
				),' - ') as orar
			FROM
			    lo_delivery_points dp
			        LEFT JOIN
			    lo_dp_program p ON dp.dp_id = p.dp_id
			WHERE
				dp_active > 0
				AND (
					dp_judet like '%" . $search . "%'
					OR dp_oras like '%" . $search . "%'
					OR dp_denumire like '%" . $search . "%'
				)
			group by
				dp.dp_id
			order by
			    dp.dp_active desc, dp.dp_id asc
				";

		$delivery_points = array();

		$query = mysqli_query($this->conn, $sql);
		if (mysqli_num_rows($query) > 0) {
			while ($row = mysqli_fetch_array($query)) {
				$delivery_points[] = array(
					'id'          => $row['dp_id'],
					'denumire'    => $row['dp_denumire'],
					'adresa'      => $row['dp_adresa'],
					'judet'       => $row['dp_judet'],
					'localitate'  => $row['dp_oras'],
					'tara'        => $row['dp_tara'],
					'cod_postal'  => $row['dp_cod_postal'],
					'latitudine'  => $row['dp_gps_lat'],
					'longitudine' => $row['dp_gps_long'],
					'tip'         => ($row['dp_tip'] == 1 ? 'Pachetomat' : 'Punct de ridicare'),
					'orar'        => $row['orar'],
					'disabled'    => ((int)$row['dp_active'] <= 0 ? true : false),
				);
			}
		}
		return json_encode($delivery_points);
	}
	// END CAUTARE PACHETOMATE DUPA LOCALITATE, JUDET SI DENUMIRE

	// AFISARE INFORMATII DESPRE SMARTLOCKER (adresa, orar) dupa selectarea smartlocker-ului din lista de pachetomate disponibile
	public function get_delivery_point_by_id($delivery_point_id)
	{
		$sql = "SELECT
			    dp.*,
			    COALESCE(group_concat(
					CASE
						WHEN p.day_active = 0 THEN CONCAT('<div>', p.day, ': Inchis')
						WHEN p.day_active = 1 THEN CONCAT('<div>', p.`day`, ': ', p.dp_start_program, ' - ', p.dp_end_program)
						WHEN p.day_active = 2 THEN CONCAT('<div>', p.day, ': Non-Stop')
					END
					order by p.day_sort_order
					separator '</div>'
				),' - ') as orar
			FROM
			    lo_delivery_points dp
			        LEFT JOIN
			    lo_dp_program p ON dp.dp_id = p.dp_id
			WHERE
				dp.dp_id = " . $delivery_point_id . "
			group by
				dp.dp_id
			order by
			    dp.dp_active desc, dp.dp_id asc
				";

		$delivery_point = array();

		$query = mysqli_query($this->conn, $sql);
		if (mysqli_num_rows($query) > 0) {
			$row = mysqli_fetch_array($query);
			$delivery_point = array(
				'id'          => $row['dp_id'],
				'denumire'    => $row['dp_denumire'],
				'adresa'      => $row['dp_adresa'],
				'judet'       => $row['dp_judet'],
				'localitate'  => $row['dp_oras'],
				'tara'        => $row['dp_tara'],
				'cod_postal'  => $row['dp_cod_postal'],
				'latitudine'  => $row['dp_gps_lat'],
				'longitudine' => $row['dp_gps_long'],
				'tip'         => ($row['dp_tip'] == 1 ? 'Pachetomat' : 'Punct de ridicare'),
				'orar'        => $row['orar'],
				'disabled'    => ((int)$row['dp_active'] <= 0 ? true : false),
			);
		}
		return json_encode($delivery_point);
	}
	// END AFISARE INFORMATII DESPRE SMARTLOCKER (adresa, orar) dupa selectarea smartlocker-ului din lista de pachetomate disponibile

	// METODA INCREMENTARE EXPECTEDIN
	public function plus_expectedin($delivery_point_id, $orderid)
	{
		$f_request_expected_in = array();
		$f_request_expected_in['f_action'] = 3;
		$f_request_expected_in['f_orderid'] = strval($orderid);
		$f_request_expected_in['f_lockerid'] = $delivery_point_id;
		$this->getExpectedIn($f_request_expected_in);
	}
	// END METODA INCREMENTARE EXPECTEDIN

	// METODA SCADERE EXPECTEDIN
	public function minus_expectedin($delivery_point_id, $orderid)
	{
		$f_request_expected_in = array();
		$f_request_expected_in['f_action'] = 8;
		$f_request_expected_in['f_orderid'] = strval($orderid);
		$f_request_expected_in['f_lockerid'] = $delivery_point_id;
		$this->cancelExpectedIn($f_request_expected_in);
	}
	// END METODA SCADERE EXPECTEDIN

	// GET RESERVATION ID
	public function get_reservationid($delivery_point_id, $orderid, $cell_size = 3)
	{
		$f_request = array();

		$f_request['f_action'] = 4;
		$f_request['f_lockerid'] = $delivery_point_id;
		$f_request['f_marime_celula'] = $cell_size; //1 -> L (440mm / 600mm / 611mm), 2 -> M (498mm / 600mm / 382mm), 3 -> S (498mm / 600mm / 300mm), 4 -> XL (600mm / 600mm / 600mm)
		$f_request['f_orders_id'] = strval($orderid);

		$response = $this->get_sl_cell_reservation_id($f_request);

		if ($response->status == 'error') {
			$raspuns['status'] = 'error';
			$raspuns['message'] = $response->message;
		} else {
			if ($response->error == 1) {
				if ($response->error_code == '01523') {
					// eroare rezervare celula
				}
				$raspuns['status'] = 'error';
				$raspuns['error_code'] = $response->error_code;
				$raspuns['message'] = $response->error_message;
			} else {
				$raspuns['status'] = 'success';
				$raspuns['f_lockerid'] = $response->f_lockerid;
				$raspuns['f_reservation_id'] = $response->f_reservation_id;
			}
		}
		return json_encode($raspuns);
	}
	// END GET RESERVATION ID

	// ISSN
	public function issn()
	{
		$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		switch ($user_agent) {
			case "mozilla/5.0 (livrarionline.ro locker push service aes)":
				$this->run_lockers_update_push();
				break;
			case "mozilla/5.0 (livrarionline.ro locker update service aes)":
				$this->run_lockers_update();
				break;
			default:
				if (empty($_POST['F_CRYPT_MESSAGE_ISSN'])) {
					die('F_CRYPT_MESSAGE_ISSN nu a fost trimis');
				}
				$this->run_issn($_POST['F_CRYPT_MESSAGE_ISSN']);
				break;
		}
	}
	// END ISSN

	//////////////////////////////////////////////////////////////
	// 					END METODE PUBLICE						//
	//////////////////////////////////////////////////////////////

	//////////////////////////////////////////////////////////////
	// 						METODE PRIVATE						//
	//////////////////////////////////////////////////////////////

	private static function registerAutoload($classname)
	{
		spl_autoload_extensions('.php'); // Only Autoload PHP Files
		spl_autoload_register(function ($classname) {
			if (strpos($classname, '\\') !== false) {
				// Namespaced Classes
				$classfile = str_replace('\\', '/', $classname);
				if ($classname[0] !== '/') {
					$classfile = dirname(__FILE__) . '/libraries/' . $classfile . '.php';
				}
				require($classfile);
			}
		});
	}

	// criptez f_request cu AES
	private function AESEnc()
	{
		$this->aes_key = substr(hash('sha256', uniqid(), 0), 0, 32);
		$aes = new AES();
		$aes->setIV($this->iv);
		$aes->setKey($this->aes_key);
		$this->f_request = bin2hex(base64_encode($aes->encrypt($this->f_request)));
	}

	//criptez cheia AES cu RSA
	private function RSAEnc()
	{
		$rsa = new RSA();
		$rsa->loadKey($this->rsa_key);
		$rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
		$this->f_secure = base64_encode($rsa->encrypt($this->aes_key));
	}

	//setez f_request, criptez f_request cu AES si cheia AES cu RSA
	private function setFRequest($f_request)
	{
		$this->f_request = json_encode($f_request);
		$this->AESEnc();
		$this->RSAEnc();
	}

	//construiesc JSON ce va fi trimis catre server
	private function createJSON($loapi = false)
	{
		$request = array();
		$request['f_login'] = $this->f_login;
		$request['f_request'] = $this->f_request;
		$request['f_secure'] = $this->f_secure;
		if (!$loapi) {
			return json_encode(array('loapi' => $request));
		} else {
			return json_encode($request);
		}
	}

	//metoda pentru verificarea daca un string este JSON - folosit la primirea raspunsului de la server
	private function isJSON($string)
	{
		if (is_object(json_decode($string))) {
			return true;
		}
		return false;
	}

	//metoda pentru verificarea raspunsului obtinut de la server. O voi apela cand primesc raspunsul de la server
	private function processResponse($response, $loapi = false)
	{
		//daca nu primesc raspuns de la server
		if ($response == false) {
			return (object)array('status' => 'error', 'message' => $this->error['server']);
		} else {
			//verific daca raspunsul este de tip JSON
			if ($this->isJSON($response)) {
				$response = json_decode($response);
				if (!$loapi) {
					return $response->loapi;
				} else {
					return $response;
				}
			} else {
				return (object)array('status' => 'error', 'message' => $this->error['notJSON']);
			}
		}
	}

	//metoda comunicare cu server LO
	private function LOCommunicate($f_request, $urltopost, $loapi = false)
	{
		$this->setFRequest($f_request);
		$payload = $this->createJSON($loapi);
		$request = new Curl\Post($urltopost, array(
			'data'       => array(
				'loapijson' => $payload,
			),
			'is_payload' => false,
		));
		$request->setCurlOption(CURLOPT_TIMEOUT, 30);
		$request->send();

		if ($request->getStatus() === 200) {
			$response = $request->getResponse();
			return $this->processResponse($response, $loapi, $payload, $f_request, $urltopost);
		} else {
			return (object)array('status' => 'error', 'message' => $this->error['server']);
		}
	}

	// SMARTLOCKER UPDATE
	private function run_lockers_update()
	{
		$posted_json = file_get_contents('php://input');
		$lockers_data = json_decode($posted_json, true);

		$login_id = $lockers_data['merchid'];
		$lo_delivery_points = $lockers_data['dulap'];
		$lo_dp_program = $lockers_data['zile2dulap'];
		$lo_dp_exceptii = $lockers_data['exceptii_zile'];

		if (!empty($lo_delivery_points)) {
			foreach ($lo_delivery_points as $delivery_point) {
				$sql = "INSERT INTO `lo_delivery_points`
							(`dp_id`,
							`dp_denumire`,
							`dp_adresa`,
							`dp_judet`,
							`dp_oras`,
							`dp_tara`,
							`dp_gps_lat`,
							`dp_gps_long`,
							`dp_tip`,
							`dp_active`,
							`version_id`,
							dp_temperatura, 
							dp_indicatii, 
							termosensibil)
						VALUES
							(" . (int)$delivery_point['dulapid'] . ",
							'" . $delivery_point['denumire'] . "',
							'" . $delivery_point['adresa'] . "',
							'" . $delivery_point['judet'] . "',
							'" . $delivery_point['oras'] . "',
							'" . $delivery_point['tara'] . "',
							" . (float)$delivery_point['latitudine'] . ",
							" . (float)$delivery_point['longitudine'] . ",
							" . (int)$delivery_point['tip_dulap'] . ",
							" . (int)$delivery_point['active'] . ",
							" . (int)$delivery_point['versionid'] . ")
						ON DUPLICATE KEY UPDATE 
							`dp_denumire` = '" . $delivery_point['denumire'] . "',
							`dp_adresa` = '" . $delivery_point['adresa'] . "',
							`dp_judet` = '" . $delivery_point['judet'] . "',
							`dp_oras` = '" . $delivery_point['oras'] . "',
							`dp_tara` = '" . $delivery_point['tara'] . "',
							`dp_gps_lat` = " . (float)$delivery_point['latitudine'] . ",
							`dp_gps_long` = " . (float)$delivery_point['longitudine'] . ",
							`dp_tip` = " . (int)$delivery_point['tip_dulap'] . ",
							`dp_active` = " . (int)$delivery_point['active'] . ",
							`version_id` = " . (int)$delivery_point['versionid'] . ",
							`dp_temperatura` = " . (float)$delivery_point['dp_temperatura'] . ",
							`dp_indicatii` = " . $delivery_point['dp_indicatii'] . ",
							`termosensibil` = " . (int)$delivery_point['termosensibil'];

				$query = mysqli_query($this->conn, $sql);
			}
		}

		if (!empty($lo_dp_program)) {
			foreach ($lo_dp_program as $program) {
				$sql = "INSERT INTO `lo_dp_program`
							(`dp_start_program`,
							`dp_end_program`,
							`dp_id`,
							`day_active`,
							`version_id`,
							`day_number`,
							`day`)
						VALUES
							('" . $program['start_program'] . "',
							'" . $program['end_program'] . "',
							" . (int)$program['dulapid'] . ",
							" . (int)$program['active'] . ",
							" . (int)$program['versionid'] . ",
							" . (int)$program['day_number'] . ",
							'" . $program['day_name'] . "')
						ON DUPLICATE KEY UPDATE 
							`dp_start_program` = '" . $program['start_program'] . "',
							`dp_end_program` = '" . $program['end_program'] . "',
							`day_active` = " . (int)$program['active'] . ",
							`version_id` = " . (int)$program['versionid'] . ",
							`day` = '" . $program['day_name'];
				$query = mysqli_query($this->conn, $sql);
			}
		}

		if (!empty($lo_dp_exceptii)) {
			foreach ($lo_dp_exceptii as $exceptie) {
				$sql = "INSERT INTO `lo_dp_day_exceptions`
							(`dp_start_program`,
							`dp_end_program`,
							`dp_id`,
							`active`,
							`version_id`,
							`exception_day`)
						VALUES
							('" . $exceptie['start_program'] . "',
							'" . $exceptie['end_program'] . "',
							" . (int)$exceptie['dulapid'] . ",
							" . (int)$exceptie['active'] . ",
							" . (int)$exceptie['versionid'] . ",
							'" . $exceptie['ziua'] . "')
						ON DUPLICATE KEY UPDATE 
							`dp_start_program` = '" . $exceptie['start_program'] . "',
							`dp_end_program` = '" . $exceptie['end_program'] . "',
							`active` = " . (int)$exceptie['active'] . ",
							`version_id` = " . (int)$exceptie['versionid'];

				$query = mysqli_query($this->conn, $sql);
			}
		}

		$sql = "SELECT
					COALESCE(MAX(dp.version_id), 0) AS max_dulap_id,
					COALESCE(MAX(dpp.version_id), 0) AS max_zile2dp,
				    COALESCE(MAX(dpe.version_id), 0) AS max_exceptii_zile
				FROM
					lo_delivery_points dp
					LEFT join lo_dp_program dpp ON dpp.dp_id = dp.dp_id
					LEFT join lo_dp_day_exceptions dpe ON dpe.dp_id = dp.dp_id";
		$query = mysqli_query($this->conn, $sql);
		$row = mysqli_fetch_array($query);

		$response['merch_id'] = (int)$login_id;
		$response['max_dulap_id'] = (int)$row['max_dulap_id'];
		$response['max_zile2dp'] = (int)$row['max_zile2dp'];
		$response['max_exceptii_zile'] = (int)$row['max_exceptii_zile'];

		echo json_encode($response);
	}
	// END SMARTLOCKER UPDATE

	// SMARTLOCKER UPDATE cu notificare si preluare doar diferente
	public function run_lockers_update_push()
	{
		$posted = file_get_contents('php://input');

		$this->f_login = (int)99999;
		$this->setRSAKey('rsa_key');

		$lockers_data = $this->decrypt_ISSN($posted);
		if (is_null($lockers_data)) {
			die('Nu am putut decripta payload-ul');
		}

		if (isset($lockers_data->update) && $lockers_data->update == true && !empty($lockers_data->f_stamp)) {
			// citesc data ultimului update din DB local
			$check_sql = "SELECT last_update FROM lo_locker_push";
			$query = mysqli_query($this->conn, $check_sql);
			$result = mysqli_fetch_array($query);

			$last_update = '2000-01-01 00:00:00';
			if (!empty($result)) {
				$last_update = $result['last_update'];
			} else {
				$sql = "INSERT INTO lo_locker_push VALUES ('" . $last_update . "')";
				$query = mysqli_query($this->conn, $sql);
			}

			$lockers_data = $this->GetPachetomatePR(array('f_action' => 10, 'f_stamp' => $last_update));
			if ($lockers_data->status == 'error') {
				throw new \Exception($lockers_data->message);
			}
		}

		$lockers_data = (array)$lockers_data;

		$lo_delivery_points = $lockers_data['dulap'];
		$lo_dp_program = $lockers_data['zile2dulap'];
		$lo_dp_exceptii = $lockers_data['exceptii_zile'];

		if (!empty($lo_delivery_points)) {
			foreach ($lo_delivery_points as $delivery_point) {
				$sql = "INSERT INTO `lo_delivery_points`
							(`dp_id`,
							`dp_denumire`,
							`dp_adresa`,
							`dp_judet`,
							`dp_oras`,
							`dp_tara`,
							`dp_gps_lat`,
							`dp_gps_long`,
							`dp_tip`,
							`dp_active`,
							`version_id`,
							dp_temperatura, 
							dp_indicatii, 
							termosensibil)
						VALUES
							(" . (int)$delivery_point['dulapid'] . ",
							'" . $delivery_point['denumire'] . "',
							'" . $delivery_point['adresa'] . "',
							'" . $delivery_point['judet'] . "',
							'" . $delivery_point['oras'] . "',
							'" . $delivery_point['tara'] . "',
							" . (float)$delivery_point['latitudine'] . ",
							" . (float)$delivery_point['longitudine'] . ",
							" . (int)$delivery_point['tip_dulap'] . ",
							" . (int)$delivery_point['active'] . ",
							" . (int)$delivery_point['versionid'] . ")
						ON DUPLICATE KEY UPDATE 
							`dp_denumire` = '" . $delivery_point['denumire'] . "',
							`dp_adresa` = '" . $delivery_point['adresa'] . "',
							`dp_judet` = '" . $delivery_point['judet'] . "',
							`dp_oras` = '" . $delivery_point['oras'] . "',
							`dp_tara` = '" . $delivery_point['tara'] . "',
							`dp_gps_lat` = " . (float)$delivery_point['latitudine'] . ",
							`dp_gps_long` = " . (float)$delivery_point['longitudine'] . ",
							`dp_tip` = " . (int)$delivery_point['tip_dulap'] . ",
							`dp_active` = " . (int)$delivery_point['active'] . ",
							`version_id` = " . (int)$delivery_point['versionid'] . ",
							`dp_temperatura` = " . (float)$delivery_point['dp_temperatura'] . ",
							`dp_indicatii` = " . $delivery_point['dp_indicatii'] . ",
							`termosensibil` = " . (int)$delivery_point['termosensibil'];

				$query = mysqli_query($this->conn, $sql);
			}
		}

		if (!empty($lo_dp_program)) {
			foreach ($lo_dp_program as $program) {
				$sql = "INSERT INTO `lo_dp_program`
							(`dp_start_program`,
							`dp_end_program`,
							`dp_id`,
							`day_active`,
							`version_id`,
							`day_number`,
							`day`)
						VALUES
							('" . $program['start_program'] . "',
							'" . $program['end_program'] . "',
							" . (int)$program['dulapid'] . ",
							" . (int)$program['active'] . ",
							" . (int)$program['versionid'] . ",
							" . (int)$program['day_number'] . ",
							'" . $program['day_name'] . "')
						ON DUPLICATE KEY UPDATE 
							`dp_start_program` = '" . $program['start_program'] . "',
							`dp_end_program` = '" . $program['end_program'] . "',
							`day_active` = " . (int)$program['active'] . ",
							`version_id` = " . (int)$program['versionid'] . ",
							`day` = '" . $program['day_name'];
				$query = mysqli_query($this->conn, $sql);
			}
		}

		if (!empty($lo_dp_exceptii)) {
			foreach ($lo_dp_exceptii as $exceptie) {
				$sql = "INSERT INTO `lo_dp_day_exceptions`
							(`dp_start_program`,
							`dp_end_program`,
							`dp_id`,
							`active`,
							`version_id`,
							`exception_day`)
						VALUES
							('" . $exceptie['start_program'] . "',
							'" . $exceptie['end_program'] . "',
							" . (int)$exceptie['dulapid'] . ",
							" . (int)$exceptie['active'] . ",
							" . (int)$exceptie['versionid'] . ",
							'" . $exceptie['ziua'] . "')
						ON DUPLICATE KEY UPDATE 
							`dp_start_program` = '" . $exceptie['start_program'] . "',
							`dp_end_program` = '" . $exceptie['end_program'] . "',
							`active` = " . (int)$exceptie['active'] . ",
							`version_id` = " . (int)$exceptie['versionid'];

				$query = mysqli_query($this->conn, $sql);
			}
		}

		// actualizez ultimul stamp de update
		$sql = "UPDATE lo_locker_push SET last_update = now()";
		$query = mysqli_query($this->conn, $sql);
	}

	// END SMARTLOCKER UPDATE cu notificare si preluare doar diferente


	// ISSN UPDATE ORDER STATUS
	private function run_issn($f_crypt_message_issn)
	{
		$error = false;
		$issn = $this->decrypt_ISSN($f_crypt_message_issn); //obiect decodat din JSON in clasa LO
		if (!isset($issn) || empty($issn)) {
			die('Hacking attempt!');
		}
		//issn este un obiect, cu atributele: f_order_number, f_statusid, f_stamp, f_awb_collection (array de AWB-uri)

		//f_order_number - referinta
		if (isset($issn->f_order_number)) {
			$vF_Ref = $issn->f_order_number;
		} else {
			$error = true;
			die('Parametrul f_order_number lipseste.');
		}
		//f_statusid
		if (isset($issn->f_statusid)) {
			$vF_statusid = $issn->f_statusid;
		} else {
			$error = true;
			die('Parametrul f_statusid lipseste.');
		}
		// f_stamp
		if (isset($issn->f_stamp)) {
			$vF_stamp = $issn->f_stamp;
		} else {
			$error = true;
			die('Parametrul f_stamp lipseste.');
		}
		// f_awb_collection
		if (isset($issn->f_awb_collection)) {
			$vF_AWB = $issn->f_awb_collection; //array de awb-uri
			$vF_AWB = $vF_AWB[0];
		} else {
			$error = true;
			die('Parametrul f_awb lipseste.');
		}
		// Obtine order id
		$raw_vF_Order_Number = explode('nr.', $vF_Ref);
		$vF_Order_Number = trim($raw_vF_Order_Number[1]);

		if (!$error) {
			switch ($vF_statusid) {
				case '100':
					// Preluata de curier de la comerciant, de actualizat starea comenzii in sistem
					break;
				case '110':
					// Preluata de curier din Smart Locker, de actualizat starea comenzii in sistem
					break;
				case '130':
					// Predata in hub, de actualizat starea comenzii in sistem
					break;
				case '150':
					// Preluata de curier din hub, de actualizat starea comenzii in sistem
					break;
				case '290':
					// Predata in Smart Locker, de actualizat starea comenzii in sistem
					break;
				case '300':
					// Livrata la destinatar, de actualizat starea comenzii in sistem
					break;
				case '600':
					// Anulata, de actualizat starea comenzii in sistem
					break;
				case '900':
					// Facturata, de actualizat starea comenzii in sistem
					break;
				case '1000':
					// Finalizata, de actualizat starea comenzii in sistem
					break;

			}
			$raspuns_xml = '<?xml version="1.0" encoding="UTF-8" ?>';
			$raspuns_xml .= '<issn>';
			$raspuns_xml .= '<x_order_number>' . $issn->f_order_number . '</x_order_number>';
			$raspuns_xml .= '<merchServerStamp>' . date("Y-m-dTH:m:s") . '</merchServerStamp>';
			$raspuns_xml .= '<f_response_code>1</f_response_code>';
			$raspuns_xml .= '</issn>';
			echo $raspuns_xml;
		}
	}
	// END ISSN UPDATE ORDER STATUS

	//////////////////////////////////////////////////////////////
	// 						END METODE PRIVATE					//
	//////////////////////////////////////////////////////////////
}

?>
