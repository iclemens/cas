<?php
	/**
	 * SessieController, Project CAS
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Controller_Action');
	Zend_Loader::loadClass('CT_Initialize');
	Zend_Loader::loadClass('CT_Db_Versie');

	/**
	 * De sessie module is verantwoordelijk voor het aan- en
	 * afmelden van gebruikers via de web-interface.
	 * 
 	 * @package Controllers
	 */
	class SessieController extends CT_Controller_Action 
	{

		public function indexAction() 
		{
			if($this->_user->isLoggedIn())
				$this->redirect('/');
	
			$this->_smarty->display('login.tpl');
		}

		public function inloggenAction()
		{
			$gebruikersnaam = $_POST['gebruikersnaam'];
			$wachtwoord = $_POST['wachtwoord'];

			$resultaat = $this->_user->login($gebruikersnaam, $wachtwoord);

			if($this->_user->isLoggedIn()) {
				$config = Zend_Registry::get('config');
				
				$initialization = CT_Initialize::factory(
					$config->database->type);
				
				if(count($initialization->listAvailableUpdates(CT_Db_Versie::getVersion())) > 0) {
					$this->redirect('/onderhoud/bijwerken');
				} else {
					$this->redirect('/');
				}
				
				return;
			}

			// The login process failed, display an error message
			$logger = Zend_Registry::get("logger");
			$logger->log("[Sessie] Ongeldige gebruiker \"" . $gebruikersnaam . 
				"\" van " . $_SERVER["REMOTE_ADDR"], Zend_Log::NOTICE);

			$fouten = array();

			if($resultaat == false) {
				if(strlen($gebruikersnaam) != 0 && strlen($wachtwoord) != 0)
					$this->_smarty->assign("login_failed", true);
				if(strlen($gebruikersnaam) == 0)
					$fouten['gebruikersnaam'] = 'De gebruikersnaam is niet ingevuld';
				if(strlen($wachtwoord) == 0)
					$fouten['wachtwoord'] = 'Het wachtwoord is niet ingevuld';
			}

			$this->_smarty->assign("gebruikersnaam", $gebruikersnaam);
			$this->_smarty->assign("wachtwoord", $wachtwoord);

			$this->_smarty->assign("fouten", $fouten);
			$this->_smarty->display('login.tpl');
		}

		public function uitloggenAction()
		{
			$this->_user->logout();
			$this->_smarty->setUserType(0);
			$this->_smarty->display('logout.tpl');
		}

		public function noRouteAction()
		{
			$this->redirect('/');
		}

	}
?>
