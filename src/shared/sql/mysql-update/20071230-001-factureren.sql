-- Verhogen maximale prijs en toestaan gebroken aantallen. 

ALTER TABLE factureren MODIFY aantal float DEFAULT NULL;
ALTER TABLE factureren MODIFY prijs mediumint(8) DEFAULT NULL;