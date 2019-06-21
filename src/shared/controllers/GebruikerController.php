<?php
	/**
	 * GebruikerController, Project CAS
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Db_Gebruikers');
	Zend_Loader::loadClass('CT_Controller_Action');
	Zend_Loader::loadClass('CT_Validation_Validator_Gebruiker');
	Zend_Loader::loadClass('CT_Validation_Validator_Gebruiker_Nieuw');

	/**
	 * Handles user-management related tasks.
	 * 
	 * @package    Controllers
	 */
	class GebruikerController extends CT_Controller_Action 
	{

		/**
		 * The default page, redirects to lijstAction
		 */
		public function indexAction() 
		{
			$this->lijstAction();
		}


		/**
		 * Displays a list containing all users.
		 */
		public function lijstAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			try {

				$table = new CT_Db_Gebruikers();
				$rowset = $table->fetchAll(null, array('actief', 'type', 'gebruikersnaam'), null, null);
				$this->_smarty->assign("gebruikers", $rowset->toArray());

			} catch (Exception $e) {

				// Database query failed, write log message and bail
				$this->_smarty->assign("message", "Kan de lijst met gebruikers niet ophalen.");
				$this->_smarty->display('error.tpl');

				Zend_Log::log("[Gebruiker] Database query failed: " . $e->getMessage(), Zend_Log::LEVEL_SEVERE);

				return;
			}

			$this->_smarty->assign('gebruiker_id', strval($this->_user->getUserId()));

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('gebruiker/lijst.tpl'));
		}


		/**
		 * Displays a form for creating a new user.
		 */
		public function nieuwAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('gebruiker/nieuw.tpl'));
		}


		/**
		 * Displays a form for editing an existing user.
	   * Requires the 'id' parameter to be a valid userid.
		 */
		public function bewerkAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			try {
				$table = new CT_Db_Gebruikers();
				$gebruikers_obj = $table->find(intval($this->_getParam('id')));
				$gebruikers_ary = $gebruikers_obj->toArray();
				$gebruiker = $gebruikers_ary[0];
			} catch (Exception $e) {
				$this->_smarty->assign("message", $e->getMessage());
				$this->_smarty->display('error.tpl');

				Zend_Log::log("[Gebruiker] Database query failed: " . $e->getMessage(), Zend_Log::LEVEL_SEVERE);

				exit(1);
			}

			$this->_smarty->assign("gebruiker", $gebruiker);

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('gebruiker/bewerk.tpl'));
		}


		/**
		 * Validates the new user form and creates the user if valid.
		 * If the validation fails it shows the form again.
		 */
		public function maakAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			/* Read from values from POST */
			$gebruiker = array(
				"gebruikersnaam" 	=> $_POST["gebruikersnaam"],
				"wachtwoord" 		=> $_POST["wachtwoord"],
				"wachtwoord2" 	=> $_POST["wachtwoord2"],
				"actief" 			=> $_POST["actief"],
				"type" 			=> $_POST["type"]); 

			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Gebruiker_Nieuw();
			$validator->validate($gebruiker, $errors);

			/* Wachtwoord2 is only used for validation */
			unset($gebruiker['wachtwoord2']);

			/* Display error page, or insert user */
			if($errors->hasErrors()) {
				$this->_smarty->assign("gebruiker", $gebruiker);
				$this->_smarty->assignByRef("errors", $errors);
				$this->_smarty->display('gebruiker/nieuw.tpl');				
			} else {
				$table = new CT_Db_Gebruikers();
				$gebruiker['wachtwoord'] = md5($gebruiker['wachtwoord']) .
					sha1($gebruiker['wachtwoord']);
				$table->insert($gebruiker);

				$this->redirect('/gebruiker/lijst');
			}
		}


		/**
		 * Validates the edit user form and updates the user if valid.
		 * If the validation fails it shows the form again.
		 * Requires the 'id' parameter to be a valid userid.
		 */
		public function opslaanAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			/* Read from values from POST */
			$gebruiker = array(
				"wachtwoord" 			=> $_POST["wachtwoord"],
				"wachtwoord2" 		=> $_POST["wachtwoord2"],
				"actief" 					=> $_POST["actief"],
				"type" 						=> $_POST["type"]); 

			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Gebruiker();
			$validator->validate($gebruiker, $errors);

			/* Wachtwoord2 is only used for validation */
			unset($gebruiker['wachtwoord2']);

			/* Display error page, or update user */
			if($errors->hasErrors()) {
				$this->_smarty->assign("gebruiker", $gebruiker);
				$this->_smarty->assignByRef("errors", $errors);
				$this->_smarty->display("gebruiker/bewerk.tpl");
			} else {
				/* Only update the password if it's correctly filled in */ 		
				if($gebruiker['wachtwoord'] == '')
					unset($gebruiker['wachtwoord']);
				else
					$gebruiker['wachtwoord'] = md5($gebruiker['wachtwoord']) . sha1($gebruiker['wachtwoord']);

				$table = new CT_Db_Gebruikers();
				$db 	 = $table->getAdapter();
				$where = $db->quoteInto('volgnummer = ?', intval($this->_getParam('id')));

				$table->update($gebruiker, $where);
	
				$this->redirect('/gebruiker/lijst');
			}
		}


		/**
		 * Removes a user from the system.
		 */
		public function verwijderAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			if($this->_user->getUserId() == intval($this->_getParam('id')))
				throw new Exception("Het is niet mogelijk jezelf te verwijderen");

			$table = new CT_Db_Gebruikers();
			$db		 = $table->getAdapter();
			$where = $db->quoteInto('volgnummer = ?', intval($this->_getParam('id')));

			$table->delete($where);

			$this->redirect('/gebruiker/lijst');
		}


		/**
		 * Displays the change_password dialog, changes the password.
		 */
		public function wachtwoordwijzigenAction()
		{
			$this->requireUserType(array(CT_User::Directie, CT_User::Boekhouding, CT_User::Klant));

			/* Process the form on POST */
			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$password_hash_old = md5($_POST['wachtwoord']) . sha1($_POST['wachtwoord']);
				$password_hash_new = md5($_POST['wachtwoord1']) . sha1($_POST['wachtwoord1']);

				/* Passwords do not match */
				if($_POST['wachtwoord1'] != $_POST['wachtwoord2']) {
					$this->_smarty->assign("message", "De wachtwoorden komen niet overeen");
					$this->_smarty->display('gebruiker/wachtwoord.tpl');
					exit(1);
				}

				/* Only change the new password if the old one matches */
				$table = new CT_Db_Gebruikers();
				$db		 = $table->getAdapter();
				$where = $db->quoteInto('volgnummer = ?', $this->_user->getUserid()) 
						   . $db->quoteInto("AND wachtwoord = ?", $password_hash_old);

				$gebruiker = array("wachtwoord" => $password_hash_new);

				/* Try update the password and check for failure */ 
				if($table->update($gebruiker, $where) != 0)
					$this->redirect("/index");
				else
					$this->_smarty->assign("message", "Het wachtwoord is NIET veranderd");
			}

			/* Request_method is not POST, or invalid form contents */
			$this->getResponse()->appendBody(
				$this->_smarty->fetch('gebruiker/wachtwoord.tpl'));
		}


		/**
		 * Invalid action, return to main page.
		 */ 
		public function noRouteAction()
		{
			$this->redirect('/');
		}

	}

?>
