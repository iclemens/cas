<?php
	/**
	 * Provides an interface to the 'versie' table.
 	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');

	/**
	 * Provides an interface to the 'versie' table.
	 * 
	 * @package CT_Db 
 	 */	
	class CT_Db_Versie extends Zend_Db_Table
	{
		/**
		 * The name of the table.
		 * @var _name
		 */
		protected $_name = 'versie';


		/**
		 * The primary key of the table.
		 * @var _primary
		 */
		protected $_primary = array('versie');
		
		
		/**
		 * Retreives the current version.
		 * 
		 * @return string Current version.
		 */
		public static function getVersion()
		{
			$versie_db = new CT_Db_Versie();
			$row = $versie_db->fetchRow();			

			return $row->versie;
		}


		/**
		 * Updates the version information.
		 * 
		 * @param string $versie Version
		 */
		public static function setVersion($versie)
		{
			$versie_db = new CT_Db_Versie();
			$versie_db->update(array('versie' => $versie), 'versie = versie');
		}
	}
