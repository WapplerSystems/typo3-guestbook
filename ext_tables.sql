#
# Table structure for table 'tx_wsguestbook_domain_model_entry'
#
CREATE TABLE tx_wsguestbook_domain_model_entry
(

	name        varchar(255)        DEFAULT ''  NOT NULL,
	city        varchar(255)        DEFAULT ''  NOT NULL,
	email       varchar(255)        DEFAULT ''  NOT NULL,
	website     varchar(255)        DEFAULT ''  NOT NULL,
	remote_addr varchar(255)        DEFAULT ''  NOT NULL,
	message     text                            NOT NULL,
	action_key  varchar(255)        DEFAULT ''  NOT NULL

);
