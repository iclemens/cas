<?php
	/**
	 * Abstractie van de 'klanten' database tabel
 	 *
 	 * @author     Ivar Clemens <ivar@citrus-it.nl>
 	 * @copyright  2008 Ivar Clemens
	 * @package    CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');
	Zend_Loader::loadClass('Zend_Registry');

	/**
	 * Abstractie van de 'klanten' database tabel
 	 *
	 * @package    CT_Db
	 */
	class CT_Db_Klanten extends Zend_Db_Table
	{
		/**
		 * De naam van de tabel
		 *
		 * @var _name
		 */
		protected $_name = 'klanten';
		
		/**
		 * De primaire sleutel van de facturen tabel
		 *
		 * @var _primary
		 */
		protected $_primary = 'klantnummer';

		/**
		 * Bepaalt het eerst volgende factuur nummer
		 *
		 * @param int $klant_type  0 voor zakelijke, 1 voor particulier
		 */
		private function _volgendKlantnummer($klant_type) 
		{
			$db = $this->getAdapter();
			$config = Zend_Registry::get('config');



			if($klant_type == 0) {
				$type_min = intval($config->customer->first_business_id);
				$type_max = intval($config->customer->first_private_id) - 1;
			} else {
				$type_min = intval($config->customer->first_private_id);
				$type_max = intval($config->customer->first_business_id) - 1;
			}

			if($type_max <= $type_min)
				$type_max = 99999;

			$rows = $db->fetchAll("SELECT klantnummer + 1 AS volgendnummer " .
				"FROM klanten WHERE " .
				"klantnummer >= :type_min AND klantnummer <= :type_max " .
				"ORDER BY klantnummer DESC LIMIT 1",
				array("type_min" => intval($type_min), 
					    "type_max" => intval($type_max)));

			/* If there are no entries in the database, start with the
					minimum id specified */
			if(count($rows) != 1)
				return $type_min;

			$row = $rows[0];
			return $row['volgendnummer'];
		}

		/**
		 * Voegt een nieuwe klant toe aan de database.
		 *
		 * @param array @klant Gegevens van de nieuwe klant.
		 */
	  public function insert(array $klant)
		{
			$db = $this->getAdapter();

			$db->query('SET AUTOCOMMIT = 0');
	    //$db->query('LOCK TABLES klanten');

			$db->beginTransaction();

			try {
				$klant['klantnummer'] = $this->_volgendKlantnummer($klant['klanttype']);
				$volgnummer = parent::insert($klant);

				$db->commit();
				//$db->query('UNLOCK TABLES');
				$db->query('SET AUTOCOMMIT=1');
			} catch (Exception $e) {
				$db->rollBack();
				//$db->query('UNLOCK TABLES');
				$db->query('SET AUTOCOMMIT=1');
				throw $e;
			}

			return $volgnummer;
		}

		/**
		 * Haalt een lijst met klanten op.
		 *
		 * @param bool $niet_actieve
		 *
		 * @return array
		 */
		static public function lijstMetKlanten($niet_actieve = false)
		{
			if($niet_actieve == false)
				$actief = "WHERE actief = 1 ";
			else
				$actief = '';

			$result = Zend_Registry::get('database')->query(
				"SELECT klanttype, klantnummer, bedrijfsnaam, " .
				"voornaam, achternaam, actief, aanhef FROM klanten ${actief}ORDER BY klanttype, " .
				"bedrijfsnaam, voornaam, achternaam");

			$klanten = array();

			foreach($result->fetchAll() as $row)
				$klanten[] = $row;

			return $klanten;
		}
	}
?>
