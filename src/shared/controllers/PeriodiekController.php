<?php
	/**
	 * PeriodiekController, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Db_Klanten');
	Zend_Loader::loadClass('CT_Db_Periodiekeregels');
	Zend_Loader::loadClass('CT_Db_Perioden');
	Zend_Loader::loadClass('CT_Db_Factureren');

	Zend_Loader::loadClass('CT_Smarty_Paginate');
	Zend_Loader::loadClass('CT_Paginate_Db');
	Zend_Loader::loadClass('CT_Sort_Db');

	Zend_Loader::loadClass('CT_Validation_Validator_Periodiekeregel');

	Zend_Loader::loadClass('CT_VAT');
	Zend_Loader::loadClass('CT_Controller_Action');

	/**
	 * Afhandeling van periodieke facturatie.
	 * 
	 * @package Controllers 
	 */
	class PeriodiekController extends CT_Controller_Action 
	{

		/**
		 * De standaard pagina, laat een lijst met beschikbare opties zien. 
		 */
		public function indexAction() 
		{
			$this->requireUserType(array(CT_User::Directie));

			$this->redirect('/factuur/index');
		}


		/**
		 * Displays a list of periodic events.
		 */
		public function lijstAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$page = intval($this->_getParam('page'));
			$count = intval($this->_getParam('count'));

			$sort = $this->_getParam('sort');
			
			try {
				$tbl_klant = new CT_Db_Klanten();
				$tbl_perioden = new CT_Db_Perioden();
				$tbl_periodiek = new CT_Db_Periodiekeregels();

				$db = $tbl_periodiek->getAdapter();

				$sql_subtotaal = "FLOOR((IFNULL(aantal, 1) * prijs) + 0.5)";
				$sql_btw = "FLOOR(($sql_subtotaal * btw_percentage / 100) + 0.5)";
				$sql_totaal = "($sql_subtotaal + $sql_btw)";		

				$searchParams = $this->getSearchParameters($_GET);

				$selectQuery = $tbl_periodiek->select()
					->setIntegrityCheck(false)
					->from('periodiekeregels',
						array(
						'periodiekeregels.*', 
						"subtotaal" => $sql_subtotaal,
						"btw" => $sql_btw,
						"totaal" => $sql_totaal))
					->join('klanten', 'periodiekeregels.klantnummer = klanten.klantnummer', array('klanttype', 'bedrijfsnaam', 'aanhef', 'voornaam', 'achternaam'));

				$selectQuery = $this->extendQueryWithSearchParams($selectQuery, $searchParams);					

				$paginate = new CT_Paginate_Db($db, $page, $count);
				$sorter = new CT_Sort_Db($db, $sort);

				$result = $paginate->getRecords($sorter->extendQuery($selectQuery));

				foreach($result as &$regel) {

					$regel['perioden'] = $tbl_perioden->fetchAll('periodiekeregel = ' . $regel['volgnummer'])->toArray();
					$regel['maanden'] = $this->periodenToMonths($regel['perioden']);

					if($regel['laatstgefactureerd'] != NULL) {
						$regel['laatstgefactureerd'] = 
							date('d/m/Y', strtotime($regel['laatstgefactureerd']));
					}
				}

				CT_Smarty_Paginate::registerPlugin($this->_smarty);
		
				$this->_smarty->assign('pager', $paginate);
				$this->_smarty->assign('sorter', $sorter);
				$this->_smarty->assign('periodiekeregels', $result);
				$this->_smarty->assign('parameters', $searchParams);

				$this->getResponse()->appendBody(
					$this->_smarty->fetch('periodiek/lijst.tpl'));

			} catch (Exception $e) {

				// Database query failed, write log message and bail
				$this->_smarty->assign("message", "Kan de lijst met periodiekeregels niet ophalen.<br/>" . $e->getMessage());
				$this->_smarty->display('error.tpl');

				Zend_Log::log("[Periodiek] Database query failed: " . $e->getMessage(), Zend_Log::LEVEL_SEVERE);

				return;
			}
		}


		/**
		 * Displays a form for creating a new rule
		 */
		public function nieuwAction()
		{
			$this->requireUserType(array(CT_User::Directie));			
			$data = CT_VAT::getVATById(CT_VAT::getDefaultVATCategory());

			$periodiekeregels = array(0 => array(
				'btw_percentage' => $data['rate'],
				'perioden' => array(0 => array('maand' => intval(date('m'))))
			));

			$this->_smarty->assign('btw_categorie', CT_VAT::getDefaultVATCategory());

			$this->displayEditor($periodiekeregels, null, true);
		}


		/**
		 * Displays a form for editing an existing periodic rule
		 */
		public function bewerkAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			try {
				$tbl_periodiekeregels = new CT_Db_Periodiekeregels;
				$tbl_perioden = new CT_Db_Perioden;

				$periodiekeregels = $tbl_periodiekeregels->find(intval($this->_getParam('id')))->toArray();

				$perioden = $tbl_perioden->fetchAll('periodiekeregel = ' . intval($this->_getParam('id')))->toArray();

				$periodiekeregels[0]['perioden'] = $perioden;
				
			} catch (Exception $e) {
				$this->showErrorAndExit($e->getMessage());
			}

			$this->_smarty->assign('btw_categorie', $_POST['btw_categorie']);

			$this->displayEditor($periodiekeregels, null, false);
		}


		/**
		 * Validates the new rule and creates the rule if valid 
		 *
		 * Because MySQL does not support nested transactions,
		 * we allow the list of lines to be partially inserted.
		 */
		public function maakAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			/* Read from values from POST */
			$periodiekeregels = $this->leesPeriodiekeRegelsUitArray($_POST);

			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Periodiekeregel();

			foreach($periodiekeregels as $periodiekeregel)
				$validator->validate($periodiekeregel, $errors);			

			/* Display error page, or create rule */
			if($errors->hasErrors()) {
				$this->displayEditor($periodiekeregels, $errors);
				return;
			} else {
				$table = new CT_Db_Periodiekeregels();
				
				$failed = array();

				foreach($periodiekeregels as $periodiekeregel) {
					try {
						$laatstgefactureerd = time();

						if($_POST['deze_maand']) {
							$day_of_month = intval(date('d'));

							/** 
							 * Ga day_of_month + 1 dagen terug zodat een timestamp ontstaat
							 * welke in de vorige maand ligt. Hierdoor wordt deze factuur
							 * in de volgende facturatie ronde meegenomen.
							 */
							$decrement = (($day_of_month + 1) * 24 * 60 * 60);

							$laatstgefactureerd -= $decrement;
						}

						$periodiekeregel['laatstgefactureerd'] = date('Y/m', $laatstgefactureerd) . '/1';

						$volgnummer = $table->insert($periodiekeregel);
					} catch (Exception $e) {
						$failed[] = $periodiekeregel;
					}
				}

				if(count($failed) > 0) {
					if(count($failed) == count($periodiekeregels))
						$errors->reject('Transactie fout, geen van de regels kon worden ingevoerd');
					else
						$errors->reject('Transactie fout, enkele regels konden niet worden ingevoerd');
				
					$this->displayEditor($failed, $errors);
					return;
				}

				$this->redirect('/periodiek/lijst');
			}
		}


		/**
		 * Verwijderd een periodieke regel uit het systeem.
		 */
		public function verwijderAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$table   = new CT_Db_Periodiekeregels();
			$db		 = $table->getAdapter();
		 	$where   = $db->quoteInto('volgnummer = ?', intval($this->_getParam('id')));

			$table->delete($where);

			$this->redirect('/periodiek/lijst');
		}

		public function verwijderxmlAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			try {
				$table   = new CT_Db_Periodiekeregels();
				$db		 = $table->getAdapter();
		 		$where   = $db->quoteInto('volgnummer = ?', intval($this->_getParam('id')));

				$table->delete($where);

				$this->_smarty->assign('success', 1);
				$this->_smarty->assign('volgnummer', intval($this->_getParam('id')));
			} catch(Exception $e) {
				$this->_smarty->assign('success', 0);				
			}

			try {
				$this->getResponse()->setHeader('Content-Type', 'application/xml');
			} catch(Exception $e) {
				// Cannot send headers, just ignore this for a moment!
			}

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('periodiek/verwijder_xml.tpl'));
		}

		/**
		 * ...
		 */
		private function periodenToMonths($perioden)
		{
			/* Determine in which months to send an invoice */
			$maanden = array();

			for($i = 1; $i <= 12; $i++)
				$maanden[$i] = false;

			if(is_array($perioden)) {
				foreach($perioden as $periode)
					$maanden[$periode['maand']] = true;
			}

			return $maanden;
		}


		/**
		 * Displays and editor for changing periodic lines after they have been rejected.
		 */
		private function displayEditor($periodiekeregels, $errors, $nieuw = true)
		{
				$this->_smarty->assign('periodiekeregels', $periodiekeregels);

				$this->_smarty->assign("btw_tarieven", CT_VAT::getVATCategories());

				$this->_smarty->assignByRef('errors', $errors);

				$this->_smarty->assign('maanden', 
					$this->periodenToMonths($periodiekeregels[0]['perioden']));

				if($nieuw) {
					$this->getResponse()->appendBody(
						$this->_smarty->fetch('periodiek/nieuw.tpl'));
				} else {
					$this->getResponse()->appendBody(
						$this->_smarty->fetch('periodiek/bewerk.tpl'));
				}
		}


		/**
		 * Validates the rule form and updates the rule if valid 
		 */
		public function opslaanAction()
		{
			$this->requireUserType(array(CT_User::Directie));			

			/* Read from values from POST */
			$periodiekeregels = $this->leesPeriodiekeRegelsUitArray($_POST);

			if(count($periodiekeregels) != 1)
				throw new Exception('Er is een fout opgetreden bij het opslaan van de periodieke regel');

			$periodiekeregel = array_pop($periodiekeregels);		

			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Periodiekeregel();

			$validator->validate($periodiekeregel, $errors);			

			/* Display error page, or create rule */
			if($errors->hasErrors()) {
				$this->displayEditor(array(0 => $periodiekeregel), $errors, false);
				return;
			} else {
				$table = new CT_Db_Periodiekeregels();
				$db	   = $table->getAdapter();
				$where = $db->quoteInto('volgnummer = ?', intval($this->_getParam('id')));

				$periodiekeregel['volgnummer'] = intval($this->_getParam('id'));

				$table->update($periodiekeregel, $where);

				$this->redirect('/periodiek/lijst');
			}
		}


		/**
		 * Processes all smarty tags in a given string as if it
		 * were a periodic invoice line. The result is returned and
		 * may be used for preview purposes.
		 */
		public function voorbeeldAction()
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie, CT_User::Klant));

			// Allow external script to override current date
			$maand = $this->_getParam('maand');

			if(!isset($maand))
				$maand = date('m');

			$jaar = $this->_getParam('jaar');

			if(!isset($jaar))
				$jaar = date('Y');

			// Get the smarty template
			$omschrijving = $this->_getParam('omschrijving');	

			// Process the template string
			$config = Zend_Registry::get('config');
			$formatter = Zend_Registry::get('formatter');

			$smarty = new CT_Smarty_String($config, $formatter);

			$smarty->assign('jaar', $jaar);
			$smarty->assign('maand', $maand);

			$omschrijving = $smarty->fetch($omschrijving);

			$this->getResponse()->appendBody($omschrijving);
		}


		/**
		 * Kopieert de periodiekeregels uit een array (bijvoorbeeld $_POST) naar
		 *  een nieuwe array die geschikt is voor gebruik met CT_Db_Periodiekeregels.
		 *
		 * @param array $bron
		 *
		 * @return array
		 */
		private function leesPeriodiekeRegelsUitArray($bron) 
		{
			$periodiekeregels = array();

			$btw_categorie = $bron['btw_categorie'];
			$data = CT_VAT::getVATById($btw_categorie);

			$periodiekeregel_base = array(
				'klantnummer' => $bron['klantnummer'],
				'btw_percentage' => $data['rate'],
				'perioden' => array());

			if(is_array($bron['maand']))
				foreach($bron['maand'] as $maand) 
					$periodiekeregel_base['perioden'][] = array('maand' => $maand);		

			if(is_array($bron['artikelcode'])) {
				for($i = 0; $i < count($bron['artikelcode']); $i++) {
					$periodiekeregel = $periodiekeregel_base;

					$periodiekeregel['artikelcode'] = $bron['artikelcode'][$i];
					$periodiekeregel['omschrijving'] = $bron['omschrijving'][$i];

					if($bron['aantal'][$i] == '')
						$periodiekeregel['aantal'] = NULL;
					else
						$periodiekeregel['aantal'] = floatval(str_replace(',', '.', $bron['aantal'][$i]));

					if($bron['prijs'][$i] == '')
						$periodiekeregel['prijs'] = NULL;
					else
						$periodiekeregel['prijs'] = round(100 * floatval(str_replace(',', '.', $bron['prijs'][$i])));

					if($periodiekeregel['aantal'] == 0 && $periodiekeregel['prijs'] == 0) {
						$periodiekeregel['aantal'] = NULL;
						$periodiekeregel['prijs'] = NULL;
					}

					if(strlen($periodiekeregel['omschrijving']) > 0 ||
						strlen($periodiekeregel['artikelcode']) > 0) {
						$periodiekeregels[] = $periodiekeregel;
					}
				}
			}

			return $periodiekeregels;
		}


		/**
		 * Adds a where clause to the SQL query.
		 *
		 * @param Zend_Db_Select @query The query object to extend
		 * @param array @params Search parameters
		 *
		 * @return Zend_Db_Select Where clause
		 */
		private function extendQueryWithSearchParams($query, $params)
		{
			if(!is_array($params))
				return $query;				

			if(array_key_exists('klantnummer', $params))
				$query = $query->where('periodiekeregels.klantnummer = ?', $params['klantnummer']);

			if(array_key_exists('maand', $params) && count($params['maand']) > 0) {
				$maandPart = '';
				
				foreach($params['maand'] as $maand) {
					if($maandPart == '')
						$maandPart = 'maand = ' . $maand;
					else
						$maandPart = $maandPart . ' OR maand = ' . $maand;
				}

				$query = $query->where('periodiekeregels.volgnummer IN (SELECT periodiekeregel FROM perioden WHERE periodiekeregel = periodiekeregels.volgnummer AND (' . $maandPart . '))');
			}
		
			return $query;
		}
		

		/**
		 * Extracts and secures search parameters from an array (such as $_POST)
		 *
		 * @param array $source Array containing the search parameters
		 *
		 * @return array Secure search parameters
		 */
		private function getSearchParameters($source)
		{
			$formatter = Zend_Registry::get('formatter');
			$parameters = array();
			$parameters['maand'] = array();

			// Stel zoek parameters vast.
			if($this->_user->getUserType() == CT_User::Klant) {
				$parameters['klantnummer'] = 
					$formatter->getCustomerRefFromUsername($this->_user->getUsername());
			} elseif(array_key_exists('klantnummer', $source) && intval($source['klantnummer'] != 0)) {
				$parameters['klantnummer'] = intval($source['klantnummer']);
			}

			if(array_key_exists('maand', $source)) {
				if(is_array($source['maand']))				
					foreach($source['maand'] as $maand)
						$parameters['maand'][] = intval($maand);
				else
					$parameters['maand'][] = intval($source['maand']);
			}
			
			return $parameters;
		}


		/**
		 * Invalid action, return to main page 
		 */ 
		public function noRouteAction()
		{
			$this->redirect('/');
		}

	}
