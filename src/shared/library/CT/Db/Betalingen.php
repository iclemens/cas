<?php
	/**
	 * Provides an interface to the 'betalingen' (payments) table.
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Provides an interface to the 'betalingen' (payments) table.
	 * 
	 * @package CT_Db 
	 */
	class CT_Db_Betalingen extends Zend_Db_Table 
	{ 
		/**
     * The name of the table.
     * @var _name
     */
		protected $_name = 'betalingen';


		/**
     * The primary key of the table.
     * @var _primary
     */
		protected $_primary = 'volgnummer';
	}
?>
