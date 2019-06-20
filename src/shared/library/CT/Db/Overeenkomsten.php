<?php
	/**
	 * Abstractie van de 'overeenkomsten' database tabel
 	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Abstractie van de 'overeenkomsten' database tabel
	 * 
	 * @package CT_Db 
 	 */
	class CT_Db_Overeenkomsten extends Zend_Db_Table 
	{ 
		/**
     * De naam van de tabel
     *
     * @var _name
     */
		protected $_name = 'overeenkomsten';


		/**
     * De primaire sleutel van de leveranciers tabel
     *
     * @var _primary
     */
		protected $_primary = 'volgnummer';
	}
?>
