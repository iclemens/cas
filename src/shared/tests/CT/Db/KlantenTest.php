<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/Db/Klanten.php";

	class CT_Db_KlantenTest extends CT_TestCase
	{

		protected function setUp() 
		{
		}

		protected function tearDown()
		{
			resetTable("facturen");
			resetTable("factuurregels");
		}

		function testCreateCustomer() {
			$database = Zend_Registry::get('database');
			$config = Zend_Registry::get('config');

			$tbl_klanten = new CT_Db_Klanten();

			$klant = array("bedrijfsnaam" => "Bedrijf 1",
				"aanhef" => "Dhr.",
				"voornaam" => "Kees",
				"achternaam" => "Meijs",
				"factuuradres" => "Redoutestraat 13",
				"factuurpostcode" => "5283 NK",
				"factuurplaats" => "Boxtel",
				"bezoekadres" => "Redoutestraat 13",
				"bezoekpostcode" => "5283 NL",
				"bezoekplaats" => "Boxtel",
				"actief" => 1,
				"klanttype" => 0);

			$id = $tbl_klanten->insert($klant);

			$this->assertEquals($id, $config->customer->first_business_id);
		}

	}
?>
