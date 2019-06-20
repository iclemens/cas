<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	@include_once '../utility/setup.php';

	Zend_Loader::loadClass('Zend_Controller_Request_Http');
	Zend_Loader::loadClass('Zend_Controller_Response_Http');

	require_once "../controllers/FacturerenController.php";

	class Controllers_FacturerenTest extends CT_TestCase
	{

		protected function setUp() 
		{
			$_GET = array();
			addSampleCustomers();
			addSampleArticleCodes();
		}

		protected function tearDown() 
		{
			resetTable("factureren");
			resetTable("klanten");
			resetTable("artikelcodes");
		}

		public function testIndexInvalidLogin()
		{
			$_SESSION = array();
			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));

			$result = $user->login("directie", "pw1directie");
			$ctrl = new FacturerenController($request, $response);

			try {
				$ctrl->indexAction();
			} catch (Exception $e) {
				$this->fail("Unexpected exception");
			}

			$user->logout();
			$result = $user->login("60000", "pw3klant_actief");

			$ctrl = new FacturerenController($request, $response);

			try {
				$ctrl->indexAction();
			} catch (Exception $e) {
				return;
			}
			
			$this->fail("Expected exception");
		}

		public function testNieuw()
		{
			$_SESSION = array();

			$user = CT_User::instance(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$ctrl = new FacturerenController($request, $response);
			$ctrl->nieuwAction();
			$klanten = $ctrl->getView()->get_template_vars('klanten');

			$this->assertTrue(is_array($klanten));
			$this->assertEquals(count($klanten), 2);		
		}

		public function testLijstXMLNoCustomer()
		{
			$_SESSION = array();

			$user = CT_User::instance(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$ctrl = new FacturerenController($request, $response);
			$ctrl->lijstXMLAction();
			$factureren = $ctrl->getView()->get_template_vars('factureren');

			$this->assertEquals($factureren['klantnummer'], 0);
			$this->assertEquals(count($factureren['regels']), 0);
		}

		public function testToevoegen()
		{
			$_SESSION = array();
			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));
			$result = $user->login("directie", "pw1directie");

			/* Forgot to specify customer, check if other data is retained */
			$_POST = array(
				"klantnummer" => 0,
				"artikelcode" => array("40123"),
				"omschrijving" => array("Een artikel"),
				"aantal" => array("10"),
				"prijs" => array("4,23"));

			$ctrl = new FacturerenController($request, $response);
			$ctrl->toevoegenAction();
			$factureren = $ctrl->getView()->get_template_vars('factureren');

			$this->assertEquals($factureren['regels'][0]['artikelcode'], "40123");
			$this->assertEquals($factureren['regels'][0]['prijs'], 423);

			$_POST = array(
				"klantnummer" => 60001,
				"artikelcode" => array("40332"),
				"omschrijving" => array("Een tweede artikel"),
				"aantal" => array("11"),
				"prijs" => array("4,24"));

			$ctrl = new FacturerenController($request, $response);
			$ctrl->toevoegenAction();
			$factureren = $ctrl->getView()->get_template_vars('factureren');

			$database = Zend_Registry::get('database');
			$result =	$database->query("SELECT * FROM factureren WHERE volgnummer = 1");
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['klantnummer'], 60001);
			$this->assertEquals($rows[0]['artikelcode'], "40332");
			$this->assertEquals($rows[0]['omschrijving'], "Een tweede artikel");
			$this->assertEquals($rows[0]['aantal'], 11);
			$this->assertEquals($rows[0]['prijs'], 424);
		}

	}
