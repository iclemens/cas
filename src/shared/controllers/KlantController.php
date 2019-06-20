<?php
	/**
	 * KlantController, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Smarty_Paginate');
	Zend_Loader::loadClass('CT_Paginate_Db');
	Zend_Loader::loadClass('CT_Sort_Db');
	
	Zend_Loader::loadClass('CT_Db_Klanten');
	Zend_Loader::loadClass('CT_Db_Emailtemplates');
	Zend_Loader::loadClass('CT_Controller_Action');
	Zend_Loader::loadClass('CT_Validation_Validator_Klant');
	Zend_Loader::loadClass('CT_VAT');

	/**
	 * Afhandeling van klantgerelateerde taken.
	 * 
	 * @package Controllers 
	 */
	class KlantController extends CT_Controller_Action 
	{

		/**
		 * De standaard pagina, laat een lijst met beschikbare opties zien. 
		 */
		public function indexAction() 
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie));

			$this->lijstAction();
		}


		/**
		 * Displays a list of current customers.
		 */
		public function lijstAction()
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie));

			$page = intval($this->_getParam('page'));
			$count = intval($this->_getParam('count'));

			$sort = $this->_getParam('sort');
			
			$klanten = new CT_Db_Klanten();
			$db = $klanten->getAdapter();
			
			$parameters = $this->getSearchParams($_GET);

			$paginate = new CT_Paginate_Db($db, $page, $count);
			$sorter = new CT_Sort_Db($db, $sort);

			$selectQuery = $klanten->select();

			// Extend query with search params
			if(array_key_exists('actief', $parameters) && $parameters['actief'] != -1)
				$selectQuery = $selectQuery->where('actief = ?', $parameters['actief']);

			if(array_key_exists('klanttype', $parameters) && $parameters['klanttype'] != -1)
				$selectQuery = $selectQuery->where('klanttype = ?', $parameters['klanttype']);

			$result = $paginate->getRecords($sorter->extendQuery($selectQuery));

			CT_Smarty_Paginate::registerPlugin($this->_smarty);
		
			$this->_smarty->assign('parameters', $parameters);
			$this->_smarty->assign('pager', $paginate);
			$this->_smarty->assign('sorter', $sorter);
			$this->_smarty->assign('klanten', $result);

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('klant/lijst.tpl'));
		}


		/**
		 * Extracts and secures search parameters from an array (such as $_POST)
		 *
		 * @param array $source Array containing the search parameters
		 *
		 * @return array Secure search parameters
		 */
		private function getSearchParams($source)
		{
			$formatter = Zend_Registry::get('formatter');
			$parameters = array();

			if(array_key_exists('klanttype', $source))
			  $parameters['klanttype'] = intval($source['klanttype']);
			else
			  $parameters['klanttype'] = -1;

			if(array_key_exists('actief', $source))
			  $parameters['actief'] = intval($source['actief']);
			else
			  $parameters['actief'] = 1;

			return $parameters;
		}


		/**
		 * Overview of a selected customer
		 */
		public function overzichtAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$table = new CT_Db_Klanten();
			$klant = array_pop($table->find(intval($this->_getParam('id')))->toArray());
			
			$this->getResponse()->appendBody(
				$this->_smarty->fetch('klant/overzicht.tpl'));
		}


		/**
		 * Displays a form for creating a new customer 
		 */
		public function nieuwAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$this->_smarty->assign('klant', array('actief' => 1, 'btwcategorie' => CT_VAT::getDefaultVATCategory()));
			$this->_smarty->assign('btw_tarieven', CT_VAT::getVATCategories());
			$this->_smarty->assign("emailtemplates", CT_Db_Emailtemplates::lijstMetTemplates());

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('klant/nieuw.tpl'));
		}


		/**
		 * Displays a form for editing an existing customer 
		 */
		public function bewerkAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			try {
				$table = new CT_Db_Klanten();
				$klanten = $table->find(intval($this->_getParam('id')))->toArray();
				$klant = $klanten[0];
			} catch (Exception $e) {
				$this->showErrorAndExit($e->getMessage());
			}

			$this->_smarty->assign("klant", $klant);
			$this->_smarty->assign("btw_tarieven", CT_VAT::getVATCategories());
			$this->_smarty->assign("emailtemplates", CT_Db_Emailtemplates::lijstMetTemplates());

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('klant/bewerk.tpl'));
		}


		/**
		 * Validates the new customer form and creates the customer if valid 
		 */
		public function maakAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			/* Read from values from POST */
			$klant = $this->leesKlantUitArray($_POST);

			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Klant();
			$validator->validate($klant, $errors);

			/* Display error page, or create customer + user */
			if($errors->hasErrors()) {
				$this->_smarty->assign('klant', $klant);
				$this->_smarty->assign_by_ref('errors', $errors);
	
				$this->_smarty->assign('btw_tarieven', CT_VAT::getVATCategories());
				$this->_smarty->assign("emailtemplates", CT_Db_Emailtemplates::lijstMetTemplates());

				$this->getResponse()->appendBody(
					$this->_smarty->fetch('klant/nieuw.tpl'));
			} else {
				$table = new CT_Db_Klanten();
				$klantnummer = $table->insert($klant);

				$klant['klantnr'] = $klantnummer;

				$formatter = Zend_Registry::get('formatter');

				$gebruiker = array(
					'gebruikersnaam' => $formatter->getUsernameFromCustomer($klant),
					'actief' => 0,
					'type' => 3);

				$table = new CT_Db_Gebruikers();
				$table->insert($gebruiker);

				$this->redirect('/index');
			}
		}


		/**
		 * Validates the customer form and updates the customer if valid 
		 */
		public function opslaanAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			/* Read from values from POST */
			$klant = $this->leesKlantUitArray($_POST);
	
			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Klant();
			$validator->validate($klant, $errors);

			/* Display error page, or update customer */
			if($errors->hasErrors()) {
				$klant['klantnummer'] = intval($this->_getParam('id'));
				$this->_smarty->assign("klant", $klant);
				$this->_smarty->assign_by_ref("errors", $errors);

				$this->_smarty->assign('btw_tarieven', CT_VAT::getVATCategories());
				$this->_smarty->assign("emailtemplates", CT_Db_Emailtemplates::lijstMetTemplates());

				$this->getResponse()->appendBody(
					$this->_smarty->fetch("klant/bewerk.tpl"));
			} else {
				$table = new CT_Db_Klanten();
				$db 	 = $table->getAdapter();
				$where = $db->quoteInto('klantnummer = ?', intval($this->_getParam('id')));

				$table->update($klant, $where);
				$this->redirect('/index');
			}
		}


		/**
		 * Returns all customer information in addition to the email template.
		 *  The default template is used if there is none.
		 */
		public function klantxmlAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$config = Zend_Registry::get('config');

			try {
				$table = new CT_Db_Klanten();
				$klanten = $table->find(intval($this->_getParam('id')))->toArray();

				if(count($klanten) == 1) {
					$klant = $klanten[0];

					$templates = new CT_Db_Emailtemplates();
					$emailtemplate = array_pop($templates->find(intval($klant['emailtemplate']))->toArray());
				} else {
					$klant = array('klantnummer' => 0);
				}
			} catch (Exception $e) {
				$klant = array('klantnummer' => 0);
			}

			try {
				$this->getResponse()->setHeader('Content-Type', 'application/xml');
			} catch(Exception $e) {
				// Cannot send headers, just ignore this for a moment!
			}

			$this->_smarty->assign('klant', $klant);
			$this->_smarty->assign('emailtemplate', $emailtemplate);
			$this->_smarty->assign('fields_str', $fields_str);

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('klant/klant_xml.tpl'));
		}


		/**
		 * Returns a list of possible customers given a partial entry
		 * These results could then be used for auto-completion.
		 */
		public function autocompleteAction()
		{			
			$this->requireUserType(array(CT_User::Directie));

			$object = $this->_getParam('object');
			$partial = $this->_getParam('partial');

			$this->_smarty->assign("object", $object);
			$this->_smarty->assign("partial", $partial);

			try {

				$table = new CT_Db_Klanten();
				$db    = $table->getAdapter();
				$where = $db->quoteInto('klantnummer LIKE ?', $partial . '%') . ' OR ' . 
								 $db->quoteInto('bedrijfsnaam LIKE ?', '%' . $partial . '%') . ' OR ' .
								 $db->quoteInto('achternaam LIKE ?', '%' . $partial . '%');

				$rowset = $table->fetchAll($where, null, 10, 0);
				$this->_smarty->assign("klanten", $rowset->toArray());
			} catch (Exception $e) {
				Zend_Log::log("[Klanten] Database query failed: " . $e->getMessage(), Zend_Log::LEVEL_SEVERE);
			}

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('klant/autocomplete.tpl'));
		}


		/**
		 * Kopieert de klant-specifieke velden uit een array (bijvoorbeeld $_POST) naar
		 *  een nieuwe array die geschikt is voor gebruik met CT_Db_Klanten.
		 *
		 * @param array $bron
		 *
		 * @return array
		 */
		private function leesKlantUitArray($bron) {
			$klant = array();

			$fields_chk = array('machtigingmaand', 'machtigingjaar');

			// De lijst met velden die worden gekopieerd.
			$fields_str = array('bedrijfsnaam', 'afdeling', 'aanhef', 'voornaam', 
					'achternaam', 'factuuradres', 'factuuradres2', 'factuurpostcode', 
					'factuurplaats', 'factuurland', 'factuuremail', 'bezoekadres', 
					'bezoekadres2', 'bezoekpostcode', 'bezoekplaats', 'bezoekland',
					'emailadres', 'website', 'telefoonvast', 'telefoonmobiel',
					'btwnummer', 'btwgecontroleerd', 'btwcategorie', 'opmerkingen');

			foreach($fields_str as $field) 
				$klant[$field] = getValueFromArray($bron, $field);

			if(isset($bron['factuurtemplatechk'])) {
				$klant['factuurtemplate'] = $bron['factuurtemplate'];
			} else {
				$klant['factuurtemplate'] = NULL;
			}

			$klant['actief'] = intval($bron['actief']);
			$klant['klanttype'] = intval($bron['klanttype']);
			$klant['emailtemplate'] = intval($bron['emailtemplate']);

			foreach($fields_chk as $field) {
				if(array_key_exists($field, $bron) && strlen($bron[$field]) > 0)
					$klant[$field] = true;
				else
					$klant[$field] = false;
			}

			return $klant;
		}


		/**
		 * Invalid action, return to main page 
		 */ 
		public function noRouteAction()
		{
			$this->redirect('/');
		}

	}
