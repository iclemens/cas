<?php
	/**
	 * Smarty uitbreiding voor KAS met ondersteuning voor strings
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @copyright  2007 Ivar Clemens
	 * @pacakge    boekhouding
	 */

	Zend_Loader::loadClass('CT_Smarty');

	/**
	 * Smarty uitbreiding voor KAS met ondersteuning voor strings
	 */
	class CT_Smarty_String extends CT_Smarty
	{
		function __construct(&$config, &$formatter)
		{
			parent::__construct($config, $formatter);
		}

	  /**
		 * FIXME: This should be merged with CT_Smarty
		 *  Add a function fetchStr or something..
		 */ 
		function fetch($str=NULL, $cache_id=NULL, $compile_id=NULL, $parent=NULL)
		{			
			if($str == '')
				return '';
			
			return parent::fetch('eval:' . $str);
		}
 
	}

