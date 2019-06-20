<?php
	/**
	 * CT_Validator_Validator_Periodiekeregel, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator');
	Zend_Loader::loadClass('CT_Validation_Errors');
	Zend_Loader::loadClass('Zend_Registry');

	Zend_Loader::loadClass('CT_Db_Artikelcodes');
	Zend_Loader::loadClass('CT_Db_Klanten');

	/**
	 * Valideert de gegevens van een periodiekeregel
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Periodiekeregel extends CT_Validation_Validator
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

			// Klantnummer
			$this->rejectOnBlank($error, 'klantnummer', $object['klantnummer']);

			$tbl_klanten	= new CT_Db_Klanten();
			$klant = $tbl_klanten->find($object['klantnummer'])->current();

			if($klant->klantnummer == null)
				$error->rejectValue('klantnummer', 'Kies eerst een geldige klant');
			
			if(count($object['perioden']) == 0)
				$error->rejectValue('perioden', 'Er zijn geen maanden geselecteerd');
		}
	}
?>
