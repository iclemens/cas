<?php
	/**
	 * BetalingController, Project CAS
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Db_Facturen');
	Zend_Loader::loadClass('CT_Db_Klanten');
	Zend_Loader::loadClass('CT_Db_Betalingen');
	Zend_Loader::loadClass('CT_iDEAL_Result');
	Zend_Loader::loadClass('CT_Mailer');
	Zend_Loader::loadClass('CT_Betaling_Methode');	
	Zend_Loader::loadClass('CT_Controller_Action');

	/**
	 * Handles the payment of invoices
	 *
	 * @package Controllers
	 */
	class BetalingController extends CT_Controller_Action 
	{

		/**
		 * The default action, it redirects to /
		 */
		public function indexAction()
		{
			$this->redirect('/');
		}

		/**
		 * This function loads all data concerning the invoice
		 * to be payed. It also prepares an iDeal transaction.
		 *
		 * @todo The iDeal stuff should be initialized later (from smarty)
		 */
		public function betaalAction()
		{
			$this->requireUserType(array(CT_User::Klant));

			$config = Zend_Registry::get('config');
			$formatter = Zend_Registry::get('formatter');

			$factuurvolgnummer = intval($this->_getParam('id'));

			/* Retreive all information associated with this invoice */
			$tbl_facturen = new CT_Db_Facturen();
			$tbl_klanten	= new CT_Db_Klanten();
			$tbl_regels		= new CT_Db_Factuurregels();

			$factuur = $tbl_facturen->find($factuurvolgnummer)->toArray();
			$factuur = $factuur[0];

			$klant	 = $tbl_klanten->find($factuur['klantnummer'])->toArray();
			$klant   = $klant[0];

			$where = $tbl_regels->getAdapter()->quoteInto("factuurvolgnummer = ?", $factuurvolgnummer);
			$factuur['regels'] = $tbl_regels->fetchAll($where)->toArray();
							
			$factuur = CT_Db_Facturen::addCalculatedFields($factuur);

			/* Restrict access */
			$klantnummer = $formatter->getCustomerRefFromUsername(
					$this->_user->getUserName());

			if($factuur['klantnummer'] != $klantnummer) {
				$this->_smarty->assign('message', 'Helaas, je mag alleen je eigen facturen betalen!');
				$this->getResponse()->appendBody($this->_smarty->fetch('error.tpl'));
				
				return;
			}

			/* Already paid for? */
			$query = "SELECT datum FROM betalingen WHERE factuurvolgnummer = " . $factuurvolgnummer;      
			$result = Zend_Registry::get('database')->query($query)->fetchAll();
      
			if(count($result) > 0) {
				$payment = array_pop($result);
        
				$this->_smarty->assign('message', 'Deze factuur is reeds voldaan op ' . $payment['datum']);
				$this->getResponse()->appendBody($this->_smarty->fetch('error.tpl'));

				return;
			}

			$payment_options = explode(",", $config->payment->options);
			$methods = array();

			foreach($payment_options as $payment_option) {
				$class_name = 'CT_Betaling_Methode_' . $payment_option;

				Zend_Loader::loadClass($class_name);
				$method = new $class_name();
				$methods[] = $method->fetchOption($klant, $factuur);
			}

			$this->_smarty->assign('methoden', $methods);
			$this->_smarty->assign('factuur', $factuur);
			$this->_smarty->assign('klant', $klant);

			$this->_smarty->display("betaling/methoden.tpl");
		}

		/**
		 * This function is called by the administrator to confirm payment of an invoice.
		 *
		 * If a valid date is specified the state of the invoice is set
		 * to payed. If no valid date is specified the function will
		 * call for the 'afboeken' template.
		 */
		public function afboekenAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$factuurvolgnummer = intval($this->_getParam('id'));

			/* Already paid for? */
			$query = "SELECT datum FROM betalingen WHERE factuurvolgnummer = " . $factuurvolgnummer;      
			$result = Zend_Registry::get('database')->query($query)->fetchAll();

			if(count($result) > 0) {
				$payment = array_pop($result);
           
				$this->_smarty->assign('message', 'Deze factuur is reeds voldaan op ' . $payment['datum']);
				$this->getResponse()->appendBody($this->_smarty->fetch('error.tpl'));

				return;
			}
		  		  
		 	/* Boek de factuur af of vraag om de boek-datum */
			if(array_key_exists('datum', $_POST)) {

				$betaling = array("factuurvolgnummer" => $factuurvolgnummer,
					"datum" => date('Y-m-d', parse_date($_POST["datum"])));

				$tbl_betalingen	= new CT_Db_Betalingen();        
				$tbl_betalingen->insert($betaling);

				$this->redirect("/factuur/openstaand");

			} else {		
  				/* Retreive all information associated with this invoice */
  				$tbl_facturen = new CT_Db_Facturen();
  				$tbl_klanten	= new CT_Db_Klanten();
				$tbl_regels		= new CT_Db_Factuurregels();

				$factuur = array_pop($tbl_facturen->find($factuurvolgnummer)->toArray());
				$klant	 = array_pop($tbl_klanten->find($factuur['klantnummer'])->toArray());

				$where = $tbl_regels->getAdapter()->quoteInto("factuurvolgnummer = ?", $factuurvolgnummer);
				$factuur['regels'] = $tbl_regels->fetchAll($where)->toArray();
				
				$subtotaal = 0;

				$factuur = CT_Db_Facturen::addCalculatedFields($factuur);

				/* Display form */
				$this->_smarty->assign('factuur', $factuur);
				$this->_smarty->assign('klant', $klant);

				$this->getResponse()->appendBody(
					$this->_smarty->fetch('betaling/afboeken.tpl'));
			}
		}

		/**
		 * This function is called when the user returns from a 
		 * successful iDEAL transaction.
		 */
		public function idealSuccessAction()
		{
			$this->_smarty->display("betaling/ideal_success.tpl");
		}

		/**
		 * This function is called when the user returns from an
		 * unsuccessful iDEAL transaction.
		 */
		public function idealFailureAction()
		{
			$this->_smarty->display("betaling/ideal_failure.tpl");
		}

		/**
		 * This function is called by iDEAL and delivers the
		 * state (success or failure) of an iDEAL transaction in
		 * XML.
		 * After accepting the payment a confirmation e-mail is 
		 * sent to both the administrator and the user.
		 */
		public function idealCallbackAction()
		{ 
			$config = Zend_Registry::get('config');
			$formatter = Zend_Registry::get('formatter');

			$parser = new CT_iDEAL_Result();

			$xml = file_get_contents("php://input");

			Zend_Log::log("[Betaling] iDEAL XML ontvangen van " .	$_SERVER["REMOTE_ADDR"]);

			try {
				$parser->parse($xml);
				$parser->finish();

				$status = $parser->status;
			} catch(Exception $e) {
				Zend_Log::log("[Betaling] Kan iDEAL XML niet parsen: " . $e->getMessage(), Zend_Log::LEVEL_SEVERE);
			}

			if($status == "Success") {
				try {
					$tbl_klanten = new CT_Db_Klanten();
					$tbl_facturen = new CT_Db_Facturen();
					$tbl_betalingen = new CT_Db_Betalingen();

					$factuurnummer = $parser->purchaseID;

					$boekjaar = intval(substr($factuurnummer, 0, 4));
					$volgnummer = intval(substr($factuurnummer, 4, 4));

					$factuur = $tbl_facturen->findByFactuurnummer($boekjaar, $volgnummer);
					$klant   = $tbl_klanten->find($factuur['klantnummer'])->toArray();
					$klant   = $klant[0];

					$betaling = array("factuurvolgnummer" => $factuur['volgnummer'],
						"datum" => date("Y-m-d", time()));
					$tbl_betalingen->insert($betaling);
				} catch (Exception $e) {
					Zend_Log::log("[Betaling] Afboeken van " . $factuurnummer . "is mislukt: " . $e->getMessage(), Zend_Log::LEVEL_SEVERE);
					exit(1);
				}

				Zend_Log::log("[Betaling] Afboeken van " . $factuurnummer . " is gelukt.");

				/* Generate mail body */
				$this->_smarty->assign("factuur", $factuur);
				$this->_smarty->assign("klant", $klant);

				$mail_body = iconv("UTF-8", "ISO-8859-1", $this->_smarty->fetch("email_ideal_success.tpl"));

				/* Generate recipient name */
				if($klant['klanttype'] == 0)
					$mail_to = $klant['bedrijfsnaam'];
				else
					$mail_to = $klant['aanhef'] . " " . $klant['voornaam'] . " " . $klant['achternaam'];

				$mail_to .= " <" . $klant['factuuremail'] . ">";

				/* Send mail to _customer_*/
				$mail = new CT_Mailer();

				$mail->setSubject($config->company_name . ": iDEAL betaling");
				$mail->setText($mail_body);

				$mail->send($mail_to);
			}
		}

		/**
		 * This is the fallback action, just redirects to /
		 */
		public function noRouteAction()
		{
			$this->redirect('/');
		}
	}
