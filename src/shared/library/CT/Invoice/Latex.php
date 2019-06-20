<?php
	/**
	 * Latex Invoices, Project CAS
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @package    CT_Invoice
	 */

	Zend_Loader::loadClass('CT_Invoice_Abstract');

	class CT_Invoice_Latex implements CT_Invoice_Abstract {

		/**
		 * Configuration options
		 * @var Zend_Config
		 */
		protected $_config;

		/**
		 * Defines TeX commands for use in the template
		 * @var array
		 */
		protected $_texCommands;

		/**
		 * Name of the template to use
		 * @var string
		 */
		protected $_template;

		/**
		 * Buffer containing invoice lines
		 * @var array
		 */
		protected $_lineBuffer = array();

		/**
		 * Escapes text for use in LaTex.
		 *
		 * @author Jeremy Cowgar <jeremy@cowgar.com>
		 * @source Text_Wiki
		 *
		 * @param string $txt Text to escape
		 *
		 * @return string Escaped string
		 */
		private function escapeLaTeX($txt) {

			$parts = explode('\\', $txt);

			$search = array('#', '$', '%', '^', '&', '_', '{', '}', '~', '...', '<', '>');
			$replace = array('\#', '\$', '\%', '\^', '\&', '\_', '\{', '\}', '$\sim$', '\ldots', '$<$', '$>$');

			$escapedParts = array();

			foreach($parts as $part)
				$escapedParts[] = str_replace($search, $replace, $part);

			return implode('$\backslash$', $escapedParts);
		}


		/**
		 * Initializes the templating system
		 */
		function __construct() {
			$this->_config = Zend_Registry::get('config');

			$this->_template = $this->_config->invoice->default_template;
			$this->_values = array();
		}

		/**
		 * Stores invoice lines for later processing
		 *
		 * @param integer $nr Number of the line
		 * @param array $regel Actual description of the line
		 */
		public function addInvoiceLine($nr, $regel) {
			$this->_lineBuffer[$nr] = $regel;
		}

		/**
		 * Creates the TeX commands to produce all invoice lines.
		 */
		private function buildInvoiceLinesTeX() {
			$lineCmd = '';
			$prev_nr = 0;

			foreach($this->_lineBuffer as $nr => $line) {

				// Check for discontinuities in the data,
				//  add an empty line if one is found.
				for($i = 0; $i < $nr - $prev_nr - 1; $i++)
					$lineCmd .= '\\\\' . "\n";

				$lineCmd .= '\AddInvoiceLine{' .
					$line['artikelcode'] . '}{' . 
					$this->escapeLaTeX($line['omschrijving']) . '}{';

				if($line['aantal'] != 0 || $line['prijs'] != 0) {
					$lineCmd .= str_replace('.', ',', $line['aantal']) . '}{';

					if($line['aantal'] != NULL)
						$lineCmd .= fmt_prijs(array('prijs' => $line['prijs'], 
							'eurosign' => '\EUR'), $this) . '}{';
					else
						$lineCmd .= '}{';

					$lineCmd .= fmt_prijs(array('prijs' => $line['totaal'],
						'eurosign' => '\EUR'), $this) . '}';
				} else {
					$lineCmd .= '}{}{}';
				}

				$lineCmd .= "\n";

				$prev_nr = $nr;
			}

			return $lineCmd;
		}

		public function setCustomerDetails($klant) {

			/**
			 * If a customer-specific invoice template is available, use that instead.
			 */
			if(strlen($klant['factuurtemplate']) > 0)  {
				$this->_template = $klant['factuurtemplate'];
			}

			$customerCmd = '\newcommand{\invCustomerInfo}{';

			if($klant['klanttype'] == 0)
				$customerCmd .= '\AddBusinessInfo';
			else
				$customerCmd .= '\AddPrivateInfo';

			$customerCmd .= '{' . $this->escapeLaTeX($klant['bedrijfsnaam']) . '}';
			$customerCmd .= '{' . $this->escapeLaTeX($klant['afdeling']) . '}';
			$customerCmd .= '{' . $this->escapeLaTeX($klant['aanhef']) . '}';
			$customerCmd .= '{' . $this->escapeLaTeX($klant['voornaam'] . ' ' .
														$klant['achternaam']) . '}';
			$customerCmd .= '{' . $this->escapeLaTeX($klant['factuuradres']) . '}';
			$customerCmd .= '{' . $this->escapeLaTeX($klant['factuuradres2']) . '}';
			$customerCmd .= '{' . $this->escapeLaTeX($klant['factuurpostcode']) . '}';
			$customerCmd .= '{' . $this->escapeLaTeX($klant['factuurplaats']) . '}';
			$customerCmd .= '{' . $this->escapeLaTeX($klant['factuurland']) . '}';

			$customerCmd .= "}\n";

			$this->_texCommands .= $customerCmd;

			$this->_texCommands .= '\newcommand{\invVATNumber}{' . $klant['btwnummer'] . "}\n";
			$this->_texCommands .= '\newcommand{\invVATValid}{' . $klant['btwgecontroleerd'] . "}\n";			
		}

		public function setInvoiceDetails($factuur) {
			$formatter = Zend_Registry::get("formatter");

			$invoiceDate = strtotime($factuur['datum']);
			$invoiceDay = intval(strftime('%d', $invoiceDate));
			$invoiceMonth = strftime('%m', $invoiceDate);
			$invoiceYear = strftime('%Y', $invoiceDate);

			$this->_texCommands .= '\newcommand{\invInvoiceDate}{' .
				'\AddDate{' . 
				$invoiceDay . '}{' . 
				$invoiceMonth . '}{' . 
				$invoiceYear . "}}\n";


			$deadlineDate = strtotime($factuur['uiterstedatum']);
			$deadlineDay = intval(strftime('%d', $deadlineDate));
			$deadlineMonth = strftime('%m', $deadlineDate);
			$deadlineYear = strftime('%Y', $deadlineDate);

			$this->_texCommands .= '\newcommand{\invPaymentDeadline}{' .
				'\AddDate{' .
				$deadlineDay . '}{' .
				$deadlineMonth . '}{' .
				$deadlineYear . "}}\n";

			$this->_texCommands .= '\newcommand{\invInvoiceRef}{' .
				$this->escapeLaTeX($formatter->getInvoiceRef($factuur)) . "}\n";

			// Note: This is a def because we're going to use \ifx later on
			if($factuur['incasso']) {
				$this->_texCommands .= '\def\invEncashment{1}' . "\n";
			} else {
				$this->_texCommands .= '\def\invEncashment{}' . "\n";
			}

			$this->_texCommands .= '\newcommand{\invVATPercentage}{' .
				$factuur['btw_percentage'] . "}\n";

			$kortingCmd = '';

			if($factuur['korting'] > 0) {
				$korting = $factuur['korting_bedrag'] / ($factuur['subtotaal'] + $factuur['korting_bedrag']) * 100;

				$kortingCmd .= '& & & Korting & ';
				$kortingCmd .= fmt_prijs(array('prijs' => $factuur['korting_bedrag'], 'eurosign' => '\EUR'), $stub);
				$kortingCmd .= '\\\\';
			}

			$this->_texCommands .= '\newcommand{\invKorting}{' .
				$kortingCmd . "}\n";

			$this->_texCommands .= '\newcommand{\invCustomerRef}{' .
				$formatter->getCustomerRef(
				array('klantnr' => $factuur['klantnummer'])) . "}\n";
		}

		public function setTotals($factuur) {
			$stub = NULL;

			$this->_texCommands .= '\newcommand{\invSubTotal}{' .
				fmt_prijs(array('prijs' => 
					$factuur['subtotaal'] + $factuur['korting_bedrag'], 
					'eurosign' => '\EUR'), $stub) . "}\n";

			$this->_texCommands .= '\newcommand{\invTotalVAT}{' .
				fmt_prijs(array('prijs' => $factuur['btw'], 
					'eurosign' => '\EUR'), $stub) . "}\n";

			$this->_texCommands .= '\newcommand{\invTotal}{' .
				fmt_prijs(array('prijs' => $factuur['totaal'], 
					'eurosign' => '\EUR'), $stub) . "}\n";
		}

		public function getPDF() {
			global $dataPath;

			$config = Zend_Registry::get('config');

			// Specify the PDF to use for a template
			$this->_texCommands .= '\newcommand{\invTemplatePDF}{' .
				joinDirStringArray(array(
					$dataPath, $config->templates,
					'factuur', 'briefpapier.pdf')) . "}\n";

			// Add invoice line definitions
			$this->_texCommands .= '\newcommand{\invLines}{' .
				$this->buildInvoiceLinesTeX() . "}\n";

			$tempDir = createTemporaryDirectory('tmp_cas_', '');

			if($tempDir == false) 
				throw new Exception('De PDF generator kan geen tijdelijke directory maken!');

			try {
				$output_file = $tempDir . '/invoice.tex';
				$output_directory = $tempDir;
				$basename = 'invoice';

				$input_file = joinDirStringArray(array(
					$dataPath, $config->templates,
					'factuur', $this->_template));

				// Allow user to specify filename without .tex extension
				//	only if the one without the extension does not exist!

				if(!file_exists($input_file))
					$input_file .= '.tex';

				if(!file_exists($input_file)) {
					rmdir($tempDir);
					throw new Exception('Kan de factuur' .
						'template niet vinden');
				}

				$input = file_get_contents($input_file);

				$output = str_replace('##COMMANDS##', $this->_texCommands, $input);

				file_put_contents($output_file, $output);

				$response = array();

				exec(escapeshellcmd('pdflatex -halt-on-error -output-directory=' . $output_directory . ' -jobname=invoice invoice.tex'));

				if(file_exists($tempDir . '/invoice.pdf'))
					$pdf = file_get_contents($tempDir . '/invoice.pdf');
				else
					$log = file_get_contents($tempDir .'/invoice.log');

			} catch(exception $e) {
				$logger = Zend_Registry::get('logger');
				$logger->log('[CT_Invoice] Exception: ' . $e->getMessage(), Zend_Log::CRIT);
			}

			/* FIXME: We assume these are the only files left by pdflatex */
			unlink($tempDir . '/invoice.tex');

			if(file_exists($tempDir . '/invoice.aux'))
				unlink($tempDir . '/invoice.aux');

			if(file_exists($tempDir . '/invoice.log'))
				unlink($tempDir . '/invoice.log');

			if(file_exists($tempDir . '/invoice.pdf'))
				unlink($tempDir . '/invoice.pdf');

			if(!rmdir($tempDir)) {
				$logger = Zend_Registry::get('logger');
				$logger->log('[CT_Invoice] Kan tijdelijke directory niet verwijderen: ' . 
					$tempDir, Zend_Log::WARN);
			}

			if(substr($pdf, 0, 4) != '%PDF')
				throw new Exception('PDFLaTeX kan de factuur ' .
					'niet genereren.' . nl2br($log));

			return $pdf;
		}
	}
