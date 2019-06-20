<?php
	/**
	 * CT_Validator_Validator_Factuur, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator');
	Zend_Loader::loadClass('CT_Validation_Errors');

	Zend_Loader::loadClass('CT_Db_Artikelcodes');
	Zend_Loader::loadClass('CT_Db_Klanten');

	/**
	 * Valideert een factuur
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Factuur extends CT_Validation_Validator
	{
		public function supports($class)
		{
			if(is_array($class))
				return true;
			return false;
		}

		public function validate(&$object, &$error)
		{
			// Klantnummer
			$this->rejectOnBlank($error, 'klantnummer', $object['klantnummer']);

			$tbl_klanten	= new CT_Db_Klanten();
			$klant = $tbl_klanten->find($object['klantnummer'])->current();

			if($klant == null || $klant->klantnummer == null)
				$error->rejectValue('klantnummer', 'Kies eerst een geldige klant');

			// Datum
			$this->rejectOnBlank($error, 'datum', $object['datum']);

			// Regels...
			$goede_regel = false;

			if(array_key_exists('regels', $object)) {
				foreach($object['regels'] as $nr => $regel) {

					// Aantal / Prijs / Omschrijving
					if(($regel['aantal'] != 0 || $regel['prijs'] != 0) 
						&& strlen($regel['omschrijving']) == 0) {
							$error->reject("Omschrijving is verplicht wanneer een aantal of prijs is opgegeven");
					} 
				
					if(strlen($regel['omschrijving']) != 0) {
						// Artikelcode
						$tbl_artikelcodes = new CT_Db_Artikelcodes();
						$artikelcode = $tbl_artikelcodes->find($regel['artikelcode'])->current();
	
						if($artikelcode->artikelcode == null)
							$error->reject('Artikelcode "' . htmlentities($regel['artikelcode']) . '" is ongeldig');

						// This is a valid line, each invoice needs at least one...
						$goede_regel = true;
					}					
				}
			}
			
			if($goede_regel == false)
				$error->reject("Er moet tenminste een factuurregel ingevuld zijn");
		}
	}
