<?php
	/**
	 * Provides an interface to the 'gebruikers' (users) table.
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Provides an interface to the 'gebruikers' (users) table.
	 * 
	 * @package CT_Db 
	 */
	class CT_Db_Gebruikers extends Zend_Db_Table
	{
		/**
     * The name of the table.
     * @var _name
     */
		protected $_name = 'gebruikers';

		/**
     * The primary key of the table.
     * @var _primary
     */
		protected $_primary = 'volgnummer';
	}
?>
