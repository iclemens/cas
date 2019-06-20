<?php
	/**
	 * ArtikelcodeController, Project CAS
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Smarty_Paginate');
	Zend_Loader::loadClass('CT_Paginate_Db');
	Zend_Loader::loadClass('CT_Sort_Db');

	Zend_Loader::loadClass('CT_Controller_Action');
	Zend_Loader::loadClass('CT_Db_Artikelcodes');
	Zend_Loader::loadClass('CT_Validation_Validator_Artikelcode');
	Zend_Loader::loadClass('CT_Validation_Validator_Artikelcode_Nieuw');

	/**
	 * De artikelcode controller draagt zorg voor de web-interface om
	 * artikelcodes te beheren.
	 * 
	 * @package Controllers
	 */
	class ArtikelcodeController extends CT_Controller_Action 
	{

		/**
		 * De standaard actie, verwijst naar de lijstActie.
		 */
		public function indexAction() 
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie));
			$this->lijstAction();
		}


		/**
		 * Geeft een lijst van alle artikelcodes in het syteem.
		 */
		public function lijstAction()
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie));

			$page = intval($this->_getParam('page'));
			$count = intval($this->_getParam('count'));

			$sort = $this->_getParam('sort');

			try {

				$table = new CT_Db_Artikelcodes();

				$db = $table->getAdapter();
			
				$paginate = new CT_Paginate_Db($db, $page, $count);
				$sorter = new CT_Sort_Db($db, $sort);
				$result = $paginate->getRecords($sorter->extendQuery($table->select()));

				CT_Smarty_Paginate::registerPlugin($this->_smarty);

				$this->_smarty->assign('pager', $paginate);
				$this->_smarty->assign('sorter', $sorter);
				$this->_smarty->assign("artikelcodes", $result);

			} catch (Exception $e) {

				// Database query failed, write log message and bail
				$this->_smarty->assign("message", "Kan de lijst met artikelcodes niet ophalen.");

				$this->getResponse()->appendBody(
					$this->_smarty->fetch('error.tpl'));

				Zend_Log::log("[Artikelcode] Database query failed: " . $e->getMessage(), Zend_Log::LEVEL_SEVERE);

				return;
			}

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('artikelcode/lijst.tpl'));
		}


		/**
		 * Toont een formulier waarmee een nieuwe artikelcode kan worden toegevoegd.
		 */
		public function nieuwAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('artikelcode/nieuw.tpl'));
		}


		/**
		 * Toont een formulier waarmee een artikelcode kan worden aangepast.
		 */
		public function bewerkAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			/* Haal de te bewerken gegevens op uit de database */
			try {
				$table = new CT_Db_ArtikelCodes();
				$artikelcodes = $table->find($this->_getParam('code'))->toArray();
				$artikelcode = $artikelcodes[0];
			} catch (Exception $e) {
				$this->showErrorAndExit($e->getMessage());
			}

			/* Laat het bewerk formulier voor de eerste keer zien */
			$this->_smarty->assign('artikelcode', $artikelcode);

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('artikelcode/bewerk.tpl'));
		}


		/**
		 * Controleert de gegevens en maakt een nieuwe artikelcode aan.
		 */
		public function maakAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$artikelcode = array(
				'artikelcode' => $_POST['artikelcode'],
				'omschrijving' => $_POST['omschrijving']);

			/* FIXME: We should verify uniqueness */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Artikelcode_Nieuw();
			$validator->validate($artikelcode, $errors);

			/**
			 * Probeer de veranderingen op te slaan, als de database
			 * niet mee wil werken geven we een foutmelding.
			 */
			if($errors->hasErrors() == false) {
				try {
					$table = new CT_Db_Artikelcodes();
					$klantnummer = $table->insert($artikelcode);
					$this->redirect('/artikelcode/index');
					return;
				} catch (Exception $e) {
					$errors->reject('Er is een fout opgetreden tijdens de database transactie.');

					/* Let's be nice and log the actual error message... */
					$logger = Zend_Registry::get("logger");
					$logger->log("[Artikelcode] Transactie fout: " . $e->getMessage(), Zend_Log::CRIT);
				}
			}

			/* Display form */
			$this->_smarty->assign('artikelcode', $artikelcode);
			$this->_smarty->assign_by_ref('errors', $errors);
	
			$this->getResponse()->appendBody(
				$this->_smarty->fetch('artikelcode/nieuw.tpl'));
		}


		/**
		 * Controleert de veranderingen en slaat ze op.
		 */
		public function opslaanAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$code  = $this->_getParam('code');

			$artikelcode = array(
				'omschrijving' => $_POST['omschrijving']);

			/* Controleer alle waarden */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Artikelcode();
			$validator->validate($artikelcode, $errors);

			/**
			 * Probeer de veranderingen op te slaan, als de database
			 * niet mee wil werken geven we een foutmelding.
			 */
			if($errors->hasErrors() == false) {
				try {
					$table = new CT_Db_Artikelcodes();
					$db    = $table->getAdapter();
					$where = $db->quoteInto('artikelcode = ?', $code);

					$table->update($artikelcode, $where);
					$this->redirect('/artikelcode/index');
					return;
				} catch (Exception $e) {
					$errors->reject('Er is een fout opgetreden tijdens de database transactie.');

					/* Let's be nice and log the actual error message... */
					$logger = Zend_Registry::get("logger");
					$logger->log("[Artikelcode] Transactie fout: " . $e->getMessage(), Zend_Log::CRIT);
				}
			}

			/* Laat het formulier nogmaals zien */
			$artikelcode['artikelcode'] = $code;

			$this->_smarty->assign('artikelcode', $artikelcode);
			$this->_smarty->assign_by_ref('errors', $errors);
	
			$this->getResponse()->appendBody(
				$this->_smarty->fetch('artikelcode/bewerk.tpl'));
		}


		/**
		 * Verwijdert een artikelcode als deze niet in gebruik is
		 */
		public function verwijderAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$code  = $this->_getParam('code');

			$artikelcodes = new CT_Db_Artikelcodes();

			$selectQuery = $artikelcodes->select();

			$selectQuery->where('artikelcode = ?', $code);
            $selectQuery->where('artikelcode NOT IN (SELECT artikelcode FROM periodiekeregels)');
			$selectQuery->where('artikelcode NOT IN (SELECT artikelcode FROM factureren)');
			$selectQuery->where('artikelcode NOT IN (SELECT artikelcode FROM factuurregels)');

			$stmt = $artikelcodes->getAdapter()->query($selectQuery);
			$records = $stmt->fetchAll();
		
			if(count($record) == 1) {
				$artikelcodes->delete($artikelcodes->getAdapter()->quoteInto('artikelcode = ?', $code));
				$this->_redirect('/artikelcode');
			} else {
				$this->showErrorAndExit('Het is niet mogelijk in gebruik zijnde artikelcodes te verwijderen.');
			}
		}


		/**
		 * Returns a list of possible artikelcodes given a partial entry
		 * These results could then be used for auto-completion.
		 */
		public function autocompleteAction()
		{			
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie));

			$object = $this->_getParam('object');
			$partial = $this->_getParam('partial');

			$this->_smarty->assign("object", $object);
			$this->_smarty->assign("partial", $partial);

			try {

				$table = new CT_Db_Artikelcodes();
				$db    = $table->getAdapter();
				$where = $db->quoteInto('artikelcode LIKE ?', $partial . '%') . ' OR ' . 
								 $db->quoteInto('omschrijving LIKE ?', '%' . $partial . '%');
				$rowset = $table->fetchAll($where, null, null, null);
				$this->_smarty->assign("artikelcodes", $rowset->toArray());
			} catch (Exception $e) {
				Zend_Log::log("[Artikelcode] Database query failed: " . $e->getMessage(), Zend_Log::LEVEL_SEVERE);
			}

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('artikelcode/autocomplete.tpl'));
		}


		public function noRouteAction()
		{
			$this->_redirect('/');
		}
	}
