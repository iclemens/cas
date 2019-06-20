
CREATE TABLE emailtemplates (
	volgnummer mediumint(7) NOT NULL auto_increment,

	omschrijving varchar(75)	 NOT NULL,
	onderwerp varchar(255) NOT NULL,
	inhoud text,

	PRIMARY KEY(volgnummer)
) DEFAULT CHARSET=utf8;

INSERT INTO emailtemplates VALUES (
	0,
	"Nieuwe factuur (Nederlands)",
	"Nieuwe factuur van {$company_name}",
	"Geachte klant,\n\nEr is een nieuwe factuur voor u aangemaakt met kenmerk {factuurnummer factuur=$factuur}.\n\nU vindt uw factuur als bijlage bij dit bericht. Mocht u nog vragen of\nopmerkingen hebben over deze factuur, dan kunt u een e-mailbericht\nsturen aan {$company_email} of bellen met {$company_telephone}.\n\nHet is raadzaam om voor uw eigen administratie de factuur te printen.\n\nMet vriendelijke groet,\n{$company_name}"
);

INSERT INTO emailtemplates VALUES(
	0,
	"Nieuwe factuur (English)",
	"Invoice {factuurnummer factuur=$factuur} from {$company_name}",
	"Dear customer,\n\nA new invoice has been created for you with invoice number {factuurnummer factuur=$factuur}.\nYou can find the invoice as an attachment to this email message. If you have any questions about this invoice, please contact us by email at {$company_email} or call us at {$company_telephone}.\n\nWe advise you to print the invoice for your own administration.\n\nKind regards,\n{$company_name}"
);
