<?php
	/**
	 * Abstractie van de 'urenregistratie' database tabel
 	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Abstractie van de 'urenregistratie' database tabel
	 * 
	 * @package CT_Db 
 	 */
	class CT_Db_Urenregistratie extends Zend_Db_Table 
	{ 
		/**
     * De naam van de tabel
     *
     * @var _name
     */
		protected $_name = 'urenregistratie';


		/**
     * De primaire sleutel van de urenregistratie tabel
     *
     * @var _primary
     */
		protected $_primary = 'volgnummer';
	}
?>
