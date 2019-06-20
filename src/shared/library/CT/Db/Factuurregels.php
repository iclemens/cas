<?php
	/**
	 * Provides an interface to the 'factuurregels' (invoice lines) table.
 	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Provides an interface to the 'factuurregels' (invoice lines) table.
	 * 
	 * @package CT_Db 
 	 */	
	class CT_Db_Factuurregels extends Zend_Db_Table
	{
		/**
		 * The name of the table.
		 * @var _name
		 */
		protected $_name = 'factuurregels';


		/**
		 * The primary key of the table.
		 * @var _primary
		 */
		protected $_primary = 'volgnummer';
	}
