<?php
	/**
	 * OnderhoudController, Project CAS
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Controller_Action');
	Zend_Loader::loadClass('CT_Db_Versie');
	Zend_Loader::loadClass('CT_Initialize');

	/**
	 * Handles user-management related tasks.
	 * 
	 * @package Controllers 
	 */
	class OnderhoudController extends CT_Controller_Action 
	{

		/**
		 * The default page, redirects to lijstAction
		 */
		public function indexAction() 
		{
		}


		/**
		 * Database bijwerkend
		 */
		public function bijwerkenAction()
		{
			$this->requireUserType(array(CT_User::Directie));			

			$config = Zend_Registry::get('config');
			$update = CT_Initialize::factory($config->database->type);
							
			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$update->applyAllPatches();
				$infoMessage = 'Het updaten van de database is voltooid.';
			} else {
				$currentVersion = CT_Db_Versie::getVersion();
				$patchList = $update->listAvailableUpdates($currentVersion);
				$infoMessage = '';

				$this->_smarty->assign('patches', $patchList);				
			}

			$this->_smarty->assign('infomessage', $infoMessage);
			
			/* Request_method is not POST, or invalid form contents */
			$this->getResponse()->appendBody(
				$this->_smarty->fetch('onderhoud/bijwerken.tpl'));		
		}


		/**
		 * Invalid action, return to main page.
		 */ 
		public function noRouteAction()
		{
			$this->redirect('/');
		}

	}
