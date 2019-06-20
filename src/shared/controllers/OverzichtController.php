<?php
	/**
	 * OverzichtController, Project CAS
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package Controllers
	 */

	Zend_Loader::loadClass('CT_Smarty_Paginate');
	Zend_Loader::loadClass('CT_Paginate_Db');
	Zend_Loader::loadClass('CT_Sort_Db');
	
	Zend_Loader::loadClass('CT_Db_Klanten');
	Zend_Loader::loadClass('CT_Db_Artikelcodes');
	Zend_Loader::loadClass('CT_Db_Emailtemplates');
	Zend_Loader::loadClass('CT_Controller_Action');

	/**
	 * Verschillende overzichten.
	 * 
	 * @package Controllers 
	 */
	class OverzichtController extends CT_Controller_Action 
	{

		/**
		 * De standaard pagina, laat een lijst met beschikbare opties zien. 
		 */
		public function indexAction() 
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie));
			
			$this->getResponse()->appendBody(
				$this->_smarty->fetch('overzicht/index.tpl')); 
		}


		/**
		 * Laat per artikelcode het totaal factuurbedrag zien.
		 */
		public function totaalperartikelcodeAction()
		{
			$this->requireUserType(array(CT_User::Boekhouding, CT_User::Directie));

			$artikelcodes = new CT_Db_Artikelcodes();
			$selectQuery = $artikelcodes->select();

			$sql_totaal_regel = "FLOOR((IFNULL(aantal, 1) * prijs) + 0.5)";
			
			$selectQuery->setIntegrityCheck(false)
				->from('artikelcodes',
					array(
					'artikelcodes.*',
					'aantal' => 'COUNT(' . $sql_totaal_regel . ')',
					'totaal' => 'SUM(' . $sql_totaal_regel . ')'))
				->group('artikelcode')
				->join('factuurregels', 'artikelcodes.artikelcode = factuurregels.artikelcode', array());

			$searchDef = array(
				'klantnummer' => 'factuurregels.factuurvolgnummer IN (SELECT volgnummer FROM facturen WHERE klantnummer = ?)',
				'boekjaar'    => 'factuurregels.factuurvolgnummer IN (SELECT volgnummer FROM facturen WHERE YEAR(datum) = ?)',
				'boekmaand'   => 'factuurregels.factuurvolgnummer IN (SELECT volgnummer FROM facturen WHERE MONTH(datum) = ?)');

			$this->genericTableAction($selectQuery, 'overzicht/totaal_per_artikelcode.tpl', $searchDef);
		}


		/**
		 * Invalid action, return to main page 
		 */ 
		public function noRouteAction()
		{
			$this->redirect('/');
		}

	}
