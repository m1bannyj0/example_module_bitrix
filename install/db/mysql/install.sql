CREATE TABLE z_mrbannyyo_ymarket (
	ID int not null auto_increment,
	TIMESTAMP_UNIX int(11) not null,
	PROFILE_ID int(11) NOT NULL,
	STEP int(11) NOT NULL,
	MAX_STEP int(11) NOT NULL,
	PARAMS text NOT NULL,
	FINAL char(1) not null default 'N',
	primary key (ID)
);