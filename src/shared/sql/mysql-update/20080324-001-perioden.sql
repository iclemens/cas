DROP TABLE IF EXISTS perioden;

CREATE TABLE perioden (
  periodiekeregel      mediumint(7) NOT NULL,

  maand                smallint(2)  default NULL,

  PRIMARY KEY(periodiekeregel, maand),
  FOREIGN KEY(periodiekeregel) REFERENCES periodiekeregels(volgnummer) ON DELETE RESTRICT
) DEFAULT CHARSET=utf8;
