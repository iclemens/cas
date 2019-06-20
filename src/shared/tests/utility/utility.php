<?php
	Zend_Loader::loadClass("CT_Db_Klanten");
	Zend_Loader::loadClass("CT_Db_Facturen");
	Zend_Loader::loadClass("CT_Db_Gebruikers");
	Zend_Loader::loadClass("CT_Db_Periodiekeregels");
	Zend_Loader::loadClass("CT_Db_Artikelcodes");

	function resetTable($table)
	{
		$database = Zend_Registry::get('database');

		$database->query('DELETE FROM ' . $table);
		$database->query('ALTER TABLE ' . $table . ' AUTO_INCREMENT = 0');
	}

	function resetAllTables()
	{
		resetTable("facturen");
		resetTable("factureren");
		resetTable("factuurregels");
		resetTable("artikelcodes");
		resetTable("gebruikers");
		resetTable("klanten");
		resetTable("perioden");
		resetTable("periodiekeregels");
	}

	function addSampleUsers()
	{
		$gebruikers = array(
			"directie" => array("gebruikersnaam" => "directie",
			"wachtwoord" => "a1a4537eb41fadfc8590ba6e58b0ce44714ef1383c53687ac367e34d5b36c481fbc818f0",
			"actief" => 1,
			"type" => 1),

		 	"administratie" => array("gebruikersnaam" => "administratie",
			"wachtwoord" => "5a37b2a2d9e15ffdfa8a6a414d10d98df8d67280a1b6a12ad07c1b1025b297220afa02d4",
			"actief" => 1,
			"type" => 2),

		 	"klant" => array("gebruikersnaam" => "60000",
			"wachtwoord" => "ce0c557ff3323d553bd4ca6b3b3cea0c1f182a0bbf82542d77afda0501a36fb5750ab67e",
			"actief" => 1,
			"type" => 3),

			"klant_inactief" => array("gebruikersnaam" => "60000",
			"wachtwoord" => "44ae50211fe5bde78391f3c64281b19dc427cc777af01dd43d62e7550ce7d0d51aa34621",
			"actief" => 0,
			"type" => 3),

			"klant_twee" => array("gebruikersnaam" => "60001",
			"wachtwoord" => "ce0c557ff3323d553bd4ca6b3b3cea0c1f182a0bbf82542d77afda0501a36fb5750ab67e",
			"actief" => 1,
			"type" => 3)
		);

		$tbl_gebruikers = new CT_Db_Gebruikers();
		foreach($gebruikers as $gebruiker) 
			$tbl_gebruikers->insert($gebruiker);
	}

	function addSampleArticleCodes()
	{
		$tbl_artikelcodes = new CT_Db_ArtikelCodes();

		$tbl_artikelcodes->insert(
			array("artikelcode" => "40123", "omschrijving" => "Artikel 1"));

		$tbl_artikelcodes->insert(
			array("artikelcode" => "40332", "omschrijving" => "Artikel 2"));
	}

	function addSampleCustomers()
	{
		$klanten = array(
			"Citrus-IT" => array("bedrijfsnaam" => "Citrus-IT",
			"aanhef" => "Dhr.",
			"voornaam" => "Cornelis",
			"achternaam" => "Van Der Steen",
			"factuuradres" => "Dorpsstraat 13",
			"factuurpostcode" => "0000 AB",
			"factuurplaats" => "Boxtel",
			"factuuremail" => "post@ivarclemens.nl",
			"bezoekadres" => "Redouteweg 11",
			"bezoekpostcode" => "0000 AB",
			"bezoekplaats" => "Boxtel",
			"actief" => 1,
			"klanttype" => 0,
			"emailadres" => "kees@citrus-it.nl",
			"website" => "http://www.citrus-it.nl",
			"telefoonvast" => "0411-123456",
			"telefoonmobiel" => "06-12345678"),

			"MijnBedrijf" => array("bedrijfsnaam" => "MijnBedrijf",
			"aanhef" => "Dhr.",
			"voornaam" => "Jan",
			"achternaam" => "Hendriks",
			"factuuradres" => "Stationsweg",
			"factuurpostcode" => "3212 AB",
			"factuurplaats" => "Roermond",
			"factuuremail" => "post@ivarclemens.nl",
			"bezoekadres" => "Stationsweg",
			"bezoekpostcode" => "1234 AB",
			"bezoekplaats" => "Roermond",
			"actief" => 1,
			"klanttype" => 0,
			"emailadres" => "post@ivarclemens.nl",
			"website" => "http://www.citrus-it.nl",
			"telefoonvast" => "0411-123456",
			"telefoonmobiel" => "06-92891871"));

		$tbl_klanten = new CT_Db_Klanten();
		foreach($klanten as $klant)
			$tbl_klanten->insert($klant);
	}

	function getRandomName($length)
	{
		$name = chr(rand(ord('A'), ord('Z')));

		for($i = 1; $i < $length; $i++)
			$name = $name . chr(rand(ord('a'), ord('z')));

		return $name;
	}

	function getRandomCustomer()
	{
		$customer = array(
			'aanhef' => 'Dhr.',
			'voornaam' => getRandomName(rand(5,10)),
			'achternaam' => getRandomName(rand(5, 10)),
			'factuuradres' => getRandomName(rand(7,20)) . ' ' . rand(1,100),
			'factuurpostcode' => '0000 AB',
			'factuurplaats' => getRandomName(rand(7,20)),
			'factuuremail' => 'post@ivarclemens.nl',
			'actief' => rand(0, 1),
			'bezoekadres' => getRandomName(rand(7,20)) . ' ' . rand(1,100),
			'bezoekpostcode' => '0000 AB',
			'bezoekplaats' => getRandomName(rand(7,20)));			

		if(rand(0,1) == 1) {
			$customer['klanttype'] = 0;
			$customer['bedrijfsnaam'] = getRandomName(rand(5,10));
		} else {
			$customer['klanttype'] = 1;	
		}

		return $customer;
	}
	
	function addRandomCustomers($amount)
	{
		$tbl_klanten = new CT_Db_Klanten();
		
		for($i = 0; $i < $amount; $i++) {
			echo($i . ' of ' . $amount . "\n");
			$tbl_klanten->insert(getRandomCustomer());
		}
	}

	function getRandomInvoice($maxBusinessId, $maxPrivateId)
	{
		if(rand(0,1) == 1)
			$customerId = rand(60001, $maxBusinessId);
		else
			$customerId = rand(65000, $maxPrivateId);

		$invoice = array(
			'klantnummer' => $customerId,
			'datum' => date("Y-m-d"),
			'uiterstedatum' => date("Y-m-d",strtotime("+1 months")),
			'korting' => rand(0, 100),
			'btw_percentage' => 19,
			'incasso' => 0,
			'tekst' => 0);
			
		$invoice['regels'] = array();
		
		for($i = 1; $i <= rand(1, 10); $i++) {
			$invoice['regels'][] = array(
				'factuurregel' => $i,
				'artikelcode' => rand(10000, 10002),
				'omschrijving' => getRandomName(5, 40),
				'aantal' => rand(1, 7),
				'prijs' => rand(100, 5000)
				);
		}

		return $invoice;			
	}
	
	function addRandomInvoices($amount)
	{
		$tbl_facturen = new CT_Db_Facturen();
		
		for($i = 0; $i < $amount; $i++) {
			echo($i . ' of ' . $amount . "\n");
			$tbl_facturen->insert(getRandomInvoice(60004, 65004));
		}				
	}
	
	function addRandomPeriodicInvoice()
	{
		$periodiek_tbl = new CT_Db_Periodiekeregels();

		$periodiekeregel = array(
			'klantnummer' => rand(60001, 60004),
			'btw_percentage' => rand(0, 19),
			'perioden' => array(
				array('maand' => rand(1,3)),
				array('maand' => rand(4,6)),
				array('maand' => rand(7,9)),
				array('maand' => rand(10,12))
				),
			'laatstgefactureerd' => date("Y-m-d",strtotime("-1 months")),
			'artikelcode' => rand(10000, 10002),
			'omschrijving' => getRandomName(5, 40),
			'aantal' => rand(1, 7),
			'prijs' => rand(100, 5000));

		$periodiek_tbl->insert($periodiekeregel);
	}
	
	function addRandomPeriodicInvoices($amount)
	{
		for($i = 0; $i < $amount; $i++) {
			echo($i . ' of ' . $amount . "\n");
			addRandomPeriodicInvoice();
		}		
	}
