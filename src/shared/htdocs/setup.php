<?php
	require_once('setup/utility.php');
	require_once('setup/view.php');
	
	$currentStep = determineSetupPhase();
	$errorMessage = '';

	/**
	 * Select for which company to install CAS
	 */
	if($currentStep == 1) {

		require_once('setup/config.php');
		require_once('setup/version.php');

		if($_SERVER["REQUEST_METHOD"] == 'POST' && $_POST['setup_step'] == '1') {
			try {
				saveConfigFromArray($_POST);
				$currentStep = determineSetupPhase();
				$infoMessage = 'Stap 1 voltooid: config.php is opgeslagen.';
			} catch(Exception $e) {
				$errorMessage = $e->getMessage();
			}
		}

		if($currentStep == 1) {
			pageChooseInstall($errorMessage);			
		}		
	} 
	
	if($currentStep > 1) {
		require_once('config.php');
		require_once($globalDataPath . '/library/utility.php');		
	}
	
	/**
	 * Configure XML
	 */
	if($currentStep == 2) {
		require_once('setup/configxml.php');
		
		$errorMessage = '';		
		
		try {
			$template = getConfigurationTemplate();

			$fieldData = normalizeFormInput($_POST, $template);
		} catch(Exception $e) {
			$errorMessage = $e->getMessage();
		}

		if($_SERVER["REQUEST_METHOD"] == 'POST' && $errorMessage == '' && $_POST['setup_step'] == '2') {
			try {
				// TODO: Should do some validation on $formData!
			
				$xml = generateXML($fieldData);
				saveConfigXML($xml);
				$currentStep = determineSetupPhase();
				$infoMessage = 'Stap 2 voltooid: config.xml is opgeslagen.';			
			} catch(Exception $e) {
				$errorMessage = $e->getMessage();
			}
		}
		
		if($currentStep == 2) {
			pageConfigureXML($fieldData, $template, $errorMessage);
		}
	}

	/**
	 * Database creation
	 */
	if($currentStep == 3) {		
		Zend_Loader::loadClass('CT_Initialize');

		$config = Zend_Registry::get('config');
		$initialization = CT_Initialize::factory($config->database->type);

		if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['setup_step'] == '3') {
			try {
				$initialization->createDatabase($_POST);		
				$currentStep = determineSetupPhase();
			} catch(Exception $e) {
				$errorMessage = $e->getMessage();
			}
		}
		
		if($currentStep == 3) {
			$config = Zend_Registry::get('config');
			pageAdminPassword($config, $errorMessage);
		}
	}

	/**
	 * Table creation
	 */
	if($currentStep == 4) {
		Zend_Loader::loadClass('CT_Initialize');

		$config = Zend_Registry::get('config');	
		$initialization = CT_Initialize::factory($config->database->type);
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['setup_step'] == '4') {
			try {
				$initialization->createTables($_POST);
				$currentStep = determineSetupPhase();
			} catch(Exception $e) {
				$errorMessage = $e->getMessage();
			}
		}
		
		if($currentStep == 4) {
			$config = Zend_Registry::get('config');
			pageCreatorPassword($config, $errorMessage);
		}
	}

	if($currentStep == -1) {
		pagePartialSQL();
	}

	if($currentStep > 4) {
		pageDone();		
	}
