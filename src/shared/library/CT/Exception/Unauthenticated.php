<?php
	/**
	 * Exception_Unauthenticated, Citrus-IT Online Boekhouding
	 *
	 * Thrown when the user not authenticated (but should be)
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @copyright  2007 Ivar Clemens
	 * @package    boekhouding
	 */

	Zend_Loader::loadClass("CT_Exception_Unauthorized");

	/**
	 * Thrown when the user not authenticated (but should be)
	 */
	class CT_Exception_Unauthenticated extends CT_Exception_Unauthorized
	{
	}
