<?php
	/**
	 * IndexController, Project CAS
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Db_Klanten');
	Zend_Loader::loadClass('CT_Controller_Action');

	/**
	 * Handles global pages such as the dashboard.
	 * 
	 * @package    Controllers 
	 */
	class IndexController extends CT_Controller_Action 
	{

		/**
		 * This functions displays the dashboard.
		 */
		public function indexAction() 
		{
			$this->requireValidUser();

			$this->_smarty->assign('klanten', $this->getQueueItems());
			$this->_smarty->display('index.tpl');
		}

		public function wachtrijAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$this->_smarty->assign('klanten', $this->getQueueItems());
			$this->_smarty->display('wachtrij.tpl');
		}

		private function getQueueItems()
		{
			$query = "SELECT klanten.*, COUNT(*) AS aantalregels FROM factureren, klanten " .
					"WHERE klanten.klantnummer = factureren.klantnummer " .
					"GROUP BY factureren.klantnummer ORDER BY bedrijfsnaam, achternaam";

			$result = Zend_Registry::get('database')->query($query);

			return $result->fetchAll();
		}

		/**
		 * This is the fallback action, just redirects to /
		 */
		public function noRouteAction()
		{
			$this->redirect('/');
		}

	}

?>
