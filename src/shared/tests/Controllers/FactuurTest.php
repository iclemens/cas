<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	@include_once '../utility/setup.php';

	Zend_Loader::loadClass('Zend_Controller_Request_Http');
	Zend_Loader::loadClass('Zend_Controller_Response_Http');

	require_once "../controllers/FactuurController.php";

	class Controllers_FactuurTest extends CT_TestCase
	{

		protected function setUp() 
		{
			addSampleCustomers();
			addSampleArticleCodes();
		}

		protected function tearDown() 
		{
			$_POST = array();
			$_GET = array();
			$_SESSION = array();

			resetTable("factuurregels");
			resetTable("facturen");
			resetTable("klanten");
			resetTable("artikelcodes");
		}

		public function testIndex() {
			$_SESSION = array();
			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));

			$result = $user->login("directie", "pw1directie");
			$ctrl = new FactuurController($request, $response);

			$ctrl->indexAction();
		}

		public function testNieuw() {
			$_SESSION = array();
			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));

			$result = $user->login("directie", "pw1directie");
			$ctrl = new FactuurController($request, $response);

			$ctrl->nieuwAction();

			/* Check whether the date is set to today */
			$factuur = $ctrl->getView()->get_template_vars('factuur');
			$this->assertEquals($factuur['datum'], date('d/m/Y'));
		}

		public function testAfronden67() {
			$_SESSION = array();

			$_POST = array(
				'klantnummer' => 60001,
				'datum' => date('d/m/Y'),
				'artikelcode' => array(40123),
				'omschrijving' => array('regel 1'),
				'aantal' => array(1),
				'prijs' => array(131.67),
				//'btw_percentage', 3,
				'btw_categorie' => 'vrij');

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));

			$result = $user->login('directie', 'pw1directie');
			$ctrl = new FactuurController($request, $response);

			$ctrl->voorbeeldAction();
			$factuur = $ctrl->getView()->get_template_vars('factuur');

			$this->assertEquals($factuur['regels'][1]['totaal'], 13167);
		}

		public function testVoorbeeld() {
			$_SESSION = array();
			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));

			$result = $user->login("directie", "pw1directie");
			$ctrl = new FactuurController($request, $response);

			$_POST = array(
				"klantnummer"	=> 60001,
				"datum"		=> date('d/m/Y'),
				"artikelcode"	=> array(40123, 40332),
				"omschrijving"	=> array("Regel 1", "Regel 2"),
				"aantal"	=> array("10,2", "5.2"),
				"prijs"		=> array("39,95", "12.50"),
				'btw_categorie' => 'hoog');

			$ctrl->voorbeeldAction();
			$factuur = $ctrl->getView()->get_template_vars('factuur');

			$this->assertEquals($factuur['regels'][1]['omschrijving'], "Regel 1");
			$this->assertEquals($factuur['regels'][2]['omschrijving'], "Regel 2");

			$this->assertEquals($factuur['regels'][1]['totaal'], 40749);
			$this->assertEquals($factuur['regels'][2]['totaal'], 6500);
			$this->assertEquals($factuur['subtotaal'], 47249);
			$this->assertEquals($factuur['btw'], 8977);
			$this->assertEquals($factuur['totaal'], 56226);
		}

		public function testMaak() {
			$database = Zend_Registry::get('database');

			$_POST = array(
				"klantnummer"	=> 60001,
				"datum"		=> '02/02/2007',
				"artikelcode"	=> array(40123, 40332),
				"omschrijving"	=> array("Regel 1", "Regel 2"),
				"aantal"	=> array("10,2", "5.2"),
				"prijs"		=> array("39,95", "12.50"),
				"btw_categorie" => 'hoog');

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));

			$result = $user->login("directie", "pw1directie");
			$ctrl = new FactuurController($request, $response);

			$ctrl->maakAction();

			$result = $database->query("SELECT * FROM facturen WHERE volgnummer = 1");
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['volgnummer'], 1);
			$this->assertEquals($rows[0]['factuurnummer'], 1);
			$this->assertEquals($rows[0]['klantnummer'], 60001);
			$this->assertEquals($rows[0]['datum'], '2007-02-02');
		}

		public function testBekijk() {
			$_POST = array(
				"klantnummer"	=> 60001,
				"datum"		=> '02/02/2007',
				"artikelcode"	=> array(40123, 40332),
				"omschrijving"	=> array("Regel 1", "Regel 2"),
				"aantal"	=> array("10,2", "5.2"),
				"prijs"		=> array("39,95", "12.50"),
				"btw_categorie" => 'hoog');

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));

			$result = $user->login("directie", "pw1directie");

			$ctrl = new FactuurController($request, $response);
			$ctrl->maakAction();

			// Opvragen van een eigen factuur
			$result = $user->login("60001", "pw3klant_actief");
			$ctrl = new FactuurController($request, $response);
			$_GET['naam'] = '20070001.pdf';

			try {
				$ctrl->bekijkAction();
			} catch(Exception $e) {
				$this->fail("Unexpected exception");
			}
			
			// Opvragen van een vreemde factuur
			$result = $user->login("60002", "pw3klant_actief");
			$ctrl = new FactuurController($request, $response);
			$_GET['naam'] = '20070001.pdf';

			try {
				$ctrl->bekijkAction();
			} catch(Exception $e) {
				return;
			}

			$this->fail("Expected exception");
		}

		public function testBekijkInvalidInvoice() {
			$_POST = array(
				"klantnummer"	=> 60001,
				"datum"		=> '02/02/2007',
				"artikelcode"	=> array(40123, 40332),
				"omschrijving"	=> array("Regel 1", "Regel 2"),
				"aantal"	=> array("10,2", "5.2"),
				"prijs"		=> array("39,95", "12.50"),
				"btw_categorie" => 'hoog');

			$request = new Zend_Controller_Request_Http();
			$response = new Zend_Controller_Response_Http();

			$user = CT_User::instance(Zend_Registry::get('database'));

			$result = $user->login("directie", "pw1directie");

			$ctrl = new FactuurController($request, $response);
			$ctrl->maakAction();

			// Opvragen van een niet-bestaande factuur
			$result = $user->login("60001", "pw3klant_actief");
			$ctrl = new FactuurController($request, $response);
			$_GET['naam'] = '20070002.pdf';

			try {
				$ctrl->bekijkAction();
			} catch(Exception $e) {
				return;
			}

			$this->fail("Expected exception");
		}
	}
