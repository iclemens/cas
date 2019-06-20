<?php
	/**
	 * Provides an interface to the 'artikelcodes' (article codes) table.
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Provides an interface to the 'artikelcodes' (article codes) table.
	 * 
	 * @package CT_Db 
	 */
	class CT_Db_Artikelcodes extends Zend_Db_Table 
	{ 
		/**
     * The name of the table.
     * @var _name
     */
		protected $_name = 'artikelcodes';


		/**
     * The primary key of the table.
     * @var _primary
     */
		protected $_primary = 'artikelcode';
	}
