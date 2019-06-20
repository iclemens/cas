<?php
	/**
	 * Abstractie van de 'leveranciers' database tabel
 	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Abstractie van de 'leveranciers' database tabel
	 * 
	 * @package CT_Db 
 	 */
	class CT_Db_Leveranciers extends Zend_Db_Table 
	{ 
		/**
     * De naam van de tabel
     *
     * @var _name
     */
		protected $_name = 'leveranciers';


		/**
     * De primaire sleutel van de leveranciers tabel
     *
     * @var _primary
     */
		protected $_primary = 'volgnummer';
	}
?>
