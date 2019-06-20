<?php
	/**
	 * CT_Validator_Validator_Urenregistratie, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator');
	Zend_Loader::loadClass('CT_Validation_Errors');
	
	Zend_Loader::loadClass('CT_Db_Klanten');

	/**
	 * Valideert de gegevens van een urenregistratie
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Urenregistratie extends CT_Validation_Validator
	{
		public function supports($class)
		{
			if(is_array($class))
				return true;
			return false;
		}

		public function validate(&$object, &$error)
		{
			/* Leveranciersnummer */
			$this->rejectOnBlank(&$error, 'klantnummer', $object['klantnummer']);

			$tbl_klanten	= new CT_Db_Klanten();
			$klant = $tbl_klanten->find($object['klantnummer']);

			if($klant->klantnummer == null)
				$error->rejectValue('klantnummer', 'Kies eerst een geldige klant');

			/* Datum */
			$this->rejectOnBlank(&$error, 'datum', $object['datum']);

			/* Uren */
			if($object['minuten'] < 0)
				$error->rejectValue('minuten', 'Ongeldige waarde (min. 0)');

			if($object['minuten'] >= 60)
				$error->rejectValue('minuten', 'Ongeldige waarde (max. 59)');

			if($object['uren'] < 0)
				$error->rejectValue('uren', 'Ongeldige waarde (min. 0)');

			if($object['uren'] > 24)
				$error->rejectValue('uren', 'Ongeldige waarde (max. 24)');

			if($object['uren'] * 60 + $object['minuten'] <= 0) {
				$error->rejectValue('uren', 'Geen uren (min. 1 minuut)');
				$error->rejectValue('minuten', 'Geen uren (min. 1 minuut)');
			}

			if($object['uren'] * 60 + $object['minuten'] > 3600) {
				$error->rejectValue('uren', 'Combinatie te groot (max. 3600 minuten)');
				$error->rejectValue('minuten', 'Combinatie te groot (max. 3600 minuten)');
			}
		}
	}
?>
