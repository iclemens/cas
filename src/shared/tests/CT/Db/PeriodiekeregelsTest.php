<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/Db/Periodiekeregels.php";

	class CT_Db_PeriodiekeregelsTest extends CT_TestCase
	{

		protected function setUp() 
		{
		}

		protected function tearDown()
		{
			resetTable("perioden");
			resetTable("periodiekeregels");
			resetTable("factureren");		
		}

		function testInsert()
		{
			$database = Zend_Registry::get('database');

			$periodiek_tbl = new CT_Db_Periodiekeregels();

			$periodiekeregel = array(
				'klantnummer' => 60001,
				'btw_percentage' => 19,
				'perioden' => array(
					array('maand' => 1),
					array('maand' => 6)),
				'laatstgefactureerd' => '2006/01/01',
				'artikelcode' => 40123,
				'omschrijving' => 'Een artikel',
				'aantal' => 10,
				'prijs' => 4.23);

			$volgnummer = $periodiek_tbl->insert($periodiekeregel);

			$result = $database->query("SELECT * FROM periodiekeregels WHERE volgnummer = " . $volgnummer);
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['volgnummer'], $volgnummer);
			$this->assertEquals($rows[0]['klantnummer'], 60001);
			$this->assertEquals($rows[0]['laatstgefactureerd'], '2006-01-01');
			$this->assertEquals($rows[0]['artikelcode'], 40123);

			unset($rows);
			unset($result);

			$result = $database->query('SELECT * FROM perioden WHERE periodiekeregel = ' . $volgnummer . ' ORDER BY maand');
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['maand'], 1);
			$this->assertEquals($rows[1]['maand'], 6);
		}

		function testUpdate()
		{
			$database = Zend_Registry::get('database');

			$periodiek_tbl = new CT_Db_Periodiekeregels();

			$periodiekeregel = array(
				'klantnummer' => 60001,
				'btw_percentage' => 19,
				'perioden' => array(
					array('maand' => 5),
					array('maand' => 2)),
				'laatstgefactureerd' => '2004/01/01',
				'artikelcode' => 40123,
				'omschrijving' => 'Een artikel',
				'aantal' => 15,
				'prijs' => 7.23);

			$volgnummer = $periodiek_tbl->insert($periodiekeregel);

			$periodiekeregel = array(
				'volgnummer' => $volgnummer,
				'klantnummer' => 60001,
				'btw_percentage' => 6,
				'perioden' => array(
					array('maand' => 2),
					array('maand' => 8)),
				'laatstgefactureerd' => '2006/01/01',
				'artikelcode' => 40123,
				'omschrijving' => 'Een artikel',
				'aantal' => 12,
				'prijs' => 9.12);

			$db	   = $periodiek_tbl->getAdapter();
			$where = $db->quoteInto('volgnummer = ?', intval($volgnummer));

			$periodiek_tbl->update($periodiekeregel, $where);

			$result = $database->query("SELECT * FROM periodiekeregels WHERE volgnummer = " . $volgnummer);
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['volgnummer'], $volgnummer);
			$this->assertEquals($rows[0]['klantnummer'], 60001);
			$this->assertEquals($rows[0]['laatstgefactureerd'], '2006-01-01');
			$this->assertEquals($rows[0]['btw_percentage'], 6);

			unset($rows);
			unset($result);

			$result = $database->query('SELECT * FROM perioden WHERE periodiekeregel = ' . $volgnummer . ' ORDER BY maand');
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['maand'], 2);
			$this->assertEquals($rows[1]['maand'], 8);

			$this->assertEquals(count($rows), 2);
		}

		function testVerwerken()
		{
			$database = Zend_Registry::get('database');

			$periodiek_tbl = new CT_Db_Periodiekeregels();

			$periodiekeregel = array(
				'klantnummer' => 60001,
				'btw_percentage' => 19,
				'perioden' => array(
					array('maand' => 1),
					array('maand' => 6)),
				'laatstgefactureerd' => '2006/01/01',
				'artikelcode' => 40123,
				'omschrijving' => 'Maand = {$maand}, jaar = {$jaar}',
				'aantal' => 10,
				'prijs' => 4.23);

			$volgnummer = $periodiek_tbl->insert($periodiekeregel);

			$periodiek_tbl->processPeriodicInvoices('2008/01/01');

			$result = $database->query("SELECT * FROM factureren");
			$rows = $result->fetchAll();

			$this->assertEquals($rows[0]['omschrijving'], 'Maand = 6, jaar = 2006');
			$this->assertEquals($rows[1]['omschrijving'], 'Maand = 1, jaar = 2007');
			$this->assertEquals($rows[2]['omschrijving'], 'Maand = 6, jaar = 2007');
			$this->assertEquals($rows[3]['omschrijving'], 'Maand = 1, jaar = 2008');

			$this->assertEquals(count($rows), 4);
		}

	}
