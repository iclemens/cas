<?php
	/**
	 * Provides an interface to the 'perioden' table.
 	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Provides an interface to the 'perioden' table.
	 * 
	 * @package CT_Db 
 	 */	
	class CT_Db_Perioden extends Zend_Db_Table
	{
		/**
		 * The name of the table.
		 * @var _name
		 */
		protected $_name = 'perioden';


		/**
		 * The primary key of the table.
		 * @var _primary
		 */
		protected $_primary = array('periodiekeregel', 'maand');
	}
