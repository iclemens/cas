<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	@include_once '../utility/setup.php';

	Zend_Loader::loadClass('Zend_Controller_Request_Http');
	Zend_Loader::loadClass('Zend_Controller_Response_Http');

	require_once "../controllers/KlantController.php";

	class Controllers_KlantenTest extends CT_TestCase
	{

		protected function setUp() 
		{
			addSampleCustomers();
		}

		protected function tearDown() 
		{
			$_POST = array();
			$_GET = array();
			resetTable("klanten");
		}

		public function testIndex() {
			$_SESSION = array();
			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));

			$result = $user->login("directie", "pw1directie");
			$ctrl = new KlantController($request, $response);

			$ctrl->indexAction();
			$klanten = $ctrl->getView()->get_template_vars('klanten');

			$this->assertTrue(is_array($klanten));
			$this->assertEquals(count($klanten), 2);
			$this->assertEquals($klanten[0]['bedrijfsnaam'], 'Citrus-IT');
		}

		public function testNieuw() {
			$_SESSION = array();
			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$ctrl = new KlantController($request, $response);

			$user = CT_User::instance(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			$ctrl->nieuwAction();

			$user->logout();

			try {
				$ctrl->nieuwAction();
			} catch(Exception $e) {
				return;
			}

			$this->fail("Exception expected");
		}

		public function testBewerk() {
			$_SESSION = array();
			$_GET = array("id" => 60001);

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			$ctrl = new KlantController($request, $response);
			$ctrl->bewerkAction();

			$klant = $ctrl->getView()->get_template_vars('klant');

			$this->assertTrue(is_array($klant));
			$this->assertEquals($klant['bedrijfsnaam'], 'Citrus-IT');
		}
                                
		public function testMaakRetainsData() {
			$_SESSION = array();
			$_POST = array(
				"bedrijfsnaam" => "MijnBedrijf",
				"aanhef" => "Dhr.", "voornaam" => "Jan", "achternaam" => "Hendriks",
				"factuuradres" => "De Wilgen 2", "factuurpostcode" => "1234 AB", "factuurplaats" => "Liempde",
				"bezoekadres" => "De Wilgen 2", "bezoekpostcode" => "1234 AB", "bezoekplaats" => "Liempde",
				"emailadres" => "mijn@email.nl", "factuuremail" => "factuur@email.nl", "website" => "",
				"telefoonvast" => "0411-674999", "telefoonmobiel" => "",
				"actief" => true, "klanttype" => 1, "factuurtekst" => "Test",
				'bezoekland' => 'Nederland', 'factuurland' => 'Nederland', 'btwgecontroleerd' => 0);

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			$ctrl = new KlantController($request, $response);
			$ctrl->maakAction();

			$klant = $ctrl->getView()->get_template_vars('klant');

			$this->assertEquals($klant['bedrijfsnaam'], 'MijnBedrijf');
			$this->assertEquals($klant['emailadres'], 'mijn@email.nl');
			$this->assertEquals($klant['factuurtekst'], NULL);
		}

		public function testOpslaan() {
			$_SESSION = array();
			$_GET = array("id" => 60001);

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			/* Gebruik de bewerk actie om de oude waarden op te halen.
						Hiermee kijken we of deze data klaar is om ingevoerd kan worden */
			$ctrl = new KlantController($request, $response);
			$ctrl->bewerkAction();

			$klant = $ctrl->getView()->get_template_vars('klant');

			$klant['bedrijfsnaam'] = 'MijnBedrijf';
			$klant['voornaam'] = 'Jan';
			$klant['achternaam'] = 'Hendriks';

			$_POST = $klant;
			$_GET = array("id" => 60001);

			$ctrl->opslaanAction();

			$database = Zend_Registry::get('database');
			$result =	$database->query("SELECT * FROM klanten WHERE klantnummer = 60001");
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['bedrijfsnaam'], 'MijnBedrijf');
			$this->assertEquals($rows[0]['voornaam'], "Jan");
			$this->assertEquals($rows[0]['achternaam'], 'Hendriks');
		}

		public function testOpslaanOngeldig() {
			$_SESSION = array();
			$_GET = array("id" => 60001);

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			/* Gebruik de bewerk actie om de oude waarden op te halen.
						Hiermee kijken we of deze data klaar is om ingevoerd kan worden */
			$ctrl = new KlantController($request, $response);
			$ctrl->bewerkAction();

			$klant = $ctrl->getView()->get_template_vars('klant');

			$klant['bedrijfsnaam'] = '';
			$klant['voornaam'] = 'Jan';
			$klant['achternaam'] = 'Hendriks';

			$_POST = $klant;
			$_GET = array("id" => 60001);

			$ctrl->opslaanAction();
			$klant = $ctrl->getView()->get_template_vars('klant');

			$this->assertEquals($klant['bedrijfsnaam'], '');
			$this->assertEquals($klant['voornaam'], "Jan");
			$this->assertEquals($klant['factuuradres'], "Redoutestraat 13");
		}

		public function testMaak() {
			$_SESSION = array();
			$_POST = array(
				"bedrijfsnaam" => "MijnBedrijf",
				"aanhef" => "Dhr.", "voornaam" => "Jan", "achternaam" => "Hendriks",
				"factuuradres" => "De Wilgen 2", "factuurpostcode" => "1234 AB", "factuurplaats" => "Liempde",
				"bezoekadres" => "De Wilgen 2", "bezoekpostcode" => "1234 AB", "bezoekplaats" => "Liempde",
				"emailadres" => "mijn@email.nl", "factuuremail" => "factuur@email.nl", "website" => "",
				"telefoonvast" => "0411-674999", "telefoonmobiel" => "", "actief" => true, "klanttype" => 0,
				'factuurland' => 'Nederland', 'bezoekland' => 'Nederland', 'btwgecontroleerd' => 0);

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			$ctrl = new KlantController($request, $response);
			$ctrl->maakAction();

			$database = Zend_Registry::get('database');
			$result =	$database->query("SELECT * FROM klanten WHERE klantnummer = 60003");
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['bedrijfsnaam'], 'MijnBedrijf');
			$this->assertEquals($rows[0]['bezoekpostcode'], '1234 AB');
		}
	}
