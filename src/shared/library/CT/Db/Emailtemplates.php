<?php
	/**
	 * Provides an interface to the 'emailtemplates' table.
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Provides an interface to the 'emailtemplates' table.
	 */
	class CT_Db_Emailtemplates extends Zend_Db_Table 
	{ 
		/**
		 * The name of the table.
		 * @var _name
		 */
		protected $_name = 'emailtemplates';


		/**
		 * The primary key of the table.
		 * @var _primary
		 */
		protected $_primary = 'volgnummer';


		/**
		 * Haalt een lijst met templates op.
		 *
		 * @return array
		 */
		static public function lijstMetTemplates()
		{
			$result = Zend_Registry::get('database')->query(
				"SELECT volgnummer, omschrijving " .
				" FROM emailtemplates ORDER BY omschrijving");

			$templates = array();

			foreach($result->fetchAll() as $row)
				$templates[$row['volgnummer']] = $row['omschrijving'];

			return $templates;
		}
	}
