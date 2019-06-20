<?php
  Zend_Loader::loadClass('Zend_Controller_Request_Http');
  Zend_Loader::loadClass('Zend_Controller_Response_Http');
	Zend_Loader::loadClass('CT_Smarty');
	Zend_Loader::loadClass('CT_User');

	require_once 'PHPUnit/Framework.php';

	class CT_Controller_TestCase extends CT_Db_TestCase {

		public function initController() {
			$smarty = new CT_Smarty();
			Zend_Registry::set("smarty", $smarty);

			$user = CT_User::instance($database);
			//$user->
		}
	}

?>
