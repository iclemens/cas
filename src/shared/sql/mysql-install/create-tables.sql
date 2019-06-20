--
-- Citrus-IT Administratie Systeem
-- Database creation script for MySQL 5
-- 
-- Database: boekhouding
-- 

-- CREATE DATABASE IF NOT EXISTS boekhouding DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
-- GRANT ALL ON boekhouding.* TO 'projectcas'@'localhost' IDENTIFIED BY 'projectcas';
-- GRANT ALL ON boekhouding.* TO 'projectcas'@'%' IDENTIFIED BY 'projectcas';
-- USE boekhouding;

-- set ts=2 for best results!

-- DROP TABLE IF EXISTS artikelcodes;
-- DROP TABLE IF EXISTS betalingen;
-- DROP TABLE IF EXISTS facturen;
-- DROP TABLE IF EXISTS factureren;
-- DROP TABLE IF EXISTS factuurregels;
-- DROP TABLE IF EXISTS gebruikers;
-- DROP TABLE IF EXISTS klanten;
-- DROP TABLE IF EXISTS periodiekeregels;
-- DROP TABLE IF EXISTS perioden;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS versie (
  versie            varchar(14) NOT NULL
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS gebruikers (
  volgnummer        tinyint(2)  NOT NULL auto_increment,
  gebruikersnaam    varchar(30) NOT NULL default '',
  wachtwoord        varchar(72) NOT NULL default '',
  actief            tinyint(1)  NOT NULL default '0',
  `type`            tinyint(1)  NOT NULL default '3',
  PRIMARY KEY(volgnummer)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------

--CREATE TABLE IF NOT EXISTS emailtemplates (
--  volgnummer           mediumint(7) NOT NULL auto_increment,

--  taal                 varchar(3)   NOT NULL,
--  `type`               tinyint(1)   NOT NULL default '0',   

--  omschrijving         varchar(75)  NOT NULL,
--  onderwerp            varchar(255) NOT NULL,
--  inhoud               text,

--  PRIMARY KEY(volgnummer)
--) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

CREATE TABLE emailtemplates (
	volgnummer mediumint(7) NOT NULL auto_increment,

	omschrijving varchar(75)	 NOT NULL,
	onderwerp varchar(255) NOT NULL,
	inhoud text,

	PRIMARY KEY(volgnummer)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS artikelcodes (
  artikelcode      varchar(5) NOT NULL,
  omschrijving     varchar(60) NOT NULL,
  PRIMARY KEY(artikelcode)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------
-- Klanten/Uitgaande facturen
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS klanten (
  klantnummer            int(6) NOT NULL,
  bedrijfsnaam           varchar(60) default NULL,
  afdeling               varchar(60) default NULL,
  aanhef                 varchar(10) NOT NULL default '',
  voornaam               varchar(30) NOT NULL default '',
  achternaam             varchar(30) NOT NULL default '',

  factuuradres           varchar(60) NOT NULL default '',
  factuuradres2          varchar(60) default NULL,
  factuurpostcode        varchar(10) NOT NULL default '',
  factuurplaats          varchar(60) NOT NULL default '',
  factuurland            varchar(60) NOT NULL default 'Nederland',
  factuuremail           varchar(60) NOT NULL,  
  factuurtemplate        varchar(75) default NULL,

  emailtemplate          mediumint(7) NOT NULL,

  btwnummer              varchar(14) default NULL,
  btwgecontroleerd       boolean     NOT NULL default false,
  btwcategorie           varchar(14) default 'hoog',

  machtigingmaand        boolean     NOT NULL default false,
  machtigingjaar         boolean     NOT NULL default false,

  bezoekadres            varchar(60) NOT NULL default '',
  bezoekadres2           varchar(60) default NULL,
  bezoekpostcode         varchar(10) NOT NULL default '',
  bezoekplaats           varchar(60) NOT NULL default '',
  bezoekland             varchar(60) NOT NULL default 'Nederland',

  actief                 tinyint(1)  NOT NULL default '0',
  klanttype              tinyint(1)  NOT NULL default '0',
  emailadres             varchar(60) default NULL,
  website                varchar(60) default NULL,
  telefoonvast           varchar(11) default NULL,
  telefoonmobiel         varchar(11) default NULL,
  
  opmerkingen            text default '',
  
  PRIMARY KEY(klantnummer),
  INDEX (emailtemplate),
  FOREIGN KEY(emailtemplate) REFERENCES emailtemplates(volgnummer) ON DELETE RESTRICT
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS periodiekeregels (
  volgnummer            mediumint(7) NOT NULL auto_increment,
  klantnummer           int(6)       NOT NULL,

  artikelcode           varchar(5)   NOT NULL,
  omschrijving          text         NOT NULL,
  aantal                float        default NULL,
  prijs                 mediumint(8) default NULL,

  btw_percentage        mediumint(8) default NULL,

  laatstgefactureerd     DATE default NULL,

  PRIMARY KEY(volgnummer),
  INDEX(klantnummer),
  INDEX(artikelcode),
  FOREIGN KEY(klantnummer) REFERENCES klanten(klantnummer) ON DELETE RESTRICT,
  FOREIGN KEY(artikelcode) REFERENCES artikelcodes(artikelcode) ON DELETE RESTRICT
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS perioden (
  periodiekeregel      mediumint(7) NOT NULL,
  maand                smallint(2)  default NULL,

  PRIMARY KEY(periodiekeregel, maand),
  INDEX(periodiekeregel),
  FOREIGN KEY(periodiekeregel) REFERENCES periodiekeregels(volgnummer) ON DELETE RESTRICT
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS facturen (
  volgnummer          mediumint(7) NOT NULL auto_increment,
  factuurnummer       smallint(3)  NOT NULL default '0',
  klantnummer         int(6)       NOT NULL default '0',
  datum               date         NOT NULL default '0000-00-00',
  uiterstedatum       date         NOT NULL default '0000-00-00',
  korting             mediumint(6) NOT NULL default '0',
  btw_percentage      float        NOT NULL,
  incasso             boolean      NOT NULL default false,
  tekst               text,
  PRIMARY KEY(volgnummer),
  INDEX(klantnummer),
  FOREIGN KEY(klantnummer) REFERENCES klanten(klantnummer) ON DELETE RESTRICT
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS factuurregels (
  volgnummer            mediumint(7) NOT NULL auto_increment,
  factuurvolgnummer     mediumint(7) NOT NULL default '0',
  factuurregel          smallint(2)  NOT NULL default '0',
  artikelcode           varchar(5)   NOT NULL,
  omschrijving          text         NOT NULL,
  aantal                float        default NULL,
  prijs                 mediumint(8) default NULL,
  PRIMARY KEY(volgnummer),
  INDEX(factuurvolgnummer),
  INDEX(artikelcode),
  FOREIGN KEY(factuurvolgnummer) REFERENCES facturen(volgnummer) ON DELETE CASCADE,
  FOREIGN KEY(artikelcode) REFERENCES artikelcodes(artikelcode) ON DELETE RESTRICT
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS betalingen (
  volgnummer           mediumint(7) NOT NULL auto_increment,
  factuurvolgnummer    mediumint(7) NOT NULL default '0',
  datum                date         default NULL,
  PRIMARY KEY(volgnummer),
  INDEX(factuurvolgnummer),
  FOREIGN KEY(factuurvolgnummer) REFERENCES facturen(volgnummer) ON DELETE CASCADE
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS incassos (
  volgnummer                      mediumint(7) NOT NULL auto_increment,
  factuurvolgnummer               mediumint(7) NOT NULL default '0',
  datum                           date default NULL,
  PRIMARY KEY(volgnummer),
  INDEX(factuurvolgnummer),
  FOREIGN KEY(factuurvolgnummer) REFERENCES facturen(volgnummer) ON DELETE CASCADE
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS factureren (
  volgnummer           mediumint(7) NOT NULL auto_increment,
  klantnummer          int(6)       NOT NULL,

  artikelcode          varchar(5)   NOT NULL,
  omschrijving         varchar(75)  NOT NULL,
  aantal               float        default NULL,
  prijs                mediumint(8) default NULL,
  PRIMARY KEY(volgnummer),
  INDEX(klantnummer),
  INDEX(artikelcode),
  FOREIGN KEY(klantnummer) REFERENCES klanten(klantnummer) ON DELETE RESTRICT,
  FOREIGN KEY(artikelcode) REFERENCES artikelcodes(artikelcode) ON DELETE RESTRICT
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

--

INSERT INTO versie VALUES('20090826-001');

INSERT INTO gebruikers VALUES(
  0, 'root', '7b24afc8bc80e548d66c4e7ff72171c5435b41068e8665513a20070c033b08b9c66e4332', 1, 1);

INSERT INTO emailtemplates VALUES (
  0,
  "Nieuwe factuur (Nederlands)",
  "Factuur {factuurnummer factuur=$factuur} van {$company_name}",
  "Geachte klant,\n\nEr is een nieuwe factuur voor u aangemaakt met kenmerk {factuurnummer factuur=$factuur}.\n\nU vindt uw factuur als bijlage bij dit bericht. Mocht u nog vragen of\nopmerkingen hebben over deze factuur, dan kunt u een e-mailbericht\nsturen aan {$company_email} of bellen met {$company_telephone}.\n\nHet is raadzaam om voor uw eigen administratie de factuur te printen.\n\nMet vriendelijke groet,\n{$company_name}"
);

INSERT INTO emailtemplates VALUES(
  0,
  "Nieuwe factuur (English)",
  "Invoice {factuurnummer factuur=$factuur} from {$company_name}",
  "Dear customer,\n\nA new invoice has been created for you with invoice number {factuurnummer factuur=$factuur}.\nYou can find the invoice as an attachment to this email message. If you have any questions about this invoice, please contact us by email at {$company_email} or call us at {$company_telephone}.\n\nWe advise you to print the invoice for your own administration.\n\nKind regards,\n{$company_name}"
);
