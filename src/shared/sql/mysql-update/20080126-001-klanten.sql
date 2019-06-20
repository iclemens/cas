-- Toevoegen velden voor afdeling, land en btwnummer.

ALTER TABLE klanten ADD COLUMN afdeling varchar(60) default NULL;

ALTER TABLE klanten ADD COLUMN factuuradres2 varchar(60) NOT NULL default '';
ALTER TABLE klanten ADD COLUMN factuurland varchar(60) NOT NULL default 'Nederland';

ALTER TABLE klanten ADD COLUMN bezoekadres2 varchar(60) NOT NULL default '';
ALTER TABLE klanten ADD COLUMN bezoekland varchar(60) NOT NULL default 'Nederland';

ALTER TABLE klanten ADD COLUMN btwnummer varchar(14) NOT NULL default '';
ALTER TABLE klanten ADD COLUMN btwgecontroleerd boolean NOT NULL default false;
