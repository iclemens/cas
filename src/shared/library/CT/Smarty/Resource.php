<?php
	/**
	 * Smarty resource voor CAS
 	 *
 	 * @author     Ivar Clemens <post@ivarclemens.nl>
 	 * @copyright  2007 Ivar Clemens
	 * @package    boekhouding
	 */

	class CT_Smarty_Resource extends Smarty_Resource_Custom {

		/**
		 * Returns the template source of the $name resource.
		 *
		 * @param string $name Name of the resource
		 * @param string $source Source of the template
		 * @param Smarty Smarty instance
		 *
		 * @return bool True on success, false otherwise
		 */
		protected function fetch($name, &$source, &$mtime)
		{
			try {
				$config = Zend_Registry::get('config');

				$filename = getResourceLocation(joinDirStrings($config->templates, $name));
				$source = file_get_contents($filename);

				return true;
			} catch(Exception $e) {
				return false;
			}
		}

		/**
		 * Returns the timestamp of the $name resource.
		 *
		 * @param string $name Name of the resource
		 * @param int $timestamp Unix timestamp
		 * @param Smarty Smarty instance
		 *
		 * @return bool True on success, false otherwise
		 */
		protected function fetchTimestamp($name)
		{
			try {
				$config = Zend_Registry::get('config');

				$filename = getResourceLocation(joinDirStrings($config->templates, $name));
				$timestamp = filemtime($filename);

				return true;
			} catch(Exception $e) {
				return false;
			}
		}
	}

