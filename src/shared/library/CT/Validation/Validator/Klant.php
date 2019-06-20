<?php
	/**
	 * CT_Validator_Validator_Klant, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator');
	Zend_Loader::loadClass('CT_Validation_Errors');
	Zend_Loader::loadClass('Zend_Registry');

	/**
	 * Valideert de gegevens van een klant
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Klant extends CT_Validation_Validator
	{
		public function supports($class)
		{
			if(is_array($class))
				return true;
			return false;
		}

		public function validate(&$object, &$error)
		{
			$config = Zend_Registry::get('config');

			// Bedrijfsnaam
			if($object['klanttype'] == 0 && self::isBlank($object['bedrijfsnaam']))
				$error->rejectValue('bedrijfsnaam', 'Waarde is verplicht voor zakelijke klanten');

			if($object['klanttype'] == 1) {
				if(!self::isBlank($object['bedrijfsnaam']))
					$error->rejectValue('bedrijfsnaam', 'Waarde alleen van toepassing op zakelijke klanten');

				if(array_key_exists('afdeling', $object) && !self::isBlank($object['afdeling']))
					$error->rejectValue('afdeling', 'Waarde is alleen van toepassing op zakelijke klanten');

				if(self::isblank($object['aanhef']))
					$error->rejectValue('aanhef', 'Waarde is verplicht voor particulieren');

				if(self::isblank($object['voornaam']))
					$error->rejectValue('voornaam', 'Waarde is verplicht voor particulieren');

				if(self::isblank($object['achternaam']))
					$error->rejectValue('achternaam', 'Waarde is verplicht voor particulieren');
			}

			if(!self::isBlank($object['aanhef']))
				if(self::isBlank($object['achternaam']))
					$error->rejectValue('achternaam', 'Waarde is verplicht als een aanhef is opgegeven');			

			if(strlen($object['bedrijfsnaam']) > 60)
				$error->rejectValue('bedrijfsnaam', 'Waarde is te lang (max. 60)');

			// Aanhef
			if(strlen($object['aanhef']) > 10)
				$error->rejectValue('aanhef', 'Waarde is te lang (max. 10)');
			
			// Voornaam
			if(strlen($object['voornaam']) > 30)
				$error->rejectValue('voornaam', 'Waarde is te lang (max. 30)');

			// Achternaam
			if(strlen($object['achternaam']) > 30)
				$error->rejectValue('achternaam', 'Waarde is te lang (max. 30)');

			// BTW Nummer
			if(self::getLengthOfArrayValue($object, 'btwnummer') > 14)
				$error->rejectValue('btwnummer', 'Waarde is te lang (max. 14)');

			if(array_key_exists('btwgecontroleerd', $object) && $object['btwgecontroleerd'])
				$this->rejectOnBlank($error, 'btwnummer', $object['btwnummer']);			

			// Factuuradres
			$this->rejectOnBlank($error, 'factuuradres', $object['factuuradres']);
	
			if(strlen($object['factuuradres']) > 60)
				$error->rejectValue('factuuradres', 'Waarde is te lang (max. 60)');

			if(self::getLengthOfArrayValue($object, 'factuuradres2') > 60)
				$error->rejectValue('factuuradres2', 'Waarde is te lang (max. 60)');

			// Factuurpostcode
			$this->rejectOnBlank($error, 'factuurpostcode', $object['factuurpostcode']);
	
			if(strlen($object['factuurpostcode']) > 10)
				$error->rejectValue('factuurpostcode', 'Waarde is te lang (max. 10)');

			// Factuurplaats
			$this->rejectOnBlank($error, 'factuurplaats', $object['factuurplaats']);
	
			if(strlen($object['factuurplaats']) > 60)
				$error->rejectValue('factuurplaats', 'Waarde is te lang (max. 60)');

			// Factuurland
			if(self::getLengthOfArrayValue($object, 'factuurland') > 60)
				$error->rejectValue('factuurland', 'Waarde is te lang (max. 60)');

			// Factuuremail
			if(self::getLengthOfArrayValue($object, 'factuuremail') > 60)
				$error->rejectValue('factuuremail', 'Waarde is te lang (max. 60)');

			if(array_key_exists('factuuremail', $object) && !self::isBlank($object['factuuremail']))
				if(!self::isEmail($object['factuuremail'], true))
					$error->rejectValue('factuuremail', 'Waarde voldoet niet aan RFC 2822');

			// Factuurtemplate
			if(self::getLengthOfArrayValue($object, 'factuurtemplate') > 75)
				$error->rejectValue('factuurtemplate', 'Waarde is te lang (max. 75)');

			if(self::getLengthOfArrayValue($object, 'factuurtemplate') > 0) {
				$templateFile = null;
				try {
					$templateFile = getResourceLocation(joinDirStringArray(array(
						$config->templates, 'factuur', $object['factuurtemplate'])));
				} catch(Exception $e) {
					$error->rejectValue('factuurtemplate', 'Kan template niet vinden');
				}

				if(!file_exists($templateFile))
					$error->rejectValue('factuurtemplate', 'Kan template niet vinden');
			}

			// Bezoekadres
			$this->rejectOnBlank($error, 'bezoekadres', $object['bezoekadres']);
	
			if(strlen($object['bezoekadres']) > 60)
				$error->rejectValue('bezoekadres', 'Waarde is te lang (max. 60)');

			if(self::getLengthOfArrayValue($object, 'bezoekadres2') > 60)
				$error->rejectValue('bezoekadres2', 'Waarde is te lang (max. 60)');


			// Bezoekpostcode
			$this->rejectOnBlank($error, 'bezoekpostcode', $object['bezoekpostcode']);
	
			if(strlen($object['bezoekpostcode']) > 10)
				$error->rejectValue('bezoekpostcode', 'Waarde is te lang (max. 7)');

			// Bezoekplaats
			$this->rejectOnBlank($error, 'bezoekplaats', $object['bezoekplaats']);
	
			if(strlen($object['bezoekplaats']) > 60)
				$error->rejectValue('bezoekplaats', 'Waarde is te lang (max. 60)');

			// Bezoekland
			if(self::getLengthOfArrayValue($object, 'bezoekland') > 60)
				$error->rejectValue('bezoekland', 'Waarde is te lang (max. 60)');

			// Actief
			if($object['actief'] != 0 && $object['actief'] != 1)
				$error->rejectValue('actief', 'Waarde moet 0 of 1 zijn');

			// Klanttype
			if($object['klanttype'] != 0 && $object['klanttype'] != 1)
				$error->rejectValue('klanttype', 'Waarde moet 0 of 1 zijn');
			
			// E-mailadres
			if(strlen($object['emailadres']) > 60)
				$error->rejectValue('emailadres', 'Waarde is te lang (max. 60)');
			
			if(!self::isBlank($object['emailadres']))
				if(!self::isEmail($object['emailadres'], false))
					$error->rejectValue('emailadres', 'Waarde voldoet niet aan RFC 2822');

			// Website
			if(strlen($object['website']) > 60)
				$error->rejectValue('website', 'Waarde is te lang (max. 60)');
			
			// Check for a valid URL

			// Telefoonvast
			if(strlen($object['telefoonvast']) > 11)
				$error->rejectValue('telefoonvast', 'Waarde is te lang (max. 11)');

			if(!self::isBlank($object['telefoonvast']))			
				if(!self::isPhonenumber($object['telefoonvast']))
					$error->rejectValue('telefoonvast', 'Waarde is geen telefoonnummer');

			// Telefoonmobiel
			if(strlen($object['telefoonmobiel']) > 11)
				$error->rejectValue('telefoonmobiel', 'Waarde is te lang (max. 11)');

			if(!self::isBlank($object['telefoonmobiel']))			
				if(!self::isPhonenumber($object['telefoonmobiel']))
					$error->rejectValue('telefoonmobiel', 'Waarde is geen telefoonnummer');
		}
	}

