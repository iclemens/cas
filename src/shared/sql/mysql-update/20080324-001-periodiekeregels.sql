DROP TABLE IF EXISTS periodiekeregels;

CREATE TABLE periodiekeregels (
  volgnummer            mediumint(7) NOT NULL auto_increment,
  klantnummer           int(6)       NOT NULL,

  artikelcode           varchar(5)   NOT NULL,
  omschrijving          text         NOT NULL,
  aantal                float        default NULL,
  prijs                 mediumint(8) default NULL,

  btw_percentage        mediumint(8) default NULL,

  laatstgefactureerd     DATE default NULL,

  PRIMARY KEY(volgnummer),
  FOREIGN KEY(klantnummer) REFERENCES klanten(klantnummer) ON DELETE RESTRICT,
  FOREIGN KEY(artikelcode) REFERENCES artikelcodes(artikelcode) ON DELETE RESTRICT
) DEFAULT CHARSET=utf8;
