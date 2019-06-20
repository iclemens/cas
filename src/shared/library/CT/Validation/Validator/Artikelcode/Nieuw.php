<?php
	/**
	 * CT_Validator_Validator_Artikelcode_Nieuw, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator_Artikelcode');
	Zend_Loader::loadClass('CT_Db_Artikelcodes');

	/**
	 * Valideert de gegevens van een nieuwe gebruiker
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Artikelcode_Nieuw extends CT_Validation_Validator_Artikelcode
	{
		public function validate(&$object, &$error)
		{
			parent::validate($object, $error);

			$this->rejectOnBlank($error, 'artikelcode', $object['artikelcode']);

			if(strlen($object['artikelcode']) > 5)
				$error->rejectValue('artikelcode', 'Waarde is te lang (max. 5)');

			// Is de artikelcode uniek?
			$table = new CT_Db_Artikelcodes();
			$db 	 = $table->getAdapter();
			$where = $db->quoteInto('artikelcode = ?', $object['artikelcode']);

			$artikelcodes = $table->fetchAll($where, null, null, null);

			if(count($artikelcodes->toArray()) != 0)
				$error->rejectValue('artikelcode', 'Waarde moet uniek zijn');
		}
	}
