-- Installeren tabel met versie informatie (voor automatische updates)

CREATE TABLE IF NOT EXISTS versie (
        versie                  varchar(14) NOT NULL
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

INSERT INTO versie VALUES ('20081230-001');

