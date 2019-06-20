<?php
	/**
	 * CT_Validator_Validator_Gebruiker_Nieuw, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator_Gebruiker');
	Zend_Loader::loadClass('CT_Db_Gebruikers');

	/**
	 * Valideert de gegevens van een nieuwe gebruiker
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Gebruiker_Nieuw extends CT_Validation_Validator_Gebruiker
	{
		public function validate(&$object, &$error)
		{
			parent::validate($object, $error);

			// Gebruikersnaam
			$this->rejectOnBlank($error, 'gebruikersnaam', $object['gebruikersnaam']);

			if(strlen($object['gebruikersnaam']) > 30)
				$error->rejectValue('gebruikersnaam', 'Waarde is te lang (max. 30)');

			// Is de gebruiker uniek? 
			$table = new CT_Db_Gebruikers();
			$db 	 = $table->getAdapter();
			$where = $db->quoteInto('gebruikersnaam = ?', $object['gebruikersnaam']);

			$gebruikers = $table->fetchAll($where, null, null, null);

			if(count($gebruikers->toArray()) != 0)
				$error->rejectValue('gebruikersnaam', 'Waarde moet uniek zijn');

			// Is dit een klant/gebruiker?
			if(preg_match('/^ct[0-9][0-9][0-9][0-9][0-9]$/', $object['gebruikersnaam']))
				$error->rejectValue('gebruikersnaam', 'Waarde lijkt op een klantnummer');

			// Type
			if($object['type'] != 1 && $object['type'] != 2)
				$error->rejectValue('type', 'Waarde moet 1 of 2 zijn');		

		}
	}
?>
