
CREATE TABLE incassos (
	volgnummer				mediumint(7) NOT NULL auto_increment,
	factuurvolgnummer		mediumint(7) NOT NULL default '0',
	datum						date default NULL,
	PRIMARY KEY(volgnummer),
	FOREIGN KEY(factuurvolgnummer) REFERENCES facturen(volgnummer) ON DELETE CASCADE
) DEFAULT CHARSET=utf8;
