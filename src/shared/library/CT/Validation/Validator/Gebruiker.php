<?php
	/**
	 * CT_Validator_Validator_Gebruiker, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator');
	Zend_Loader::loadClass('CT_Validation_Errors');

	/**
	 * Valideert de gegevens van een gebruiker
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Gebruiker extends CT_Validation_Validator
	{
		public function supports($class)
		{
			if(is_array($class))
				return true;
			return false;
		}

		public function validate(&$object, &$error)
		{
			// Wachtwoord
			if($object['wachtwoord'] != $object['wachtwoord2'])
				$error->reject('De wachtwoorden komen niet overeen');

			if($object['wachtwoord'] != '')
				if(self::isStrongPassword($object['wachtwoord']))
					$error->rejectValue('wachtwoord', 'Wachtwoord is niet sterk genoeg');

			// Actief
			if($object['actief'] != 0 && $object['actief'] != 1)
				$error->rejectValue('actief', 'Waarde moet 0 of 1 zijn');

			// Type
			if($object['type'] != 1 && $object['type'] != 2 && $object['type'] != 3)
				$error->rejectValue('type', 'Waarde moet 1, 2 of 3 zijn');		
		}
	}
?>
