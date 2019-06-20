<?php
	/**
   * Valideert een object en construeert het CT_Validation_Errors object
   *
   * @author     Ivar Clemens <post@ivarclemens.nl>
   * @copyright  2007 Ivar Clemens
   * @package    boekhouding
   */

	require_once "IBAN.php";

	/**
	 * Validates an object and constructs the CT_Validation_Errors object
	 */
	abstract class CT_Validation_Validator
	{
		abstract public function supports($class); 

		public function validate(&$object, &$errors)
		{
			if(!$this->supports($object))
				throw new Exception("Validator does not support " . get_class($object));
		}

		protected function rejectOnBlank(&$error, $field, $value)
		{
			if(self::isBlank($value))
				$error->rejectValue($field, 'Waarde mag niet leeg zijn');
		}

		/**
	 	 * True if the string is empty or only contains blanks, false otherwise
		 * @param string $value
		 * @return boolean
		 */
		public static function isBlank($value) 
		{
			for($i = 0; $i < strlen($value); $i++) {
				if(!in_array(substr($value, $i, 1), array("\t", "\n", "\r", ' ')))
					return false;
			}

			return true;
		}

		/**
	 	 * True if the string LOOKS like a dutch postalcode, false otherwise
		 * @param string $value
		 * @return boolean
		 */
		public static function isPostcode($value)
		{
			// NOTE: Various letter combinations (such as SS) are invalid!
			return (preg_match("/^[1-9][0-9][0-9][0-9] [A-Z][A-Z]$/", $value) == 1);
		}

		public static function isIBAN($iban)
		{
			$ibanValidator = new Validate_Finance_IBAN($iban);
			
			if($ibanValidator->validate() == true)
				return true;
			return false;
		}

		public static function isBIC($bic)
		{
			if(strlen($bic) == 8 || strlen($bic) == 11)
				return true;
			return false;
		}

		public static function getLengthOfArrayValue(&$source, $field)
		{
			return strlen(getValueFromArray($source, $field));
		}

		/**
	 	 * True if the string is an email address, false otherwise
		 *
		 * Licensed under a Creative Commons Attribution-ShareAlike 2.5 License
		 *
		 * @author Cal Henderson
		 * @param string $email
		 * @param boolean $multiple   Allow comma separated list of addresses
		 * @return boolean
		 */
		public static function isEmail($email, $multiple = false)
		{
			$no_ws_ctl    = "[\\x01-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]";
			$alpha        = "[\\x41-\\x5a\\x61-\\x7a]";
			$digit        = "[\\x30-\\x39]";
			$cr        = "\\x0d";
			$lf        = "\\x0a";
			$crlf        = "($cr$lf)";

			$obs_char    = "[\\x00-\\x09\\x0b\\x0c\\x0e-\\x7f]";
			$obs_text    = "($lf*$cr*($obs_char$lf*$cr*)*)";
			$text        = "([\\x01-\\x09\\x0b\\x0c\\x0e-\\x7f]|$obs_text)";
			$obs_qp        = "(\\x5c[\\x00-\\x7f])";
			$quoted_pair    = "(\\x5c$text|$obs_qp)";

			$wsp        = "[\\x20\\x09]";
			$obs_fws    = "($wsp+($crlf$wsp+)*)";
			$fws        = "((($wsp*$crlf)?$wsp+)|$obs_fws)";
			$ctext        = "($no_ws_ctl|[\\x21-\\x27\\x2A-\\x5b\\x5d-\\x7e])";
			$ccontent    = "($ctext|$quoted_pair)";
			$comment    = "(\\x28($fws?$ccontent)*$fws?\\x29)";
			$cfws        = "(($fws?$comment)*($fws?$comment|$fws))";
			$cfws        = "$fws*";

			$atext        = "($alpha|$digit|[\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2e\\x3d\\x3f\\x5e\\x5f\\x60\\x7b-\\x7e])";
			$atom        = "($cfws?$atext+$cfws?)";

			$qtext        = "($no_ws_ctl|[\\x21\\x23-\\x5b\\x5d-\\x7e])";
			$qcontent    = "($qtext|$quoted_pair)";
			$quoted_string    = "($cfws?\\x22($fws?$qcontent)*$fws?\\x22$cfws?)";
			$word        = "($atom|$quoted_string)";

			$obs_local_part    = "($word(\\x2e$word)*)";
			$obs_domain    = "($atom(\\x2e$atom)*)";

			$dot_atom_text    = "($atext+(\\x2e$atext+)*)";
			$dot_atom    = "($cfws?$dot_atom_text$cfws?)";

			$dtext        = "($no_ws_ctl|[\\x21-\\x5a\\x5e-\\x7e])";
			$dcontent    = "($dtext|$quoted_pair)";
			$domain_literal    = "($cfws?\\x5b($fws?$dcontent)*$fws?\\x5d$cfws?)";

			$local_part    = "($dot_atom|$quoted_string|$obs_local_part)";
			$domain        = "($dot_atom|$domain_literal|$obs_domain)";
			$addr_spec    = "($local_part\\x40$domain)";

			// Remove comments from email address
			$done = 0;
			while(!$done) {
				$new = preg_replace("!$comment!", '', $email);

				if (strlen($new) == strlen($email)) {
					$done = 1;
				}
					$email = $new;
			}

			/**
			 * In case multiple email addresses are allowed, split the string at the comma and
			 * process each part individually.
			 */
			if($multiple) {
				$email_addresses = explode(',', $email);

				foreach($email_addresses as $email) {
					if(!self::isEmail(trim($email), false)) {
						return false;
					}
				}

				return true;
			} else {
				return preg_match("!^$addr_spec$!", $email) ? true : false;
			}
		}	

		public static function isURI($uri) 
		{
		}

		public static function isPhonenumber($number) 
		{
			return preg_match("!^0[0-9]+-[0-9]+$!", $number) ? true : false;
		}

		// Use "pecl install crack" to enable cracklib support...
		public static function isStrongPassword($password)
		{
			if(extension_loaded("crack")) {

			}
			return false;
		}
	}
?>
