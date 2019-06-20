<?php
	/**
	 * Smarty uitbreiding voor KAS
 	 *
 	 * PHP version 5
 	 *
 	 * @author     Ivar Clemens <post@ivarclemens.nl>
 	 * @copyright  2007 Ivar Clemens
	 * @package    boekhouding
	 */

	require_once 'utility.php';

	if(!isset(Zend_Registry::get('config')->libraries) || Zend_Registry::get('config')->libraries->smarty == '') {
		$smartyLocation = getSmartyLocation();
	} else {
		$smartyLocation = Zend_Registry::get('config')->libraries->smarty;
	}

	@include_once joinDirStrings($smartyLocation, "Smarty.class.php");

	unset($smartyLocation);

	Zend_Loader::loadClass('CT_Smarty_Resource');

	if(!class_exists("Smarty")) {
		throw new Exception("Kan template engine niet initializeren");
	}

	class CT_Smarty extends Smarty {

		private $_formatter;

		function __construct($config, $formatter) 
		{
			global $dataPath, $globalDataPath;

			$this->_formatter = $formatter;

			parent::__construct();
			
			require_once $this->_get_plugin_filepath('function', 'eval');
			
			$this->register_resource('cas', array(
				'CT_Smarty_Resource', 
				'cas_get_template', 'cas_get_timestamp',
				'cas_get_secure', 'cas_get_trusted'));

			$this->default_resource_type = 'cas';

			$this->template_dir = joinDirStrings($globalDataPath, $config->get('templates', 'templates'));
			$this->compile_dir  = joinDirStrings($dataPath, $config->get('templates_compile_dir', 'tmp'));
		
			$this->assign('base', $config->web_root);

			$this->assign('company_name', 		$config->branding->get('company_name', 'Company'));
			$this->assign('company_email', 		$config->branding->get('company_email', 'info@ivarclemens.nl'));
			$this->assign('company_telephone', 	$config->branding->get('company_telephone', '####-######'));
			$this->assign('application_name', 	$config->branding->get('application_name', 'Project CAS'));
			$this->assign('application_abbr', 	$config->branding->get('application_abbr', 'CAS'));
			$this->assign('default_stylesheet',	$config->branding->get('default_stylesheet', 'blue'));

			$this->assign('use_icons', 0);

			// Custom smarty functions
			$this->register_function('html_global_error', array($this, 'html_global_error'));

			// Form related functions
			$this->register_function('html_textbox', array($this, 'html_textbox'));
			$this->register_function('html_file_upload', array($this, 'html_file_upload'));
			$this->register_function('html_error_for_field', array($this, 'html_error_for_field'));

			$this->register_function('klantnaam', 'fmt_klantnaam');

			$this->register_function('maand', array($this, 'maand_nummer_naar_tekst'));

			$this->register_function('factuurnummer', array($this, 'maak_factuurnummer'));
			$this->register_function('klantnummer', array($this, 'maak_klantnummer'));
			$this->register_function('prijs', 'fmt_prijs');

			$this->register_modifier('escapexml', array($this, 'escapeXML'));
		}

		/**
		 * Sets the current usertype
		 *
 		 * @param int $usertype The current user's type
		 */
		public function setUserType($usertype) 
		{
			switch($usertype) {
				case CT_User::Directie:
					$this->assign('user_type', 'Directie');
					break;
				case CT_User::Boekhouding:
					$this->assign('user_type', 'Boekhouding');
					break;
				case CT_User::Klant:
					$this->assign('user_type', 'Klant');
					break;
				default:
					$this->assign('user_type', '');
			}
		}

		/**
		 * Escapes a string for use in XML
		 *
		 * @param $string String to escape
		 * @return string Escaped string
		 */
		function escapeXML($string)
		{
			return str_replace('&#039;', '&apos;', htmlspecialchars($string, ENT_QUOTES, 'UTF-8'));
		}

		/**
		 * Evaluates smarty-tags in a given string
		 *
		 * @throws Exception An exception is thrown in case of syntax errors
		 *
		 * @param string $str String to evaluate
		 * @return string Evaluates string
		 */
		 function fetchFromString($str) {
		 	if($str == '')
				return '';

			try {
				return smarty_function_eval(array('var' => $str), $this);
			} catch(Exception $e) {
				throw new Exception('Kan smarty-tags niet verwerken.');
			}
		}

		function trigger_error($str, $level) {
			throw new Exception($str);
		}

		// Some common functions!

		function html_file_upload($params, &$smarty)
		{
			$field = $params['field'];
			$errors = $params['errors'];

			$filename = $smarty->get_template_vars($field . '_name');

			if($filename != '') {
				$out = 'Bestand: <b>' . $filename . '</b><br />';
				$out .= '<input type="submit" name="verwijder_' . $field . '" value="Verwijderen">';
				$out .= html_error_for_field(array("field" => $field, "errors" => $errors));
			} else {
				$out = '<input type="file" name="' . $field . '" size="40" />';
			}

			return out;
		}

		function html_textbox($params, &$smarty) 
		{
			$field = $params['field'];
			$value = $params['value'];
			$errors = $params['errors'];
	
			$out = "<input name=\"$field\" type=\"text\"";

			if(array_key_exists('size', $params))
				$out .= " size=\"" . $params['size'] . "\"";

			$out .= " value=\"";
			$out .= htmlentities($value, ENT_COMPAT, "UTF-8");
			$out .= "\" />";

			$out .= $this->html_error_for_field(array("field" => $field, "errors" => &$errors), $smarty);

			return $out;
		}

		function html_error_for_field($params, &$smarty)
		{
			$field = $params['field'];
			$errors = $params['errors'];
			$class = "error";

			if(isset($params['class']))
				$class = $params['class'];

			if(is_object($errors)) {
				if($errors->hasFieldErrors($field)) {
					$out .= "<div class=\"$class\">";
					$out .= htmlentities(array_pop(array_reverse($errors->getFieldErrors($field))), ENT_COMPAT, "UTF-8");
					$out .= "</div>";
				}
			}

			return $out;
		}

		function html_global_error($params, &$smarty)
		{
			$errors = $params['errors'];
			$class = "errorbox";

			if(array_key_exists('class', $params))
				$class = $params['class'];

			if(is_object($errors)) {
				if($errors->hasGlobalErrors()) {
					$out .= "<div class=\"$class\">";
					$out .= htmlentities(array_pop(array_reverse($errors->getGlobalErrors())), ENT_COMPAT, "UTF-8");
					$out .= "</div>";
				} elseif($errors->hasErrors()) {
					$out .= "<div class=\"$class\">";
					$out .= "Het formulier bevat een fout en is om die reden niet verwerkt.";
					$out .= "</div>";
				}
			}

			return $out;
		}

		function maand_nummer_naar_tekst($params, &$smarty)
		{
			if(!array_key_exists('type', $params)) {
				$params['type'] = 'full';
			}

			if(!array_key_exists('lang', $params)) {
				$params['lang'] = 'nl';
			}

			switch($params['type']) {
				case 'full':
					switch($params['lang']) {
						case 'nl':
							$maanden = array('januari', 'februari', 'maart', 'april', 'mei', 'juni', 
							 	'juli', 'augustus', 'september', 'oktober', 'november', 'december');
							break;
						case 'en':
							$maanden = array('January', 'February', 'March', 'April', 'May', 'June',
								'July', 'August', 'September', 'October', 'November', 'December');
							break;
					}
					break;

				case 'letter':
					$maanden = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
					break;
			}
			
			return($maanden[intval($params['nr']) - 1]);
		}

		function maak_factuurnummer($params, &$smarty)
		{
			return $this->_formatter->getInvoiceRef($params["factuur"]);
		}

		function maak_klantnummer($params, &$smarty)
		{
			return $this->_formatter->getCustomerRef($params);
		}
	}
?>
