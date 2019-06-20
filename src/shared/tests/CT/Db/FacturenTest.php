<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/Db/Facturen.php";

	class CT_Db_FacturenTest extends CT_TestCase
	{

		protected function setUp() 
		{
		}

		protected function tearDown()
		{
			resetTable("facturen");
			resetTable("factuurregels");
		}

		function testCreateInvoice() {
			$database = Zend_Registry::get('database');

			$facturen_tbl = new CT_Db_Facturen();

			$factuur = array("datum" => "2006/01/01", "klantnummer" => 1);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));

			$factuur['regels'] = $regels;

			$volgnummer = $facturen_tbl->insert($factuur);

			$result = $database->query("SELECT * FROM facturen WHERE volgnummer = " . $volgnummer);
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['volgnummer'], $volgnummer);
			$this->assertEquals($rows[0]['factuurnummer'], 1);
			$this->assertEquals($rows[0]['klantnummer'], 1);
			$this->assertEquals($rows[0]['datum'], '2006-01-01');

			/** Test adding a second invoice in the same year **/

			$factuur = array("datum" => "2006/04/05", "klantnummer" => 1);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));

			$factuur['regels'] = $regels;

			$volgnummer = $facturen_tbl->insert($factuur);

			unset($result);
			$result =	$database->query("SELECT * FROM facturen WHERE volgnummer = " . $volgnummer);
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['volgnummer'], $volgnummer);
			$this->assertEquals($rows[0]['factuurnummer'], 2);
			$this->assertEquals($rows[0]['klantnummer'], 1);
			$this->assertEquals($rows[0]['datum'], '2006-04-05');

			/** Test adding a third invoice in another year **/

			$factuur = array("datum" => "2007/04/05", "klantnummer" => 1);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));

			$factuur['regels'] = $regels;

			$volgnummer = $facturen_tbl->insert($factuur);

			unset($result);
			$result =	$database->query("SELECT * FROM facturen WHERE volgnummer = " . $volgnummer);
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['volgnummer'], $volgnummer);
			$this->assertEquals($rows[0]['factuurnummer'], 1);
			$this->assertEquals($rows[0]['klantnummer'], 1);
			$this->assertEquals($rows[0]['datum'], '2007-04-05');
		}

		function testInvoiceWithDiscount() {
			$database = Zend_Registry::get('database');

			$facturen_tbl = new CT_Db_Facturen();

			$factuur = array("datum" => "2006/01/01", "klantnummer" => 60005);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));

			$factuur['kortingtype'] = 'absolute';
			$factuur['korting'] = 10;

			$factuur['regels'] = $regels;

			$volgnummer = $facturen_tbl->insert($factuur);

			$result =	$database->query("SELECT * FROM facturen WHERE volgnummer = " . $volgnummer);
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['volgnummer'], $volgnummer);
			$this->assertEquals($rows[0]['factuurnummer'], 1);
			$this->assertEquals($rows[0]['klantnummer'], 60005);
			$this->assertEquals($rows[0]['datum'], '2006-01-01');
			$this->assertEquals($rows[0]['korting'], 10);

			/** Test adding a second invoice in the same year **/

			$factuur = array("datum" => "2006/04/05", "klantnummer" => 1);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));

			$factuur['kortingtype'] = 'relative';
			$factuur['korting'] = 10;

			$factuur['regels'] = $regels;

			$volgnummer = $facturen_tbl->insert($factuur);

			unset($result);
			$result =	$database->query("SELECT * FROM facturen WHERE volgnummer = " . $volgnummer);
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['volgnummer'], $volgnummer);
			$this->assertEquals($rows[0]['factuurnummer'], 2);
			$this->assertEquals($rows[0]['klantnummer'], 1);
			$this->assertEquals($rows[0]['datum'], '2006-04-05');
			$this->assertEquals($rows[0]['korting'], 50);
		}

		function testFindInvoiceByNumber() {
			$facturen_tbl = new CT_Db_Facturen();

			$factuur = array("datum" => "2006/05/03", "klantnummer" => 1);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));

			$factuur['regels'] = $regels;

			$volgnummer = $facturen_tbl->insert($factuur);

			$factuur = array("datum" => "2006/07/05", "klantnummer" => 1);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));

			$factuur['regels'] = $regels;

			$volgnummer = $facturen_tbl->insert($factuur);

			$factuur = array("datum" => "2007/05/03", "klantnummer" => 1);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));

			$factuur['regels'] = $regels;

			$volgnummer = $facturen_tbl->insert($factuur);

			$factuur = $facturen_tbl->findByFactuurnummer('2006', 1);
			$this->assertEquals($factuur['datum'], '2006-05-03');

			$factuur = $facturen_tbl->findByFactuurnummer('2006', 2);
			$this->assertEquals($factuur['datum'], '2006-07-05');

			$factuur = $facturen_tbl->findByFactuurnummer('2007', 1);
			$this->assertEquals($factuur['datum'], '2007-05-03');
		}

		function testDiscounts() {
			$facturen_tbl = new CT_Db_Facturen();

			$factuur = array("datum" => "2006/05/03", "klantnummer" => 1);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));
			
			$factuur['regels'] = $regels;

			$factuur['btw_percentage'] = 19;
			$factuur['kortingtype'] = 'relative';
			$factuur['korting'] = 10;
			$factuur_new = CT_Db_Facturen::addCalculatedFields($factuur);

			$this->assertEquals($factuur_new['korting_bedrag'], 50);
			$this->assertEquals($factuur_new['btw'], 86);
			$this->assertEquals($factuur_new['totaal'], 536);
		}

		function testVATCategories() {
			$facturen_tbl = new CT_Db_Facturen();

			$factuur = array("datum" => "2006/05/03", "klantnummer" => 1);
			$regels = array(
				1 => array("omschrijving" => "Regel een", "aantal" => 1, "prijs" => 100),
				2 => array("omschrijving" => "Regel twee", "aantal" => 2, "prijs" => 200));
			
			$factuur['regels'] = $regels;

			$factuur['btw_percentage'] = 19;
			$factuur_new = CT_Db_Facturen::addCalculatedFields($factuur);

			$this->assertEquals($factuur_new['btw'], 95);
			$this->assertEquals($factuur_new['totaal'], 595);

			$factuur['btw_percentage'] = 6;
			$factuur_new = CT_Db_Facturen::addCalculatedFields($factuur);

			$this->assertEquals($factuur_new['btw'], 30);
			$this->assertEquals($factuur_new['totaal'], 530);
		}
	}
