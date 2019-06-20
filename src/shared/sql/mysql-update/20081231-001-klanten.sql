-- Repareren foreign key in klanten tabel.

ALTER TABLE klanten MODIFY emailtemplate mediumint(7) NOT NULL;
