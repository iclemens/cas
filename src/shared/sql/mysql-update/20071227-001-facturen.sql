-- Opslaan BTW percentage bij elke factuur. Bestaande facturen worden op 19% gezet.

ALTER TABLE facturen ADD COLUMN btw_percentage FLOAT;
UPDATE facturen SET btw_percentage=19;
