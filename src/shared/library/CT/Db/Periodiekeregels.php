<?php
	/**
	 * Abstractie van de 'periodiekeregels' database tabel
 	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Db
	 */

	Zend_Loader::loadClass('Zend_Db_Table');
	Zend_Loader::loadClass('CT_Db_Factureren');
	Zend_Loader::loadClass('CT_Db_Perioden');
	Zend_Loader::loadClass('CT_Smarty_String');

	/**
	 * Abstractie van de 'periodiekeregels' database tabel
	 * 
	 * @package CT_Db 
 	 */
	class CT_Db_Periodiekeregels extends Zend_Db_Table 
	{ 

		/**
		 * De naam van de tabel
		 *
		 * @var _name
		 */
		protected $_name = 'periodiekeregels';


		/**
		 * De primaire sleutel van de periodiekeregels tabel
		 *
		 * @var _primary
		 */
		protected $_primary = 'volgnummer';


		/**
		 * Voegt een nieuwe periodieke regel toe aan de database.
		 *
 		 * @param  array $periodiekeregel	De periodieke regel
		 * @return int 	 					Het volgnummer van de nieuwe regel
		 */
		public function insert(array $periodiekeregel) 
		{
			$pr_table = new CT_Db_Perioden();

			$db = $this->getAdapter();

			$db->beginTransaction();

			try {
				$perioden = $periodiekeregel['perioden'];
				unset($periodiekeregel['perioden']);

				$volgnummer = parent::insert($periodiekeregel);

				if(is_array($perioden)) {
					foreach($perioden as $periode) {
						$periode['periodiekeregel'] = $volgnummer;

						$pr_table->insert($periode);
					}
				}

				$db->commit();
			} catch (Exception $e) {
				$db->rollBack();
				throw $e;
			}

			return $volgnummer;
		}

		/**
		 * Werkt een bestaande periodieke regel bij. Als een 'perioden'
		 * array present is worden ook de perioden bijgewerkt. Als deze
		 * ontbreekt dan worden deze niet aangepast.
		 *
		 * Omdat nested transactions niet worden ondersteund in MySQL worden
		 * transacties uitgeschakeld als de perioden niet geupdate hoeven
		 * te worden.
		 *
		 * @param array $periodiekeregel	Een bijgewerkte periodieke regel
		 */
		public function update(array $periodiekeregel, $where)
		{
			$updatePerioden = array_key_exists('perioden', $periodiekeregel);

			if($updatePerioden) {
				if(!array_key_exists('volgnummer', $periodiekeregel))
					throw new Exception('CT_Db_Periodiekeregels::update requires volgnummer');

				$perioden = $periodiekeregel['perioden'];
				unset($periodiekeregel['perioden']);

				$volgnummer = $periodiekeregel['volgnummer'];
				unset($periodiekeregel['volgnummer']);
			}

			$pr_table = new CT_Db_Perioden();
			$db = $this->getAdapter();

			if($updatePerioden)
				$db->beginTransaction();

			try {
				$return_code = parent::update($periodiekeregel, $where);

				if($updatePerioden) {
					$this->updatePeriods($volgnummer, $perioden);				
					$db->commit();
				}

			} catch (Exception $e) {
				if($updatePerioden)
					$db->rollBack();
				throw $e;
			}

			return $return_code;
		}

		/**
		 * Werkt de perioden van een periodieke regel bij.
		 *
		 * FIXME: Deze functie is verre van optimaal!
		 *
		 * @param int $volgnummer	Het volgnummer van de periodieke regel
		 * @param array $perioden	De perioden waarin de regel moet worden gefactureerd
		 */
		public function updatePeriods($volgnummer, array $perioden)
		{
			$pr_table = new CT_Db_Perioden();			
			$db = $pr_table->getAdapter();

			$pr_table->delete($db->quoteInto('periodiekeregel = ?', $volgnummer));
				
			if(is_array($perioden)) {
				foreach($perioden as $periode) {
					$periode['periodiekeregel'] = $volgnummer;

					$pr_table->insert($periode);
				}
			}			
		}

		/**
		 * Retourneert een lijst met regels die deze maand gefactureerd
		 * kunnen/moeten worden. 
 		 *
		 * @param Zend_Db_Row $periodiekeregel 	De periodieke regel
		 * @return array 						Een array met te factureren regels
		 */
		static private function generateNewInvoiceLines($periodiekeregel, $until_date = NULL)
		{
			$pr_table = new CT_Db_Perioden();
			$perioden = $pr_table->fetchAll('periodiekeregel = ' . intval($periodiekeregel->volgnummer));

			$config = Zend_Registry::get('config');
			$formatter = Zend_Registry::get('formatter');

			$smarty = new CT_Smarty_String($config, $formatter);

			$target = $periodiekeregel->laatstgefactureerd;

			$factureren_regels = array();

			for($i = relativeMonth($target, $until_date) + 1; $i <= relativeMonth($until_date, $until_date); $i++) {

				$month = monthFromRelativeMonth($i);

				foreach($perioden as $periode) {
					
					if($periode->maand == $month) {
						$smarty->assign('maand', $month);
						$smarty->assign('jaar', yearFromRelativeMonth($i, $until_date));

						$factureren_regel = array(
							'klantnummer' => $periodiekeregel->klantnummer,
							'artikelcode' => $periodiekeregel->artikelcode,
							'omschrijving' => $smarty->fetch($periodiekeregel->omschrijving),
							'aantal' => $periodiekeregel->aantal,
							'prijs' => $periodiekeregel->prijs);

						$factureren_regels[] = $factureren_regel;	
					}
				}
			}

			return $factureren_regels;
		}
	

		/**
		 * Loopt alle periodieke facturen na en kopieert ze naar de queue
		 * als dat nodig is.
		 *
		 * @param string $until_date	Datum tot waar facturen worden verwerkt
		 */
		static public function processPeriodicInvoices($until_date = NULL)
		{
			if($until_date == NULL)
				$until_date = date('Y/m/d');

			$table_pr = new CT_Db_Periodiekeregels();
			$periodiekeregels = $table_pr->fetchAll('YEAR(laatstgefactureerd) < YEAR("' . $until_date . '") OR MONTH(laatstgefactureerd) < MONTH("' . $until_date . '")');

			$table_fr = new CT_Db_Factureren();

			foreach($periodiekeregels as $periodiekeregel) {
				$facturerenregels = CT_Db_Periodiekeregels::generateNewInvoiceLines($periodiekeregel, $until_date);	

				$db = $table_fr->getAdapter();
				
				$db->beginTransaction();

				try {
					foreach($facturerenregels as $facturerenregel) 
						$table_fr->insert($facturerenregel);
					
					$periodiekeregel->laatstgefactureerd = $until_date;
					
					$periodiekeregel->save();

				} catch(Exception $e) {
					$db->rollBack();

					$logger = Zend_Registry::get('logger');
					$logger->log('[Periodiekeregels] : ' . $e->getMessage(), Zend_Log::CRIT);

					// TODO: Hier moet wellicht een e-mail achteraan?!

					continue;
				}

				$db->commit();
			}
		}
	}
