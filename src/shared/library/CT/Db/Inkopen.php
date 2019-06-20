<?php
	/**
	 * Abstractie van de 'inkopen' database tabel
 	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Abstractie van de 'inkopen' database tabel
	 *
	 * @package CT_Db 
 	 */
	class CT_Db_Inkopen extends Zend_Db_Table 
	{ 
		/**
     * De naam van de tabel
     *
     * @var _name
     */
		protected $_name = 'inkopen';


		/**
     * De primaire sleutel van de inkopen tabel
     *
     * @var _primary
     */
		protected $_primary = 'volgnummer';
	}
?>
