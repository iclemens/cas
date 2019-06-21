<?php
	/**
	 * Prepares the name of a field for use as a label.
	 *
	 * @param string $name Name of the field
	 * @return string Label
	 */
	function fieldNameToLabel($name)
	{
		$label = strtoupper(substr($name, 0, 1)) . strtolower(substr($name, 1));
		return str_replace('_', ' ', $label); 
	}
	
	
	/**
	 * Returns the HTML code for a form field (e.g. textbox/list)
	 *
	 * @param string $name Name of the control
	 * @param array $options Control preferences
	 * @return string HTML Code
	 */
	function formField($name, $options) {		
		if(array_key_exists('value', $options))
			$value = $options['value'];
		elseif(array_key_exists('environment', $options) && getenv($options['environment']))
			$value = getenv($options['environment']);
		elseif(array_key_exists('default', $options))
			$value = $options['default'];
		else
			$value = '';
				
		if($options['type'] == 'list')
			return html_dropdown($name, $options['options'], $value);
		if($options['type'] == 'password')
			return '<input type="password" name="' . $name . '" value="' . $value . '" />';			
		if($options['type'] == 'text')
			return '<input type="text" name="' . $name . '" value="' . $value . '" />';
	}

	
	/**
	 * Makes sure the input from e.g. $_POST conforms to the
	 * guidelines as specified in the configuration template.
	 *
	 * @param array $source Datasource (e.g. $_POST)
	 * @param array $template Configuration template
	 * @return array Conforming configuration
	 */
	function normalizeFormInput($source, $template)
	{
		/** Remove POST variables from previous step */
		unset($source['globalOpt']);
		unset($source['localOpt']);
		unset($source['globalPath']);
		unset($source['localPath']);
		unset($source['setup_step']);
		
		$formData = array();

		foreach($template as $block => $fieldData) {
			foreach($fieldData as $field => $options) {
				if(!array_key_exists($block, $formData))
					$formData[$block] = array();

				if(array_key_exists('default', $options))
					$formData[$block][$field] = $options['default']; 				
			}
		}

		foreach($source as $key => $value) {
			$pos = strpos($key, '+');
			
			if($pos === false) {
				$block = '';
				$field = $key;
			} else {
				$block = substr($key, 0, $pos);
				$field = substr($key, $pos + 1);
			}
					
			if(!array_key_exists($block, $template))
				throw new Exception('Unknown block: ' . $block);
				
			$blockData = $template[$block];
			
			if(!array_key_exists($field, $blockData))
				throw new Exception("Unknown field: " . $field);
			
			$fieldData = $blockData[$field];
			
			if($fieldData['type'] == 'list') {
				if(!array_key_exists($value, $fieldData['options']))
					throw new Exception('Field ' . $field . ' has an invalid value');
			}
			
			if(!array_key_exists($block, $formData))
				$formData[$block] = array();

			$formData[$block][$field] = $value; 
		}
			
		return $formData;
	}

	
	/**
	 * Generates a configuration file from submitted form data.
	 *
	 * @param array $formData Form data, make sure it is normalized!
	 * @return string XML code
	 */
	function generateXML($formData)
	{
		$xml = '<?xml version="1.0"?>';
		$xml .= "\n<config>\n";
		$xml .= "\t<production>\n";
		
		foreach($formData as $block => $data) {
			if($block != '')
				$xml .= "\t<$block>\n";
			
			foreach($data as $field => $value) {
				if($block != '')
					$xml .= "\t";
				$xml .= "\t<$field>$value</$field>\n"; 
			}
			
			if($block != '')
				$xml .= "\t</$block>\n";			
		}
		
		$xml .= "\t</production>\n";
		$xml .= "</config>\n";
		
		return $xml;
	}

	
	/**
	 * Writes XML configuration file
	 *
	 * @throw Exception In case of problems
	 *
	 * @param String $xml The XML data to save
	 */
	function saveConfigXML($xml)
	{
		global $dataPath;
		
		$configFile = $dataPath . '/config/config.xml';
		
		if(file_exists($configFile))
			throw new Exception('Configuration file already exists.');

		try {
			$file = @fopen($configFile, 'w');
		} catch(Exception $e) {
			$file = false;
		}

		if(!$file)
			throw new Exception('Kan het configuratie bestand niet opslaan, zijn de rechten correct ingesteld? ' . 
				'Indien u de rechten niet kunt aanpassen, maak dan handmatig het bestand: <pre>' . $configFile . '</pre>' .
				'Het bestand moet de volgend inhoud hebben: <pre>' .
				htmlspecialchars($xml) . '</pre>');

		fputs($file, $xml);

		fclose($file);
	}


	/**
	 * Returns an array which describes the structure of the config.xml file. 
	 *
	 * @return array The configuration template
	 */
	function getConfigurationTemplate()
	{ 
		global $globalDataPath;
		global $dataPath;

		$ignoreList = array('Abstract', 'Factory');	
		
		$cssOptions = array('blue' => 'Default (blue)');
	
		$pdoOptions = array(
			'pdo_dblib' => 'Microsoft SQL Server', 
			'pdo_mysql' => 'MySQL', 
			'pdo_pgsql' => 'PostgreSQL',
			'pdo_sqlite' => 'SQLite');

		$texOptions = array_merge(
			getResourceList($globalDataPath . '/templates/factuur', array(), '.tex'),
			getResourceList($dataPath . '/templates/factuur', array(), '.tex'));
		
		$formattingOptions = array_merge(
			getResourceList($globalDataPath . '/library/CT/Formatting', $ignoreList),
			getResourceList($dataPath . '/library/CT/Formatting', $ignoreList));

		$invoiceOptions = array_merge(
			getResourceList($globalDataPath . '/library/CT/Invoice', $ignoreList),
			getResourceList($dataPath . '/library/CT/Invoice', $ignoreList));
	
		if($_SERVER['SERVER_PORT'] == 443)
			$url = 'https//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		elseif($_SERVER['SERVER_PORT'] == 80)
			$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		else
			$url = 'http://' . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
	
		$url = substr($url, 0, -strlen('/setup.php'));

		$rewriteBase = substr($_SERVER['REQUEST_URI'], 0, -strlen('/setup.php'));
		
		$blocks = array(
			"" => array(
				"web_root" => array("type" => "text", "default" => $url),
				"rewrite_base" => array("type" => "text", "default" => $rewriteBase),
				"temp_dir" => array("type" => "text", "default" => "tmp", "advanced" => true),
				"log_file" => array("type" => "text", "default" => "tmp/cas.log", "advanced" => true),
				"templates" => array("type" => "text", "default" => "templates", "advanced" => true),
				"templates_compile_dir" => array("type" => "text", "default" => "tmp", "advanced" => true)
			),
				
			"branding" => array(
				"application_name" => array("type" => "text", "default" => "Project CAS", "environment" => "APPLICATION_NAME"),
				"application_abbr" => array("type" => "text", "default" => "CAS", "environment" => "APPLICATION_ABBR"),
				"company_name" => array("type" => "text", "environment" => "COMPANY_NAME"),
				"company_email" => array("type" => "text", "environment" => "COMPANY_EMAIL"),
				"company_telephone" => array("type" => "text", "environment" => "COMPANY_TELEPHONE"),
				"default_stylesheet" => array("type" => "list", "options" => $cssOptions),
				"formatter" => array("type" => "list", "options" => $formattingOptions)
			),
			
			"database" => array(
				"type" => array("type" => "list", "options" => $pdoOptions, "default" => "pdo_mysql", "environment" => "DATABASE_TYPE"),
				"host" => array("type" => "text", "default" => "localhost", "environment" => "DATABASE_HOST"),
				"username" => array("type" => "text", "default" => "projectcas", "environment" => "DATABASE_USER"),
				"password" => array("type" => "password", "environment" => "DATABASE_PASSWORD"),
				"name" => array("type" => "text", "default" => "projectcas", "environment" => "DATABASE_NAME")
			),
			
			"mailer" => array(
				"method" => array("type" => "list", "options" => array("mail" => "mail", "smtp" => "smtp")),
				"server" => array("type" => "text"),
				"username" => array("type" => "text"),
				"password" => array("type" => "password"),
				"return_path" => array("type" => "text"),
				"from_address" => array("type" => "text"),
				"copy_to" => array("type" => "text")
			),
			
			/*"ideal" => array(
				"post_address" => array("type" => "text", "default" => "https://idealtest.rabobank.nl/ideal/mpiPayInitRabo.do"),
				"merchant_id" => array("type" => "text"),
				"key" => array("type" => "text")),
			"clieop" => array(
				"account" => array("type" => "text")),*/
			
			"invoice" => array(
				"location" => array("type" => "text", "default" => "facturen", "advanced" => true),
				"generator" => array("type" => "list", "options" => $invoiceOptions),
				"default_template" => array("type" => "list", "options" => $texOptions),
				"payment_due_delta" => array("type" => "text", "default" => "31", "advanced" => true)
			),
			
			"customer" => array(
				"first_business_id" => array("type" => "text", "default" => "60001", "advanced" => true),
				"first_private_id" => array("type" => "text", "default" => "65000", "advanced" => true)
			),
			
			"payment" => array(
				"options" => array("type"=> "list", "options" => array("Overboeking" => "Overboeking"), "default" => "Overboeking", "advanced" => true)
			)
		);
		
		return $blocks;
	}
