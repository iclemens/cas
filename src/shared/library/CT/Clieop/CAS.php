<?php
	/**
	 * CT_Clieop_CAS, Project CAS
	 *
	 * @author		Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package		CT_Clieop
	 */

	Zend_Loader::loadClass('CT_Clieop');
	Zend_Loader::loadClass('CT_Clieop_Item');
	Zend_Loader::loadClass('CT_Clieop_Batch');

	/**
	 * Creates Cliep03 files specific to Project CAS
	 */
	class CT_Clieop_CAS
	{

		static function createFromDirectDebit($directDebits)
		{
			$config = Zend_Registry::get('config');
			$formatter = Zend_Registry::get('formatter');

			$clieop = new CT_Clieop();

			$clieop->header->fileCreationDate = intval(date('dmy'));
			$clieop->header->fileName = 'CLIEOP03';
			$clieop->header->senderIdentification = 'PRCAS';		// Verify?
			$clieop->header->fileIdentification = date('d') . '01';	// ASSUME 1 per day
			$clieop->header->duplicateCode = 1;

			$batch = new CT_Clieop_Batch();
			$batch->header->variantCode = 'B';
			$batch->header->transactionGroup = 10;					// Direct debit
			$batch->header->accountOrderingParty = $config->clieop->account;
			$batch->header->batchSequenceNumber = 1;			// Use 0?
			$batch->header->deliveryCurrency = 'EUR';
			$batch->header->batchIdentification = '';					// Varaint code B, no ident

			$batch->orderingParty->nameCode = '1';					// Niet gewenst, incasso
			$batch->orderingParty->processingDate = 0;			// Don't care
			$batch->orderingParty->nameOrderingParty = $config->branding->company_name;		// Overwritten by equens
			$batch->orderingParty->testCode = 'T';					// Set to P for production

			foreach($directDebits as $directDebit) {
				$item = new CT_Clieop_Item();

				$item->transaction->transactionType = 1001;		// Wat is een onzuivere incasso
				$item->transaction->amount = $directDebit['totaal'];
				$item->transaction->accountPayer = 0;				// Currently not known
				$item->transaction->accountBeneficiary = $config->clieop->account;

				$klant = $directDebit['klant'];

				if($klant['klanttype'] == 0)
					$item->addPayerName($klant['bedrijfsnaam']);
				else
					$item->addPayerName(trim($klant['voornaam'] . ' ' . $klant['achternaam']));

				$item->addPaymentReference($formatter->getInvoiceRef($directDebit));
				$item->addDescription('Factuur ' . $directDebit['datum']);

                $batch->addItem($item);
				$grantTotal = $grandTotal + $directDebit['totaal'];
				$itemCount = $itemCount++;
			}

			$batch->footer->totalAmount = $grandTotal;
			// Alle records met rekening nummers... nog tellen?
			$batch->footer->totalAccounts = $itemCount * 2;				
			$batch->footer->itemCount = $itemCount;

			$clieop->addBatch($batch);

			return $clieop;
		}
	}