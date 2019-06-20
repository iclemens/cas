<?php
	/**
	 * Upload systeem voor bestanden.
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_Upload
	 */

	require_once 'utility.php';

	/**
	 * Accepteert bestanden over HTTP en slaat ze tijdelijk op
	 * 
	 * @package CT_Upload
	 */
	class CT_Upload 
	{
		var $_basename;

		function CT_Upload($basename, $remove)
		{
			$this->_basename = $basename;

			$config = Zend_Registry::get('config');
			$user = Zend_Registry::get('user');

			/* Remove uploaded file if requested */
			if($remove || !file_exists($_SESSION[$basename . '_file'])) {
				if(file_exists($_SESSION[$basename . '_file']))
					unlink($_SESSION[$basename . '_file']);
				unset($_SESSION[$basename . '_file']);
				unset($_SESSION[$basename . '_name']);
			}

			/* Check for uploaded file and place it in temporary storage */
			if(!empty($_FILES[$basename])) {

				/* Replace the file if it exists, otherwise create new tempfile */
				if(isset($_SESSION[$basename . '_file'])) {
					move_uploaded_file($_FILES[$basename]['tmp_name'], $_SESSION[$basename . '_file']);
				} else {
					$uploaddir = join_dirs($config->app_root, $config->uploads);
					$filename = $user->getUsername() . '-' . rand() . '.dat';

					while(file_exists(join_dirs($uploaddir, $filename)))
						$filename = $user->getUsername() . '-' . rand() . '.dat';

					if(move_uploaded_file($_FILES[$basename]['tmp_name'], join_dirs($uploaddir, $filename))) {
						$_SESSION[$basename . '_file'] = join_dirs($uploaddir, $filename);
						$_SESSION[$basename . '_name'] = $_FILES[$basename]['name'];
					}
				}
			} 

		} /* CTOR */

		public function validate(&$errors)
		{
			if(!empty($_FILES[$this->_basename])) {
				switch($_FILES[$this->_basename]['error']) {
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$errors->rejectValue($this->_basename, "Het bestand is te groot.");
						break;
					case UPLOAD_ERR_NO_FILE:
						$errors->rejectValue($this->_basename, "Geen bestand verstuurd.");
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$errors->rejectValue($this->_basename, "Kan bestand niet opslaan.");
						break;
					case UPLOAD_ERR_CANT_WRITE:
						$errors->rejectValue($this->_basename, "Kan bestand niet schrijven.");
						break;
					case UPLOAD_ERR_EXTENSION:
						$errors->rejectValue($this->_basename, "Upload geblokkeerd door extensie.");
						break;
				}
			}
		}

		public function configureSmarty(&$smarty)
		{
			if(!empty($_SESSION[$basename . '_name']))
				$smarty->assign($basename . '_name', $_SESSION[$basename . '_name']);
		}
	}
			
