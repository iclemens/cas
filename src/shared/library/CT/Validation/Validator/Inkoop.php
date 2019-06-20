<?php
	/**
	 * CT_Validator_Validator_Inkoop, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator');
	Zend_Loader::loadClass('CT_Validation_Errors');
	
	Zend_Loader::loadClass('CT_Db_Leveranciers');

	/**
	 * Valideert de gegevens van een inkomende factuur
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Inkoop extends CT_Validation_Validator
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
			$tbl_leveranciers	= new CT_Db_Leveranciers();
			$leverancier = $tbl_leveranciers->find($object['leveranciersnummer']);

			if($leverancier->volgnummer == null)
				$error->rejectValue('leveranciersnummer', 'Kies eerst een geldige leverancier');

			/* Datum */
			$this->rejectOnBlank(&$error, 'datum', $object['datum']);

			/* Kenmerk */
			$this->rejectOnBlank(&$error, 'kenmerk', $object['kenmerk']);
	
			if(strlen($object['kenmerk']) > 30)
				$error->rejectValue('kenmerk', 'Waarde is te lang (max. 30)');

			/* Controleer inkoop regels */
			$goede_regel = false;

			if(array_key_exists('regels', $object)) {
				foreach($object['regels'] as $nr => $regel) {
					if(($regel['aantal'] != 0 || $regel['prijs'] != 0) 
						&& strlen($regel['omschrijving']) == 0) {
							$error->reject("Omschrijving is verplicht wanneer een aantal of prijs is opgegeven");
					} 
				
					if(strlen($regel['omschrijving']) != 0)
						$goede_regel = true;				
				}
			}
			
			if($goede_regel == false)
				$error->reject("Er moet tenminste een factuurregel ingevuld zijn");
		}

	}
?>
