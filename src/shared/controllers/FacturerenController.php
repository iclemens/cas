<?php
	/**
	 * FacturerenController, Project CAS
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @package    Controllers
	 */

	Zend_Loader::loadClass('CT_Db_Klanten');
	Zend_Loader::loadClass('CT_Db_Factureren');
	Zend_Loader::loadClass('CT_Controller_Action');
	Zend_Loader::loadClass('CT_Validation_Validator_Factureren');

	/**
	 * De web-interface om de lijst met de factureren items
	 * te beheren wordt door de factureren controller beheert.
	 * 
	 * @package    Controllers
	 */
	class FacturerenController extends CT_Controller_Action 
	{

		public function indexAction() 
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie));

			$this->getResponse()->appendBody(
				$this->_smarty->fetch("factuur/index.tpl"));
		}

		public function lijstxmlAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			try {
				$tblFactureren = new CT_Db_Factureren();
				$factureren_regels = $tblFactureren->fetchAll(array('klantnummer = ?' => intval($this->_getParam('id'))));

				$this->_smarty->assign("factureren", array(
					"klantnummer" => intval($this->_getParam('id')),
					"regels" => $factureren_regels->toArray()));

				try {
					$this->getResponse()->setHeader('Content-Type', 'application/xml');
				} catch(Exception $e) {
					// Cannot send headers, just ignore this for a moment!
				}

				$this->getResponse()->appendBody(
					$this->_smarty->fetch('factureren/lijst_xml.tpl'));
			} catch(Exception $e) {
				throw new Exception('Kan lijst met te factureren items niet ophalen.');
			}
		}

		public function verwijderxmlAction()
		{
			$this->requireUserType(array(CT_User::Directie));
	
			try {
				$tblFactureren = new CT_Db_Factureren();
				$where = 'volgnummer = ' . intval($this->_getParam('id'));

				$cnt = $tblFactureren->delete($where);

				$this->_smarty->assign('success', $cnt);
				$this->_smarty->assign('volgnummer', intval($this->_getParam('id')));

				try {
					$this->getResponse()->setHeader('Content-Type', 'application/xml');
				} catch(Exception $e) {
					// Cannot send headers, just ignore this for a moment!
				}

				$this->getResponse()->appendBody(
					$this->_smarty->fetch('factureren/verwijder_xml.tpl'));
			} catch(Exception $e) {
				throw new Exception('Item bestaat niet');
			}
		}

		public function nieuwAction()
		{
			$this->requireUserType(array(CT_User::Directie));
			$this->_smarty->assign("klanten", CT_Db_Klanten::lijstMetKlanten());

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('factureren/nieuw.tpl'));
		}

		public function toevoegenAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$factureren = $this->leesRegelsUitArray($_POST);

			/* Make sure all fields are correctly filled in */
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Factureren();
			$validator->validate($factureren, $errors);

			if($errors->hasErrors()) {
				$this->displayFacturerenEditor($factureren, $errors);
				return;
			} else {
				$tblFactureren = new CT_Db_Factureren();

				/* Starts a transaction, should make sure that either all or none of the
						data are being inserted */
				$db = $tblFactureren->getAdapter();
				$db->beginTransaction();

				foreach($factureren['regels'] as $regel) {
					$regel['klantnummer'] = $factureren['klantnummer'];

					try {
						$tblFactureren->insert($regel);
					} catch(Exception $e) {
						$db->rollBack();

						/* User visible error message... */
						$errors->reject("Er is een fout opgetreden tijdens de database transactie. Transactie is teruggedraaid.");

						/* Let's be nice and log the actual error message... */
						$logger = Zend_Registry::get("logger");
						$logger->log("[Factureren] Transactie fout: " . $e->getMessage(), Zend_Log::CRIT);

						$this->displayFacturerenEditor($factureren, $errors);
						return;
					}
				}

				$db->commit();

				$this->redirect('/factureren/index');
			}
		}

		public function lijstAction()
		{
			$this->requireUserType(array(CT_User::Directie));

			$sql_totaal_regel = "FLOOR((IFNULL(aantal, 1) * prijs) + 0.5)";
			$sql_subtotaal = "(FLOOR(SUM($sql_totaal_regel) + 0.5) - korting)";
			$sql_btw = "FLOOR(($sql_subtotaal * btw_percentage / 100) + 0.5)";
			$sql_totaal = "($sql_subtotaal + $sql_btw)";

			$query = "SELECT factureren.*, klanten.*, " .
				"$sql_totaal_regel AS totaal " .				
				"FROM factureren, klanten " .
				"WHERE factureren.klantnummer = klanten.klantnummer";

			$result = Zend_Registry::get('database')->query($query);

			$this->_smarty->assign('factureren', $result->fetchAll());

			$this->getResponse()->appendBody(
				$this->_smarty->fetch('factureren/lijst.tpl'));
		}

		public function noRouteAction()
		{
			$this->_redirect('/');
		}

		/**
		 * Laat het factureren-bewerk-formulier zien.
		 *
		 * @param array $factureren
		 * @param object $errors
		 */
		private function displayFacturerenEditor($factureren, &$errors)
		{
			$this->_smarty->assignByRef("errors", $errors);
			$this->_smarty->assign("factureren", $factureren);

			$this->getResponse()->appendBody(
				$this->_smarty->fetch("factureren/nieuw.tpl"));
		}

		/**
		 * Stopt te factureren regels in een array.
		 *
		 * @param array $bron
		 * @return array
		 */
		private function leesRegelsUitArray($bron)
		{
			$factureren = array("klantnummer" => intval($bron['klantnummer']));

			$factureren_regels = array();

			if(is_array($bron['artikelcode'])) {
				foreach($bron['artikelcode'] as $id=>$code) {
					$factureren_regel =
						array("artikelcode" => $bron['artikelcode'][$id],
							"omschrijving" => $bron['omschrijving'][$id]);

					if($bron['aantal'][$id] == '')
						$factureren_regel['aantal'] = NULL;
					else
						$factureren_regel['aantal'] = floatval(str_replace(',', '.', $bron['aantal'][$id]));

					if($bron['prijs'][$id] == '')
						$factureren_regel['prijs'] = NULL;
					else
						$factureren_regel['prijs'] = round(100 * floatval(str_replace(',', '.', $bron['prijs'][$id])));

					if($factureren_regel['aantal'] == 0 && $factureren_regel['prijs'] == 0) {
						$factureren_regel['aantal'] = NULL;
						$factureren_regel['prijs'] = NULL;
					}

					if($factureren_regel['prijs'] != 0 || $factureren_regel['aantal'] != 0 ||
							strlen($factureren_regel['omschrijving']) > 0 || strlen($factureren_regel['artikelcode'] > 0))
						$factureren_regels[] = $factureren_regel;
				}
			}

			$factureren['regels'] = &$factureren_regels;
			return $factureren;
		}
	}
