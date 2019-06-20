<?php
	/**
	 * Handles caching, concatenating and generating invoices.
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @copyright  2008 Ivar Clemens
	 * @package    CT_Invoice
	 */

	Zend_Loader::loadClass('CT_Db_Facturen');
	Zend_Loader::loadClass('CT_Db_Klanten');
	Zend_Loader::loadClass('CT_Invoice_Factory');

	/**
	 * Handles caching, concatenating and generating invoices.
	 */
	class CT_Invoice {

		/**
		 * Generates a PDF from an array
		 *
		 * @param array $klant Customer data
		 * @param array $factuur Invoice
		 *
		 * @return string PDF representation of the invoice.
		 *
		 */
		static public function generatePDFFromArray($klant, $factuur)
		{
			$config = Zend_Registry::get('config');
			$invoiceBuilder = CT_Invoice_Factory::getInvoiceBuilder($config->invoice->generator);
			$factuur = CT_Db_Facturen::addCalculatedFields($factuur);

			$invoiceBuilder->setCustomerDetails($klant);
			$invoiceBuilder->setInvoiceDetails($factuur);
			$invoiceBuilder->setTotals($factuur);

			foreach($factuur['regels'] as $regel) {
				$invoiceBuilder->addInvoiceLine($regel['factuurregel'], $regel);
			}

			$pdf = $invoiceBuilder->getPDF();

			if(substr($pdf, 0, 4) != '%PDF')
				throw new Exception('De PDF kan niet worden gemaakt.');

			return $pdf;
		}

		/**
		 * Generates a PDF from the database
		 *
		 * @param int $factuurvolgnummer Number identifying the invoice.
		 *
		 * @return string PDF representation of the invoice.
		 */
		static public function generatePDFFromDB($factuurvolgnummer)
		{
			$tbl_facturen = new CT_Db_Facturen();
			$tbl_klanten  = new CT_Db_Klanten();
			$tbl_regels   = new CT_Db_Factuurregels();

			$config = Zend_Registry::get('config');
	
			/* Read data */
			$facturen = $tbl_facturen->find($factuurvolgnummer)->toArray();
			$factuur = $facturen[0];

			$klanten   = $tbl_klanten->find($factuur['klantnummer'])->toArray();
			$klant = $klanten[0];

			$where = $tbl_regels->getAdapter()->quoteInto('factuurvolgnummer = ?', $factuurvolgnummer);

			$factuur_regels = $tbl_regels->fetchAll($where)->toArray();
			$factuur['regels'] = $factuur_regels;

			return CT_Invoice::generatePDFFromArray($klant, $factuur);
		}

		/**
		 * Retreives the name (absolute path) of the invoice.
		 * If a file does not yet exist a PDF version is generated.
		 *
		 * @param int $factuurvolgnummer Number identifying the invoice.
		 *
		 * @return string Filename of the PDF (including absolute path.)
		 */
		static public function getPDFFilename($factuurvolgnummer)
		{
			$tbl_facturen = new CT_Db_Facturen();

			$config = Zend_Registry::get('config');

			$facturen = $tbl_facturen->find($factuurvolgnummer)->toArray();
			$factuur = $facturen[0];

			/* Build cache filename */
			$formatter = Zend_Registry::get('formatter');
			$filename = $formatter->getInvoiceRef($factuur) . '.pdf';

			$invoice_dir = getResourceLocation($config->invoice->location, false);

			$filename_full = joinDirStrings($invoice_dir, $filename);

			if(file_exists($filename_full))
				return $filename_full;

			$pdf = CT_Invoice::generatePDFFromDB($factuurvolgnummer);

			$file = @fopen($filename_full, 'w');

			if(!$file)
				throw new Exception('Kan PDF niet genereren, geen schrijfrechten in cache directory.');

			@fprintf($file, '%s', $pdf);
			@fclose($file);

			return $filename_full;
		}

		/**
		 * Retreives the invoice as PDF in a string.
		 *
		 * @param int $factuurvolgnummer Number identifying the invoice.
		 *
		 * @return string The PDF in string form.
		 */
		static public function retreivePDF($factuurvolgnummer)
		{
			$filename = CT_Invoice::getPDFFilename($factuurvolgnummer);
			$pdf = file_get_contents($filename);

			if(substr($pdf, 0, 4) != '%PDF')
				throw new Exception('De PDF kan niet worden opgehaald.');

			return $pdf;
		}

		/**
		 * Combines several invoices in one PDF and returns the location of the resulting file.
		 * NOTE: The user of this function is responsible for removing the file!
		 *
		 * @param array $factuurvolgnummers Array of numbers identifying invoices to combine.
		 *
		 * @return string The locations of the PDF on the server.
		 */
		static public function generateCombinedPDF($factuurvolgnummers)
		{
			$texCommands = '\documentclass[a4paper]{article}' . "\n";
			$texCommands .= '\usepackage{pdfpages}' . "\n";
			$texCommands .= '\begin{document}' . "\n";

			foreach($factuurvolgnummers as $factuurvolgnummer) {
				$texCommands .= '\includepdf[pages=-]{';
				$texCommands .= CT_Invoice::getPDFFilename($factuurvolgnummer);
				$texCommands .= '}' . "\n";
			}

			$texCommands .= '\end{document}' . "\n";

			$tempDir = createTemporaryDirectory('tmp_cas_', '');

			if($tempDir == false)
				throw new Exception('De PDF generator kan geen tijdelijke directory maken!');

			try {
				$texFile = $tempDir . '/invoices.tex';

				file_put_contents($texFile, $texCommands);
				exec(escapeshellcmd('pdflatex -output-directory=' . $tempDir . ' -jobname=invoices ' . $texFile));
				
				$filename = tempnam(getTemporaryDirectory(), 'cpd');
				
				copy($tempDir . '/invoices.pdf', $filename);
			} catch(exception $e) {
			}

			// Remove our temporary files, but do
			//  not bother the user if this fails.
			try {
				unlink($tempDir . '/invoices.tex');
				unlink($tempDir . '/invoices.aux');
				unlink($tempDir . '/invoices.log');
				unlink($tempDir . '/invoices.pdf');

				rmdir($tempDir);
			} catch(Exception $e) {
			}

			// Try reading the header of the file and
			//  throw an exception if it does not match
			//  the standard PDF header.
			try {
				$file = fopen($filename, 'r');
				$header = fread($file, 4);
				fclose($file);
			} catch(Exception $e) {
				$header = '';				
			}
			
			if($header != '%PDF') {
				unlink($filename);
				throw new Exception('De PDF kan niet worden gemaakt.');
			}

			return $filename;
		}
	}
