<?php
	/**
	 * FactuurController, Project CAS
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @package    Controllers
	 */

	Zend_Loader::loadClass('CT_Paginate_Db');
	Zend_Loader::loadClass('CT_Smarty_Paginate');
	Zend_Loader::loadClass('CT_Sort_Db');
	
	Zend_Loader::loadClass('CT_Db_Incassos');
	Zend_Loader::loadClass('CT_Db_Facturen');
	Zend_Loader::loadClass('CT_Db_Klanten');
	Zend_Loader::loadClass('CT_Db_Emailtemplates');
	Zend_Loader::loadClass('CT_Invoice');
	Zend_Loader::loadClass('CT_Controller_Action');
	Zend_Loader::loadClass('CT_Validation_Validator_Factuur');
	Zend_Loader::loadClass('CT_Mailer');
	Zend_Loader::loadClass('CT_Smarty_String');
	Zend_Loader::loadClass('CT_VAT');
	Zend_Loader::loadClass('CT_Clieop_CAS');

	$config = Zend_Registry::get('config');

	/**
	 * Handles invoice related tasks (except for payment.)
	 * 
	 * @package    Controllers 
	 */
	class FactuurController extends CT_Controller_Action 
	{

		/**
		 * De standaard pagina, laat een lijst met beschikbare opties zien.
		 */
		public function indexAction() 
		{
			$this->requireValidUser();

			$this->getResponse()->appendBody(
				$this->_smarty->fetch("factuur/index.tpl"));
		}


		/**
		 * Laat het formulier nieuwe factuur zien. 
		 */
		public function nieuwAction() 
		{
			$this->requireUserType(array(CT_User::Directie));
			$errors = array();

			$klantnummer = intval($this->_getParam('klant'));

			if($klantnummer != 0)
				$this->_smarty->assign("klantnummer", $klantnummer);

			$this->displayInvoiceEditor(array('datum' => date('Y/m/d')),  CT_VAT::getDefaultVATCategory(), $errors);			
		}


		/**
		 * Laat een voorbeeld van de factuur zien, maar maakt hem nog niet. 
		 */
		public function voorbeeldAction()
		{			
			$this->requireUserType(array(CT_User::Directie));

			$tbl_klanten = new CT_Db_Klanten();
			
			$factuur = $this->leesFactuurUitArray($_POST);
			$btw_categorie = $_POST['btw_categorie'];
			
			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Factuur();
			$validator->validate($factuur, $errors);
			
			/* Display error page, or show preview */
			if($errors->hasErrors()) {
				$this->displayInvoiceEditor($factuur, $btw_categorie, $errors);
			} else {				
				$factuur = CT_Db_Facturen::addCalculatedFields($factuur);
				
				$klanten   = $tbl_klanten->find($factuur['klantnummer'])->toArray();
				$klant = $klanten[0];

				$mail_body = wrapMultiline($this->generateEmail($klant, $factuur), 75);
				
				$this->_smarty->assign('mail_body', $mail_body);

				$factuur['datum'] = date('d/m/Y', strtotime($factuur['datum']));
				
				$this->_smarty->assign('factuur', $factuur);
				$this->_smarty->assign('btw_categorie', $btw_categorie);
				
				$this->getResponse()->appendBody(
					$this->_smarty->fetch("factuur/voorbeeld.tpl"));
			}
		}


		/**
		 * Laat een voorbeeld van de factuur in PDF zien, maar maakt hem nog niet. 
		 */
		public function voorbeeldpdfAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$config = Zend_Registry::get('config');

			$tbl_klanten  = new CT_Db_Klanten();

			$factuur = $this->leesFactuurUitArray($_POST);

			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Factuur();
			$validator->validate($factuur, $errors);

			/* Display error or show preview */
			if($errors->hasErrors()) {
				// Display error/
			} else {
				$factuur = CT_Db_Facturen::addCalculatedFields($factuur);

				$klanten   = $tbl_klanten->find($factuur['klantnummer'])->toArray();
				$klant = $klanten[0];

				$factuur['uiterstedatum'] = date('Y/m/d/',
					strtotime($factuur['datum']) + 60 * 60 * 24 * $config->invoice->payment_due_delta);

				$pdf = CT_Invoice::generatePDFFromArray($klant, $factuur);

				if($pdf == '')
					throw new Exception('Kan PDF niet genereren.');

				if($this->getResponse()->canSendHeaders()) {
					$this->getResponse()->setHeader('Content-type', 'application/pdf');
					$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="voorbeeld.pdf"');
				}

				$this->getResponse()->appendBody($pdf);
			}
		}


		/**
		 * Geeft de mogelijkheid een voorbeeld factuur opnieuw te bewerken.
		 */
		public function herzienAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$factuur = $this->leesFactuurUitArray($_POST);
			$btw_categorie = $_POST['btw_categorie'];

			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Factuur();
			$validator->validate($factuur, $errors);

			$this->displayInvoiceEditor($factuur, $btw_categorie, $errors);
		}


		/**
		 * Controleert de factuur en maakt hem indien hij correct is.
		 */ 
		public function maakAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$config = Zend_Registry::get('config');

			$factuur = $this->leesFactuurUitArray($_POST);
			$btw_categorie = $_POST['btw_categorie'];

			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Factuur();
			$validator->validate($factuur, $errors);

			if($errors->hasErrors()) {
				$this->displayInvoiceEditor($factuur, $btw_categorie, $errors);
			} else {
				$tbl_facturen = new CT_Db_Facturen();
				$tbl_klanten = new CT_Db_Klanten();
				$tbl_regels = new CT_Db_Factuurregels();

				try {
					$factuur['incasso'] = 0;
					$factuur['datum'] = date('Y/m/d', strtotime($factuur['datum']));
					$factuur['uiterstedatum'] = date('Y/m/d',
						strtotime($factuur['datum']) + 60 * 60 * 24 * $config->invoice->payment_due_delta);

					$factuurvolgnummer = $tbl_facturen->insert($factuur);
				} catch(Exception $e) {
					echo($e->getMessage());
					/* User visible error message... */
					$errors->reject("Er is een fout opgetreden tijdens de database transactie. Transactie is teruggedraaid.");

					/* Let's be nice and log the actual error message... */
					$logger = Zend_Registry::get("logger");
					$logger->log("[Factureren] Transactie fout: " . $e->getMessage(), Zend_Log::CRIT);

					$this->displayInvoiceEditor($factuur, $btw_categorie, $errors);	
					exit(1);
				}

				/* TODO: We rely on emailPDF to generate a cached version.
						Should make the cache generation more explicit */
				echo('whoot');
				//$pdf = $this->retreivePDF($factuurvolgnummer);
				$this->emailPDF($factuurvolgnummer);

				$this->redirect('/index');
			}
		}


		/**
		 * Sends the invoice again.
		 */
		public function herzendenAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$config = Zend_Registry::get('config');

			try {
				$tbl_facturen = new CT_Db_Facturen();
				$factuur = array_pop($tbl_facturen->find(intval($this->_getParam('id')))->toArray());
				
				$factuur = CT_Db_Facturen::addCalculatedFields($factuur);
				
				if(!is_array($factuur))
					throw new Exception("Ongeldige factuur");
			
				$tbl_klanten = new CT_Db_Klanten();
				$klant = array_pop($tbl_klanten->find(intval($factuur['klantnummer']))->toArray());
				
				if(!is_array($klant))
					throw new Exception("Ongeldige klant");					
			} catch(Exception $e) {
				throw $e;
			}

			if(array_key_exists('tekst', $_POST)) {
				$onderwerp = $_POST['onderwerp'];
				$begeleidendetekst = $_POST['tekst'];
				
				$copy_to = $_POST['email'];
				
				if(strlen($copy_to) == 0)
					$copy_to = NULL;
				
				$this->emailPDF($factuur['volgnummer'], $copy_to, $onderwerp, $begeleidendetekst);

				$this->redirect('/factuur/index');
			} else {
				$email = $this->generateEmailFromTemplate($klant['emailtemplate'], $klant, $factuur);
				
				$herzenden = array('onderwerp' => $email['onderwerp'], tekst => $email['inhoud']);
			
				$this->_smarty->assign('klant', $klant);
				$this->_smarty->assign('factuur', $factuur);
				$this->_smarty->assign('herzenden', $herzenden);

				$this->getResponse()->appendBody(
					$this->_smarty->fetch("factuur/herzenden.tpl"));
			}
		}


		/**
		 * Retourneert de pdf voor een bepaalde factuur 
		 */
		public function bekijkAction()
		{
			$this->requireUserType(array(CT_User::Klant, CT_User::Boekhouding, CT_User::Directie));

			$tbl_facturen = new CT_Db_Facturen();
			$config = Zend_Registry::get('config');
			$filename = $this->_getParam('naam');

			/* Haal het boekjaar en factuurvolgnummer uit de bestandsnaam */
			$formatter = Zend_Registry::get('formatter');

			try {
				$details = $formatter->getInvoiceDetailsFromFilename($filename);
			} catch(Exception $e) {
				$this->showErrorAndExit("Ongeldig factuurnummer");
			}

			$boekjaar = $details['boekjaar'];
			$factuurnummer = $details['factuurnummer'];

			/* Haal de juiste PDF op */
			$factuur = $tbl_facturen->findByFactuurnummer($boekjaar, $factuurnummer);

			// Check whether this invoice actually belongs to the current user...
			if($this->_user->getUserType() == CT_User::Klant) {
				$klantnr = $formatter->getCustomerRefFromUsername($this->_user->getUsername());

				if($factuur['klantnummer'] != $klantnr) {
					$this->showErrorAndExit("De opgevraagde factuur hoort bij een andere klant");
				}
			}

			if($factuur) {
				$pdf = CT_Invoice::retreivePDF($factuur['volgnummer']);			

				if($this->getResponse()->canSendHeaders())
					$this->getResponse()->setHeader('Content-type', 'application/pdf');

				$this->getResponse()->appendBody($pdf);
			} else {
				$this->showErrorAndExit("Factuurnummer niet in gebruik");
			}
		}


		/**
		 * Maakt een Clieop03 file met alle nog niet verwerkte incassos
		 */
		public function clieopAction()
		{
			$facturen = new CT_Db_Facturen;
			
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie));

			$config = Zend_Registry::get('config');
			$formatter = Zend_Registry::get('formatter');

			$selectQuery = $facturen->getBasicQuery();
			$selectQuery->where(
				'facturen.incasso',
				'facturen.volgnummer NOT IN (SELECT factuurvolgnummer FROM betalingen)) AND (facturen.volgnummer NOT IN (SELECT factuurvolgnummer FROM incassos))'
				);

			try {				
				$result = Zend_Registry::get('database')->query($selectQuery);				

				$incassos = array();
				$subtotaal = 0;
				$totaal = 0;

				foreach($result->fetchAll() as $row) {
					$tbl_klanten = new CT_Db_Klanten();
					$klant = array_pop($tbl_klanten->find(intval($row['klantnummer']))->toArray());
					$row['klant'] = $klant;

					$incassos[$row['volgnummer']] = $row;
					$subtotaal += $row['subtotaal'];
					$totaal += $row['totaal'];
				}
			} catch (Exception $e) {
				$this->showErrorAndExit($e->getMessage());			
			}

			$clieop = CT_Clieop_CAS::createFromDirectDebit($incassos);

			header('Content-type: text/plain');
			$this->getResponse()->appendBody($clieop->getFile());
		}


		/**
		 * Marks the direct-debt invoice as processed.
		 */
		public function invoerenAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$factuurvolgnummer = intval($this->_getParam('id'));

			/* Incasso flag set? */
			$tbl_facturen = new CT_Db_Facturen();
			$factuur = array_pop($tbl_facturen->find(intval($this->_getParam('id')))->toArray());

			if(!$factuur['incasso']) {
				$this->_smarty->assign('message', 'Deze factuur is niet per incasso voldaan.');
				$this->getResponse()->appendBody($this->_smarty->fetch('error.tpl'));

				return;
			}

			/* Already paid for? */
			$query = "SELECT datum FROM incassos WHERE factuurvolgnummer = " . $factuurvolgnummer;      
			$result = Zend_Registry::get('database')->query($query)->fetchAll();

			if(count($result) > 0) {
				$directDebit = array_pop($result);
           
				$this->_smarty->assign('message', 'Deze factuur is ingevoerd op ' . $directDebit['datum']);
				$this->getResponse()->appendBody($this->_smarty->fetch('error.tpl'));

				return;
			}
		  		  
			$incasso = array("factuurvolgnummer" => $factuurvolgnummer,
				"datum" => date('Y-m-d'));

			$tbl_incassos	= new CT_Db_Incassos();
			$tbl_incassos->insert($incasso);

			$this->redirect('/factuur/incasso');
		}

		
		/**
		 * Maakt een grote PDF met alle zoek-resultaten.
		 * FIXME: This is broken, need a test suite
		 */
		public function zoekresultaatalspdfAction()
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie, CT_User::Klant));

			$parameters = $this->getSearchParams($_POST);

			$facturen = new CT_Db_Facturen();

			$selectQuery = $facturen->getBasicQuery();
			$selectQuery = $this->addCustomerClause($selectQuery);
			$selectQuery = $this->extendQueryWithSearchParams($selectQuery, $parameters);

			$stmt = $facturen->getAdapter()->query($selectQuery);

			$results = $stmt->fetchAll();

			$factuurvolgnummers = array();

			foreach($results as $row)
				$factuurvolgnummers[] = intval($row['volgnummer']);

			// Allow script to use 10 seconds per invoice
			set_time_limit(count($factuurvolgnummers) * 10);
			$filename = CT_Invoice::generateCombinedPDF($factuurvolgnummers);

			try {
				$file = fopen($filename, 'r');
				
				if($this->getResponse()->canSendHeaders()) {
					$this->getResponse()->setHeader('Content-type', 'application/pdf');
					$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="facturen.pdf"');
				}
				
				// Send headers
				$this->getResponse()->sendResponse();

				// Turn off output buffering and send file
				//  directly to user.
				ob_end_flush();
				
				fpassthru($file);
				fclose($file);				
			} catch(Exception $e) {
			}
			
			unlink($filename);		
		}


		/**
		 * Laat het factuur-bewerk-formulier zien.
		 *
		 * @param array $factuur
		 * @param object $errors
		 */
		private function displayInvoiceEditor($factuur, $btw_categorie, &$errors = array())
		{
			$factuur['datum'] = date('d/m/Y', strtotime($factuur['datum']));

			/**
			 * Aantal initiele regels op de factuur, dit is GEEN maximum.
			 */
			$this->_smarty->assign("aantalregels", 18);
			$this->_smarty->assign("factuur", $factuur);
			$this->_smarty->assign("btw_categorie", $btw_categorie);
			$this->_smarty->assign("btw_tarieven", CT_VAT::getVATCategories());
			$this->_smarty->assignByRef("errors", $errors);

			if(array_key_exists('klantnummer', $factuur) && $factuur['klantnummer'] > 0) {
				$tbl_klanten = new CT_Db_Klanten();
				$klant = array_pop($tbl_klanten->find($factuur['klantnummer'])->toArray());
				$this->_smarty->assign('klant', $klant);
			}

			$this->getResponse()->appendBody(
				$this->_smarty->fetch("factuur/nieuw.tpl"));
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

			// Stel zoek parameters vast.
			if($this->_user->getUserType() == CT_User::Klant) {
				$parameters['klantnummer'] = 
					$formatter->getCustomerRefFromUsername($this->_user->getUsername());
			} elseif(array_key_exists('klantnummer', $source) && intval($source['klantnummer'] != 0)) {
				$parameters['klantnummer'] = intval($source['klantnummer']);
			}

			if(array_key_exists('boekjaar', $source) && intval($source['boekjaar']) != 0)
				$parameters['boekjaar'] = intval($source['boekjaar']);

			if(array_key_exists('boekmaand', $source) && intval($source['boekmaand']) != 0)
				$parameters['boekmaand'] = intval($source['boekmaand']);

			if(array_key_exists('factuurnummer', $source) && intval($source['factuurnummer']) != 0)
				$parameters['factuurnummer'] = intval($source['factuurnummer']);

			return $parameters;
		}


		/**
		 * Reads invoice data from an array, such as $_POST.
		 *
		 * @param array $bron The source from which to read the invoice data
		 *
		 * @return array Default invoice
		 */
		private function leesFactuurUitArray($bron)
		{
			$btw_categorie = $bron['btw_categorie'];
			$data = CT_VAT::getVATById($btw_categorie);

			/* Read from values from POST */
			$factuur = array(
				'klantnummer' => intval($bron['klantnummer']),
				'datum' => date('Y-m-d', parse_date($bron['datum'])),
				'kortingtype' => getValueFromArray($bron, 'kortingtype', ''),
				'btw_percentage' => $data['rate'],
				'tekst' => getValueFromArray($bron, 'tekst', ''));

			if(array_key_exists('incasso', $bron)) {
				$factuur['incasso'] = $bron['incasso'] == 'on' || intval($bron['incasso']) != '0';
			} else { 
				$factuur['incasso'] = '';
			}

			if(array_key_exists('kortingtype', $factuur) && array_key_exists('korting', $bron)) {
				if($factuur['kortingtype'] == 'absolute') {
					$factuur['korting'] = intval(100 * floatval(str_replace(',', '.', $bron['korting'])));
				} else {
					$factuur['korting'] = floatval(str_replace(',', '.', $bron['korting']));
				}
			} else {
				$factuur['korting'] = 0;
			}

			$factuur_regels = array();
	
			if(is_array($bron['artikelcode'])) {
				foreach($bron['artikelcode'] as $id=>$code) {
					
					/* 
						The reference field points to an entry in the factureren table. This field 
						allows us to keep track of not-yet-deleted entries.
					*/
					$factuur_regel = array(
						"ref" => getValueFromArray(getValueFromArray($bron, 'ref'), $id),
						"factuurregel" => $id + 1,
						"artikelcode" => $code,
						"omschrijving" => getValueFromArray(getValueFromArray($bron, 'omschrijving'), $id));

					if($bron['aantal'][$id] == '')
						$factuur_regel['aantal'] = NULL;
					else
						$factuur_regel['aantal'] = floatval(str_replace(',', '.', $bron['aantal'][$id]));

					if($bron['prijs'][$id] == '')
						$factuur_regel['prijs'] = NULL;
					else
						$factuur_regel['prijs'] = round(100 * floatval(str_replace(',', '.', $bron['prijs'][$id])));

					if($factuur_regel['aantal'] == 0 && $factuur_regel['prijs'] == 0) {
						$factuur_regel['aantal'] = NULL;
						$factuur_regel['prijs'] = NULL;
					}

					$factuur_regels[$id + 1] = $factuur_regel;
				}
			}

			$factuur['regels'] = $factuur_regels;

			return $factuur;
		}

		
		/** **********************************************************
		 ** Various lists
		 ** **********************************************************/		
		
		
		/**
		 * Lijst met incassos.
		 */
		public function incassoAction()
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie, CT_User::Klant));

			$facturen = new CT_Db_Facturen();

			$selectQuery = $facturen->getBasicQuery();
			$selectQuery = $this->addCustomerClause($selectQuery);

			$selectQuery = $selectQuery->where("facturen.incasso AND (facturen.volgnummer NOT IN (SELECT factuurvolgnummer FROM betalingen)) AND (facturen.volgnummer NOT IN (SELECT factuurvolgnummer FROM incassos))");

			$this->displayQueryResults($selectQuery, 'incasso.tpl');
		}


		/**
		 * Laat een lijst met openstaande facturen zien.
		 */
		public function openstaandAction() 
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie, CT_User::Klant));

			$facturen = new CT_Db_Facturen();

			$selectQuery = $facturen->getBasicQuery();
			$selectQuery = $this->addCustomerClause($selectQuery);
			$selectQuery = $selectQuery->where("facturen.volgnummer NOT IN (SELECT factuurvolgnummer FROM betalingen)");

			$this->displayQueryResults($selectQuery, 'openstaand.tpl');
		}


		/**
		 * Zoek een factuur op basis van verschillende veld-waarden.
		 */
		public function zoekAction()
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie, CT_User::Klant));

			$facturen = new CT_Db_Facturen();

			$parameters = $this->getSearchParams($_GET);
			$this->_smarty->assign("parameters", $parameters);

			$selectQuery = $facturen->getBasicQuery();
			$selectQuery = $this->addCustomerClause($selectQuery);
			$selectQuery = $this->extendQueryWithSearchParams($selectQuery, $parameters);

			$this->displayQueryResults($selectQuery, 'opvragen.tpl');
		}

		
		/**
		 * Runs and SQL query and displays the result in a paginated, sortable
		 * table. Note that sorting statements are added to the query by this function, 
		 * therefore one must not add those manually.
		 *
		 * @param Zend_Db_Select $query The query to run
		 * @param string $template The template to use
		 */
		private function displayQueryResults($query, $template)
		{
			$config = Zend_Registry::get('config');

			$this->_smarty->assign('payment_due_delta', $config->invoice->payment_due_delta);

			// First fetch the grand totals (maybe we should cache this?)		
			$queryStr = "SELECT SUM(subquery.subtotaal) AS subtotaal, SUM(subquery.totaal) AS totaal FROM ($query) AS subquery";
			$stmt = Zend_Registry::get('database')->query($queryStr);
			
			$record = array_pop($stmt->fetchAll());
			
			$this->_smarty->assign('subtotaal', $record['subtotaal']);
			$this->_smarty->assign('totaal', $record['totaal']);

			// The call the shared-query result function
			$this->genericTableAction($query, 'factuur/' . $template);
		}
		
		
		/** **********************************************************
		 ** Query building
		 ** **********************************************************/


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
				$query = $query->where('facturen.klantnummer = ?', $params['klantnummer']);

			if(array_key_exists('boekjaar', $params))
				$query = $query->where('YEAR(datum) = ?', $params['boekjaar']);

			if(array_key_exists('boekmaand', $params))
				$query = $query->where('MONTH(datum) = ?', $params['boekmaand']);

			if(array_key_exists('factuurnummer', $params))
				$query = $query->where('factuurnummer = ?', $params['factuurnummer']);

			return $query;
		}


		/**
		 * Adds a clause to an SQL statement which makes sure only results
		 * belonging to the currently logged in customer are available.
		 *
		 * @param Zend_Db_Select $query The basic query
		 *
		 * @return Zend_Db_Select Query extended with the customer extension clause
		 */
		private function addCustomerClause($query)
		{
			$formatter = Zend_Registry::get('formatter');

			if($this->_user->getUserType() == CT_User::Klant) {
				$query = $query->where("facturen.klantnummer = ?", 
					$formatter->getCustomerRefFromUsername($this->_user->getUsername()));
			}

			return $query;
		}
	

		/** **********************************************************
		 ** Email functions
		 ** **********************************************************/

		
		/**
		 * Genereert een begleidende email gebaseert op een template.
		 *
		 * @param $template_id Het nummer van de basis template
		 * @param $klant Klantgegevens voor op de factuur
		 * @param $factuur Factuurgegevens (incl. regels en totalen)
		 * 
		 * @return array Onderwerp en inhoud van de e-mail
		 */
		private function generateEmailFromTemplate($template_id, $klant, $factuur)
		{
			$config = Zend_Registry::get('config');
			$formatter = Zend_Registry::get('formatter');

			$emailtemplates = new CT_Db_Emailtemplates();
			$emailtemplate = array_pop($emailtemplates->find($template_id)->toArray());

			/* Initializeer templating engine */
			$smarty_string = new CT_Smarty_String($config, $formatter);

			$smarty_string->assign("factuur", $factuur);
			$smarty_string->assign("klant", $klant);

			$onderwerp = $smarty_string->fetch($emailtemplate['onderwerp']);
			$inhoud = $smarty_string->fetch($emailtemplate['inhoud']);

			return array('onderwerp' => $onderwerp, 'inhoud' => $inhoud);
		}
		

		/**
		 * Genereert een begleidende email voor een factuur.
		 *
		 * @param array $klant Klant gegevens
		 * @param array $factuur Factuur gegevens (incl. regels en totalen)
		 *
		 * @return string Inhoud van de e-mail
		 */
		private function generateEmail($klant, $factuur)
		{
			$config = Zend_Registry::get('config');
			$formatter = Zend_Registry::get('formatter');
			
			/* Initializeer templating engine */
			$smarty_string = new CT_Smarty_String($config, $formatter);

			$smarty_string->assign("factuur", $factuur);
			$smarty_string->assign("klant", $klant);

			return $smarty_string->fetch($factuur['tekst']);
		}


		/**
		 * Stuurt een email met de factuur naar zowel de klant als het
		 *  in de config.xml opgegeven e-mailadres.
		 *
		 * @param int $factuurvolgnummer
		 * @param string $copy_to Emailadres waar een copie naartoe gestuurd wordt
		 * @param string $onderwerp Het onderwerp van het email bericht
		 * @param string $begeleidendetekst Tekst welke de automatisch gegenereerde tekst vervangt
		 */
		private function emailPDF($factuurvolgnummer, $copy_to = NULL, $onderwerp = NULL, $begeleidendetekst = NULL)
		{
			$config = Zend_Registry::get('config');

			$tbl_facturen = new CT_Db_Facturen();
			$tbl_klanten = new CT_Db_Klanten();
			$tbl_regels	= new CT_Db_Factuurregels();


			/* Haal de complete factuur uit de database (inclusief regels */
			$facturen = $tbl_facturen->find($factuurvolgnummer)->toArray();
			$factuur  = $facturen[0];

			$where = $tbl_regels->getAdapter()->quoteInto("factuurvolgnummer = ?", $factuurvolgnummer);
			$factuur_regels = $tbl_regels->fetchAll($where)->toArray();
			$factuur['regels'] = $factuur_regels;

			$factuur = CT_Db_Facturen::addCalculatedFields($factuur);

			/* Haal de klant uit de database */
			$klanten = $tbl_klanten->find($factuur['klantnummer'])->toArray();
			$klant   = $klanten[0];


			/* Haal de factuur op */
			$formatter = Zend_Registry::get('formatter');
			$filename = $formatter->getInvoiceRef($factuur) . '.pdf';

			$pdf = CT_Invoice::retreivePDF($factuurvolgnummer);

			if($onderwerp == NULL) {
				$emailtemplates = new CT_Db_Emailtemplates();
				$emailtemplate = array_pop($emailtemplates->find(intval($klant['emailtemplate']))->toArray());
				$onderwerp = $emailtemplate['onderwerp'];

				$config = Zend_Registry::get('config');
				$formatter = Zend_Registry::get('formatter');
			
				/* Initializeer templating engine */
				$smarty_string = new CT_Smarty_String($config, $formatter);

				$smarty_string->assign("factuur", $factuur);
				$smarty_string->assign("klant", $klant);

				$onderwerp = $smarty_string->fetch($onderwerp);
			}

			if($begeleidendetekst == NULL) {
				$mail_body = wrapMultiline(iconv("UTF-8", "ISO-8859-1", 
					$this->generateEmail($klant, $factuur)), 75);
			} else {
				$mail_body = iconv("UTF-8", "ISO-8859-1", $begeleidendetekst);
			}

			$recipients = array();

			if(strlen($klant['factuuremail']) > 0)
			{
				/* Recipient name */
				if($klant['klanttype'] == 0)
					$customer_name = '"' . $klant['bedrijfsnaam'] . '"';
				else
					$customer_name = '"' . $klant['aanhef'] . ' ' . $klant['voornaam'] . ' ' . $klant['achternaam'] . '"';

				$email_addresses = explode(',', $klant['factuuremail']);

				foreach($email_addresses as $email_address) {
					$recipient = iconv("UTF-8", "ISO-8859-1", ($customer_name . ' <' . trim($email_address) . '>'));
					$recipients[] = $recipient;
				}
			} else {
				$mail_body = wrapMultiline('De volgende e-mail kon niet worden verzonden ' .
						'omdat geen factuur e-mail adres is opgegeven.', 75) .
						"\n\n----\n\n" . $mail_body;

				$recipients[] = iconv("UTF-8", "ISO-8859-1", $config->mailer->return_path);
			}

			if($copy_to != NULL) {
				$recipients[] = iconv('UTF-8', 'ISO-8859-1', $copy_to);
			}

			$onderwerp = iconv('UTF-8', 'ISO-8859-1', $onderwerp);

			/* Send mail */
			try {
				$mail = new CT_Mailer();

				$mail->addAttachment($pdf, $filename);

				$mail->setSubject($onderwerp);
				$mail->setText($mail_body);

				$mail->send($recipients);
			} catch (Exception $e) {
				throw new Exception('Kan de facturatie email niet versturen.');
			}
		}


		public function noRouteAction()
		{
			$this->redirect('/');
		}

	}

?>
