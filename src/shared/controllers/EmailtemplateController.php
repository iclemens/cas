<?php
	/**
	 * EmailtemplateController, Project CAS
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Smarty_Paginate');
	Zend_Loader::loadClass('CT_Paginate_Db');
	Zend_Loader::loadClass('CT_Sort_Db');

	Zend_Loader::loadClass('CT_Controller_Action');
	Zend_Loader::loadClass('CT_Db_Emailtemplates');
	Zend_Loader::loadClass('CT_Validation_Validator_Emailtemplate');
/*	Zend_Loader::loadClass('CT_Validation_Validator_Artikelcode_Nieuw');*/

	/**
	 * Deze contoller is verantwoordelijk voor de web-interface om
	 * email-templates te beheren. 
	 * 
	 * @package Controllers 
	 */
	class EmailtemplateController extends CT_Controller_Action 
	{

		/**
		 * De standaard actie, verwijst naar de lijstActie.
		 */
		public function indexAction() 
		{
			$this->requireUserType(array(CT_User::Directie));
			$this->lijstAction();
		}


		/**
		 * Geeft een lijst van alle emailtemplates in het syteem.
		 */
		public function lijstAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$page = intval($this->_getParam('page'));
			$count = intval($this->_getParam('count'));

			$sort = $this->_getParam('sort');

			try {

				$table = new CT_Db_Emailtemplates();

				$db = $table->getAdapter();
			
				$paginate = new CT_Paginate_Db($db, $page, $count);
				$sorter = new CT_Sort_Db($db, $sort);
				$result = $paginate->getRecords($sorter->extendQuery($table->select()));

				CT_Smarty_Paginate::registerPlugin($this->_smarty);

				$this->_smarty->assign('pager', $paginate);
				$this->_smarty->assign('sorter', $sorter);
				$this->_smarty->assign("emailtemplates", $result);

			} catch (Exception $e) {

				// Database query failed, write log message and bail
				$this->_smarty->assign("message", "Kan de lijst met emailtemplates niet ophalen.");

				$this->getResponse()->appendBody(
					$this->_smarty->fetch('error.tpl'));

				Zend_Log::log("[Emailtemplate] Database query failed: " . $e->getMessage(), Zend_Log::ERR);

				return;
			}

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('emailtemplate/lijst.tpl'));
		}


		/**
		 * Toont een formulier waarmee een nieuwe artikelcode kan worden toegevoegd.
		 */
		public function nieuwAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('emailtemplate/nieuw.tpl'));
		}


		/**
		 * Toont een formulier waarmee een artikelcode kan worden aangepast.
		 */
		public function bewerkAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			/* Haal de te bewerken gegevens op uit de database */
			try {
				$table = new CT_Db_EmailTemplates();
				$emailtemplate = array_pop($table->find($this->_getParam('id'))->toArray());
			} catch (Exception $e) {
				$this->showErrorAndExit($e->getMessage());
			}

			/* Laat het bewerk formulier voor de eerste keer zien */
			$this->_smarty->assign('emailtemplate', $emailtemplate);

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('emailtemplate/bewerk.tpl'));
		}


		/**
		 * Controleert de gegevens en maakt een nieuwe artikelcode aan.
		 */
		public function maakAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$emailtemplate = array(
				'omschrijving' => $_POST['omschrijving'],
				'onderwerp' => $_POST['onderwerp'],
				'inhoud' => $_POST['inhoud']);

			/* FIXME: We should verify uniqueness */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Emailtemplate();
			$validator->validate($emailtemplate, $errors);

			/**
			 * Probeer de veranderingen op te slaan, als de database
			 * niet mee wil werken geven we een foutmelding.
			 */
			if($errors->hasErrors() == false) {
				try {
					$table = new CT_Db_Emailtemplates();
					$volgnummer = $table->insert($emailtemplate);
					$this->redirect('/emailtemplate/index');
					return;
				} catch (Exception $e) {
					$errors->reject('Er is een fout opgetreden tijdens de database transactie.');

					/* Let's be nice and log the actual error message... */
					$logger = Zend_Registry::get("logger");
					$logger->log("[Emailtemplate] Transactie fout: " . $e->getMessage(), Zend_Log::CRIT);
				}
			}

			/* Display form */
			$this->_smarty->assign('emailtemplate', $emailtemplate);
			$this->_smarty->assign_by_ref('errors', $errors);
	
			$this->getResponse()->appendBody(
				$this->_smarty->fetch('emailtemplate/nieuw.tpl'));
		}


		/**
		 * Controleert de veranderingen en slaat ze op.
		 */
		public function opslaanAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$id  = $this->_getParam('id');

			$emailtemplate = array(
				'omschrijving' => $_POST['omschrijving'],
				'onderwerp' => $_POST['onderwerp'],
				'inhoud' => $_POST['inhoud']);

			/* Controleer alle waarden */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Emailtemplate();
			$validator->validate($emailtemplate, $errors);

			/**
			 * Probeer de veranderingen op te slaan, als de database
			 * niet mee wil werken geven we een foutmelding.
			 */
			if($errors->hasErrors() == false) {
				try {
					$table = new CT_Db_Emailtemplates();
					$db    = $table->getAdapter();
					$where = $db->quoteInto('volgnummer = ?', $id);

					$table->update($emailtemplate, $where);
					$this->redirect('/emailtemplate/index');
					return;
				} catch (Exception $e) {
					$errors->reject('Er is een fout opgetreden tijdens de database transactie.');

					/* Let's be nice and log the actual error message... */
					$logger = Zend_Registry::get("logger");
					$logger->log("[Emailtemplate] Transactie fout: " . $e->getMessage(), Zend_Log::CRIT);
				}
			}

			/* Laat het formulier nogmaals zien */
			$emailtemplate['volgnummer'] = $id;

			$this->_smarty->assign('emailtemplate', $emailtemplate);
			$this->_smarty->assign_by_ref('errors', $errors);
	
			$this->getResponse()->appendBody(
				$this->_smarty->fetch('emailtemplate/bewerk.tpl'));
		}


		/**
		 * Verwijdert een emailtemplate als deze niet in gebruik is
		 */
		public function verwijderAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$id  = $this->_getParam('id');

			$emailtemplates = new CT_Db_Emailtemplates();

			$selectQuery = $emailtemplates->select();

			$selectQuery->where('volgnummer = ?', $id);
            $selectQuery->where('volgnummer NOT IN (SELECT emailtemplate FROM klanten)');

			$stmt = $emailtemplates->getAdapter()->query($selectQuery);
			$records = $stmt->fetchAll();
		
			if(count($record) == 1) {
				$emailtemplates->delete($emailtemplates->getAdapter()->quoteInto('volgnummer = ?', $id));
				$this->_redirect('/emailtemplate');
			} else {
				$this->showErrorAndExit('Het is niet mogelijk in gebruik zijnde templates te verwijderen.');
			}
		}


		public function noRouteAction()
		{
			$this->_redirect('/');
		}
	}
